<?php

namespace App\Http\Controllers;
use App\Services\BakongPayment;
use Illuminate\Http\Request;

class BakongPaymentController extends Controller
{
    protected $bakong;
    public function generateQR(Request $request)
    {
        $userId = $request->user()->id;
        $qrCode = $this->bakong->generateIndividualQR($userId);

        return response()->json(['qr' => $qrCode]);
    }
}
