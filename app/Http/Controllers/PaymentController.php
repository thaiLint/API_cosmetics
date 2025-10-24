<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Shop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentService;
use App\Services\BakongPaymentService;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $bakongPaymentService;

    public function __construct(PaymentService $paymentService, BakongPaymentService $bakongPaymentService)
    {
        $this->paymentService = $paymentService;
        $this->bakongPaymentService = $bakongPaymentService;
    }

    /**
     * Make a new payment
     */
    public function makePayment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'shop_id' => 'required|integer',
            'total_price' => 'required|numeric',
        ]);

        $payment = $this->paymentService->makePayment(
            $request->user_id,
            $request->shop_id,
            $request->total_price
        );

        return response()->json([
            'success' => true,
            'payment' => $payment
        ]);
    }

    /**
     * Get all payments, optionally filtered by shop
     */
    public function getAllPayments(Request $request)
    {
        $shopId = $request->query('shop_id');

        $payments = $this->paymentService->getAllPayments($shopId);

        return response()->json([
            'success' => true,
            'payments' => $payments
        ]);
    }

    /**
     * Handle payment callback and verify MD5
     */
    public function paymentCallback(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|integer',
            'md5_hash' => 'required|string',
        ]);

        // Start DB transaction
        DB::beginTransaction();
        try {
            // Retrieve payment
            $payment = $this->paymentService->getPaymentById($request->payment_id);

            if (!$payment) {
                return response()->json(['success' => false, 'message' => 'Payment not found'], 404);
            }

            // Build the data string exactly as in QR generation
            $data = "shop:{$payment->shop_id}|amount:{$payment->amount}|time:" . strtotime($payment->created_at);

            // Verify MD5
            $isValid = $this->bakongPaymentService->verifyMD5($data, $request->md5_hash);

            if (!$isValid) {
                return response()->json(['success' => false, 'message' => 'Invalid MD5 hash'], 400);
            }

            // Update payment status
            $payment->status = 'completed';
            $payment->save();

            DB::commit();

            Log::info("Payment verified and completed: {$payment->id}");

            return response()->json(['success' => true, 'payment' => $payment]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Payment callback failed: " . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
