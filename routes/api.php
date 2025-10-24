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
use App\Http\Controllers\ReviewController;

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




// Public routes
Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('reset-password', [AuthController::class, 'resetPassword']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// // Protected routes – requires JWT token
// Route::middleware('auth:api')->group(function () {
//     Route::post('/payment', [PaymentController::class, 'generatePayment']);
// });
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
Route::group(['middleware' => ['jwt.auth']], function () {
    
    Route::post('cart/add', [CartController::class, 'addToCart']);


    Route::get('cart/user', [CartController::class, 'getCartByUserId']);

    
    Route::post('cart/remove-one', [CartController::class, 'removeQtyByOne']);

    
    Route::delete('cart/remove-all', [CartController::class, 'removeAllItems']);
});
//Reviews
Route::get('products/{id}/reviews', [ReviewController::class, 'getReviews']);
Route::group(['middleware' => ['jwt.auth']], function() {
    Route::post('reviews', [ReviewController::class, 'create']); // post review
});
// Favorite
Route::middleware('auth:sanctum')->group(function() {
    Route::post('/favorites', [FavoriteController::class, 'toggleFavorite']);
    Route::get('/favorites', [FavoriteController::class, 'getFavorites']);
});

Route::post('bakong/make-payment', [BakongPaymentController::class, 'makePayment']);
Route::get('bakong/payments', [BakongPaymentController::class, 'getAllPayments']);


