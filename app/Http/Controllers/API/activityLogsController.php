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
