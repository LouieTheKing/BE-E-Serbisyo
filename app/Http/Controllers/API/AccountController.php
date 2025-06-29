<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

class AccountController extends Controller
{
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
            $account->status = $request->status;
            $account->save();

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

            return response()->json(['message' => 'Password updated successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 4. Get current authenticated user
    public function current(Request $request)
    {
        return response()->json($request->user());
    }

    // 5. Get user by id
    public function show($id)
    {
        $user = Account::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user);
    }

    // 6. Get all accounts with pagination and filter for type and status
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $query = Account::query();

        if ($request->has('type')) {
            $query->where('type', $request->query('type'));
        }
        if ($request->has('status')) {
            $query->where('status', $request->query('status'));
        }

        $accounts = $query->paginate($perPage);
        return response()->json($accounts);
    }

    // 7. Reject (delete) account
    public function rejectAccount($id)
    {
        try {
            $account = Account::findOrFail($id);
            $account->delete();
            return response()->json(['message' => 'Account rejected and deleted successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 8. Update profile picture
    public function updateProfilePicture(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'profile_picture' => 'required|image|mimes:jpeg,jpg,png|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $account = Account::findOrFail($id);
            $file = $request->file('profile_picture');
            $filename = 'profile_' . $id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_pictures', $filename, 'public');
            $account->profile_picture_path = '/storage/' . $path;
            $account->save();

            return response()->json(['message' => 'Profile picture updated successfully', 'profile_picture_path' => $account->profile_picture_path]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
