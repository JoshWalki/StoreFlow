<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure Subscription Active Middleware
 *
 * Verifies that the authenticated user's merchant has an active subscription.
 * Blocks access to protected features if subscription is expired or inactive.
 *
 * Allows access if:
 * - Subscription status is 'active'
 * - Subscription status is 'trialing'
 * - Subscription status is 'past_due' with grace period
 *
 * Blocks access if:
 * - Subscription status is 'canceled', 'unpaid', or 'incomplete'
 * - Subscription has expired without grace period
 * - No subscription exists
 */
class EnsureSubscriptionActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // DEVELOPMENT: Bypass subscription check in local environment
        if (config('app.env') === 'local') {
            return $next($request);
        }

        $user = $request->user();

        // If no user or no merchant, deny access
        if (!$user || !$user->merchant) {
            Log::warning('Subscription check failed: No user or merchant', [
                'user_id' => $user?->id,
            ]);

            return $this->denyAccess($request, 'No merchant account found.');
        }

        $merchant = $user->merchant;

        // Check if merchant has active subscription
        if (!$merchant->hasActiveSubscription()) {
            Log::info('Subscription check failed: Inactive subscription', [
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
                'subscription_status' => $merchant->subscription_status,
            ]);

            return $this->denyAccess($request, 'Your subscription is not active. Please update your subscription to continue.');
        }

        // Check grace period for past_due subscriptions
        if ($merchant->subscription_status === 'past_due') {
            if (!$merchant->isWithinGracePeriod()) {
                Log::info('Subscription check failed: Grace period expired', [
                    'user_id' => $user->id,
                    'merchant_id' => $merchant->id,
                ]);

                return $this->denyAccess($request, 'Your payment is past due and grace period has expired. Please update your payment method.');
            }

            // Allow access but log warning
            Log::warning('Subscription past due but within grace period', [
                'user_id' => $user->id,
                'merchant_id' => $merchant->id,
                'grace_days_remaining' => $merchant->gracePeriodDaysRemaining(),
            ]);
        }

        // Subscription is active - allow access
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
                'error' => 'Subscription required',
                'message' => $message,
                'subscription_status' => $request->user()?->merchant?->subscription_status,
            ], 403);
        }

        // For web requests using Inertia, redirect to subscriptions page
        if ($request->header('X-Inertia')) {
            return redirect()->route('subscriptions.index')
                ->with('error', $message);
        }

        // For regular web requests, redirect with error
        return redirect()->route('subscriptions.index')
            ->with('error', $message);
    }
}
