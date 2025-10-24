<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    // Protect all routes with JWT auth
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function generatePayment(Request $request)
    {
        $user = auth()->user(); // Get logged-in user

        $request->validate([
            'amount' => 'required|numeric|min:1'
        ]);

        $amount = $request->amount;

        //         $response = Http::timeout(30)->withHeaders([
        //     'Content-Type' => 'application/json',
        //     'apikey' => 'YOUR_SANDBOX_API_KEY'
        // ])->post('https://sandbox.ababank.com/payments', [
        //     'merchantID' => 'YOUR_MERCHANT_ID',
        //     'amount' => $amount,
        //     'orderID' => 'ORDER-'.$user->id.'-'.time(),
        //     'currency' => 'USD'
        // ]);
        return response()->json([
            'user_id' => $user->id,
            'amount' => $amount,
            'aba_response' => [
                'status' => 'success',
                'transactionID' => 'TEST-' . time()
            ]
        ]);
    }
}