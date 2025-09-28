<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Blotter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BlotterController extends Controller
{
    /**
     * List blotters with filters & pagination
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $status = $request->get('status');

        $query = Blotter::with('creator')->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($request->get('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('complainant_name', 'like', "%$search%")
                  ->orWhere('respondent_name', 'like', "%$search%")
                  ->orWhere('case_number', 'like', "%search%")
                  ->orWhere('complaint_details', 'like', "%$search%");
            });
        }

        return response()->json([
            'success' => true,
            'data' => $query->paginate($perPage)
        ]);
    }

    /**
     * Store blotter
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'complainant_name' => 'required|string',
            'respondent_name' => 'required|string',
            'additional_respondent' => 'nullable|array',
            'complaint_details' => 'required|string',
            'relief_sought' => 'required|string',
            'date_filed' => 'required|date',
            'status' => 'in:filed,ongoing,settled',
            'case_type' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $blotter = Blotter::create([
            'case_number' => strtoupper('BLT-' . date('Ymd') . '-' . Str::random(5)),
            'complainant_name' => $request->complainant_name,
            'respondent_name' => $request->respondent_name,
            'additional_respondent' => $request->additional_respondent,
            'complaint_details' => $request->complaint_details,
            'case_type' => $request->case_type,
            'relief_sought' => $request->relief_sought,
            'date_filed' => $request->date_filed,
            'received_by' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
            'created_by' => auth()->id(),
            'status' => $request->status ?? 'filed',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Blotter created successfully',
            'data' => $blotter
        ], 201);
    }

    /**
     * Show blotter by case number
     */
    public function show($case_number)
    {
        $blotter = Blotter::with(['createdBy', 'receivedBy'])
            ->where('case_number', $case_number)
            ->first();

        if (!$blotter) {
            return response()->json([
                'success' => false,
                'message' => 'Blotter not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $blotter
        ], 200);
    }



    /**
     * Update blotter
     */
    public function update(Request $request, $case_number)
    {
        $blotter = Blotter::where('case_number', $case_number)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'complainant_name' => 'sometimes|string',
            'respondent_name' => 'sometimes|string',
            'additional_respondent' => 'nullable|array',
            'complaint_details' => 'sometimes|string',
            'relief_sought' => 'sometimes|string',
            'case_type' => 'sometimes|string|max:255',
            'date_filed' => 'sometimes|date',
            'status' => 'sometimes|in:filed,ongoing,settled',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $blotter->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Blotter updated successfully',
            'data' => $blotter
        ]);
    }

    /**
     * Delete blotter
     */
    public function destroy($case_number)
    {
        $blotter = Blotter::where('case_number', $case_number)->firstOrFail();
        $blotter->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blotter deleted successfully'
        ]);
    }
}
