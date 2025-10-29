<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BakongPaymentController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CartController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\QuizController;
use App\Models\QuizResult;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});




// User and Auth
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('jwt.auth');
Route::middleware('auth:api')->post('/change-password', [AuthController::class, 'changePassword']);






// Products
Route::post('/product/create', [ProductController::class, 'create']);
Route::get('/product/all', [ProductController::class, 'getAll']);
Route::get('/product/{id}', [ProductController::class, 'getById']);
Route::get('/product/category/{category}', [ProductController::class, 'getByCategory']);
Route::get('/products/search', [ProductController::class, 'search']);


// Category part
Route::prefix('category')->group(function () {
    Route::post('/create', [CategoryController::class, 'create']);
    Route::get('/all', [CategoryController::class, 'getAll']);
    Route::get('/{id}', [CategoryController::class, 'getById']);
    Route::put('/{id}', [CategoryController::class, 'updateById']);
    Route::delete('/{id}', [CategoryController::class, 'deleteById']);
});

//brand
Route::prefix('brand')->group(function () {
    Route::post('/create', [BrandController::class, 'create']);
    Route::get('/all', [BrandController::class, 'getAll']);
    Route::get('/{id}', [BrandController::class, 'getById']);
    Route::put('/{id}', [BrandController::class, 'updateById']);
    Route::delete('/{id}', [BrandController::class, 'deleteById']);
});

// add to cart
Route::post('cart/add', [CartController::class, 'addToCart']);
Route::get('cart/user', [CartController::class, 'getCartByUserId']);
Route::post('cart/remove-one', [CartController::class, 'removeQtyByOne']);
Route::delete('cart/remove-all', [CartController::class, 'removeAllItems']);


//Reviews
Route::get('products/{id}/reviews', [ReviewController::class, 'getReviews']);
Route::group(['middleware' => ['jwt.auth']], function() {
    Route::post('reviews', [ReviewController::class, 'create']); // post review
});

// Favorite
Route::middleware('auth:sanctu')->group(function() {
    Route::post('/favorites', [FavoriteController::class, 'toggleFavorite']);
    Route::get('/favorites', [FavoriteController::class, 'getFavorites']);
});

// Orders
Route::middleware('auth:api')->group(function () {
    Route::post('/orders', [OrderController::class, 'placeOrder']);
    Route::get('/orders', [OrderController::class, 'getOrderByUser']);
    Route::put('/orders/{id}', [OrderController::class, 'updateStatus']);
    Route::delete('/orders/{id}', [OrderController::class, 'cancelOrder']);
});

Route::middleware('auth:api')->group(function () {
    Route::post('/payment', [PaymentController::class, 'generatePayment']);
});
    // Payments
 Route::prefix('payment')->group(function () {
    Route::post('/make', [PaymentController::class, 'makePayment']);
    Route::get('/all', [PaymentController::class, 'getAllPayments']);
    Route::get('/{id}', [PaymentController::class, 'getPaymentById']);
    Route::post('/payment/success', [PaymentController::class, 'paymentSuccess']);


    // Bakong QR routes
    Route::post('/bakong-qr', [PaymentController::class, 'makeBakongQR']);
    Route::post('/bakong-callback', [PaymentController::class, 'bakongCallback']);
});


Route::middleware('auth:api')->group(function () {
    Route::post('/chat/send', [MessageController::class, 'sendMessage']);
    Route::get('/chat/{receiver_id}', [MessageController::class, 'getConversation']);
});
//Quiz


Route::post('/quiz/result', function (Request $request) {
    $skinType = $request->input('skin_type');
    $userId = $request->input('user_id'); // optional if user logged in

    // Recommended products (you can later fetch from DB)
    $products = [
        'Dry Skin' => [
            ['name' => 'Hydrating Cream', 'price' => 25],
            ['name' => 'Gentle Cleanser', 'price' => 18],
        ],
        'Oily Skin' => [
            ['name' => 'Oil Control Gel', 'price' => 20],
            ['name' => 'Matte Cleanser', 'price' => 15],
        ],
        'Combination Skin' => [
            ['name' => 'Balanced Moisturizer', 'price' => 22],
            ['name' => 'Dual Cleanser', 'price' => 19],
        ],
        'Normal Skin' => [
            ['name' => 'Daily Moisturizer', 'price' => 20],
            ['name' => 'Refreshing Toner', 'price' => 17],
        ],
    ];

    $recommended = $products[$skinType] ?? [];

    // Save to DB
    $result = QuizResult::create([
        'user_id' => $userId,
        'skin_type' => $skinType,
        'recommended_products' => $recommended,
    ]);

    return response()->json([
        'message' => 'success',
        'skin_type' => $skinType,
        'recommended_products' => $recommended,
        'quiz_id' => $result->id,
    ]);
});
Route::get('/quiz/history/{user_id}', function ($user_id) {
    return QuizResult::where('user_id', $user_id)
        ->orderBy('created_at', 'desc')
        ->get();
});


