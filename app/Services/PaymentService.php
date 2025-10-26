<?php

namespace App\Services;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\PaymentIntent;
class PaymentService
{
    public function makePayment($data)
    {
        $status = 'pending';
        $stripeId = null;
        $qrCodeUrl = null;

        // Normalize method
        $method = strtolower($data['method'] ?? 'cash');
        $amount = round($data['amount'], 2);

        // Handle payment method
        switch ($method) {
            case 'cash':
            case 'card':
            case 'credit_card':
                $status = 'pending'; // cash/card just record for now
                break;

            case 'stripe':
                $stripeResponse = $this->chargeStripe($amount, $data['user_id']);
                $status = $stripeResponse['status'] ?? 'failed';
                $stripeId = $stripeResponse['id'] ?? null;
                break;

            case 'bakong_qr':
                // Assume you have a BakongService with makeQR()
                $qrCodeUrl = app('App\Services\BakongPaymentService')->makeQR([
                    'amount' => $amount,
                    'order_id' => $data['order_id'] ?? 'ORDER' . time()
                ])['qr_code_url'];
                $status = 'pending';
                break;

            default:
                throw new \Exception('Invalid payment method.');
        }

        // Save payment
        $payment = new Payment();
        $payment->user_id = $data['user_id'];
        $payment->amount = $amount;
        $payment->method = $method;
        $payment->status = $status;
        $payment->stripe_id = $stripeId;
        $payment->qr_code_url = $qrCodeUrl;
        $payment->order_id = $data['order_id'] ?? null;
        $payment->save();

        return $payment;
    }

    private function chargeStripe($amount, $userId)
    {
        try {
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            $paymentIntent = PaymentIntent::create([
                'amount' => round($amount * 100),
                'currency' => 'usd',
                'payment_method_types' => ['card'],
                'description' => 'Payment for user ' . $userId,
            ]);

            return [
                'id' => $paymentIntent->id,
                'status' => $paymentIntent->status
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Stripe Error: ' . $e->getMessage());
            return [
                'id' => null,
                'status' => 'failed',
                'error' => $e->getMessage()
            ];
        }
    }

    public function getAllPayments()
    {
        return Payment::all();
    }

    public function getPaymentById($id)
    {
        return Payment::findOrFail($id);
    }
}
