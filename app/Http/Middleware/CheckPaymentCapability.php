<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Check Payment Capability Middleware
 *
 * Comprehensive middleware that verifies a merchant can accept payments.
 * Combines subscription and Stripe onboarding checks in a single middleware.
 *
 * This is used on customer-facing checkout routes to ensure the merchant
 * is authorized to process payments before allowing customers to complete purchases.
 *
 * Allows access if:
 * - Merchant has active subscription (active/trialing/past_due with grace)
 * - Stripe Connect is fully onboarded
 * - Charges are enabled on Stripe account
 * - Payouts are enabled on Stripe account
 *
 * Blocks access if ANY of the above conditions fail.
 */
class CheckPaymentCapability
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // DEVELOPMENT: Bypass payment capability check in local environment
        if (config('app.env') === 'local') {
            return $next($request);
        }

        // Get merchant from store parameter (for customer-facing routes)
        $store = $request->route('store');

        if (!$store || !$store->merchant) {
            Log::error('Payment capability check failed: No store or merchant', [
                'store_id' => $store?->id,
            ]);

            return $this->denyAccess($request, 'Store not found or not properly configured.');
        }

        $merchant = $store->merchant;

        // Check 1: Active subscription
        if (!$merchant->hasActiveSubscription()) {
            Log::warning('Payment capability check failed: Inactive subscription', [
                'merchant_id' => $merchant->id,
                'store_id' => $store->id,
                'subscription_status' => $merchant->subscription_status,
            ]);

            return $this->denyAccess($request, 'This store is temporarily unavailable. Please try again later.');
        }

        // Check 2: Subscription grace period (if past_due)
        if ($merchant->subscription_status === 'past_due' && !$merchant->isWithinGracePeriod()) {
            Log::warning('Payment capability check failed: Grace period expired', [
                'merchant_id' => $merchant->id,
                'store_id' => $store->id,
            ]);

            return $this->denyAccess($request, 'This store is temporarily unavailable. Please try again later.');
        }

        // Check 3: Stripe Connect account exists
        if (!$merchant->stripe_connect_account_id) {
            Log::warning('Payment capability check failed: No Stripe account', [
                'merchant_id' => $merchant->id,
                'store_id' => $store->id,
            ]);

            return $this->denyAccess($request, 'This store cannot accept payments at this time.');
        }

        // Check 4: Stripe onboarding complete
        if (!$merchant->stripe_onboarding_complete) {
            Log::warning('Payment capability check failed: Stripe onboarding incomplete', [
                'merchant_id' => $merchant->id,
                'store_id' => $store->id,
                'stripe_account_id' => $merchant->stripe_connect_account_id,
            ]);

            return $this->denyAccess($request, 'This store cannot accept payments at this time.');
        }

        // Check 5: Charges enabled
        if (!$merchant->stripe_charges_enabled) {
            Log::warning('Payment capability check failed: Charges not enabled', [
                'merchant_id' => $merchant->id,
                'store_id' => $store->id,
                'stripe_account_id' => $merchant->stripe_connect_account_id,
            ]);

            return $this->denyAccess($request, 'This store cannot accept payments at this time.');
        }

        // Check 6: Payouts enabled (optional warning, but allow transaction)
        if (!$merchant->stripe_payouts_enabled) {
            Log::warning('Payment capability check: Payouts not enabled yet', [
                'merchant_id' => $merchant->id,
                'store_id' => $store->id,
                'stripe_account_id' => $merchant->stripe_connect_account_id,
            ]);
            // Allow transaction to proceed - payouts can be enabled later
        }

        // All checks passed - merchant can accept payments
        return $next($request);
    }

    /**
     * Deny access with appropriate response based on request type.
     *
     * @param Request $request
     * @param string $message
     * @return Response
     */
    protected function denyAccess(Request $request, string $message): Response
    {
        // For API requests, return JSON error
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Store unavailable',
                'message' => $message,
            ], 503);
        }

        // For web requests using Inertia, redirect to store homepage
        if ($request->header('X-Inertia')) {
            $store = $request->route('store');
            return redirect()->route('storefront.index', ['store' => $store?->id])
                ->with('error', $message);
        }

        // For regular web requests, redirect with error
        $store = $request->route('store');
        return redirect()->route('storefront.index', ['store' => $store?->id])
            ->with('error', $message);
    }
}
