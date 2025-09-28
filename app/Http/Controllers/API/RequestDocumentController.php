<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequestDocument;
use App\Models\UploadedDocumentRequirement;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class RequestDocumentController extends Controller
{
    // 1. Create a new request document
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'document' => 'required|exists:documents,id',
                'requirements' => 'sometimes|array',
                'requirements.*.requirement_id' => 'required|exists:document_requirements,id',
                'requirements.*.file' => 'required|file|mimes:pdf|max:5120',
            ]);

            // ✅ requestor is the logged-in user
            $requestorId = auth()->id();

            // Step 1: Create the request document
            $requestDocument = RequestDocument::create([
                'requestor' => $requestorId,
                'document' => $validated['document'],
                'status' => 'pending',
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
                        ]);

                        $uploads[] = $upload;
                    }
                }
            }

            return response()->json([
                'message' => 'Request created successfully',
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
                    Rule::in(['pending', 'released', 'rejected'])
                ]
            ]);

            $requestDocument = RequestDocument::findOrFail($id);
            $requestDocument->status = $validated['status'];
            $requestDocument->save();

            return response()->json($requestDocument);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    // 3. Get all with filters and pagination
    public function index(Request $request)
    {
        $query = RequestDocument::query();

        if ($request->has('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->has('requestor')) {
            $query->where('requestor', $request->input('requestor'));
        }
        if ($request->has('document')) {
            $query->where('document', $request->input('document'));
        }

        $perPage = $request->input('per_page', 10);
        $results = $query->with(['account', 'document', 'uploadedRequirements.requirement'])->paginate($perPage);

        return response()->json($results);
    }

    // 4. Get by id
    public function show($id)
    {
        $requestDocument = RequestDocument::with(['account', 'document', 'uploadedRequirements.requirement'])->findOrFail($id);
        return response()->json($requestDocument);
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

            return response()->json([
                'message' => 'Requirement uploaded successfully',
                'upload' => $upload,
                'file_url' => Storage::url($path)
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }
}
