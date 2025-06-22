<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequestDocument;
use Illuminate\Validation\Rule;

class RequestDocumentController extends Controller
{
    // 1. Create a new request document
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'requestor' => 'required|exists:accounts,id',
                'document' => 'required|exists:documents,id',
            ]);

            $requestDocument = RequestDocument::create([
                'requestor' => $validated['requestor'],
                'document' => $validated['document'],
                'status' => 'pending',
            ]);

            return response()->json($requestDocument, 201);
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
        $results = $query->with(['account', 'document'])->paginate($perPage);

        return response()->json($results);
    }

    // 4. Get by id
    public function show($id)
    {
        $requestDocument = RequestDocument::with(['account', 'document'])->findOrFail($id);
        return response()->json($requestDocument);
    }
}
