<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class QuizController extends Controller
{
     public function getResult(Request $request)
    {
        $skinType = $request->input('skin_type');

        // You can use database logic here if you want
        $products = [];

        switch ($skinType) {
            case 'Dry Skin':
                $products = [
                    ['name' => 'Hydrating Moisturizer', 'price' => 25.00],
                    ['name' => 'Gentle Cleanser', 'price' => 15.50],
                ];
                break;

            case 'Oily Skin':
                $products = [
                    ['name' => 'Oil-Free Lotion', 'price' => 20.00],
                    ['name' => 'Mattifying Toner', 'price' => 18.00],
                ];
                break;

            case 'Combination Skin':
                $products = [
                    ['name' => 'Balancing Cream', 'price' => 22.00],
                    ['name' => 'Lightweight Serum', 'price' => 19.00],
                ];
                break;

            default:
                $products = [
                    ['name' => 'Normal Skin Lotion', 'price' => 21.00],
                ];
        }

        return response()->json([
            'message' => 'success',
            'skin_type' => $skinType,
            'recommended_products' => $products
        ]);
    }
}
