<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure Stripe Onboarded Middleware
 *
 * Verifies that the authenticated user's merchant has completed Stripe Connect onboarding.
 * Blocks access to payment-related features if Stripe is not connected or onboarding is incomplete.
 *
 * Allows access if:
 * - Merchant has Stripe Connect account ID
 * - Onboarding is marked as complete
 * - Charges are enabled on the Stripe account
 *
 * Blocks access if:
 * - No Stripe Connect account exists
 * - Onboarding is not complete
 * - Charges are not enabled
 */
class EnsureStripeOnboarded
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // DEVELOPMENT: Bypass Stripe check in local environment
        if (config('app.env') === 'local') {
            return $next($request);
        }

        $user = $request->user();

        // If no user or no merchant, deny access
        if (!$user || !$user->merchant) {
            Log::warning('Stripe onboarding check failed: No user or merchant', [
                'user_id' => $user?->id,
            ]);

            return $this->denyAccess($request, 'No merchant account found.');
        }

        $merchant = $user->merchant;

        // Check if merchant has Stripe Connect account
        if (!$merchant->stripe_connect_account_id) {
            Log::info('Stripe onboarding check failed: No Connect account', [
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
            ]);

            return $this->denyAccess($request, 'Please connect your Stripe account to accept payments.');
        }

        // Check if onboarding is complete
        if (!$merchant->stripe_onboarding_complete) {
            Log::info('Stripe onboarding check failed: Onboarding incomplete', [
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
                'stripe_account_id' => $merchant->stripe_connect_account_id,
            ]);

            return $this->denyAccess($request, 'Please complete your Stripe onboarding to accept payments.');
        }

        // Check if charges are enabled
        if (!$merchant->stripe_charges_enabled) {
            Log::info('Stripe onboarding check failed: Charges not enabled', [
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
                'stripe_account_id' => $merchant->stripe_connect_account_id,
            ]);

            return $this->denyAccess($request, 'Your Stripe account cannot accept charges yet. Please complete all requirements.');
        }

        // Stripe is fully onboarded and functional - allow access
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
        $merchant = $request->user()?->merchant;

        // For API requests, return JSON error
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'Stripe onboarding required',
                'message' => $message,
                'stripe_connected' => (bool) $merchant?->stripe_connect_account_id,
                'onboarding_complete' => $merchant?->stripe_onboarding_complete ?? false,
                'charges_enabled' => $merchant?->stripe_charges_enabled ?? false,
            ], 403);
        }

        // For web requests using Inertia, redirect to dashboard with error
        if ($request->header('X-Inertia')) {
            return redirect()->route('dashboard')
                ->with('error', $message)
                ->with('show_stripe_setup', true);
        }

        // For regular web requests, redirect with error
        return redirect()->route('dashboard')
            ->with('error', $message)
            ->with('show_stripe_setup', true);
    }
}
