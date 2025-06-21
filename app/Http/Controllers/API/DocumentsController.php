<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Validator;
use Exception;

class DocumentsController extends Controller
{
    // 1. List all documents
    public function index(Request $request)
    {
        try {
            $query = Document::query();

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $documents = $query->get();
            return response()->json($documents);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. Show a single document
    public function show($id)
    {
        try {
            $document = Document::find($id);
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
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $document = Document::find($id);
            if (!$document) {
                return response()->json(['error' => 'Document not found'], 404);
            }

            $document->update($request->only(['document_name', 'description', 'status']));

            return response()->json(['message' => 'Document updated successfully', 'document' => $document]);
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
