<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use App\Models\Products;
use App\Models\Reviwes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    // Create a review (only authenticated users)
    public function create(Request $request)
    {
        $user = auth()->user(); // JWT authenticated user

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        $review = Reviwes::create([
            'user_id' => $user->id,
            'product_id' => $request->product_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review
        ], 201);
    }

    // Get all reviews for a specific product
    public function getReviews($id)
    {
        $product = Products::find($id);
        if (!$product) {
            return response()->json([
                'message' => 'Product not found'
            ], 404);
        }

        $reviews = Reviwes::where('product_id', $id)
            ->with('user:id,name,email') // include user info
            ->get();

        return response()->json([
            'message' => 'Reviews fetched successfully',
            'data' => $reviews
        ]);
    }
}
