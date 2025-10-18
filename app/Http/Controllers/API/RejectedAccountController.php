<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\RejectedAccount;
use App\Traits\LogsActivity;

class RejectedAccountController extends Controller
{
    use LogsActivity;
    // List rejected accounts with pagination, search, and sorting
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        $search = $request->query('search');

        $query = RejectedAccount::query();

        // Search by name
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                ->orWhere('last_name', 'like', "%$search%")
                ->orWhere('middle_name', 'like', "%$search%")
                ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ["%$search%"]);
            });
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'created_at'); // default sort by date
        $order = $request->query('order', 'desc'); // default descending

        $allowedSorts = ['first_name', 'last_name', 'created_at']; // allowed columns
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }

        // Sort by full name if requested
        if ($sortBy === 'first_name' || $sortBy === 'last_name') {
            $query->orderBy($sortBy, $order);
        } else {
            $query->orderBy($sortBy, $order);
        }

        $accounts = $query->paginate($perPage);

        return response()->json($accounts);
    }


    // Create a rejected account with validation and try-catch
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required|string',
                'status' => 'required|string',
                'type' => 'required|string',
                'first_name' => 'required|string',
                'middle_name' => 'nullable|string',
                'last_name' => 'required|string',
                'suffix' => 'nullable|string',
                'sex' => 'required|string',
                'nationality' => 'required|string',
                'birthday' => 'required|date',
                'contact_no' => 'required|string',
                'birth_place' => 'required|string',
                'municipality' => 'required|string',
                'barangay' => 'required|string',
                'house_no' => 'required|string',
                'zip_code' => 'required|string',
                'street' => 'required|string',
                'pwd_number' => 'nullable|string',
                'single_parent_number' => 'nullable|string',
                'profile_picture_path' => 'nullable|string',
                'reason' => 'nullable|string',
            ]);
            $rejected = RejectedAccount::create($validated);

            // Log the activity
            $this->logActivity('Account Management', "Manually created rejected account record for: {$validated['email']}");

            return response()->json(['message' => 'Rejected account created successfully', 'rejected_account' => $rejected], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
