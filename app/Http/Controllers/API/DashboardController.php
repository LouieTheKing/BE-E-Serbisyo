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
use App\Models\Document;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

        // Add detailed processing breakdown if requested
        if ($request->input('detailed', false)) {
            $metrics['detailed_processing'] = $this->calculateDetailedProcessingTime($dateFrom, $dateTo);
        }

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
     * Calculate average processing time in days using certificate logs
     */
    private function calculateAverageProcessingTime($dateFrom, $dateTo)
    {
        // Get completed requests with their certificate logs
        $completedRequests = RequestDocument::where('status', 'released')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['certificateLogs' => function($query) {
                $query->orderBy('created_at');
            }])
            ->get();

        if ($completedRequests->isEmpty()) {
            return 0;
        }

        $totalDays = 0;
        $validRequests = 0;

        foreach ($completedRequests as $request) {
            $logs = $request->certificateLogs;

            if ($logs->isEmpty()) {
                // Fallback to original method if no logs
                $createdAt = Carbon::parse($request->created_at);
                $updatedAt = Carbon::parse($request->updated_at);
                $totalDays += $createdAt->diffInDays($updatedAt);
                $validRequests++;
                continue;
            }

            // Use the first log (request created/approved) and last log (released)
            $firstLog = $logs->first();
            $lastLog = $logs->last();

            // Use the actual log timestamps for more accurate calculation
            $startDate = Carbon::parse($firstLog->created_at);
            $endDate = Carbon::parse($lastLog->created_at);

            $processingDays = $startDate->diffInDays($endDate);
            $totalDays += $processingDays;
            $validRequests++;
        }

        return $validRequests > 0 ? round($totalDays / $validRequests, 2) : 0;
    }

    /**
     * Calculate detailed processing time breakdown using certificate logs
     */
    private function calculateDetailedProcessingTime($dateFrom, $dateTo)
    {
        $completedRequests = RequestDocument::where('status', 'released')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->with(['certificateLogs' => function($query) {
                $query->orderBy('created_at');
            }])
            ->get();

        $breakdown = [
            'average_total_time' => 0,
            'median_processing_time' => 0,
            'stage_breakdown' => [],
            'processing_time_distribution' => [
                'same_day' => 0,
                'two_to_three_days' => 0,
                'one_week' => 0,
                'over_week' => 0
            ]
        ];

        if ($completedRequests->isEmpty()) {
            return $breakdown;
        }

        $totalProcessingTime = 0;
        $processingTimes = [];
        $stageTimings = [];
        $validRequests = 0;

        foreach ($completedRequests as $request) {
            $logs = $request->certificateLogs->sortBy('created_at');

            if ($logs->count() < 2) {
                // Use fallback method for requests without sufficient logs
                $createdAt = Carbon::parse($request->created_at);
                $updatedAt = Carbon::parse($request->updated_at);
                $totalTime = $createdAt->diffInDays($updatedAt);
            } else {
                $requestStart = Carbon::parse($logs->first()->created_at);
                $requestEnd = Carbon::parse($logs->last()->created_at);
                $totalTime = $requestStart->diffInDays($requestEnd);

                // Calculate time between stages
                $previousLog = null;
                foreach ($logs as $log) {
                    if ($previousLog) {
                        $stageDuration = Carbon::parse($previousLog->created_at)
                            ->diffInDays(Carbon::parse($log->created_at));

                        $stageKey = $this->getStageFromRemark($log->remark);
                        if (!isset($stageTimings[$stageKey])) {
                            $stageTimings[$stageKey] = [];
                        }
                        $stageTimings[$stageKey][] = $stageDuration;
                    }
                    $previousLog = $log;
                }
            }

            $totalProcessingTime += $totalTime;
            $processingTimes[] = $totalTime;
            $validRequests++;

            // Categorize processing time
            if ($totalTime <= 1) {
                $breakdown['processing_time_distribution']['same_day']++;
            } elseif ($totalTime <= 3) {
                $breakdown['processing_time_distribution']['two_to_three_days']++;
            } elseif ($totalTime <= 7) {
                $breakdown['processing_time_distribution']['one_week']++;
            } else {
                $breakdown['processing_time_distribution']['over_week']++;
            }
        }

        // Calculate averages and median
        $breakdown['average_total_time'] = $validRequests > 0 ?
            round($totalProcessingTime / $validRequests, 2) : 0;

        // Calculate median
        sort($processingTimes);
        $count = count($processingTimes);
        if ($count > 0) {
            $middle = floor($count / 2);
            if ($count % 2 == 0) {
                $breakdown['median_processing_time'] = round(($processingTimes[$middle - 1] + $processingTimes[$middle]) / 2, 2);
            } else {
                $breakdown['median_processing_time'] = $processingTimes[$middle];
            }
        }

        // Calculate average time for each stage
        foreach ($stageTimings as $stage => $times) {
            $breakdown['stage_breakdown'][$stage] = [
                'average_days' => round(array_sum($times) / count($times), 2),
                'min_days' => min($times),
                'max_days' => max($times),
                'occurrences' => count($times)
            ];
        }

        return $breakdown;
    }

    /**
     * Helper method to extract stage from remark
     */
    private function getStageFromRemark($remark)
    {
        $remark = strtolower($remark);

        if (strpos($remark, 'approved') !== false) {
            return 'approval';
        } elseif (strpos($remark, 'processing') !== false) {
            return 'processing';
        } elseif (strpos($remark, 'ready') !== false || strpos($remark, 'pickup') !== false) {
            return 'ready_for_pickup';
        } elseif (strpos($remark, 'released') !== false) {
            return 'release';
        } else {
            return 'other';
        }
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

    /**
     * Generate comprehensive monthly/yearly report for all modules
     */
    public function generateReport(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'report_type' => 'required|in:monthly,yearly',
            'period' => 'required|string', // Format: YYYY-MM for monthly, YYYY for yearly
            'modules' => 'sometimes|array',
            'modules.*' => 'in:overview,users,requests,blotters,feedbacks,announcements,certificates,activity_logs,documents',
            'format' => 'sometimes|in:json,summary'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $reportType = $request->input('report_type');
        $period = $request->input('period');
        $selectedModules = $request->input('modules', ['overview', 'users', 'requests', 'blotters', 'feedbacks', 'announcements', 'certificates', 'activity_logs', 'documents']);
        $format = $request->input('format', 'json');

        try {
            // Calculate date range based on report type
            if ($reportType === 'monthly') {
                $date = Carbon::createFromFormat('Y-m', $period);
                $dateFrom = $date->copy()->startOfMonth();
                $dateTo = $date->copy()->endOfMonth();
                $reportTitle = $date->format('F Y') . ' Monthly Report';
            } else {
                $date = Carbon::createFromFormat('Y', $period);
                $dateFrom = $date->copy()->startOfYear();
                $dateTo = $date->copy()->endOfYear();
                $reportTitle = $date->format('Y') . ' Annual Report';
            }

            $report = [
                'report_info' => [
                    'title' => $reportTitle,
                    'type' => $reportType,
                    'period' => $period,
                    'date_range' => [
                        'from' => $dateFrom->format('Y-m-d'),
                        'to' => $dateTo->format('Y-m-d')
                    ],
                    'generated_at' => now()->format('Y-m-d H:i:s'),
                    'generated_by' => auth()->user() ? auth()->user()->first_name . ' ' . auth()->user()->last_name : 'System'
                ],
                'data' => []
            ];

            // Generate data for each selected module
            foreach ($selectedModules as $module) {
                switch ($module) {
                    case 'overview':
                        $report['data']['overview'] = $this->generateOverviewReport($dateFrom, $dateTo);
                        break;
                    case 'users':
                        $report['data']['users'] = $this->generateUserReport($dateFrom, $dateTo, $reportType);
                        break;
                    case 'requests':
                        $report['data']['requests'] = $this->generateRequestReport($dateFrom, $dateTo, $reportType);
                        break;
                    case 'blotters':
                        $report['data']['blotters'] = $this->generateBlotterReport($dateFrom, $dateTo, $reportType);
                        break;
                    case 'feedbacks':
                        $report['data']['feedbacks'] = $this->generateFeedbackReport($dateFrom, $dateTo, $reportType);
                        break;
                    case 'announcements':
                        $report['data']['announcements'] = $this->generateAnnouncementReport($dateFrom, $dateTo, $reportType);
                        break;
                    case 'certificates':
                        $report['data']['certificates'] = $this->generateCertificateReport($dateFrom, $dateTo, $reportType);
                        break;
                    case 'activity_logs':
                        $report['data']['activity_logs'] = $this->generateActivityReport($dateFrom, $dateTo, $reportType);
                        break;
                    case 'documents':
                        $report['data']['documents'] = $this->generateDocumentReport($dateFrom, $dateTo, $reportType);
                        break;
                }
            }

            $response = [
                'success' => true,
                'data' => $report['data'],
                'date_range' => [
                    'from' => $dateFrom->format('Y-m-d'),
                    'to' => $dateTo->format('Y-m-d')
                ],
                'report_info' => $report['report_info']
            ];

            // Add summary if requested
            if ($format === 'summary') {
                $response['summary'] = $this->generateReportSummary($report['data'], $dateFrom, $dateTo);
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate report',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate overview report data
     */
    private function generateOverviewReport($dateFrom, $dateTo)
    {
        $totalRequests = RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $totalUsers = Account::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $totalFeedbacks = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $totalBlotters = Blotter::whereBetween('created_at', [$dateFrom, $dateTo])->count();

        // Calculate period length for daily averages
        $periodDays = max(1, $dateFrom->diffInDays($dateTo));

        return [
            'totals' => [
                'total_users' => $totalUsers,
                'total_requests' => $totalRequests,
                'total_blotters' => $totalBlotters,
                'total_feedbacks' => $totalFeedbacks,
                'total_announcements' => Announcement::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_certificate_logs' => CertificateLog::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
                'total_activity_logs' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            ],
            'averages' => [
                'daily_users' => round($totalUsers / $periodDays, 2),
                'daily_requests' => round($totalRequests / $periodDays, 2),
                'daily_blotters' => round($totalBlotters / $periodDays, 2),
                'daily_feedbacks' => round($totalFeedbacks / $periodDays, 2),
                'feedback_rating' => round(Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->avg('rating') ?? 0, 2),
                'processing_time_days' => $this->calculateAverageProcessingTime($dateFrom, $dateTo)
            ],
            'rates' => [
                'completion_rate' => $this->calculateCompletionRate($dateFrom, $dateTo),
                'satisfaction_rate' => $this->calculateSatisfactionRate($dateFrom, $dateTo),
                'blotter_resolution_rate' => $this->calculateBlotterResolutionRate($dateFrom, $dateTo),
                'user_engagement_rate' => $totalUsers > 0 ? round(($totalRequests / $totalUsers) * 100, 2) : 0
            ],
            'period_info' => [
                'period_days' => $periodDays,
                'period_weeks' => round($periodDays / 7, 1),
                'period_months' => round($periodDays / 30, 1)
            ]
        ];
    }

    /**
     * Generate user report data
     */
    private function generateUserReport($dateFrom, $dateTo, $reportType)
    {
        $totalRegistered = Account::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $periodDays = max(1, $dateFrom->diffInDays($dateTo));

        // Get active users (those who made requests or activity logs)
        $activeUsers = Account::whereBetween('accounts.created_at', [$dateFrom, $dateTo])
            ->join('request_documents', 'accounts.id', '=', 'request_documents.requestor')
            ->distinct('accounts.id')
            ->count();

        $data = [
            'summary' => [
                'total_registered' => $totalRegistered,
                'active_users' => $activeUsers,
                'activation_rate' => $totalRegistered > 0 ? round(($activeUsers / $totalRegistered) * 100, 2) : 0,
                'daily_average_registrations' => round($totalRegistered / $periodDays, 2)
            ],
            'demographics' => [
                'by_type' => Account::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->select('type', DB::raw('count(*) as count'))
                    ->groupBy('type')
                    ->get(),
                'by_status' => Account::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->select('status', DB::raw('count(*) as count'))
                    ->groupBy('status')
                    ->get(),
                'by_municipality' => Account::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->select('municipality', DB::raw('count(*) as count'))
                    ->groupBy('municipality')
                    ->orderByDesc('count')
                    ->limit(10)
                    ->get()
            ],
            'engagement' => [
                'users_with_requests' => Account::whereBetween('accounts.created_at', [$dateFrom, $dateTo])
                    ->join('request_documents', 'accounts.id', '=', 'request_documents.requestor')
                    ->distinct('accounts.id')
                    ->count(),
                'users_with_feedback' => Account::whereBetween('accounts.created_at', [$dateFrom, $dateTo])
                    ->join('feedbacks', 'accounts.id', '=', 'feedbacks.user')
                    ->distinct('accounts.id')
                    ->count(),
                'average_requests_per_user' => $totalRegistered > 0 ?
                    round(RequestDocument::join('accounts', 'request_documents.requestor', '=', 'accounts.id')
                        ->whereBetween('accounts.created_at', [$dateFrom, $dateTo])
                        ->count() / $totalRegistered, 2) : 0
            ]
        ];

        if ($reportType === 'yearly') {
            $data['trends'] = [
                'monthly_breakdown' => Account::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->select(DB::raw('MONTH(created_at) as month'),
                             DB::raw('MONTHNAME(created_at) as month_name'),
                             DB::raw('count(*) as count'))
                    ->groupBy('month', 'month_name')
                    ->orderBy('month')
                    ->get(),
                'growth_rate' => $this->calculateUserGrowthRate($dateFrom, $dateTo)
            ];
        }

        return $data;
    }

    /**
     * Generate request report data
     */
    private function generateRequestReport($dateFrom, $dateTo, $reportType)
    {
        $totalRequests = RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $completedRequests = RequestDocument::where('status', 'released')
            ->whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $pendingRequests = RequestDocument::where('status', 'pending')
            ->whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $rejectedRequests = RequestDocument::where('status', 'rejected')
            ->whereBetween('created_at', [$dateFrom, $dateTo])->count();

        $periodDays = max(1, $dateFrom->diffInDays($dateTo));
        $avgProcessingTime = $this->calculateAverageProcessingTime($dateFrom, $dateTo);

        $data = [
            'summary' => [
                'total_requests' => $totalRequests,
                'completed_requests' => $completedRequests,
                'pending_requests' => $pendingRequests,
                'rejected_requests' => $rejectedRequests,
                'in_progress_requests' => RequestDocument::whereIn('status', ['approved', 'processing', 'ready to pickup'])
                    ->whereBetween('created_at', [$dateFrom, $dateTo])->count()
            ],
            'performance_metrics' => [
                'completion_rate' => $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 2) : 0,
                'rejection_rate' => $totalRequests > 0 ? round(($rejectedRequests / $totalRequests) * 100, 2) : 0,
                'pending_rate' => $totalRequests > 0 ? round(($pendingRequests / $totalRequests) * 100, 2) : 0,
                'average_processing_time_days' => $avgProcessingTime,
                'daily_average_requests' => round($totalRequests / $periodDays, 2)
            ],
            'status_breakdown' => RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->orderByDesc('count')
                ->get(),
            'document_analytics' => [
                'by_document_type' => RequestDocument::whereBetween('request_documents.created_at', [$dateFrom, $dateTo])
                    ->join('documents', 'request_documents.document', '=', 'documents.id')
                    ->select('documents.document_name',
                             DB::raw('count(*) as total_requests'),
                             DB::raw('AVG(CASE WHEN request_documents.status = "released" THEN DATEDIFF(request_documents.updated_at, request_documents.created_at) END) as avg_processing_days'),
                             DB::raw('SUM(CASE WHEN request_documents.status = "released" THEN 1 ELSE 0 END) as completed'),
                             DB::raw('ROUND((SUM(CASE WHEN request_documents.status = "released" THEN 1 ELSE 0 END) * 100.0 / count(*)), 2) as completion_rate'))
                    ->groupBy('documents.id', 'documents.document_name')
                    ->orderByDesc('total_requests')
                    ->get(),
                'most_requested' => RequestDocument::whereBetween('request_documents.created_at', [$dateFrom, $dateTo])
                    ->join('documents', 'request_documents.document', '=', 'documents.id')
                    ->select('documents.document_name', DB::raw('count(*) as count'))
                    ->groupBy('documents.document_name')
                    ->orderByDesc('count')
                    ->limit(5)
                    ->get(),
                'fastest_processed' => RequestDocument::whereBetween('request_documents.created_at', [$dateFrom, $dateTo])
                    ->join('documents', 'request_documents.document', '=', 'documents.id')
                    ->where('request_documents.status', 'released')
                    ->select('documents.document_name',
                             DB::raw('AVG(DATEDIFF(request_documents.updated_at, request_documents.created_at)) as avg_days'))
                    ->groupBy('documents.document_name')
                    ->orderBy('avg_days')
                    ->limit(5)
                    ->get()
            ],
            'time_analysis' => [
                'processing_time_distribution' => RequestDocument::where('status', 'released')
                    ->whereBetween('created_at', [$dateFrom, $dateTo])
                    ->select(
                        DB::raw('SUM(CASE WHEN DATEDIFF(updated_at, created_at) <= 1 THEN 1 ELSE 0 END) as same_day'),
                        DB::raw('SUM(CASE WHEN DATEDIFF(updated_at, created_at) BETWEEN 2 AND 3 THEN 1 ELSE 0 END) as two_to_three_days'),
                        DB::raw('SUM(CASE WHEN DATEDIFF(updated_at, created_at) BETWEEN 4 AND 7 THEN 1 ELSE 0 END) as one_week'),
                        DB::raw('SUM(CASE WHEN DATEDIFF(updated_at, created_at) > 7 THEN 1 ELSE 0 END) as over_week')
                    )
                    ->first(),
                'median_processing_time' => $this->calculateMedianProcessingTime($dateFrom, $dateTo)
            ]
        ];

        if ($reportType === 'yearly') {
            $data['trends'] = [
                'monthly_breakdown' => RequestDocument::whereBetween('request_documents.created_at', [$dateFrom, $dateTo])
                    ->select(DB::raw('MONTH(request_documents.created_at) as month'),
                             DB::raw('MONTHNAME(request_documents.created_at) as month_name'),
                             DB::raw('count(*) as total_requests'),
                             DB::raw('SUM(CASE WHEN status = "released" THEN 1 ELSE 0 END) as completed'),
                             DB::raw('ROUND((SUM(CASE WHEN status = "released" THEN 1 ELSE 0 END) * 100.0 / count(*)), 2) as completion_rate'))
                    ->groupBy('month', 'month_name')
                    ->orderBy('month')
                    ->get(),
                'seasonal_patterns' => $this->calculateSeasonalRequestPatterns($dateFrom, $dateTo)
            ];
        }

        return $data;
    }

    /**
     * Generate blotter report data
     */
    private function generateBlotterReport($dateFrom, $dateTo, $reportType)
    {
        $data = [
            'total_cases' => Blotter::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'by_status' => Blotter::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'by_case_type' => Blotter::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('case_type', DB::raw('count(*) as count'))
                ->groupBy('case_type')
                ->orderByDesc('count')
                ->get(),
            'resolution_rate' => $this->calculateBlotterResolutionRate($dateFrom, $dateTo)
        ];

        if ($reportType === 'yearly') {
            $data['monthly_breakdown'] = Blotter::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        return $data;
    }

    /**
     * Generate feedback report data
     */
    private function generateFeedbackReport($dateFrom, $dateTo, $reportType)
    {
        $totalFeedbacks = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $avgRating = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->avg('rating') ?? 0;
        $periodDays = max(1, $dateFrom->diffInDays($dateTo));

        $data = [
            'summary' => [
                'total_feedbacks' => $totalFeedbacks,
                'average_rating' => round($avgRating, 2),
                'daily_average_feedbacks' => round($totalFeedbacks / $periodDays, 2),
                'satisfaction_rate' => $this->calculateSatisfactionRate($dateFrom, $dateTo),
                'response_rate' => $this->calculateFeedbackResponseRate($dateFrom, $dateTo)
            ],
            'rating_analysis' => [
                'by_rating' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->select('rating', DB::raw('count(*) as count'))
                    ->groupBy('rating')
                    ->orderBy('rating')
                    ->get(),
                'rating_distribution' => [
                    'excellent' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->where('rating', 5)->count(),
                    'good' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->where('rating', 4)->count(),
                    'average' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->where('rating', 3)->count(),
                    'poor' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->where('rating', 2)->count(),
                    'very_poor' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->where('rating', 1)->count()
                ],
                'rating_statistics' => [
                    'median_rating' => $this->calculateMedianRating($dateFrom, $dateTo),
                    'mode_rating' => $this->calculateModeRating($dateFrom, $dateTo),
                    'standard_deviation' => $this->calculateRatingStandardDeviation($dateFrom, $dateTo)
                ]
            ],
            'category_analysis' => [
                'by_category' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->select('category', DB::raw('count(*) as count'), DB::raw('AVG(rating) as avg_rating'))
                    ->groupBy('category')
                    ->orderByDesc('count')
                    ->get(),
                'best_performing_categories' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->select('category', DB::raw('AVG(rating) as avg_rating'), DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->having('count', '>=', 3) // Only categories with at least 3 feedbacks
                    ->orderByDesc('avg_rating')
                    ->limit(5)
                    ->get(),
                'improvement_areas' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->select('category', DB::raw('AVG(rating) as avg_rating'), DB::raw('count(*) as count'))
                    ->groupBy('category')
                    ->having('count', '>=', 3)
                    ->orderBy('avg_rating')
                    ->limit(3)
                    ->get()
            ],
            'sentiment_analysis' => [
                'positive_feedback' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->where('rating', '>=', 4)->count(),
                'neutral_feedback' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->where('rating', 3)->count(),
                'negative_feedback' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->where('rating', '<=', 2)->count(),
                'positive_percentage' => $totalFeedbacks > 0 ? round((Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->where('rating', '>=', 4)->count() / $totalFeedbacks) * 100, 2) : 0,
                'nps_score' => $this->calculateNPSScore($dateFrom, $dateTo)
            ]
        ];

        if ($reportType === 'yearly') {
            $data['trends'] = [
                'monthly_breakdown' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
                    ->select(DB::raw('MONTH(created_at) as month'),
                             DB::raw('MONTHNAME(created_at) as month_name'),
                             DB::raw('AVG(rating) as avg_rating'),
                             DB::raw('count(*) as count'))
                    ->groupBy('month', 'month_name')
                    ->orderBy('month')
                    ->get(),
                'rating_trend' => $this->calculateRatingTrend($dateFrom, $dateTo),
                'seasonal_satisfaction' => $this->calculateSeasonalSatisfaction($dateFrom, $dateTo)
            ];
        }

        return $data;
    }

    /**
     * Generate announcement report data
     */
    private function generateAnnouncementReport($dateFrom, $dateTo, $reportType)
    {
        $data = [
            'total_announcements' => Announcement::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'by_type' => Announcement::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('type', DB::raw('count(*) as count'))
                ->groupBy('type')
                ->get()
        ];

        if ($reportType === 'yearly') {
            $data['monthly_breakdown'] = Announcement::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        return $data;
    }

    /**
     * Generate certificate report data
     */
    private function generateCertificateReport($dateFrom, $dateTo, $reportType)
    {
        $data = [
            'total_logs' => CertificateLog::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'by_staff' => CertificateLog::whereBetween('certificate_logs.created_at', [$dateFrom, $dateTo])
                ->join('accounts', 'certificate_logs.staff', '=', 'accounts.id')
                ->select(DB::raw('CONCAT(accounts.first_name, " ", accounts.last_name) as staff_name'), DB::raw('count(*) as count'))
                ->groupBy('accounts.id', 'accounts.first_name', 'accounts.last_name')
                ->orderByDesc('count')
                ->get()
        ];

        if ($reportType === 'yearly') {
            $data['monthly_breakdown'] = CertificateLog::whereBetween('certificate_logs.created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('MONTH(certificate_logs.created_at) as month'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        return $data;
    }

    /**
     * Generate activity logs report data
     */
    private function generateActivityReport($dateFrom, $dateTo, $reportType)
    {
        $data = [
            'total_activities' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'by_module' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('module', DB::raw('count(*) as count'))
                ->groupBy('module')
                ->orderByDesc('count')
                ->get(),
            'top_users' => ActivityLog::whereBetween('activity_logs.created_at', [$dateFrom, $dateTo])
                ->join('accounts', 'activity_logs.account', '=', 'accounts.id')
                ->select(DB::raw('CONCAT(accounts.first_name, " ", accounts.last_name) as user_name'), DB::raw('count(*) as count'))
                ->groupBy('accounts.id', 'accounts.first_name', 'accounts.last_name')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
        ];

        if ($reportType === 'yearly') {
            $data['monthly_breakdown'] = ActivityLog::whereBetween('activity_logs.created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('MONTH(activity_logs.created_at) as month'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
        }

        return $data;
    }

    /**
     * Generate document statistics report
     */
    private function generateDocumentReport($dateFrom, $dateTo, $reportType)
    {
        $data = [
            'total_documents' => Document::count(),
            'active_documents' => Document::where('status', 'active')->count(),
            'most_requested' => RequestDocument::whereBetween('request_documents.created_at', [$dateFrom, $dateTo])
                ->join('documents', 'request_documents.document', '=', 'documents.id')
                ->select('documents.document_name', DB::raw('count(*) as count'))
                ->groupBy('documents.id', 'documents.document_name')
                ->orderByDesc('count')
                ->limit(5)
                ->get()
        ];

        return $data;
    }

    /**
     * Calculate blotter resolution rate
     */
    private function calculateBlotterResolutionRate($dateFrom, $dateTo)
    {
        $totalBlotters = Blotter::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $settledBlotters = Blotter::where('status', 'settled')
            ->whereBetween('created_at', [$dateFrom, $dateTo])->count();

        return $totalBlotters > 0 ? round(($settledBlotters / $totalBlotters) * 100, 2) : 0;
    }

    /**
     * Calculate satisfaction rate based on feedback ratings
     */
    private function calculateSatisfactionRate($dateFrom, $dateTo)
    {
        $totalFeedbacks = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $satisfiedFeedbacks = Feedback::where('rating', '>=', 4)
            ->whereBetween('created_at', [$dateFrom, $dateTo])->count();

        return $totalFeedbacks > 0 ? round(($satisfiedFeedbacks / $totalFeedbacks) * 100, 2) : 0;
    }

    /**
     * Generate report summary
     */
    private function generateReportSummary($data, $dateFrom, $dateTo)
    {
        $summary = [
            'period_summary' => "Report covering " . $dateFrom->format('M d, Y') . " to " . $dateTo->format('M d, Y'),
            'key_metrics' => [],
            'trends' => [],
            'recommendations' => []
        ];

        // Key metrics
        if (isset($data['overview'])) {
            $summary['key_metrics'] = [
                'New users registered: ' . ($data['overview']['totals']['total_users'] ?? 0),
                'Total document requests: ' . ($data['overview']['totals']['total_requests'] ?? 0),
                'Blotter cases filed: ' . ($data['overview']['totals']['total_blotters'] ?? 0),
                'Average satisfaction rating: ' . ($data['overview']['averages']['feedback_rating'] ?? 0) . '/5'
            ];
        }

        // Performance indicators
        if (isset($data['requests'])) {
            $completionRate = $data['requests']['performance_metrics']['completion_rate'] ?? 0;
            $processingTime = $data['requests']['performance_metrics']['average_processing_time_days'] ?? 0;

            $summary['trends'][] = "Document completion rate: " . $completionRate . "%";
            $summary['trends'][] = "Average processing time: " . $processingTime . " days";
        }        if (isset($data['feedbacks'])) {
            $satisfactionRate = $data['feedbacks']['summary']['satisfaction_rate'] ?? 0;
            $summary['trends'][] = "Customer satisfaction rate: " . $satisfactionRate . "%";
        }

        // Basic recommendations
        if (isset($data['requests']['performance_metrics']['completion_rate']) && $data['requests']['performance_metrics']['completion_rate'] < 80) {
            $summary['recommendations'][] = "Consider improving document processing workflow to increase completion rate";
        }

        if (isset($data['feedbacks']['summary']['average_rating']) && $data['feedbacks']['summary']['average_rating'] < 4) {
            $summary['recommendations'][] = "Focus on service quality improvement to increase satisfaction ratings";
        }

        return $summary;
    }

    /**
     * Calculate completion rate for requests
     */
    private function calculateCompletionRate($dateFrom, $dateTo)
    {
        $totalRequests = RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $completedRequests = RequestDocument::where('status', 'released')
            ->whereBetween('created_at', [$dateFrom, $dateTo])->count();

        return $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 2) : 0;
    }

    /**
     * Calculate user growth rate
     */
    private function calculateUserGrowthRate($dateFrom, $dateTo)
    {
        $currentPeriodUsers = Account::whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $periodLength = $dateFrom->diffInDays($dateTo);

        // Calculate previous period
        $previousDateFrom = $dateFrom->copy()->subDays($periodLength);
        $previousDateTo = $dateFrom->copy()->subDay();
        $previousPeriodUsers = Account::whereBetween('created_at', [$previousDateFrom, $previousDateTo])->count();

        if ($previousPeriodUsers == 0) {
            return $currentPeriodUsers > 0 ? 100 : 0;
        }

        return round((($currentPeriodUsers - $previousPeriodUsers) / $previousPeriodUsers) * 100, 2);
    }

    /**
     * Calculate median processing time
     */
    private function calculateMedianProcessingTime($dateFrom, $dateTo)
    {
        $processingTimes = RequestDocument::where('status', 'released')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->selectRaw('DATEDIFF(updated_at, created_at) as processing_days')
            ->orderBy('processing_days')
            ->pluck('processing_days')
            ->toArray();

        if (empty($processingTimes)) {
            return 0;
        }

        $count = count($processingTimes);
        $middle = floor($count / 2);

        if ($count % 2 == 0) {
            return ($processingTimes[$middle - 1] + $processingTimes[$middle]) / 2;
        } else {
            return $processingTimes[$middle];
        }
    }

    /**
     * Calculate seasonal request patterns
     */
    private function calculateSeasonalRequestPatterns($dateFrom, $dateTo)
    {
        return RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw('QUARTER(created_at) as quarter'),
                DB::raw('count(*) as requests'),
                DB::raw('AVG(CASE WHEN status = "released" THEN DATEDIFF(updated_at, created_at) END) as avg_processing_time')
            )
            ->groupBy('quarter')
            ->orderBy('quarter')
            ->get()
            ->map(function ($item) {
                $quarters = ['Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)'];
                return [
                    'quarter' => $quarters[$item->quarter - 1] ?? 'Q' . $item->quarter,
                    'requests' => $item->requests,
                    'avg_processing_time' => round($item->avg_processing_time ?? 0, 2)
                ];
            });
    }

    /**
     * Calculate feedback response rate
     */
    private function calculateFeedbackResponseRate($dateFrom, $dateTo)
    {
        $totalRequests = RequestDocument::where('status', 'released')
            ->whereBetween('created_at', [$dateFrom, $dateTo])->count();
        $requestsWithFeedback = RequestDocument::where('status', 'released')
            ->whereBetween('request_documents.created_at', [$dateFrom, $dateTo])
            ->join('feedbacks', 'request_documents.requestor', '=', 'feedbacks.user')
            ->distinct('request_documents.id')
            ->count();

        return $totalRequests > 0 ? round(($requestsWithFeedback / $totalRequests) * 100, 2) : 0;
    }

    /**
     * Calculate median rating
     */
    private function calculateMedianRating($dateFrom, $dateTo)
    {
        $ratings = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
            ->orderBy('rating')
            ->pluck('rating')
            ->toArray();

        if (empty($ratings)) {
            return 0;
        }

        $count = count($ratings);
        $middle = floor($count / 2);

        if ($count % 2 == 0) {
            return ($ratings[$middle - 1] + $ratings[$middle]) / 2;
        } else {
            return $ratings[$middle];
        }
    }

    /**
     * Calculate mode rating (most frequent rating)
     */
    private function calculateModeRating($dateFrom, $dateTo)
    {
        $ratingCounts = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderByDesc('count')
            ->first();

        return $ratingCounts ? $ratingCounts->rating : 0;
    }

    /**
     * Calculate rating standard deviation
     */
    private function calculateRatingStandardDeviation($dateFrom, $dateTo)
    {
        $ratings = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
            ->pluck('rating')
            ->toArray();

        if (empty($ratings)) {
            return 0;
        }

        $mean = array_sum($ratings) / count($ratings);
        $squaredDifferences = array_map(function($rating) use ($mean) {
            return pow($rating - $mean, 2);
        }, $ratings);

        $variance = array_sum($squaredDifferences) / count($ratings);
        return round(sqrt($variance), 2);
    }

    /**
     * Calculate NPS Score (Net Promoter Score)
     */
    private function calculateNPSScore($dateFrom, $dateTo)
    {
        $totalFeedbacks = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->count();

        if ($totalFeedbacks == 0) {
            return 0;
        }

        $promoters = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('rating', [4, 5])->count();
        $detractors = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereIn('rating', [1, 2])->count();

        $promoterPercentage = ($promoters / $totalFeedbacks) * 100;
        $detractorPercentage = ($detractors / $totalFeedbacks) * 100;

        return round($promoterPercentage - $detractorPercentage, 2);
    }

    /**
     * Calculate rating trend
     */
    private function calculateRatingTrend($dateFrom, $dateTo)
    {
        $monthlyRatings = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(DB::raw('MONTH(created_at) as month'), DB::raw('AVG(rating) as avg_rating'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        if ($monthlyRatings->count() < 2) {
            return 'insufficient_data';
        }

        $first = $monthlyRatings->first()->avg_rating;
        $last = $monthlyRatings->last()->avg_rating;

        if ($last > $first + 0.2) {
            return 'improving';
        } elseif ($last < $first - 0.2) {
            return 'declining';
        } else {
            return 'stable';
        }
    }

    /**
     * Calculate seasonal satisfaction
     */
    private function calculateSeasonalSatisfaction($dateFrom, $dateTo)
    {
        return Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select(
                DB::raw('QUARTER(created_at) as quarter'),
                DB::raw('AVG(rating) as avg_rating'),
                DB::raw('count(*) as feedbacks')
            )
            ->groupBy('quarter')
            ->orderBy('quarter')
            ->get()
            ->map(function ($item) {
                $quarters = ['Q1 (Jan-Mar)', 'Q2 (Apr-Jun)', 'Q3 (Jul-Sep)', 'Q4 (Oct-Dec)'];
                return [
                    'quarter' => $quarters[$item->quarter - 1] ?? 'Q' . $item->quarter,
                    'avg_rating' => round($item->avg_rating, 2),
                    'feedbacks' => $item->feedbacks
                ];
            });
    }
}
