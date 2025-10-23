<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Blotter;
use App\Models\BlotterHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Traits\LogsActivity;
use App\Mail\BlotterCreatedMail;
use App\Mail\BlotterStatusUpdateMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class BlotterController extends Controller
{
    use LogsActivity;
    /**
     * List blotters with filters & pagination
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $status = $request->get('status');
        $sortBy = $request->get('sort_by', 'created_at'); // column to sort
        $sortOrder = $request->get('order', 'desc'); // asc or desc

        $query = Blotter::with(['createdBy:id,first_name,last_name,email']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($request->get('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('complainant_name', 'like', "%$search%")
                ->orWhere('respondent_name', 'like', "%$search%")
                ->orWhere('case_number', 'like', "%$search%")
                ->orWhere('complaint_details', 'like', "%$search%");
            });
        }
 
        // Apply sorting safely
        $allowedSorts = ['case_number', 'date_filed', 'status', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        $query->orderBy($sortBy, $sortOrder);

        return response()->json([
            'success' => true,
            'data' => $query->paginate($perPage)
        ]);
    }


    /**
     * Store blotter
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'complainant_name' => 'required|string',
            'respondent_name' => 'required|string',
            'additional_respondent' => 'nullable|array',
            'complaint_details' => 'required|string',
            'relief_sought' => 'required|string',
            'date_filed' => 'required|date',
            'status' => 'in:filed,ongoing,settled,reopen,unsettled',
            'case_type' => 'required|string|max:255',
            'attached_proof' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $caseNumber = strtoupper('BLT-' . date('Ymd') . '-' . Str::random(5));

        // Handle file upload if provided
        $attachedProofPath = null;
        if ($request->hasFile('attached_proof')) {
            try {
                $file = $request->file('attached_proof');
                $fileName = $caseNumber . '_proof_' . time() . '.' . $file->getClientOriginalExtension();
                $attachedProofPath = $file->storeAs('blotter_proofs', $fileName, 'public');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to upload attached proof',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        $blotter = Blotter::create([
            'case_number' => $caseNumber,
            'complainant_name' => $request->complainant_name,
            'respondent_name' => $request->respondent_name,
            'additional_respondent' => $request->additional_respondent,
            'complaint_details' => $request->complaint_details,
            'case_type' => $request->case_type,
            'relief_sought' => $request->relief_sought,
            'date_filed' => $request->date_filed,
            'received_by' => auth()->user()->first_name . ' ' . auth()->user()->last_name,
            'created_by' => auth()->id(),
            'status' => $request->status ?? 'filed',
            'attached_proof' => $attachedProofPath,
        ]);

        // Create initial history entry
        BlotterHistory::create([
            'case_number' => $blotter->case_number,
            'status' => $blotter->status,
            'updated_by' => auth()->id(),
            'notes' => 'Initial case filing',
        ]);

        // Log the activity
        $this->logActivity('Blotter Management', "Created new blotter case: {$caseNumber}");

        // Load the created blotter with its relationships
        $blotter->load('createdBy:id,first_name,last_name,email');

        // Send email notification to the complainant (if email is available)
        if ($blotter->createdBy && $blotter->createdBy->email) {
            try {
                Mail::to($blotter->createdBy->email)->send(new BlotterCreatedMail($blotter));
            } catch (\Exception $e) {
                // Log email error but don't fail the request
                \Log::error("Failed to send blotter creation email: " . $e->getMessage());
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Blotter created successfully',
            'data' => $blotter
        ], 201);
    }

    /**
     * Show blotter by case number
     */
    public function show($case_number)
    {
        $blotter = Blotter::with([
            'createdBy:id,first_name,last_name,email',
            'statusHistory' => function($query) {
                $query->with('updatedBy:id,first_name,last_name,email')
                      ->orderBy('created_at', 'desc');
            }
        ])->where('case_number', $case_number)->first();

        if (!$blotter) {
            return response()->json([
                'success' => false,
                'message' => 'Blotter not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $blotter
        ], 200);
    }



    /**
     * Update blotter
     */
    public function update(Request $request, $case_number)
    {
        $blotter = Blotter::where('case_number', $case_number)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'complainant_name' => 'sometimes|string',
            'respondent_name' => 'sometimes|string',
            'additional_respondent' => 'nullable|array',
            'complaint_details' => 'sometimes|string',
            'relief_sought' => 'sometimes|string',
            'case_type' => 'sometimes|string|max:255',
            'date_filed' => 'sometimes|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $blotter->update($request->all());

        // Log the activity
        $this->logActivity('Blotter Management', "Updated blotter case: {$blotter->case_number}");

        // Load relationships for response
        $blotter->load('createdBy:id,first_name,last_name,email');

        return response()->json([
            'success' => true,
            'message' => 'Blotter updated successfully',
            'data' => $blotter
        ]);
    }

    /**
     * Delete blotter
     */
    public function destroy($case_number)
    {
        $blotter = Blotter::where('case_number', $case_number)->firstOrFail();

        // Delete attached proof file if exists
        if ($blotter->attached_proof && Storage::disk('public')->exists($blotter->attached_proof)) {
            Storage::disk('public')->delete($blotter->attached_proof);
        }

        // Log the activity before deletion
        $this->logActivity('Blotter Management', "Deleted blotter case: {$blotter->case_number}");

        $blotter->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blotter deleted successfully'
        ]);
    }

    /**
     * Update blotter status with email notification
     */
    public function updateStatus(Request $request, $case_number)
    {
        $blotter = Blotter::where('case_number', $case_number)->firstOrFail();

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:filed,ongoing,settled,reopen,unsettled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        // Store old status for email notification
        $oldStatus = $blotter->status;
        $newStatus = $request->status;

        // Update only if status is different
        if ($oldStatus !== $newStatus) {
            $blotter->update(['status' => $newStatus]);

            // Create history entry
            BlotterHistory::create([
                'case_number' => $blotter->case_number,
                'status' => $newStatus,
                'updated_by' => auth()->id(),
                'notes' => $request->notes,
            ]);

            // Log the activity
            $this->logActivity('Blotter Management', "Updated blotter case status from {$oldStatus} to {$newStatus}: {$blotter->case_number}");

            // Load relationships for response
            $blotter->load('createdBy:id,first_name,last_name,email');

            // Send email notification to the complainant (if email is available)
            if ($blotter->createdBy && $blotter->createdBy->email) {
                try {
                    Mail::to($blotter->createdBy->email)->send(new BlotterStatusUpdateMail($blotter, $oldStatus));
                } catch (\Exception $e) {
                    // Log email error but don't fail the request
                    \Log::error("Failed to send blotter status update email: " . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Blotter status updated successfully',
                'data' => $blotter,
                'old_status' => $oldStatus,
                'new_status' => $newStatus
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No status change detected'
        ], 400);
    }

    /**
     * Get blotter status history
     */
    public function getHistory($case_number)
    {
        $blotter = Blotter::where('case_number', $case_number)->first();
        
        if (!$blotter) {
            return response()->json([
                'success' => false,
                'message' => 'Blotter not found'
            ], 404);
        }
        
        $history = BlotterHistory::with(['updatedBy:id,first_name,last_name,email'])
            ->where('case_number', $case_number)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'case_number' => $case_number,
                'current_status' => $blotter->status,
                'history' => $history
            ]
        ]);
    }
}
