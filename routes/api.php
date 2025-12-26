<?php

use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\OrderTrackingController;
use App\Http\Controllers\Api\ProductAddonController;
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

// CSRF token refresh endpoint
Route::get('/csrf-token', function () {
    return response()->json([
        'token' => csrf_token(),
    ]);
})->middleware('web');

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

    // Payment intent creation endpoint
    Route::post('stores/{store}/payment-intent', [CheckoutController::class, 'createPaymentIntent'])
        ->middleware('throttle:10,1')
        ->name('api.v1.payment-intent');

    // Checkout endpoint
    Route::post('stores/{store}/checkout', [CheckoutController::class, 'checkout'])
        ->middleware('throttle:10,1')
        ->name('api.v1.checkout');

    // Order tracking endpoint
    Route::get('orders/{public_id}', [OrderTrackingController::class, 'show'])
        ->middleware('throttle:60,1')
        ->name('api.v1.orders.show');

    // Product addons endpoints
    Route::prefix('stores/{store}')->group(function () {
        // Get all active addons for a store
        Route::get('addons', [ProductAddonController::class, 'getStoreAddons'])
            ->middleware('throttle:60,1')
            ->name('api.v1.addons.store');

        // Get addons for a specific product
        Route::get('products/{product}/addons', [ProductAddonController::class, 'getProductAddons'])
            ->middleware('throttle:60,1')
            ->name('api.v1.addons.product');
    });

    // Pricing calculation endpoints
    Route::post('calculate-price', [ProductAddonController::class, 'calculatePrice'])
        ->middleware('throttle:60,1')
        ->name('api.v1.addons.calculate-price');

    Route::post('calculate-cart-total', [ProductAddonController::class, 'calculateCartTotal'])
        ->middleware('throttle:60,1')
        ->name('api.v1.addons.calculate-cart-total');
});

/*
|--------------------------------------------------------------------------
| Stripe Webhook Routes
|--------------------------------------------------------------------------
| Webhooks do NOT use auth middleware - they verify via signature
*/

Route::post('/webhooks/stripe', [App\Http\Controllers\StripeWebhookController::class, 'handleWebhook'])
    ->name('stripe.webhook');

/*
|--------------------------------------------------------------------------
| Temporary Testing Routes (NO AUTH - Remove in production!)
|--------------------------------------------------------------------------
*/

// Temporary test route without auth for quick testing
Route::post('/test/stores/{store}/migrations', [App\Http\Controllers\DataMigrationController::class, 'start'])
    ->name('api.test.migrations.start');

Route::get('/test/migrations/{migration}/status', [App\Http\Controllers\DataMigrationController::class, 'status'])
    ->name('api.test.migrations.status');

Route::get('/test/migrations/{migration}/preview', [App\Http\Controllers\DataMigrationController::class, 'preview'])
    ->name('api.test.migrations.preview');

Route::post('/test/migrations/{migration}/import', [App\Http\Controllers\DataMigrationController::class, 'import'])
    ->name('api.test.migrations.import');

/*
|--------------------------------------------------------------------------
| Authenticated Merchant API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['web', 'auth:web'])->group(function () {
    // Customer orders endpoint (requires web middleware for session support)
    Route::get('/customers/{customer}/orders', [App\Http\Controllers\CustomerController::class, 'orders'])
        ->name('api.customers.orders');

    /*
    |--------------------------------------------------------------------------
    | Data Migration API Routes (MVP)
    |--------------------------------------------------------------------------
    */

    // Test endpoint
    Route::get('/data-migration/test', [App\Http\Controllers\DataMigrationController::class, 'test'])
        ->name('api.data-migration.test');

    // Store-specific migration routes
    Route::prefix('stores/{store}/migrations')->group(function () {
        Route::get('/', [App\Http\Controllers\DataMigrationController::class, 'index'])
            ->name('api.stores.migrations.index');

        Route::post('/', [App\Http\Controllers\DataMigrationController::class, 'start'])
            ->name('api.stores.migrations.start');
    });

    // Individual migration routes
    Route::prefix('migrations/{migration}')->group(function () {
        Route::get('/status', [App\Http\Controllers\DataMigrationController::class, 'status'])
            ->name('api.migrations.status');

        Route::get('/preview', [App\Http\Controllers\DataMigrationController::class, 'preview'])
            ->name('api.migrations.preview');

        Route::post('/import', [App\Http\Controllers\DataMigrationController::class, 'import'])
            ->name('api.migrations.import');

        Route::patch('/products/approval', [App\Http\Controllers\DataMigrationController::class, 'updateProductApproval'])
            ->name('api.migrations.products.approval');

        Route::delete('/', [App\Http\Controllers\DataMigrationController::class, 'destroy'])
            ->name('api.migrations.destroy');
    });
});
