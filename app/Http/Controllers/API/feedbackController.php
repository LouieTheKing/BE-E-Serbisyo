<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feedback;
use Illuminate\Support\Facades\Validator;
use App\Traits\LogsActivity;

class FeedbackController extends Controller
{
    use LogsActivity;
    /**
     * Display a listing of feedbacks with pagination, filters, and sorting.
     */
    public function index(Request $request)
    {
        $query = Feedback::query();

        // Filters
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->has('user')) {
            $query->where('user', $request->user);
        }

        if ($request->has('search')) {
            $query->where('remarks', 'like', '%' . $request->search . '%');
        }

        // Sorting
        $sortBy = $request->query('sort_by', 'created_at'); // default sort by creation date
        $order = $request->query('order', 'desc'); // default descending

        $allowedSorts = ['created_at', 'rating', 'category', 'user', 'remarks', 'module'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'created_at';
        }
 
        $query->orderBy($sortBy, $order);

        // Pagination
        $perPage = $request->query('per_page', 10);
        $feedbacks = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $feedbacks
        ]);
    }


    /**
     * Store a newly created feedback.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'required|exists:accounts,id',
            'remarks' => 'required|string',
            'category' => 'required|string',
            'rating' => 'required|integer|min:1|max:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feedback = Feedback::create($request->all());

        // Log the activity
        $this->logActivity('Feedback Management', "Created new feedback (ID: {$feedback->id}) - Category: {$feedback->category}, Rating: {$feedback->rating}/5");

        return response()->json([
            'success' => true,
            'data' => $feedback
        ]);
    }

    /**
     * Update the specified feedback (id from body, not param).
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:feedbacks,id',
            'remarks' => 'nullable|string',
            'category' => 'nullable|string',
            'rating' => 'nullable|integer|min:1|max:5'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feedback = Feedback::find($request->id);
        $feedback->update($request->only(['remarks', 'category', 'rating']));

        // Log the activity
        $this->logActivity('Feedback Management', "Updated feedback (ID: {$feedback->id})");

        return response()->json([
            'success' => true,
            'data' => $feedback
        ]);
    }

    /**
     * Remove the specified feedback (id from body).
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:feedbacks,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $feedback = Feedback::find($request->id);

        // Log the activity before deletion
        $this->logActivity('Feedback Management', "Deleted feedback (ID: {$feedback->id})");

        $feedback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feedback deleted successfully.'
        ]);
    }
}
