<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feedback;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    /**
     * Display a listing of feedbacks with pagination and filters.
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

        $feedbacks = $query->paginate($request->get('per_page', 10));

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
        $feedback->delete();

        return response()->json([
            'success' => true,
            'message' => 'Feedback deleted successfully.'
        ]);
    }
}
