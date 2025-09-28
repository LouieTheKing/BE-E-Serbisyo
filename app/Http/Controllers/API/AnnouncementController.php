<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Announcement;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements with pagination, filter, and sorting.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10); // default 10
        $type = $request->get('type'); // filter type (optional)

        $sortBy = $request->query('sort_by', 'created_at'); // default sorting
        $order = $request->query('order', 'desc'); // default descending

        $allowedSorts = ['created_at', 'type', 'description'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query = Announcement::query();

        if ($type) {
            $query->where('type', $type);
        }

        $query->orderBy($sortBy, $order);

        $announcements = $query->paginate($perPage);

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
            'description' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // validate each file
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('announcements', 'public');
                $imagePaths[] = $path;
            }
        }

        $announcement = Announcement::create([
            'type' => $request->type,
            'description' => $request->description,
            'images' => $imagePaths,
        ]);

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
            'description' => 'sometimes|required|string',
            'images' => 'nullable|array',
            'images.*' => 'file|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $announcement = Announcement::findOrFail($request->id);

        // Delete old images from storage
        if ($announcement->images) {
            foreach ($announcement->images as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }
        }

        $newImages = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $file->store('announcements', 'public');
                $newImages[] = $path;
            }
        }

        $announcement->update([
            'type' => $request->input('type', $announcement->type),
            'description' => $request->input('description', $announcement->description),
            'images' => $newImages,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Announcement updated successfully',
            'data' => array_merge($announcement->toArray(), [
                'image_urls' => array_map(fn($p) => Storage::url($p), $newImages)
            ])
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
