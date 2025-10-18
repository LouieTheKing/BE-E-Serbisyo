<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequestDocument;
use App\Models\UploadedDocumentRequirement;
use App\Models\CertificateLog;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use App\Mail\RequestDocumentStatusMail;
use App\Services\PdfGeneratorService;
use App\Traits\LogsActivity;

class RequestDocumentController extends Controller
{
    use LogsActivity;
    // 1. Create a new request document
    public function store(Request $request)
    {
        try {
            // Handle case where information is sent as a JSON string (e.g., in multipart/form-data)
            if ($request->has('information') && is_string($request->information)) {
                $data = $request->all();

                $decoded = json_decode($data['information'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $data['information'] = $decoded;
                } else {
                    return response()->json([
                        'error' => 'Invalid JSON format for information field',
                        'received' => $data['information'],
                    ], 400);
                }

                $validated = validator($data, [
                    'document' => 'required|exists:documents,id',
                    'information' => 'sometimes|array',
                    'requirements' => 'sometimes|array',
                    'requirements.*.requirement_id' => 'required|exists:document_requirements,id',
                    'requirements.*.file' => 'required|file|mimes:pdf|max:5120',
                ])->validate();
            } else {
                // Normal JSON request
                $validated = $request->validate([
                    'document' => 'required|exists:documents,id',
                    'information' => 'sometimes|array',
                    'requirements' => 'sometimes|array',
                    'requirements.*.requirement_id' => 'required|exists:document_requirements,id',
                    'requirements.*.file' => 'required|file|mimes:pdf|max:5120',
                ]);
            }

            // ✅ requestor is the logged-in user
            $requestorId = auth()->id();

            // Generate unique transaction ID
            do {
                $transactionId = 'TXN_DOC_' . str_pad(random_int(0, 9999999), 7, '0', STR_PAD_LEFT);
            } while (RequestDocument::where('transaction_id', $transactionId)->exists());

            // Step 1: Create the request document
            $requestDocument = RequestDocument::create([
                'transaction_id' => $transactionId,
                'requestor' => $requestorId,
                'document' => $validated['document'],
                'status' => 'pending',
                'information' => $validated['information'] ?? null,
            ]);

            $uploads = [];

            // Step 2: Save uploaded requirement files (if provided)
            if ($request->has('requirements')) {
                foreach ($request->requirements as $reqData) {
                    if (isset($reqData['file'])) {
                        $path = $reqData['file']->store('requirements', 'public');

                        $upload = UploadedDocumentRequirement::create([
                            'uploader' => $requestorId,
                            'document' => $requestDocument->document,
                            'requirement' => $reqData['requirement_id'],
                            'file_path' => $path,
                            'request_document_id' => $requestDocument->id
                        ]);

                        $uploads[] = $upload;
                    }
                }
            }

            // Load relationships for email
            $requestDocument->load(['account', 'documentDetails']);

            // Create initial certificate log
            CertificateLog::create([
                'document_request' => $requestDocument->id,
                'staff' => null, // No staff involved in initial request
                'remark' => 'Document request created by requestor'
            ]);

            // Send email notification to the requestor
            if ($requestDocument->account && $requestDocument->account->email) {
                Mail::to($requestDocument->account->email)
                    ->send(new RequestDocumentStatusMail($requestDocument));
            }

            // Log the activity
            $this->logActivity('Document Requests', "Created new document request with transaction ID: {$transactionId}");

            return response()->json([
                'message' => 'Request created successfully and email sent',
                'request_document' => $requestDocument,
                'uploaded_requirements' => $uploads,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }


    // 2. Change status of a request document
    public function changeStatus(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'status' => [
                    'required',
                    Rule::in(['pending', 'released', 'rejected', 'approved', 'processing', 'ready to pickup'])
                ],
                'remark' => 'nullable|string'
            ]);

            $requestDocument = RequestDocument::with(['account', 'documentDetails'])->findOrFail($id);
            $oldStatus = $requestDocument->status;
            $requestDocument->status = $validated['status'];
            $requestDocument->save();

            // Automatically create certificate log
            $staffId = auth()->check() ? auth()->id() : null;

            // Default remarks based on status if no remark is provided
            $defaultRemarks = [
                'pending' => 'Document request status changed to pending',
                'approved' => 'Document request has been approved',
                'processing' => 'Document is currently being processed',
                'ready to pickup' => 'Document is ready for pickup',
                'released' => 'Document has been released to requestor',
                'rejected' => 'Document request has been rejected'
            ];

            $remark = $validated['remark'] ?? $defaultRemarks[$validated['status']] ?? 'Status updated to ' . $validated['status'];

            CertificateLog::create([
                'document_request' => $requestDocument->id,
                'staff' => $staffId,
                'remark' => $remark
            ]);

            // Send email notification to the requestor
            if ($requestDocument->account && $requestDocument->account->email) {
                Mail::to($requestDocument->account->email)
                    ->send(new RequestDocumentStatusMail($requestDocument));
            }

            // Log the activity
            $this->logActivity('Document Requests', "Changed request status from '{$oldStatus}' to '{$validated['status']}' for transaction ID: {$requestDocument->transaction_id}");

            return response()->json([
                'message' => 'Status updated and email sent successfully',
                'request_document' => $requestDocument
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    // 3. Get all with filters, pagination, and sorting
    public function index(Request $request)
    {
        $query = RequestDocument::query();

        // Search (broad across transaction, status, account, document)
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('transaction_id', 'like', "%{$term}%")
                  ->orWhere('status', 'like', "%{$term}%")
                  ->orWhere('id', $term)
                  ->orWhereHas('account', function ($qa) use ($term) {
                      $qa->where('first_name', 'like', "%{$term}%")
                         ->orWhere('last_name', 'like', "%{$term}%")
                         ->orWhere('email', 'like', "%{$term}%");
                  })
                  ->orWhereHas('documentDetails', function ($qd) use ($term) {
                      $qd->where('document_name', 'like', "%{$term}%");
                  });
            });
        }

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('requestor')) {
            $query->where('requestor', $request->input('requestor'));
        }
        if ($request->has('document')) {
            $query->where('document', $request->input('document'));
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'created_at'); // default sorting
        $order = $request->query('order', 'desc');

        // Allowed sortable columns
        $allowedSorts = ['created_at', 'document', 'transaction_id', 'status'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $order);

        // Pagination
        $perPage = $request->input('per_page', 10);
        $results = $query->with(['account', 'documentDetails', 'uploadedRequirements.requirement'])->paginate($perPage);

        return response()->json($results);
    }


    // 4. Get by id
    public function show($id)
    {
        $requestDocument = RequestDocument::with(['account', 'documentDetails', 'uploadedRequirements.requirement'])->findOrFail($id);
        return response()->json($requestDocument);
    }

    // 5. Track document by transaction ID
    public function trackByTransactionId($transactionId)
    {
        try {
            $requestDocument = RequestDocument::where('transaction_id', $transactionId)
                ->with(['account', 'documentDetails', 'uploadedRequirements.requirement'])
                ->first();

            if (!$requestDocument) {
                return response()->json([
                    'message' => 'Document not found',
                    'error' => 'No document found with the provided transaction ID'
                ], 404);
            }

            // Get certificate logs for this request
            $certificateLogs = CertificateLog::where('document_request', $requestDocument->id)
                ->with(['staffAccount'])
                ->orderBy('created_at', 'desc')
                ->get();

            // Format the response with tracking information
            return response()->json([
                'transaction_id' => $requestDocument->transaction_id,
                'request_id' => $requestDocument->id,
                'status' => $requestDocument->status,
                'document_type' => $requestDocument->documentDetails->document_name ?? 'N/A',
                'requestor' => [
                    'name' => ($requestDocument->account->first_name ?? '') . ' ' . ($requestDocument->account->last_name ?? ''),
                    'email' => $requestDocument->account->email ?? 'N/A',
                ],
                'request_date' => $requestDocument->created_at->format('F d, Y h:i A'),
                'last_updated' => $requestDocument->updated_at->format('F d, Y h:i A'),
                'certificate_logs' => $certificateLogs->map(function($log) {
                    return [
                        'id' => $log->id,
                        'remark' => $log->remark,
                        'staff_name' => ($log->staffAccount->first_name ?? '') . ' ' . ($log->staffAccount->last_name ?? ''),
                        'staff_email' => $log->staffAccount->email ?? 'N/A',
                        'logged_at' => $log->created_at->format('F d, Y h:i A'),
                    ];
                }),
                'uploaded_requirements' => $requestDocument->uploadedRequirements->map(function ($upload) {
                    return [
                        'requirement_name' => $upload->requirement->requirement_name ?? 'N/A',
                        'file_url' => Storage::url($upload->file_path),
                        'uploaded_at' => $upload->created_at->format('F d, Y h:i A'),
                    ];
                }),
                'status_timeline' => [
                    'pending' => $requestDocument->status === 'pending',
                    'approved' => in_array($requestDocument->status, ['approved', 'processing', 'ready to pickup', 'released']),
                    'processing' => in_array($requestDocument->status, ['processing', 'ready to pickup', 'released']),
                    'ready_to_pickup' => in_array($requestDocument->status, ['ready to pickup', 'released']),
                    'released' => $requestDocument->status === 'released',
                    'rejected' => $requestDocument->status === 'rejected',
                ],
                'full_details' => $requestDocument,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error tracking document',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function uploadRequirement(Request $request, $requestDocumentId)
    {
        try {
            $validated = $request->validate([
                'requirement_id' => 'required|exists:document_requirements,id',
                'file' => 'required|file|mimes:pdf|max:5120', // PDF max 5MB
            ]);

            $requestDocument = RequestDocument::findOrFail($requestDocumentId);

            // Store file
            $path = $request->file('file')->store('requirements', 'public');

            // Save record
            $upload = UploadedDocumentRequirement::create([
                'uploader' => $requestDocument->requestor,
                'document' => $requestDocument->document,
                'requirement' => $validated['requirement_id'],
                'file_path' => $path,
            ]);

            // Log the activity
            $this->logActivity('Document Requirements', "Uploaded requirement file for transaction: {$requestDocument->transaction_id}");

            return response()->json([
                'message' => 'Requirement uploaded successfully',
                'upload' => $upload,
                'file_url' => Storage::url($path)
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    /**
     * Generate filled PDF document from template (with caching)
     */
    public function generateFilledDocument(Request $request, $id)
    {
        try {
            $requestDocument = RequestDocument::with(['documentDetails'])->findOrFail($id);
            $forceRegenerate = $request->input('force_regenerate', false);

            // Check if document is already generated and file still exists
            if (!$forceRegenerate &&
                $requestDocument->generated_document_path &&
                Storage::disk('public')->exists($requestDocument->generated_document_path)) {

                // Return cached document
                return response()->json([
                    'message' => 'Document retrieved from cache',
                    'file_path' => $requestDocument->generated_document_path,
                    'file_url' => Storage::url($requestDocument->generated_document_path),
                    'cached' => true
                ]);
            }

            // Check if information is provided
            if (empty($requestDocument->information)) {
                return response()->json([
                    'error' => 'No information data available for this request'
                ], 400);
            }

            // If forcing regeneration, delete old file
            if ($forceRegenerate && $requestDocument->generated_document_path) {
                Storage::disk('public')->delete($requestDocument->generated_document_path);
            }

            $pdfGenerator = new PdfGeneratorService();

            // Validate required fields
            $missingFields = $pdfGenerator->validateRequiredFields(
                $requestDocument->documentDetails,
                $requestDocument->information
            );

            if (!empty($missingFields)) {
                return response()->json([
                    'error' => 'Missing required fields',
                    'missing_fields' => $missingFields
                ], 400);
            }

            // Generate new document
            $filledDocumentPath = $pdfGenerator->generateFilledDocument($requestDocument);

            // Cache the file path in database
            $requestDocument->update(['generated_document_path' => $filledDocumentPath]);

            // Log the activity
            $this->logActivity('Document Processing', "Generated filled document for transaction: {$requestDocument->transaction_id}");

            return response()->json([
                'message' => 'Document generated successfully',
                'file_path' => $filledDocumentPath,
                'file_url' => Storage::url($filledDocumentPath),
                'cached' => false
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to generate document',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
