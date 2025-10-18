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

            // Add summary if requested
            if ($format === 'summary') {
                $report['summary'] = $this->generateReportSummary($report['data'], $dateFrom, $dateTo);
            }

            return response()->json([
                'success' => true,
                'report' => $report
            ]);

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
        return [
            'total_users' => Account::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_requests' => RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_blotters' => Blotter::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_feedbacks' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_announcements' => Announcement::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_certificate_logs' => CertificateLog::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'total_activity_logs' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'average_rating' => round(Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->avg('rating') ?? 0, 2)
        ];
    }

    /**
     * Generate user report data
     */
    private function generateUserReport($dateFrom, $dateTo, $reportType)
    {
        $data = [
            'total_registered' => Account::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
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
        ];

        if ($reportType === 'yearly') {
            $data['monthly_breakdown'] = Account::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
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

        $data = [
            'total_requests' => $totalRequests,
            'completed_requests' => $completedRequests,
            'completion_rate' => $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 2) : 0,
            'by_status' => RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get(),
            'by_document_type' => RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])
                ->join('documents', 'request_documents.document', '=', 'documents.id')
                ->select('documents.document_name', DB::raw('count(*) as count'))
                ->groupBy('documents.document_name')
                ->orderByDesc('count')
                ->get(),
            'average_processing_time' => $this->calculateAverageProcessingTime($dateFrom, $dateTo)
        ];

        if ($reportType === 'yearly') {
            $data['monthly_breakdown'] = RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
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
        $data = [
            'total_feedbacks' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->count(),
            'average_rating' => round(Feedback::whereBetween('created_at', [$dateFrom, $dateTo])->avg('rating') ?? 0, 2),
            'by_rating' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('rating', DB::raw('count(*) as count'))
                ->groupBy('rating')
                ->orderBy('rating')
                ->get(),
            'by_category' => Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select('category', DB::raw('count(*) as count'))
                ->groupBy('category')
                ->get(),
            'satisfaction_rate' => $this->calculateSatisfactionRate($dateFrom, $dateTo)
        ];

        if ($reportType === 'yearly') {
            $data['monthly_breakdown'] = Feedback::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('AVG(rating) as avg_rating'), DB::raw('count(*) as count'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();
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
            'by_staff' => CertificateLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->join('accounts', 'certificate_logs.staff', '=', 'accounts.id')
                ->select(DB::raw('CONCAT(accounts.first_name, " ", accounts.last_name) as staff_name'), DB::raw('count(*) as count'))
                ->groupBy('accounts.id', 'accounts.first_name', 'accounts.last_name')
                ->orderByDesc('count')
                ->get()
        ];

        if ($reportType === 'yearly') {
            $data['monthly_breakdown'] = CertificateLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
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
            'top_users' => ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->join('accounts', 'activity_logs.account', '=', 'accounts.id')
                ->select(DB::raw('CONCAT(accounts.first_name, " ", accounts.last_name) as user_name'), DB::raw('count(*) as count'))
                ->groupBy('accounts.id', 'accounts.first_name', 'accounts.last_name')
                ->orderByDesc('count')
                ->limit(10)
                ->get()
        ];

        if ($reportType === 'yearly') {
            $data['monthly_breakdown'] = ActivityLog::whereBetween('created_at', [$dateFrom, $dateTo])
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('count(*) as count'))
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
            'most_requested' => RequestDocument::whereBetween('created_at', [$dateFrom, $dateTo])
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
                'New users registered: ' . ($data['overview']['total_users'] ?? 0),
                'Total document requests: ' . ($data['overview']['total_requests'] ?? 0),
                'Blotter cases filed: ' . ($data['overview']['total_blotters'] ?? 0),
                'Average satisfaction rating: ' . ($data['overview']['average_rating'] ?? 0) . '/5'
            ];
        }

        // Performance indicators
        if (isset($data['requests'])) {
            $completionRate = $data['requests']['completion_rate'] ?? 0;
            $processingTime = $data['requests']['average_processing_time'] ?? 0;

            $summary['trends'][] = "Document completion rate: " . $completionRate . "%";
            $summary['trends'][] = "Average processing time: " . $processingTime . " days";
        }

        if (isset($data['feedbacks'])) {
            $satisfactionRate = $data['feedbacks']['satisfaction_rate'] ?? 0;
            $summary['trends'][] = "Customer satisfaction rate: " . $satisfactionRate . "%";
        }

        // Basic recommendations
        if (isset($data['requests']['completion_rate']) && $data['requests']['completion_rate'] < 80) {
            $summary['recommendations'][] = "Consider improving document processing workflow to increase completion rate";
        }

        if (isset($data['feedbacks']['average_rating']) && $data['feedbacks']['average_rating'] < 4) {
            $summary['recommendations'][] = "Focus on service quality improvement to increase satisfaction ratings";
        }

        return $summary;
    }
}
