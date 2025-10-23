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
        // Validate date inputs
        $validator = Validator::make($request->all(), [
            'date_from' => 'nullable|date_format:Y-m-d',
            'date_to' => 'nullable|date_format:Y-m-d',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
            'sort_by' => 'nullable|string|in:created_at,account,module,remark',
            'order' => 'nullable|string|in:asc,desc'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $validator->errors()
            ], 400);
        }

        $query = ActivityLog::with('account');

        // Filters
        if ($request->filled('account')) {
            $query->where('account', $request->account);
        }

        if ($request->filled('module')) {
            $query->where('module', 'like', '%' . $request->module . '%');
        }

        // Date range filter with better error handling
        if ($request->filled('date_from')) {
            $dateFrom = $request->date_from;
            $query->whereDate('created_at', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = $request->date_to;
            $query->whereDate('created_at', '<=', $dateTo);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('remark', 'like', "%{$search}%")
                ->orWhere('module', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('order', 'desc');

        $allowedSorts = ['created_at', 'account', 'module', 'remark'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        if (!in_array($sortOrder, ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }

        $perPage = $request->get('per_page', 10);
        $perPage = max(1, min(100, (int)$perPage)); // Ensure it's between 1 and 100

        $logs = $query->orderBy($sortBy, $sortOrder)->paginate($perPage);

        return response()->json([
            'data' => $logs->items(),
            'current_page' => $logs->currentPage(),
            'last_page' => $logs->lastPage(),
            'per_page' => $logs->perPage(),
            'total' => $logs->total(),
            'from' => $logs->firstItem(),
            'to' => $logs->lastItem(),
            'filters_applied' => [
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'account' => $request->account,
                'module' => $request->module,
                'search' => $request->search,
                'sort_by' => $sortBy,
                'order' => $sortOrder
            ]
        ]);
    }


    /**
     * Show a single activity log
     */
    public function show($id)
    {
        try {
            $log = ActivityLog::with('account')->findOrFail($id);
            return response()->json($log);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Activity log not found'], 404);
        }
    }

    /**
     * Delete activity log (admin only)
     */
    public function destroy($id)
    {
        try {
            $log = ActivityLog::findOrFail($id);
            $log->delete();

            return response()->json([
                'status' => true,
                'message' => 'Activity log deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Activity log not found'], 404);
        }
    }
}
