<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\ActivityLog;

class ActivityLogsController extends Controller
{
    /**
     * List activity logs with pagination and filters
     */
    public function index(Request $request)
    {
        $query = ActivityLog::with('account');

        // Filters
        if ($request->filled('account')) {
            $query->where('account', $request->account);
        }

        if ($request->filled('module')) {
            $query->where('module', 'like', '%' . $request->module . '%');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('remark', 'like', "%{$search}%")
                ->orWhere('module', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at'); // default column
        $sortOrder = $request->get('order', 'desc'); // default order

        $allowedSorts = ['created_at', 'account', 'module', 'remark'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $perPage = $request->get('per_page', 10);

        $logs = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        return response()->json($logs);
    }


    /**
     * Store a new activity log
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'account' => 'required|exists:accounts,id',
            'module' => 'required|string|max:255',
            'remark' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $log = ActivityLog::create($request->only(['account', 'module', 'remark']));

        return response()->json([
            'status' => true,
            'message' => 'Activity log created successfully',
            'data' => $log
        ], 201);
    }

    /**
     * Show a single activity log
     */
    public function show(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:activity_logs,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $log = ActivityLog::with('account')->find($request->id);

        return response()->json($log);
    }

    /**
     * Update activity log
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:activity_logs,id',
            'account' => 'sometimes|exists:accounts,id',
            'module' => 'sometimes|string|max:255',
            'remark' => 'sometimes|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $log = ActivityLog::find($request->id);
        $log->update($request->only(['account', 'module', 'remark']));

        return response()->json([
            'status' => true,
            'message' => 'Activity log updated successfully',
            'data' => $log
        ]);
    }

    /**
     * Delete activity log
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:activity_logs,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $log = ActivityLog::find($request->id);
        $log->delete();

        return response()->json([
            'status' => true,
            'message' => 'Activity log deleted successfully'
        ]);
    }
}
