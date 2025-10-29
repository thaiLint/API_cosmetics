<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class OrderController extends Controller
{
    // 1. Place Order
    public function placeOrder(Request $request) {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $total_price = 0;
        foreach ($request->items as $item) {
            $product = \App\Models\Products::find($item['product_id']);
            $total_price += $product->price * $item['quantity'];
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'total_price' => $total_price,
        ]);

        foreach ($request->items as $item) {
            $product = \App\Models\Products::find($item['product_id']);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);
        }

        return response()->json(['message' => 'Order placed', 'order' => $order->load('items')], 201);
    }

    // 2. Get Orders by User
  public function getOrderByUser() {
    $orders = Order::with('items.product')->where('user_id', Auth::id())->get();
    return response()->json($orders);
}
    // 3. Update Status
    public function updateStatus(Request $request, $id) {
        $request->validate(['status' => 'required|string']);
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Status updated', 'order' => $order]);
    }

    // 4. Cancel Order
    public function cancelOrder($id) {
        $order = Order::findOrFail($id);
        if($order->status === 'canceled') {
            return response()->json(['message' => 'Order already canceled'], 400);
        }

        $order->status = 'canceled';
        $order->save();

        return response()->json(['message' => 'Order canceled', 'order' => $order]);
    }
}
