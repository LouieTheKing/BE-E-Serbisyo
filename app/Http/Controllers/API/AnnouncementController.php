<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Announcement;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements with pagination and filter.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10); // default 10
        $type = $request->get('type'); // filter type (optional)

        $query = Announcement::query();

        if ($type) {
            $query->where('type', $type);
        }

        $announcements = $query->latest()->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $announcements
        ], 200);
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:information,problem,warning',
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $announcement = Announcement::create($request->only(['type', 'description']));

        return response()->json([
            'success' => true,
            'message' => 'Announcement created successfully',
            'data' => $announcement
        ], 201);
    }

    /**
     * Display a specific announcement (id in body).
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:announcements,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $announcement = Announcement::find($request->id);

        return response()->json([
            'success' => true,
            'data' => $announcement
        ], 200);
    }

    /**
     * Update a specific announcement (id in body).
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:announcements,id',
            'type' => 'sometimes|required|string|in:information,problem,warning',
            'description' => 'sometimes|required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $announcement = Announcement::find($request->id);

        $announcement->update($request->only(['type', 'description']));

        return response()->json([
            'success' => true,
            'message' => 'Announcement updated successfully',
            'data' => $announcement
        ], 200);
    }

    /**
     * Remove a specific announcement (id in body).
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:announcements,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $announcement = Announcement::find($request->id);
        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully'
        ], 200);
    }
}
