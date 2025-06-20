<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Exception;

class AuthController extends Controller
{
    // 1. Register
    public function register(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|unique:accounts,email',
                'password' => 'required|string|min:8|confirmed',
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

            $account = Account::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
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
                'type' => $request->type
            ]);

            return response()->json(['message' => 'Registration successful', 'account' => $account], 201);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 2. Login
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $account = Account::where('email', $request->email)->first();
            if (!$account || !Hash::check($request->password, $account->password)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }

            // Generate Sanctum token
            $token = $account->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message' => 'Login successful',
                'token' => $token,
                'account' => $account
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // 3. Logout
    public function logout(Request $request)
    {
        try {
            // If using Sanctum or Passport, revoke token here
            // $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logout successful']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
