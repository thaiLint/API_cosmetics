<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use App\Models\Payment;
use App\Models\Message;
use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Services\BakongPaymentService;

class PaymentController extends Controller
{
    public function makePayment(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|string|in:cash,card,credit_card,bakong_qr,stripe',
            'order_id' => 'nullable|integer'
        ]);

        $payment = app(PaymentService::class)->makePayment($validated);

        return response()->json([
            'message' => 'Payment processed',
            'payment' => $payment
        ]);
    }

    // Bakong callback
    public function bakongCallback(Request $request)
    {
        $payload = $request->all();
        $amount = $payload['amount'] ?? 0;
        $orderId = $payload['order_id'] ?? null;

        $verified = app(BakongPaymentService::class)->verifyMD5($payload);
        if (!$verified) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        $payment = Payment::where('order_id', $orderId)->first();
        if ($payment) {
            $payment->status = 'completed';
            $payment->amount = $amount;
            $payment->save();
        }

        return response()->json(['message' => 'Callback processed successfully']);
    }

    // Private function inside controller
    private function sendTelegramNotification($message)
    {
        $botToken = env('TELEGRAM_BOT_TOKEN');
        $chatId = env('TELEGRAM_CHAT_ID');

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        Http::post($url, [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'HTML'
        ]);
    }

    public function paymentSuccess(Request $request)
    {
        $user = auth()->user();
        $order = $request->order; // order info from frontend

        // Build product list string
        $itemsText = "";
        foreach ($order['items'] as $item) {
            $itemsText .= "• <b>{$item['name']}</b> x {$item['quantity']} = {$item['price']} USD\n";
        }

        // Full Telegram message
        $message = " <b>New Order Completed</b>\n\n";
        $message .= "<b>User:</b> {$user->name} ({$user->email})\n";
        $message .= "<b>Order ID:</b> {$order['id']}\n";
        $message .= "<b>Items:</b>\n{$itemsText}";
        $message .= "<b>Total:</b> {$order['total']} USD\n";
        $message .= "<b>Payment Status:</b> Successful \n";
        $message .= "<b>Date:</b> " . now()->format('Y-m-d H:i') . "\n";

        // Send Telegram notification
        $this->sendTelegramNotification($message);

        // Optional: Send chat message to shop
        if(isset($order['shop_id'])) {
            Message::create([
                'sender_id' => $user->id,
                'sender_type' => 'user',
                'receiver_id' => $order['shop_id'],
                'receiver_type' => 'shop',
                'message' => "New order #{$order['id']} completed by {$user->name}. Total: {$order['total']} USD"
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Order completed, Telegram notified, and shop chat updated'
        ]);
    }
}
