<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RequestDocument;
use App\Models\Account;
use App\Models\Blotter;
use App\Models\Feedback;
use App\Models\Announcement;
use App\Models\CertificateLog;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get overview statistics with date range
     */
    public function overview(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonth());
        $dateTo = $request->input('date_to', Carbon::now());

        $stats = [
            'total_users' => Account::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_requests' => RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_blotters' => Blotter::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_feedbacks' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_announcements' => Announcement::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get document request statistics with date range
     */
    public function documentRequests(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonth());
        $dateTo = $request->input('date_to', Carbon::now());
        $sortBy = $request->input('sort_by', 'date');
        $order = $request->input('order', 'desc');

        $query = RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo]);

        $stats = [
            'total' => $query->count(),
            'by_status' => $query->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
        ];

        // Daily breakdown
        if ($sortBy === 'date') {
            $dailyBreakdown = RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date', $order)
                ->get();
            $stats['daily_breakdown'] = $dailyBreakdown;
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ]);
    }

    /**
     * Get user registration statistics with date range
     */
    public function userStatistics(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonth());
        $dateTo = $request->input('date_to', Carbon::now());
        $sortBy = $request->input('sort_by', 'date');
        $order = $request->input('order', 'desc');

        $query = Account::whereBetween('created_at', [$dateFrom, $dateTo]);

        $stats = [
            'total' => $query->count(),
            'by_type' => Account::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get(),
            'by_status' => Account::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
        ];

        // Daily breakdown
        if ($sortBy === 'date') {
            $dailyBreakdown = Account::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date', $order)
                ->get();
            $stats['daily_breakdown'] = $dailyBreakdown;
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ]);
    }

    /**
     * Get blotter statistics with date range
     */
    public function blotterStatistics(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonth());
        $dateTo = $request->input('date_to', Carbon::now());
        $sortBy = $request->input('sort_by', 'date');
        $order = $request->input('order', 'desc');

        $query = Blotter::whereBetween('created_at', [$dateFrom, $dateTo]);

        $stats = [
            'total' => $query->count(),
            'by_status' => Blotter::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'by_case_type' => Blotter::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('case_type', DB::raw('count(*) as count'))
                ->groupBy('case_type')
                ->get()
        ];

        // Daily breakdown
        if ($sortBy === 'date') {
            $dailyBreakdown = Blotter::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date', $order)
                ->get();
            $stats['daily_breakdown'] = $dailyBreakdown;
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ]);
    }

    /**
     * Get feedback statistics with date range
     */
    public function feedbackStatistics(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonth());
        $dateTo = $request->input('date_to', Carbon::now());
        $sortBy = $request->input('sort_by', 'date');
        $order = $request->input('order', 'desc');

        $query = Feedback::whereBetween('created_at', [$dateFrom, $dateTo]);

        $stats = [
            'total' => $query->count(),
            'by_rating' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->orderBy('rating')
                ->get(),
            'by_category' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->get(),
            'average_rating' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
                ->avg('rating')
        ];

        // Daily breakdown
        if ($sortBy === 'date') {
            $dailyBreakdown = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('AVG(rating) as avg_rating'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date', $order)
                ->get();
            $stats['daily_breakdown'] = $dailyBreakdown;
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ]);
    }

    /**
     * Get certificate logs statistics with date range
     */
    public function certificateStatistics(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonth());
        $dateTo = $request->input('date_to', Carbon::now());
        $sortBy = $request->input('sort_by', 'date');
        $order = $request->input('order', 'desc');

        $query = CertificateLog::whereBetween('created_at', [$dateFrom, $dateTo]);

        $stats = [
            'total' => $query->count(),
            'by_staff' => CertificateLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('staff', DB::raw('count(*) as count'))
                ->groupBy('staff')
                ->get(),
        ];

        // Daily breakdown
        if ($sortBy === 'date') {
            $dailyBreakdown = CertificateLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date', $order)
                ->get();
            $stats['daily_breakdown'] = $dailyBreakdown;
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ]);
    }

    /**
     * Get activity logs statistics with date range
     */
    public function activityStatistics(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonth());
        $dateTo = $request->input('date_to', Carbon::now());
        $sortBy = $request->input('sort_by', 'date');
        $order = $request->input('order', 'desc');

        $query = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo]);

        $stats = [
            'total' => $query->count(),
            'by_module' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('module', DB::raw('count(*) as count'))
                ->groupBy('module')
                ->get(),
            'by_account' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('account', DB::raw('count(*) as count'))
                ->groupBy('account')
                ->limit(10)
                ->orderByDesc(DB::raw('count(*)'))
                ->get(),
        ];

        // Daily breakdown
        if ($sortBy === 'date') {
            $dailyBreakdown = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date', $order)
                ->get();
            $stats['daily_breakdown'] = $dailyBreakdown;
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ]);
    }

    /**
     * Get announcement statistics with date range
     */
    public function announcementStatistics(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonth());
        $dateTo = $request->input('date_to', Carbon::now());
        $sortBy = $request->input('sort_by', 'date');
        $order = $request->input('order', 'desc');

        $query = Announcement::whereBetween('created_at', [$dateFrom, $dateTo]);

        $stats = [
            'total' => $query->count(),
            'by_type' => Announcement::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
        ];

        // Daily breakdown
        if ($sortBy === 'date') {
            $dailyBreakdown = Announcement::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date', $order)
                ->get();
            $stats['daily_breakdown'] = $dailyBreakdown;
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ]);
    }

    /**
     * Get document type statistics with date range
     */
    public function documentTypeStatistics(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonth());
        $dateTo = $request->input('date_to', Carbon::now());
        $sortBy = $request->input('sort_by', 'count');
        $order = $request->input('order', 'desc');

        $stats = RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('document', DB::raw('count(*) as count'))
            ->groupBy('document')
            ->orderBy('count', $order)
            ->get()
            ->map(function ($item) {
                return [
                    'document_id' => $item->document,
                    'count' => $item->count
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $stats,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ]);
    }

    /**
     * Get monthly comparison statistics
     */
    public function monthlyComparison(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthStart = Carbon::create($year, $i, 1)->startOfMonth();
            $monthEnd = Carbon::create($year, $i, 1)->endOfMonth();

            $months[] = [
                'month' => $monthStart->format('F'),
                'month_number' => $i,
                'users' => Account::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'requests' => RequestDocument::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'blotters' => Blotter::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'feedbacks' => Feedback::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
                'avg_rating' => Feedback::whereBetween('created_at', [$monthStart, $monthEnd])->avg('rating') ?? 0,
            ];
        }

        return response()->json([
            'success' => true,
            'year' => $year,
            'data' => $months
        ]);
    }

    /**
     * Get top performing documents by request count
     */
    public function topDocuments(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonth());
        $dateTo = $request->input('date_to', Carbon::now());
        $limit = $request->input('limit', 10);

        $topDocuments = RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('document', DB::raw('count(*) as total_requests'))
            ->groupBy('document')
            ->orderByDesc('total_requests')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                return [
                    'document_id' => $item->document,
                    'total_requests' => $item->total_requests
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $topDocuments,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ]);
    }

    /**
     * Get system performance metrics
     */
    public function performanceMetrics(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonth());
        $dateTo = $request->input('date_to', Carbon::now());

        $totalRequests = RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $completedRequests = RequestDocument::where('status', 'released')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->count();

        $metrics = [
            'total_requests' => $totalRequests,
            'completed_requests' => $completedRequests,
            'completion_rate' => $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 2) : 0,
            'average_processing_time' => $this->calculateAverageProcessingTime($dateFrom, $dateTo),
            'pending_requests' => RequestDocument::where('status', 'pending')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'rejected_requests' => RequestDocument::where('status', 'rejected')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'rejection_rate' => $totalRequests > 0 ? round((RequestDocument::where('status', 'rejected')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count() / $totalRequests) * 100, 2) : 0,
        ];

        return response()->json([
            'success' => true,
            'data' => $metrics,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ]);
    }

    /**
     * Calculate average processing time in days
     */
    private function calculateAverageProcessingTime($dateFrom, $dateTo)
    {
        $releasedDocuments = RequestDocument::where('status', 'released')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        if ($releasedDocuments->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        foreach ($releasedDocuments as $doc) {
            $createdAt = Carbon::parse($doc->created_at);
            $updatedAt = Carbon::parse($doc->updated_at);
            $totalDays += $createdAt->diffInDays($updatedAt);
        }

        return round($totalDays / $releasedDocuments->count(), 2);
    }

    /**
     * Get combined statistics for all modules
     */
    public function allStatistics(Request $request)
    {
        $dateFrom = $request->input('date_from', Carbon::now()->subMonth());
        $dateTo = $request->input('date_to', Carbon::now());

        $stats = [
            'overview' => [
                'total_users' => Account::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_requests' => RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_blotters' => Blotter::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_feedbacks' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_announcements' => Announcement::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'average_rating' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->avg('rating') ?? 0,
            ],
            'requests' => [
                'pending' => RequestDocument::where('status', 'pending')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'approved' => RequestDocument::where('status', 'approved')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'processing' => RequestDocument::where('status', 'processing')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'ready_to_pickup' => RequestDocument::where('status', 'ready to pickup')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'released' => RequestDocument::where('status', 'released')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'rejected' => RequestDocument::where('status', 'rejected')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            ],
            'blotters' => [
                'filed' => Blotter::where('status', 'filed')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'ongoing' => Blotter::where('status', 'ongoing')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'settled' => Blotter::where('status', 'settled')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            ],
            'users' => [
                'residence' => Account::where('type', 'residence')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'admin' => Account::where('type', 'admin')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'staff' => Account::where('type', 'staff')->whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            ],
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
