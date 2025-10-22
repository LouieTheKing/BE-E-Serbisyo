<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use App\Models\RejectedAccount;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountAcceptedMail;
use App\Mail\AccountRejectedMail;
use Exception;
use App\Traits\LogsActivity;

class AccountController extends Controller
{
    use LogsActivity;
    // 1. Update Informations with type
    public function updateInformation(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string',
                'last_name' => 'required|string',
                'sex' => 'required|string',
                'birthday' => 'required|date',
                'contact_no' => 'required|string',
                'birth_place' => 'required|string',
                'municipality' => 'required|string',
                'barangay' => 'required|string',
                'house_no' => 'required|string',
                'zip_code' => 'required|string',
                'street' => 'required|string',
                'type' => 'required|string|in:residence,admin,staff',
                'pwd_number' => 'nullable|string',
                'single_parent_number' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $account = Account::findOrFail($id);
            $account->update($request->only([
                'first_name', 'middle_name', 'last_name', 'suffix', 'sex', 'nationality', 'birthday', 'contact_no', 'birth_place',
                'municipality', 'barangay', 'house_no', 'zip_code', 'street', 'type', 'pwd_number', 'single_parent_number'
            ]));

            // Log the activity
            $this->logActivity('Account Management', "Updated profile information for user: {$account->first_name} {$account->last_name} ({$account->email})");

            return response()->json(['message' => 'Account information updated successfully', 'account' => $account]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. Update status
    public function updateStatus(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'status' => 'required|in:active,inactive,pending',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $account = Account::findOrFail($id);
            $oldStatus = $account->status;
            $account->status = $request->status;
            $account->save();

            // Log the activity
            $this->logActivity('Account Management', "Changed account status from '{$oldStatus}' to '{$request->status}' for user: {$account->first_name} {$account->last_name} ({$account->email})");

            return response()->json(['message' => 'Account status updated successfully', 'account' => $account]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 3. Update password
    public function updatePassword(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $account = Account::findOrFail($id);

            // Ensure $account->password is the hashed password from the database
            if (!$account || !Hash::check($request->current_password, $account->getOriginal('password'))) {
                return response()->json(['error' => ['current_password' => ['Current password is incorrect.']]], 403);
            }

            $account->password = Hash::make($request->password);
            $account->save();

            // Log the activity
            $this->logActivity('Account Management', "Password updated for user: {$account->first_name} {$account->last_name} ({$account->email})");

            return response()->json(['message' => 'Password updated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 4. Get current authenticated user
    public function current(Request $request)
    {
        $user = $request->user()->load('accountProof');
        return response()->json($user);
    }

    // 5. Get user by id
    public function show($id)
    {
        $user = Account::with('accountProof')->find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    // 6. Get all accounts with pagination, filter, and sorting
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $query = Account::query()->with('accountProof');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%{$search}%"])
                  ->orWhereRaw("CONCAT(first_name, ' ', middle_name, ' ', last_name) like ?", ["%{$search}%"]);
            });
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'created_at'); // default sort by date
        $order = $request->query('order', 'desc'); // default descending

        // Validate allowed columns
        $allowedSorts = ['name', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        // If sorting by name, use last_name + first_name
        if ($sortBy === 'name') {
            $query->orderBy('last_name', $order)->orderBy('first_name', $order);
        } else {
            $query->orderBy($sortBy, $order);
        }

        $accounts = $query->paginate($perPage);
        return response()->json($accounts);
    }


    // 7. Reject (delete) account
    public function rejectAccount(Request $request, $id)
    {
        try {
            $account = Account::findOrFail($id);
            $rejectedData = $account->toArray();
            // Ensure password is included
            $rejectedData['password'] = $account->getOriginal('password');
            unset($rejectedData['id']); // Let the rejected_accounts table auto-increment its own id

            // Check if reason is provided
            if (!$request->has('reason') || is_null($request->input('reason'))) {
                return response()->json(['error' => ['reason' => ['Reason is required.']]], 422);
            }

            $rejectedData['reason'] = $request->input('reason');
            $rejectedAccount = RejectedAccount::create($rejectedData);

            // Send rejection email
            Mail::to($account->email)->send(new AccountRejectedMail($rejectedAccount));

            // Log the activity
            $this->logActivity('Account Management', "Rejected and deleted account for user: {$rejectedData['email']} - Reason: {$rejectedData['reason']}");

            $account->delete();
            return response()->json(['message' => 'Account rejected and deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function acceptAccount(Request $request, $id)
    {
        try {
            $account = Account::with('accountProof')->findOrFail($id);
            $account->status = 'active';
            $account->save();

            // Send acceptance email
            Mail::to($account->email)->send(new AccountAcceptedMail($account));

            // Log the activity
            $this->logActivity('Account Management', "Accepted account for user: {$account->first_name} {$account->last_name} ({$account->email})");

            return response()->json(['message' => 'Account has been accepted', 'account' => $account]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // 8. Update profile picture
    public function updateProfilePicture(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_picture' => 'required|image|mimes:jpeg,jpg,png|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $account = Account::findOrFail($id);
            $file = $request->file('profile_picture');
            $filename = 'profile_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            // Ensure directory exists
            \Illuminate\Support\Facades\Storage::disk('public')->makeDirectory('profile_pictures');
            $path = $file->storeAs('profile_pictures', $filename, 'public');
            $account->profile_picture_path = '/storage/' . $path;
            $account->save();

            // Log the activity
            $this->logActivity('Account Management', "Updated profile picture for user: {$account->first_name} {$account->last_name} ({$account->email})");

            return response()->json(['message' => 'Profile picture updated successfully', 'profile_picture_path' => $account->profile_picture_path]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateType(Request $request, $id)
    {
        try {
            // ✅ Only admin can update type
            if (!$request->user() || !in_array($request->user()->type, ['admin', 'staff'])) {
                return response()->json(['error' => 'Unauthorized. Only admins can update account types.'], 403);
            }

            $validator = Validator::make($request->all(), [
                'type' => 'required|string|in:residence,admin,staff',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $account = Account::findOrFail($id);
            $oldType = $account->type;
            $account->type = $request->type;
            $account->save();

            // Log the activity
            $this->logActivity('Account Management', "Changed account type from '{$oldType}' to '{$request->type}' for user: {$account->first_name} {$account->last_name} ({$account->email})");

            return response()->json([
                'message' => 'Account type updated successfully',
                'account' => $account
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
