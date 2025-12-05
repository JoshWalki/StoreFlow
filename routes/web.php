<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShippingManagementController;
use App\Http\Controllers\StoreSelectionController;
use App\Http\Controllers\Storefront\OrderTrackingController as StorefrontOrderTrackingController;
use App\Http\Controllers\Storefront\StorefrontController;
use App\Http\Controllers\Auth\CustomerAuthController;
use App\Http\Controllers\Storefront\CustomerDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/login');

// Store Settings Routes (Must be defined BEFORE storefront routes to avoid route conflict)
// These are protected by auth middleware later in the file
Route::middleware(['auth', 'tenant.context', 'store.selected'])->prefix('store')->group(function () {
    Route::get('/settings', [App\Http\Controllers\StoreSettingsController::class, 'index'])->name('store.settings');
    Route::put('/settings/basic', [App\Http\Controllers\StoreSettingsController::class, 'updateBasic'])->name('store.settings.basic');
    Route::put('/settings/contact', [App\Http\Controllers\StoreSettingsController::class, 'updateContact'])->name('store.settings.contact');
    Route::put('/settings/business', [App\Http\Controllers\StoreSettingsController::class, 'updateBusiness'])->name('store.settings.business');
    Route::put('/settings/shipping', [App\Http\Controllers\StoreSettingsController::class, 'updateShipping'])->name('store.settings.shipping');
    Route::put('/settings/theme', [App\Http\Controllers\StoreSettingsController::class, 'updateTheme'])->name('store.settings.theme');
    Route::post('/settings/logo', [App\Http\Controllers\StoreSettingsController::class, 'updateLogo'])->name('store.settings.logo');
    Route::delete('/settings/logo', [App\Http\Controllers\StoreSettingsController::class, 'removeLogo'])->name('store.settings.logo.remove');
});

// Public Storefront Routes
Route::prefix('store/{store}')->group(function () {
    Route::get('/', [StorefrontController::class, 'index'])->name('storefront.index');
    Route::get('/products', [StorefrontController::class, 'products'])->name('storefront.products');
    Route::get('/products/{product}', [StorefrontController::class, 'showProduct'])->name('storefront.products.show');
    Route::get('/checkout', [StorefrontController::class, 'checkout'])->name('storefront.checkout');
    Route::post('/checkout/process', [StorefrontController::class, 'processCheckout'])->name('storefront.checkout.process');
    Route::get('/order/success/{order}', [StorefrontController::class, 'orderSuccess'])->name('storefront.order.success');

    // Customer Authentication Routes (Public)
    Route::middleware('guest:customer')->group(function () {
        Route::get('/login', [CustomerAuthController::class, 'showLogin'])->name('customer.login');
        Route::post('/login', [CustomerAuthController::class, 'login'])
            ->middleware('throttle:5,1')
            ->name('customer.login.post');
        Route::get('/register', [CustomerAuthController::class, 'showRegister'])->name('customer.register');
        Route::post('/register', [CustomerAuthController::class, 'register'])
            ->middleware('throttle:3,60')
            ->name('customer.register.post');
    });

    // Customer Authenticated Routes (Protected)
    Route::middleware('customer.auth')->group(function () {
        Route::post('/logout', [CustomerAuthController::class, 'logout'])->name('customer.logout');
        // Redirect old dashboard route to profile
        Route::get('/dashboard', function (App\Models\Store $store) {
            return redirect()->route('customer.profile', ['store' => $store->id]);
        })->name('customer.dashboard');
        Route::get('/orders', [CustomerDashboardController::class, 'orders'])->name('customer.orders');
        Route::get('/orders/{orderId}/details', [CustomerDashboardController::class, 'getOrderDetails'])->name('customer.order.details');
        Route::get('/profile', [CustomerDashboardController::class, 'profile'])->name('customer.profile');
        Route::put('/profile', [CustomerDashboardController::class, 'updateProfile'])->name('customer.profile.update');
        Route::put('/password', [CustomerDashboardController::class, 'updatePassword'])->name('customer.password.update');
    });
});

// Public Order Tracking
Route::get('/track', [StorefrontOrderTrackingController::class, 'show'])->name('storefront.track');
Route::post('/track', [StorefrontOrderTrackingController::class, 'track'])->name('storefront.track.post');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);
});

Route::middleware(['auth', 'tenant.context'])->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    // Store Selection
    Route::get('/store-selection', [StoreSelectionController::class, 'show'])->name('store-selection');
    Route::post('/store-selection', [StoreSelectionController::class, 'store']);

    // Dashboard (requires store selection and tenant context)
    Route::middleware('store.selected')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/orders/poll/{store}', [DashboardController::class, 'pollOrders'])->name('dashboard.orders.poll');

        // Orders (specific routes must come before parameterized routes)
        Route::get('/orders/history', [OrderController::class, 'history'])->name('orders.history');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status.update');
        Route::put('/orders/{order}/shipping', [OrderController::class, 'updateShipping'])->name('orders.shipping.update');

        // Products & Categories (with policy authorization)
        Route::resource('products', ProductController::class);
        Route::resource('categories', CategoryController::class);

        // Customers
        Route::get('/customers', [App\Http\Controllers\CustomerController::class, 'index'])->name('customers.index');

        // Staff Management (Owner only)
        Route::get('/staff', [App\Http\Controllers\StaffController::class, 'index'])->name('staff.index');
        Route::post('/staff', [App\Http\Controllers\StaffController::class, 'store'])->name('staff.store');
        Route::put('/staff/{staff}', [App\Http\Controllers\StaffController::class, 'update'])->name('staff.update');
        Route::delete('/staff/{staff}', [App\Http\Controllers\StaffController::class, 'destroy'])->name('staff.destroy');

        // Shipping Management
        Route::get('/shipping', [ShippingManagementController::class, 'index'])->name('shipping.index');
        Route::post('/shipping/zones', [ShippingManagementController::class, 'storeZone'])->name('shipping.zones.store');
        Route::put('/shipping/zones/{zone}', [ShippingManagementController::class, 'updateZone'])->name('shipping.zones.update');
        Route::delete('/shipping/zones/{zone}', [ShippingManagementController::class, 'destroyZone'])->name('shipping.zones.destroy');
        Route::post('/shipping/methods', [ShippingManagementController::class, 'storeMethod'])->name('shipping.methods.store');
        Route::put('/shipping/methods/{method}', [ShippingManagementController::class, 'updateMethod'])->name('shipping.methods.update');
        Route::delete('/shipping/methods/{method}', [ShippingManagementController::class, 'destroyMethod'])->name('shipping.methods.destroy');
        Route::post('/shipping/rates', [ShippingManagementController::class, 'storeRate'])->name('shipping.rates.store');
        Route::put('/shipping/rates/{rate}', [ShippingManagementController::class, 'updateRate'])->name('shipping.rates.update');
        Route::delete('/shipping/rates/{rate}', [ShippingManagementController::class, 'destroyRate'])->name('shipping.rates.destroy');
        Route::post('/shipping/calculate', [ShippingManagementController::class, 'calculateRates'])->name('shipping.calculate');

        // Loyalty Program
        Route::get('/loyalty', [App\Http\Controllers\LoyaltyController::class, 'index'])->name('loyalty.index');
        Route::post('/loyalty', [App\Http\Controllers\LoyaltyController::class, 'update'])->name('loyalty.update');

        // Audit Logs
        Route::get('/audit-logs', [App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
    });
});
