<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Official;
use App\Models\Account;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountRegisteredMail;
use App\Traits\LogsActivity;

class OfficialsController extends Controller
{
    use LogsActivity;
    // 1. Create Official
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'position' => 'required|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120',
            'term_start' => 'required|date',
            'term_end' => 'required|date|after_or_equal:term_start',
            'status' => 'required|in:active,inactive',
            // Account fields
            'email' => 'required|email|unique:accounts,email',
            'first_name' => 'required|string',
            'middle_name' => 'nullable|string',
            'last_name' => 'required|string',
            'suffix' => 'nullable|string',
            'sex' => 'required|string',
            'nationality' => 'nullable|string',
            'birthday' => 'required|date',
            'contact_no' => 'required|string',
            'birth_place' => 'required|string',
            'municipality' => 'required|string',
            'barangay' => 'required|string',
            'house_no' => 'required|string',
            'zip_code' => 'required|string',
            'street' => 'required|string',
            'civil_status' => 'required|string|in:single,married,widowed,divorced,separated',
            'type' => 'required|string|in:staff,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Create account for the official first
            $account = Account::create([
                'email' => $request->email,
                'password' => Hash::make($request->email), // Default password is the email
                'first_name' => $request->first_name,
                'middle_name' => $request->middle_name,
                'last_name' => $request->last_name,
                'suffix' => $request->suffix,
                'sex' => $request->sex,
                'nationality' => $request->nationality ?? 'Filipino',
                'birthday' => $request->birthday,
                'contact_no' => $request->contact_no,
                'birth_place' => $request->birth_place,
                'municipality' => $request->municipality,
                'barangay' => $request->barangay,
                'house_no' => $request->house_no,
                'zip_code' => $request->zip_code,
                'street' => $request->street,
                'civil_status' => $request->civil_status,
                'type' => $request->type,
                'status' => 'active', // Account is active immediately
            ]);

            // Upload official image
            $imagePath = null;
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                // Ensure directory exists
                Storage::disk('public')->makeDirectory('officials');
                $imagePath = $image->store('officials', 'public');
            }

            // Create official record
            $official = Official::create([
                'account_id' => $account->id,
                'position' => $request->position,
                'image_path' => $imagePath,
                'term_start' => $request->term_start,
                'term_end' => $request->term_end,
                'status' => $request->status,
            ]);

            // Send email notification
            Mail::to($account->email)->send(new AccountRegisteredMail($account));

            // Log the activity
            $this->logActivity('Officials Management', "Created new official: {$account->first_name} {$account->last_name} - Position: {$request->position}");

            DB::commit();

            return response()->json([
                'message' => 'Official and account created successfully. Email sent.',
                'official' => $official->load('account')
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to create official and account',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // 2. Update Official
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'position' => 'sometimes|required|string|max:255',
            'image' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:5120',
            'term_start' => 'sometimes|required|date',
            'term_end' => 'sometimes|required|date|after_or_equal:term_start',
            'status' => 'sometimes|required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $official = Official::find($id);
        if (!$official) {
            return response()->json(['error' => 'Official not found'], 404);
        }

        DB::beginTransaction();
        try {
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($official->image_path && Storage::disk('public')->exists($official->image_path)) {
                    Storage::disk('public')->delete($official->image_path);
                }
                $image = $request->file('image');
                // Ensure directory exists
                Storage::disk('public')->makeDirectory('officials');
                $official->image_path = $image->store('officials', 'public');
            }
            if ($request->has('position')) $official->position = $request->position;
            if ($request->has('term_start')) $official->term_start = $request->term_start;
            if ($request->has('term_end')) $official->term_end = $request->term_end;
            if ($request->has('status')) $official->status = $request->status;
            $official->save();

            // Log the activity
            $this->logActivity('Officials Management', "Updated official: {$official->account->first_name} {$official->account->last_name} - Position: {$official->position}");

            DB::commit();
            return response()->json(['official' => $official->load('account')], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update official', 'message' => $e->getMessage()], 500);
        }
    }

    // 3. Update Status
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:active,inactive',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        $official = Official::find($id);
        if (!$official) {
            return response()->json(['error' => 'Official not found'], 404);
        }
        try {
            $oldStatus = $official->status;
            $official->status = $request->status;
            $official->save();

            // Log the activity
            $this->logActivity('Officials Management', "Changed status from '{$oldStatus}' to '{$request->status}' for official: {$official->account->first_name} {$official->account->last_name}");

            return response()->json(['official' => $official->load('account')], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update status', 'message' => $e->getMessage()], 500);
        }
    }

    // 4. Get all officials with status filter, pagination, and sorting
    public function index(Request $request)
    {
        $status = $request->query('status');
        $perPage = $request->query('per_page', 10);

        $sortBy = $request->query('sort_by', 'term_start'); // default sorting
        $order = $request->query('order', 'asc'); // default ascending

        $allowedSorts = ['position', 'term_start', 'term_end'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'term_start';
        }

        try {
            $query = Official::with('account');

            if ($status) {
                $query->where('status', $status);
            }

            $query->orderBy($sortBy, $order);

            $officials = $query->paginate($perPage);
            return response()->json($officials, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch officials', 'message' => $e->getMessage()], 500);
        }
    }


    // 5. Get official by id
    public function show($id)
    {
        try {
            $official = Official::with('account')->find($id);
            if (!$official) {
                return response()->json(['error' => 'Official not found'], 404);
            }
            return response()->json(['official' => $official], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch official', 'message' => $e->getMessage()], 500);
        }
    }
}
