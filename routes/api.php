<?php

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\ShippingQuoteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

/*
|--------------------------------------------------------------------------
| Public Storefront API (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Shipping quote endpoint
    Route::post('stores/{store}/shipping/quote', [ShippingQuoteController::class, 'calculate'])
        ->middleware('throttle:60,1')
        ->name('api.v1.shipping.quote');

    // Checkout endpoint
    Route::post('stores/{store}/checkout', [CheckoutController::class, 'checkout'])
        ->middleware('throttle:10,1')
        ->name('api.v1.checkout');

    // Order tracking endpoint
    Route::get('orders/{public_id}', [OrderTrackingController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('api.v1.orders.show');
});
