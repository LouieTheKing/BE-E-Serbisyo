<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Exception;
use App\Services\PdfGeneratorService;
use App\Traits\LogsActivity;

class DocumentsController extends Controller
{
    use LogsActivity;
    // 1. List all documents with optional sorting
    public function index(Request $request)
    {
        try {
            $query = Document::query();

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Sorting
            $sortBy = $request->query('sort_by', 'created_at'); // default sort
            $order = $request->query('order', 'desc'); // default order

            $allowedSorts = ['document_name', 'status', 'created_at'];
            if (!in_array($sortBy, $allowedSorts)) {
                $sortBy = 'created_at';
            }

            $query->orderBy($sortBy, $order);

            $documents = $query->with('requirements')->get();

            return response()->json($documents);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. Show a single document
    public function show($id)
    {
        try {
            $document = Document::with('requirements')->find($id);
            if (!$document) {
                return response()->json(['error' => 'Document not found'], 404);
            }
            return response()->json($document);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 3. Create a new document
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'document_name' => 'required|string|unique:documents,document_name',
                'description' => 'required|string',
                'status' => 'in:active,inactive',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $document = Document::create([
                'document_name' => $request->document_name,
                'description' => $request->description,
                'status' => $request->status ?? 'active',
            ]);

            // Log the activity
            $this->logActivity('Document Management', "Created new document: {$document->document_name} (ID: {$document->id})");

            return response()->json(['message' => 'Document created successfully', 'document' => $document], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 4. Update a document
    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'document_name' => 'sometimes|required|string|unique:documents,document_name,' . $id,
                'description' => 'sometimes|required|string',
                'status' => 'sometimes|in:active,inactive',
                'template_path' => 'sometimes|nullable|string',
                'template_fields' => 'sometimes|nullable|array',
                'template_fields.*.name' => 'required_with:template_fields|string',
                'template_fields.*.label' => 'required_with:template_fields|string',
                'template_fields.*.type' => 'required_with:template_fields|in:text,number,date,textarea,email',
                'template_fields.*.required' => 'sometimes|boolean',
                'requirements' => 'sometimes|array',
                'requirements.*.id' => 'sometimes|exists:document_requirements,id',
                'requirements.*.name' => 'required_with:requirements|string',
                'requirements.*.description' => 'required_with:requirements|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $document = Document::with('requirements')->find($id);
            if (!$document) {
                return response()->json(['error' => 'Document not found'], 404);
            }

            // Update document info
            $document->update($request->only(['document_name', 'description', 'status', 'template_path', 'template_fields']));

            // Handle requirements replacement if provided
            if ($request->has('requirements')) {
                // Delete all existing requirements first
                $document->requirements()->delete();

                // Create all new requirements
                foreach ($request->requirements as $reqData) {
                    $document->requirements()->create([
                        'name' => $reqData['name'],
                        'description' => $reqData['description'],
                    ]);
                }
            }

            // Log the activity
            $this->logActivity('Document Management', "Updated document: {$document->document_name} (ID: {$document->id})");

            return response()->json([
                'message' => 'Document updated successfully',
                'document' => $document->fresh()->load('requirements')
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    // 5. Delete a document
    public function destroy($id)
    {
        try {
            $document = Document::find($id);
            if (!$document) {
                return response()->json(['error' => 'Document not found'], 404);
            }

            // Delete the template file if it exists
            if ($document->template_path && Storage::disk('public')->exists($document->template_path)) {
                Storage::disk('public')->delete($document->template_path);
            }

            // Log the activity before deletion
            $this->logActivity('Document Management', "Deleted document: {$document->document_name} (ID: {$document->id})");

            $document->delete();
            return response()->json(['message' => 'Document deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 6. Upload template for a document (PDF, DOCX, or HTML)
    public function uploadTemplate(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'template' => 'required|file|mimes:pdf,docx,doc,html|max:10240', // Max 10MB
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $document = Document::find($id);
            if (!$document) {
                return response()->json(['error' => 'Document not found'], 404);
            }

            // Delete old template if exists
            if ($document->template_path && Storage::disk('public')->exists($document->template_path)) {
                Storage::disk('public')->delete($document->template_path);
            }

            // Store the new template
            $file = $request->file('template');
            $extension = $file->getClientOriginalExtension();
            $filename = $document->id . '_' . time() . '.' . $extension;
            $path = $file->storeAs('document_templates', $filename, 'public');

            // Update the document with the template path
            $document->update(['template_path' => $path]);

            // Log the activity
            $this->logActivity('Document Management', "Uploaded template for document: {$document->document_name} (ID: {$document->id})");

            // Auto-extract placeholders
            try {
                $pdfGenerator = new PdfGeneratorService();
                $placeholders = $pdfGenerator->extractPlaceholdersFromTemplate($path);

                return response()->json([
                    'message' => 'Template uploaded successfully',
                    'template_path' => $path,
                    'template_type' => $extension,
                    'placeholders_found' => $placeholders,
                    'placeholders_count' => count($placeholders),
                    'document' => $document
                ]);
            } catch (\Exception $e) {
                // If extraction fails, still return success for upload
                return response()->json([
                    'message' => 'Template uploaded successfully',
                    'template_path' => $path,
                    'template_type' => $extension,
                    'document' => $document,
                    'note' => 'Could not auto-extract placeholders: ' . $e->getMessage()
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 7. Retrieve PDF template for a document
    public function getTemplate($id)
    {
        try {
            $document = Document::find($id);
            if (!$document) {
                return response()->json(['error' => 'Document not found'], 404);
            }

            if (!$document->template_path) {
                return response()->json(['error' => 'No template found for this document'], 404);
            }

            if (!Storage::disk('public')->exists($document->template_path)) {
                return response()->json(['error' => 'Template file not found'], 404);
            }

            $filePath = Storage::disk('public')->path($document->template_path);
            $fileName = $document->document_name . '_template.pdf';

            return response()->download($filePath, $fileName, [
                'Content-Type' => 'application/pdf',
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 8. Delete PDF template for a document
    public function deleteTemplate($id)
    {
        try {
            $document = Document::find($id);
            if (!$document) {
                return response()->json(['error' => 'Document not found'], 404);
            }

            if (!$document->template_path) {
                return response()->json(['error' => 'No template found for this document'], 404);
            }

            // Delete the template file
            if (Storage::disk('public')->exists($document->template_path)) {
                Storage::disk('public')->delete($document->template_path);
            }

            // Update the document to remove template path
            $document->update(['template_path' => null]);

            // Log the activity
            $this->logActivity('Document Management', "Deleted template for document: {$document->document_name} (ID: {$document->id})");

            return response()->json(['message' => 'Template deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 9. Extract placeholders from uploaded template
    public function extractPlaceholders($id)
    {
        try {
            $document = Document::find($id);
            if (!$document) {
                return response()->json(['error' => 'Document not found'], 404);
            }

            if (!$document->template_path) {
                return response()->json(['error' => 'No template found for this document'], 404);
            }

            $pdfGenerator = new PdfGeneratorService();
            $placeholders = $pdfGenerator->extractPlaceholdersFromTemplate($document->template_path);

            $filePath = Storage::disk('public')->path($document->template_path);
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            return response()->json([
                'message' => 'Placeholders extracted successfully',
                'template_path' => $document->template_path,
                'template_type' => $extension,
                'placeholders' => $placeholders,
                'count' => count($placeholders),
                'debug_info' => [
                    'file_exists' => Storage::disk('public')->exists($document->template_path),
                    'file_path' => $filePath,
                    'file_size' => file_exists($filePath) ? filesize($filePath) : 0,
                    'extension' => $extension
                ]
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Failed to extract placeholders',
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : 'Enable debug mode for trace'
            ], 500);
        }
    }
}
