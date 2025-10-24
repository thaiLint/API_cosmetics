<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FavoriteController extends Controller
{
     public function toggleFavorite(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $user = $request->user();

        if ($user->favorites()->where('product_id', $request->product_id)->exists()) {
            // remove favorite
            $user->favorites()->detach($request->product_id);
            return response()->json(['message' => 'Removed from favorites']);
        } else {
            // add favorite
            $user->favorites()->attach($request->product_id);
            return response()->json(['message' => 'Added to favorites']);
        }
    }

    public function getFavorites(Request $request)
    {
        $user = $request->user();
        $favorites = $user->favorites()->get();
        return response()->json($favorites);
    }
}
