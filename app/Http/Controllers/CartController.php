<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Products;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // Add multiple products to cart
    public function addToCart(Request $request)
    {
        $user = auth()->user(); // get authenticated user
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1'
        ]);

        $addedItems = [];

        foreach ($request->items as $item) {
            $product = Products::find($item['product_id']);
            if (!$product) continue;

            $cartItem = Cart::where('user_id', $user->id)
                ->where('product_id', $item['product_id'])
                ->first();

            if ($cartItem) {
                $cartItem->quantity += $item['quantity'];
                $cartItem->save();
            } else {
                $cartItem = Cart::create([
                    'user_id' => $user->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity']
                ]);
            }

            $addedItems[] = $cartItem->load('product');
        }

        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();
        $total = $cartItems->sum(fn($cart) => $cart->product->price * $cart->quantity);

        return response()->json([
            'message' => 'Products added to cart successfully',
            'total_price' => $total,
            'data' => $cartItems
        ], 200);
    }

    // Get cart items for authenticated user
    public function getCartByUserId()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();
        $total = $cartItems->sum(fn($item) => $item->product->price * $item->quantity);

        return response()->json([
            'message' => 'Cart fetched successfully',
            'total_price' => $total,
            'data' => $cartItems
        ]);
    }

    // Reduce quantity by 1 or remove item if quantity is 1
    public function removeQtyByOne(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $request->validate([
            'product_id' => 'required|integer|exists:products,id'
        ]);

        $cart = Cart::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$cart) {
            return response()->json(['message' => 'Item not found'], 404);
        }

        if ($cart->quantity > 1) {
            $cart->quantity -= 1;
            $cart->save();
        } else {
            $cart->delete();
        }

        return response()->json(['message' => 'Quantity reduced or item removed']);
    }

    // Remove all items from cart
    public function removeAllItems()
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        Cart::where('user_id', $user->id)->delete();

        return response()->json(['message' => 'All items removed from cart']);
    }
}
