<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Official;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class OfficialsController extends Controller
{
    // 1. Create Official
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'position' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'term_start' => 'required|date',
            'term_end' => 'required|date|after_or_equal:term_start',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imagePath = $image->store('officials', 'public');
            }

            $official = Official::create([
                'full_name' => $request->full_name,
                'position' => $request->position,
                'image_path' => $imagePath,
                'term_start' => $request->term_start,
                'term_end' => $request->term_end,
                'status' => $request->status,
            ]);
            DB::commit();
            return response()->json(['official' => $official], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to create official', 'message' => $e->getMessage()], 500);
        }
    }

    // 2. Update Official
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:255',
            'position' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'term_start' => 'sometimes|required|date',
            'term_end' => 'sometimes|required|date|after_or_equal:term_start',
            'status' => 'sometimes|required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $official = Official::find($id);
        if (!$official) {
            return response()->json(['error' => 'Official not found'], 404);
        }

        DB::beginTransaction();
        try {
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($official->image_path && Storage::disk('public')->exists($official->image_path)) {
                    Storage::disk('public')->delete($official->image_path);
                }
                $image = $request->file('image');
                $official->image_path = $image->store('officials', 'public');
            }
            if ($request->has('full_name')) $official->full_name = $request->full_name;
            if ($request->has('position')) $official->position = $request->position;
            if ($request->has('term_start')) $official->term_start = $request->term_start;
            if ($request->has('term_end')) $official->term_end = $request->term_end;
            if ($request->has('status')) $official->status = $request->status;
            $official->save();
            DB::commit();
            return response()->json(['official' => $official], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update official', 'message' => $e->getMessage()], 500);
        }
    }

    // 3. Update Status
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $official = Official::find($id);
        if (!$official) {
            return response()->json(['error' => 'Official not found'], 404);
        }
        try {
            $official->status = $request->status;
            $official->save();
            return response()->json(['official' => $official], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update status', 'message' => $e->getMessage()], 500);
        }
    }

    // 4. Get all officials with status filter
    public function index(Request $request)
    {
        $status = $request->query('status');
        $perPage = $request->query('per_page', default: 10);

        try {
            $query = Official::query();
            if ($status) {
                $query->where('status', $status);
            }
            $officials = $query->paginate($perPage);
            return response()->json($officials, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch officials', 'message' => $e->getMessage()], 500);
        }
    }

    // 5. Get official by id
    public function show($id)
    {
        try {
            $official = Official::find($id);
            if (!$official) {
                return response()->json(['error' => 'Official not found'], 404);
            }
            return response()->json(['official' => $official], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch official', 'message' => $e->getMessage()], 500);
        }
    }
}
