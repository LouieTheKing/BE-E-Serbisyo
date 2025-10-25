<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\Official;
use App\Models\RequestDocument;
use App\Models\Document;
use App\Models\Blotter;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SystemStatsController extends Controller
{
    /**
     * Return combined system statistics in one endpoint
     */
    public function index(Request $request)
    {
        $dateFrom = $request->input('date_from') ? Carbon::parse($request->input('date_from'))->startOfDay() : Carbon::now()->subMonth()->startOfDay();
        $dateTo = $request->input('date_to') ? Carbon::parse($request->input('date_to'))->endOfDay() : Carbon::now()->endOfDay();

        // Global (non-date-dependent) counts
        $totalUsers = Account::count();
        $officials = Official::count();

        // Filtered accounts (only within date range)
        $filteredAccounts = Account::whereBetween('created_at', [$dateFrom, $dateTo]);

        // Gender breakdown within date range
        $maleCount = (clone $filteredAccounts)
            ->whereRaw("LOWER(TRIM(COALESCE(sex, ''))) IN (?, ?)", ['male', 'm'])
            ->count();

        $femaleCount = (clone $filteredAccounts)
            ->whereRaw("LOWER(TRIM(COALESCE(sex, ''))) IN (?, ?)", ['female', 'f'])
            ->count();

        // Average age (filtered)
        $avgAge = (clone $filteredAccounts)
            ->whereNotNull('birthday')
            ->selectRaw('AVG(TIMESTAMPDIFF(YEAR, birthday, CURDATE())) as avg_age')
            ->value('avg_age');
        $avgAge = $avgAge !== null ? round((float) $avgAge, 2) : 0;

        // Use raw SQL SELECT for senior, PWD, and single parent counts
        $dateFromStr = $dateFrom->format('Y-m-d H:i:s');
        $dateToStr = $dateTo->format('Y-m-d H:i:s');

        $seniorCitizen = DB::selectOne(
            "SELECT COUNT(*) as count FROM accounts WHERE created_at BETWEEN ? AND ? AND birthday IS NOT NULL AND TIMESTAMPDIFF(YEAR, birthday, CURDATE()) >= 60",
            [$dateFromStr, $dateToStr]
        )->count;

        $totalPWD = DB::selectOne(
            "SELECT COUNT(*) as count FROM accounts WHERE created_at BETWEEN ? AND ? AND pwd_number IS NOT NULL AND pwd_number <> ''",
            [$dateFromStr, $dateToStr]
        )->count;

        $totalSingleParent = DB::selectOne(
            "SELECT COUNT(*) as count FROM accounts WHERE created_at BETWEEN ? AND ? AND single_parent_number IS NOT NULL AND single_parent_number <> ''",
            [$dateFromStr, $dateToStr]
        )->count;

        // Requests within date range
        $filteredRequests = RequestDocument::whereBetween('request_documents.created_at', [$dateFrom, $dateTo]);

        $totalRequests = (clone $filteredRequests)->count();
        $completedRequests = (clone $filteredRequests)->where('status', 'released')->count();
        $completionRate = $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 2) : 0;

        // Most requested document (filtered)
        $mostRequested = RequestDocument::join('documents', 'request_documents.document', '=', 'documents.id')
            ->whereBetween('request_documents.created_at', [$dateFrom, $dateTo]) // ✅ disambiguated column
            ->select('documents.id as document_id', 'documents.document_name', DB::raw('count(*) as total'))
            ->groupBy('documents.id', 'documents.document_name')
            ->orderByDesc('total')
            ->first();


        $mostRequestedDoc = $mostRequested ? [
            'document_id' => $mostRequested->document_id,
            'document_name' => $mostRequested->document_name,
            'count' => (int) $mostRequested->total
        ] : null;

        // Average processing time (filtered)
        $avgProcessing = (clone $filteredRequests)
            ->where('status', 'released')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, created_at, updated_at)) as avg_minutes')
            ->value('avg_minutes');
        $avgProcessing = $avgProcessing !== null ? round((float) $avgProcessing, 2) : 0;

        // Pending (filtered)
        $pendingAccount = (clone $filteredAccounts)->where('status', 'pending')->count();
        $pendingDocumentRequest = (clone $filteredRequests)->where('status', 'pending')->count();

        // User types (filtered)
        $userTypes = (clone $filteredAccounts)
            ->select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(fn($item) => [$item->type => (int) $item->count]);

        // Document distribution (filtered)
        $docTypeDistribution = (clone $filteredRequests)
            ->join('documents', 'request_documents.document', '=', 'documents.id')
            ->select('documents.id as document_id', 'documents.document_name', DB::raw('count(*) as count'))
            ->groupBy('documents.id', 'documents.document_name')
            ->orderByDesc('count')
            ->get()
            ->map(fn($item) => [
                'document_id' => $item->document_id,
                'document_name' => $item->document_name,
                'count' => (int) $item->count
            ]);

        // Monthly breakdown (always past 12 months)
        $months = [];
        $start = $request->input('start_month')
            ? Carbon::parse($request->input('start_month'))->startOfMonth()
            : Carbon::now()->startOfMonth()->subMonths(11);

        for ($i = 0; $i < 12; $i++) {
            $mStart = $start->copy()->addMonths($i)->startOfMonth();
            $mEnd = $mStart->copy()->endOfMonth();

            $months[] = [
                'month' => $mStart->format('Y-m'),
                'blotter_count' => Blotter::whereBetween('created_at', [$mStart, $mEnd])->count(),
                'request_count' => RequestDocument::whereBetween('created_at', [$mStart, $mEnd])->count(),
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'officials' => $officials,
                'senior_citizen_60_plus' => $seniorCitizen,
                'total_pwd' => $totalPWD,
                'total_single_parent' => $totalSingleParent,
                'total_requests' => $totalRequests,
                'completion_rate_percent' => $completionRate,
                'most_requested_document' => $mostRequestedDoc,
                'average_processing_time_minutes' => $avgProcessing,
                'average_age' => $avgAge,
                'male_count' => $maleCount,
                'female_count' => $femaleCount,
                'user_type' => $userTypes,
                'pending_accounts' => $pendingAccount,
                'pending_document_requests' => $pendingDocumentRequest,
                'document_type_distribution' => $docTypeDistribution,
                'monthly_blotter_and_request' => $months,
            ],
            'date_range' => [
                'from' => $dateFrom->format('Y-m-d'),
                'to' => $dateTo->format('Y-m-d'),
            ]
        ]);
    }

}
