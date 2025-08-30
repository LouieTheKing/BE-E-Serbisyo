<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Blotter;

class BlotterController extends Controller
{
    /**
     * Display a listing of blotters with pagination and optional filters.
     */
    public function index(Request $request)
{
    $perPage = $request->get('per_page', 10);
    $status = $request->get('status');
    $reporter = $request->get('reporter');
    $location = $request->get('location');
    $incidentDate = $request->get('incident_date'); // single date
    $fromDate = $request->get('from_date');
    $toDate = $request->get('to_date');
    $search = $request->get('search'); // keyword search

    $query = Blotter::with('account')->latest();

    if ($status) {
        $query->where('status', $status);
    }

    if ($reporter) {
        $query->where('reporter', $reporter);
    }

    if ($location) {
        $query->where('location', $location);
    }

    if ($incidentDate) {
        $query->whereDate('incident_date', $incidentDate);
    }

    if ($fromDate && $toDate) {
        $query->whereBetween('incident_date', [$fromDate, $toDate]);
    }

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('incidents', 'like', '%' . $search . '%')
              ->orWhere('remarks', 'like', '%' . $search . '%');
        });
    }

    $blotters = $query->paginate($perPage);

    return response()->json([
        'success' => true,
        'data' => $blotters
    ], 200);
}


    /**
     * Store a newly created blotter.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'blotter_number' => 'required|string|unique:blotters,blotter_number',
            'remarks' => 'required|string',
            'incidents' => 'required|string',
            'location' => 'required|string',
            'incident_date' => 'required|date',
            'reporter' => 'required|integer|exists:accounts,id',
            'status' => 'in:pending,resolved,unresolved'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $blotter = Blotter::create($request->only([
            'blotter_number', 'status', 'remarks', 'incidents',
            'location', 'incident_date', 'reporter'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Blotter created successfully',
            'data' => $blotter
        ], 201);
    }

    /**
     * Show a blotter by id (from request body).
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:blotters,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $blotter = Blotter::with('account')->find($request->id);

        return response()->json([
            'success' => true,
            'data' => $blotter
        ], 200);
    }

    /**
     * Update blotter by id (from request body).
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:blotters,id',
            'blotter_number' => 'sometimes|required|string|unique:blotters,blotter_number,' . $request->id,
            'remarks' => 'sometimes|required|string',
            'incidents' => 'sometimes|required|string',
            'location' => 'sometimes|required|string',
            'incident_date' => 'sometimes|required|date',
            'reporter' => 'sometimes|required|integer|exists:accounts,id',
            'status' => 'sometimes|required|in:pending,resolved,unresolved'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $blotter = Blotter::find($request->id);
        $blotter->update($request->only([
            'blotter_number', 'status', 'remarks', 'incidents',
            'location', 'incident_date', 'reporter'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Blotter updated successfully',
            'data' => $blotter
        ], 200);
    }

    /**
     * Destroy blotter by id (from request body).
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:blotters,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $blotter = Blotter::find($request->id);
        $blotter->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blotter deleted successfully'
        ], 200);
    }
}
