<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Validator;
use Exception;

class DocumentsController extends Controller
{
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
            $document->update($request->only(['document_name', 'description', 'status']));

            // Handle requirements update if provided
            if ($request->has('requirements')) {
                $reqIds = [];

                foreach ($request->requirements as $reqData) {
                    if (isset($reqData['id'])) {
                        // Update existing requirement
                        $requirement = $document->requirements()->find($reqData['id']);
                        if ($requirement) {
                            $requirement->update([
                                'name' => $reqData['name'],
                                'description' => $reqData['description'],
                            ]);
                            $reqIds[] = $requirement->id;
                        }
                    } else {
                        // Create new requirement
                        $newReq = $document->requirements()->create([
                            'name' => $reqData['name'],
                            'description' => $reqData['description'],
                        ]);
                        $reqIds[] = $newReq->id;
                    }
                }

                // Delete requirements not included anymore
                $document->requirements()->whereNotIn('id', $reqIds)->delete();
            }

            return response()->json([
                'message' => 'Document updated successfully',
                'document' => $document->load('requirements')
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
            $document->delete();
            return response()->json(['message' => 'Document deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
