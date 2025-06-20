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
                'type' => 'required|string|in:residence,admin,staff'
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $account = Account::findOrFail($id);
            $account->update($request->only([
                'first_name', 'middle_name', 'last_name', 'suffix', 'sex', 'nationality', 'birthday', 'contact_no', 'birth_place',
                'municipality', 'barangay', 'house_no', 'zip_code', 'street', 'type'
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
                'status' => 'required|in:active,inactive',
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
}
