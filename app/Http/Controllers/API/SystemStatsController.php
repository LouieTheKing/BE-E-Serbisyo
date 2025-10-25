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

        // Basic counts
        $totalUsers = Account::count();
        $officials = Official::count();

    // Gender breakdown (tolerant of variants like 'M','F','Male','Female', trailing spaces or NULL)
    // Normalize via LOWER(TRIM(...)) and match common variants
    $maleCount = Account::whereRaw("LOWER(TRIM(COALESCE(sex, ''))) IN (?, ?)", ['male', 'm'])->count();
    $femaleCount = Account::whereRaw("LOWER(TRIM(COALESCE(sex, ''))) IN (?, ?)", ['female', 'f'])->count();

        // Average age (in years) for accounts with birthday set; fallback to 0 when none
        $avgAge = Account::whereNotNull('birthday')
            ->selectRaw('AVG(TIMESTAMPDIFF(YEAR, birthday, CURDATE())) as avg_age')
            ->value('avg_age');
        $avgAge = $avgAge !== null ? round((float) $avgAge, 2) : 0;

        // Senior citizens (age 60 and above) — using birthday
        $seniorCutoff = Carbon::now()->subYears(60)->endOfDay();
        $seniorCitizen = Account::whereNotNull('birthday')->where('birthday', '<=', $seniorCutoff)->count();

        // PWD and single parent counts (assume presence of pwd_number and single_parent_number)
        $totalPWD = Account::whereNotNull('pwd_number')->where('pwd_number', '<>', '')->count();
        $totalSingleParent = Account::whereNotNull('single_parent_number')->where('single_parent_number', '<>', '')->count();

        // Requests and completion
        $totalRequests = RequestDocument::count();
        $completedRequests = RequestDocument::where('status', 'released')->count();
        $completionRate = $totalRequests > 0 ? round(($completedRequests / $totalRequests) * 100, 2) : 0;

        // Most requested document
        $mostRequested = RequestDocument::join('documents', 'request_documents.document', '=', 'documents.id')
            ->select('documents.id as document_id', 'documents.document_name', DB::raw('count(*) as total'))
            ->groupBy('documents.id', 'documents.document_name')
            ->orderByDesc('total')
            ->first();

        $mostRequestedDoc = $mostRequested ? [
            'document_id' => $mostRequested->document_id,
            'document_name' => $mostRequested->document_name,
            'count' => (int) $mostRequested->total
        ] : null;

        // Average processing time in days (released requests)
        $avgProcessing = RequestDocument::where('status', 'released')
            ->selectRaw('AVG(DATEDIFF(updated_at, created_at)) as avg_days')
            ->value('avg_days');
        $avgProcessing = $avgProcessing !== null ? round((float) $avgProcessing, 2) : 0;

        // Pending counts
        $pendingAccount = Account::where('status', 'pending')->count();
        $pendingDocumentRequest = RequestDocument::where('status', 'pending')->count();

        // User type counts (group by type)
        $userTypes = Account::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->type => (int) $item->count];
            });

        // Document type distribution (by request count)
        $docTypeDistribution = RequestDocument::join('documents', 'request_documents.document', '=', 'documents.id')
            ->whereBetween('request_documents.created_at', [$dateFrom, $dateTo])
            ->select('documents.id as document_id', 'documents.document_name', DB::raw('count(*) as count'))
            ->groupBy('documents.id', 'documents.document_name')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                return [
                    'document_id' => $item->document_id,
                    'document_name' => $item->document_name,
                    'count' => (int) $item->count
                ];
            });

        // Monthly blotter cases and monthly requests — default last 12 months
        $months = [];
        $start = $request->input('start_month') ? Carbon::parse($request->input('start_month'))->startOfMonth() : Carbon::now()->startOfMonth()->subMonths(11);
        for ($i = 0; $i < 12; $i++) {
            $mStart = $start->copy()->addMonths($i)->startOfMonth();
            $mEnd = $mStart->copy()->endOfMonth();

            $months[] = [
                'month' => $mStart->format('Y-m'),
                'blotter_count' => Blotter::whereBetween('created_at', [$mStart, $mEnd])->count(),
                'request_count' => RequestDocument::whereBetween('created_at', [$mStart, $mEnd])->count()
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
                'average_processing_time_days' => $avgProcessing,
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
                'to' => $dateTo->format('Y-m-d')
            ]
        ]);
    }
}
