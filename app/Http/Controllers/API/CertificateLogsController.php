<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CertificateLog;
use Illuminate\Support\Facades\Validator;
use Exception;

class CertificateLogsController extends Controller
{
    // 1. Create a certificate log
    public function create(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'document_request' => 'required|exists:request_documents,id',
                'staff' => 'required|exists:accounts,id',
                'remark' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $log = CertificateLog::create($validator->validated());
            return response()->json($log, 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. Get all certificate logs with filters and pagination, including date range
    public function index(Request $request)
    {
        $query = CertificateLog::query();
        if ($request->has('document_request')) {
            $query->where('document_request', $request->input('document_request'));
        }
        if ($request->has('staff')) {
            $query->where('staff', $request->input('staff'));
        }
        if ($request->has('remark')) {
            $query->where('remark', 'like', '%' . $request->input('remark') . '%');
        }
        if ($request->has(['date_from', 'date_to'])) {
            $query->whereBetween('created_at', [$request->input('date_from'), $request->input('date_to')]);
        }
        // Filter by requestor (on related request_documents)
        if ($request->has('requestor')) {
            $query->whereHas('documentRequest', function($q) use ($request) {
                $q->where('requestor', $request->input('requestor'));
            });
        }
        // Filter by document (on related request_documents)
        if ($request->has('document')) {
            $query->whereHas('documentRequest', function($q) use ($request) {
                $q->where('document', $request->input('document'));
            });
        }
        $perPage = $request->input('per_page', 10);
        $logs = $query->with([
            'documentRequest.account',
            'documentRequest.document',
            'staffAccount'
        ])->paginate($perPage);
        return response()->json($logs);
    }

    // 3. Get certificate log by id
    public function show($id)
    {
        $log = CertificateLog::with([
            'documentRequest.account',
            'documentRequest.document',
            'staffAccount'
        ])->findOrFail($id);
        return response()->json($log);
    }
}
