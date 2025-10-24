<?php

namespace App\Services;

use App\Models\Payment;

class PaymentService
{
    /**
     * Make a new payment
     */
    public function makePayment($userId, $shopId, $amount)
    {
        return Payment::create([
            'user_id' => $userId,
            'shop_id' => $shopId,
            'amount' => $amount,
            'status' => 'pending',
        ]);
    }

    /**
     * Get all payments, optionally by shop
     */
    public function getAllPayments($shopId = null)
    {
        $query = Payment::query();

        if ($shopId) {
            $query->where('shop_id', $shopId);
        }

        return $query->get();
    }

    /**
     * Get a single payment by ID
     */
    public function getPaymentById($id)
    {
        return Payment::find($id); // returns null if not found
    }
}
