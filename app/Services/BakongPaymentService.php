<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use SimpleSoftwareIO\QrCode\Generator;
class BakongPaymentService
{
  


public function makeQR($data)
{
    $filename = 'bakong_' . time() . '.png';
    $path = 'public/qr_codes/' . $filename;

    $qrContent = json_encode([
        'amount' => $data['amount'],
        'order_id' => $data['order_id'],
        'shop' => 'MyShop',
    ]);

    Storage::makeDirectory('public/qr_codes');

    // Use GD backend
    QrCode::format('png')
          ->size(300)
          ->generate($qrContent, storage_path('app/' . $path));

    return [
        'qr_code_url' => asset(Storage::url('qr_codes/' . $filename))
    ];
}


    public function verifyMD5($payload)
    {
        $secretKey = env('BAKONG_SECRET_KEY');
        $expected = md5($payload['order_id'] . $payload['amount'] . $secretKey);
        return $expected === $payload['md5_hash'];
    }

    public function updatePaymentStatus($payload)
    {
        // update payment in database
        $payment = \App\Models\Payment::where('order_id', $payload['order_id'])->first();
        if ($payment) {
            $payment->status = 'completed';
            $payment->save();
        }
    }
}
