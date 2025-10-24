<?php

namespace App\Services;

use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;

class BakongPaymentService
{
    // Generate QR for a user to scan
    public function generateIndividualQR($shopId, $amount)
    {
        $data = "shop:$shopId|amount:$amount|time:".time();
        return $this->makeQR($data);
    }

    // Make a QR code image and return URL
    public function makeQR($data)
    {
        $fileName = 'qr_codes/QR_'.time().'.png';
        $path = storage_path('app/public/' . $fileName);

        QrCode::format('png')->size(300)->generate($data, $path);

        return asset('storage/' . $fileName);
    }

    // Verify MD5 hash
    public function verifyMD5($data, $hash)
    {
        return md5($data) === $hash;
    }
}
