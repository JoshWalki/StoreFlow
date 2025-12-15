<?php

namespace App\Services;

use App\Models\Merchant;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Services\StripeService;

/**
 * Subscription Management Service
 *
 * Handles merchant subscription lifecycle:
 * - Subscription status checks
 * - Trial period management
 * - Grace period handling
 * - Subscription expiration logic
 * - Payment failure tracking
 */
class SubscriptionService
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
    }
    /**
     * Create or update subscription from Stripe webhook data.
     *
     * @param Merchant $merchant
     * @param array $stripeSubscription Stripe subscription object
     * @return Merchant
     */
    public function syncSubscriptionFromStripe(Merchant $merchant, array $stripeSubscription): Merchant
    {
        // Debug: Log what Stripe is sending
        Log::debug('Syncing subscription from Stripe', [
            'merchant_id' => $merchant->id,
            'subscription_id' => $stripeSubscription['id'] ?? null,
            'status' => $stripeSubscription['status'] ?? null,
            'has_current_period_start' => isset($stripeSubscription['current_period_start']),
            'current_period_start_value' => $stripeSubscription['current_period_start'] ?? null,
            'has_current_period_end' => isset($stripeSubscription['current_period_end']),
            'current_period_end_value' => $stripeSubscription['current_period_end'] ?? null,
        ]);

        $data = [
            'stripe_subscription_id' => $stripeSubscription['id'],
            'stripe_customer_id' => $stripeSubscription['customer'],
            'subscription_status' => $stripeSubscription['status'],
            'subscription_current_period_start' => isset($stripeSubscription['current_period_start'])
                ? Carbon::createFromTimestamp($stripeSubscription['current_period_start'])
                : null,
            'subscription_current_period_end' => isset($stripeSubscription['current_period_end'])
                ? Carbon::createFromTimestamp($stripeSubscription['current_period_end'])
                : null,
            'subscription_trial_end' => isset($stripeSubscription['trial_end'])
                ? Carbon::createFromTimestamp($stripeSubscription['trial_end'])
                : null,
            'subscription_cancel_at_period_end' => $stripeSubscription['cancel_at_period_end'] ?? false,
            'subscription_canceled_at' => isset($stripeSubscription['canceled_at'])
                ? Carbon::createFromTimestamp($stripeSubscription['canceled_at'])
                : null,
            'subscription_ended_at' => isset($stripeSubscription['ended_at'])
                ? Carbon::createFromTimestamp($stripeSubscription['ended_at'])
                : null,
        ];

        // Extract plan ID from subscription items
        if (isset($stripeSubscription['items']['data'][0]['price']['id'])) {
            $stripePriceId = $stripeSubscription['items']['data'][0]['price']['id'];
            $data['subscription_plan_id'] = $this->resolvePlanId($stripePriceId);
        }

        $merchant->update($data);

        Log::info('Subscription synced from Stripe', [
            'merchant_id' => $merchant->id,
            'status' => $data['subscription_status'],
        ]);

        return $merchant->fresh();
    }

    /**
     * Check if merchant's subscription is expired or inactive.
     *
     * @param Merchant $merchant
     * @return bool
     */
    public function isSubscriptionExpired(Merchant $merchant): bool
    {
        return $merchant->isSubscriptionExpired();
    }

    /**
     * Check if merchant can accept payments.
     *
     * @param Merchant $merchant
     * @return bool
     */
    public function canAcceptPayments(Merchant $merchant): bool
    {
        return $merchant->canAcceptPayments();
    }

    /**
     * Check if merchant is in grace period after payment failure.
     *
     * @param Merchant $merchant
     * @return bool
     */
    public function isInGracePeriod(Merchant $merchant): bool
    {
        return $merchant->isInGracePeriod();
    }

    /**
     * Get days remaining in grace period.
     *
     * @param Merchant $merchant
     * @return int
     */
    public function getGracePeriodDaysRemaining(Merchant $merchant): int
    {
        if (!$this->isInGracePeriod($merchant)) {
            return 0;
        }

        $gracePeriodEnd = $merchant->subscription_current_period_end->addDays(3);
        return max(0, now()->diffInDays($gracePeriodEnd, false));
    }

    /**
     * Get days remaining in trial period.
     *
     * @param Merchant $merchant
     * @return int
     */
    public function getTrialDaysRemaining(Merchant $merchant): int
    {
        if ($merchant->subscription_status !== 'trialing' || !$merchant->subscription_trial_end) {
            return 0;
        }

        return max(0, now()->diffInDays($merchant->subscription_trial_end, false));
    }

    /**
     * Handle subscription payment failure.
     *
     * @param Merchant $merchant
     * @param int $attemptCount Number of payment attempts
     * @return void
     */
    public function handlePaymentFailure(Merchant $merchant, int $attemptCount = 1): void
    {
        Log::warning('Subscription payment failed', [
            'merchant_id' => $merchant->id,
            'subscription_id' => $merchant->stripe_subscription_id,
            'attempt_count' => $attemptCount,
        ]);

        // Update status to past_due if not already
        if ($merchant->subscription_status !== 'past_due') {
            $merchant->update(['subscription_status' => 'past_due']);
        }

        // TODO: Send notification to merchant
        // Notification::send($merchant->owner, new SubscriptionPaymentFailedNotification($merchant));
    }

    /**
     * Handle subscription payment success.
     *
     * @param Merchant $merchant
     * @return void
     */
    public function handlePaymentSuccess(Merchant $merchant): void
    {
        Log::info('Subscription payment succeeded', [
            'merchant_id' => $merchant->id,
            'subscription_id' => $merchant->stripe_subscription_id,
        ]);

        // Reactivate subscription if it was past_due
        if (in_array($merchant->subscription_status, ['past_due', 'unpaid'])) {
            $merchant->update(['subscription_status' => 'active']);

            // Reactivate stores if payment is settled
            $storesReactivated = $this->reactivateStores($merchant);

            Log::info('Subscription reactivated after payment', [
                'merchant_id' => $merchant->id,
                'stores_reactivated' => $storesReactivated,
            ]);

            // TODO: Send notification
            // Notification::send($merchant->owner, new SubscriptionReactivatedNotification($merchant));
        }
    }

    /**
     * Handle subscription cancellation.
     *
     * @param Merchant $merchant
     * @return void
     */
    public function handleSubscriptionCanceled(Merchant $merchant): void
    {
        Log::info('Subscription canceled', [
            'merchant_id' => $merchant->id,
            'subscription_id' => $merchant->stripe_subscription_id,
        ]);

        $merchant->update([
            'subscription_status' => 'canceled',
            'subscription_ended_at' => now(),
        ]);

        // TODO: Send notification
        // Notification::send($merchant->owner, new SubscriptionCanceledNotification($merchant));
    }

    /**
     * Handle trial ending soon (3 days warning).
     *
     * @param Merchant $merchant
     * @return void
     */
    public function handleTrialWillEnd(Merchant $merchant): void
    {
        Log::info('Trial will end soon', [
            'merchant_id' => $merchant->id,
            'trial_end' => $merchant->subscription_trial_end,
        ]);

        // TODO: Send notification
        // Notification::send($merchant->owner, new TrialEndingNotification($merchant));
    }

    /**
     * Get subscription status message for display.
     *
     * @param Merchant $merchant
     * @return string
     */
    public function getStatusMessage(Merchant $merchant): string
    {
        return $merchant->getSubscriptionStatusMessage();
    }

    /**
     * Get subscription badge color for UI.
     *
     * @param Merchant $merchant
     * @return string Tailwind color class
     */
    public function getStatusBadgeColor(Merchant $merchant): string
    {
        return match($merchant->subscription_status) {
            'active' => 'green',
            'trialing' => 'blue',
            'past_due' => 'yellow',
            'unpaid', 'canceled', 'incomplete_expired' => 'red',
            'paused' => 'gray',
            default => 'gray',
        };
    }

    /**
     * Check if merchant needs to update payment method.
     *
     * @param Merchant $merchant
     * @return bool
     */
    public function needsPaymentMethodUpdate(Merchant $merchant): bool
    {
        return in_array($merchant->subscription_status, ['past_due', 'unpaid', 'incomplete']);
    }

    /**
     * Resolve plan ID from Stripe price ID.
     *
     * @param string $stripePriceId
     * @return string
     */
    protected function resolvePlanId(string $stripePriceId): string
    {
        $plans = config('services.stripe.plans', []);

        foreach ($plans as $planId => $priceId) {
            if ($priceId === $stripePriceId) {
                return $planId;
            }
        }

        return 'unknown';
    }

    /**
     * Get subscription metrics for dashboard.
     *
     * @param Merchant $merchant
     * @return array
     */
    public function getSubscriptionMetrics(Merchant $merchant): array
    {
        return [
            'status' => $merchant->subscription_status,
            'is_active' => $merchant->hasActiveSubscription(),
            'is_expired' => $this->isSubscriptionExpired($merchant),
            'is_in_grace_period' => $this->isInGracePeriod($merchant),
            'can_accept_payments' => $this->canAcceptPayments($merchant),
            'trial_days_remaining' => $this->getTrialDaysRemaining($merchant),
            'grace_period_days_remaining' => $this->getGracePeriodDaysRemaining($merchant),
            'current_period_end' => $merchant->subscription_current_period_end?->toDateString(),
            'plan_id' => $merchant->subscription_plan_id,
            'status_message' => $this->getStatusMessage($merchant),
            'badge_color' => $this->getStatusBadgeColor($merchant),
            'needs_payment_update' => $this->needsPaymentMethodUpdate($merchant),
        ];
    }

    /**
     * Create a Stripe subscription for a merchant.
     *
     * @param Merchant $merchant
     * @param string $planId
     * @param string|null $paymentMethodId
     * @return array Stripe subscription object
     */
    public function createSubscription(Merchant $merchant, string $planId, ?string $paymentMethodId = null): array
    {
        // Get price ID from config or database
        $priceId = config('services.stripe.price_basic') ?? env('STRIPE_PRICE_BASIC');

        // Create subscription via centralized StripeService
        $subscription = $this->stripeService->createSubscription(
            $merchant,
            $priceId,
            $paymentMethodId,
            [
                'metadata' => [
                    'plan_id' => $planId,
                ],
            ]
        );

        // Sync subscription data
        $this->syncSubscriptionFromStripe($merchant, $subscription->toArray());

        return $subscription->toArray();
    }

    /**
     * Check if subscription is overdue (3+ days past due).
     *
     * @param Merchant $merchant
     * @return bool
     */
    public function isSubscriptionOverdue(Merchant $merchant): bool
    {
        if ($merchant->subscription_status !== 'past_due') {
            return false;
        }

        if (!$merchant->subscription_current_period_end) {
            return false;
        }

        // Check if more than 3 days past the period end
        $overdueDate = $merchant->subscription_current_period_end->addDays(3);
        return $overdueDate->isPast();
    }

    /**
     * Deactivate all stores for a merchant due to subscription issues.
     *
     * @param Merchant $merchant
     * @param string $reason
     * @return int Number of stores deactivated
     */
    public function deactivateStores(Merchant $merchant, string $reason = 'subscription_overdue'): int
    {
        $storesDeactivated = 0;

        foreach ($merchant->stores as $store) {
            if ($store->is_active) {
                $store->update([
                    'is_active' => false,
                    'deactivated_at' => now(),
                    'deactivation_reason' => $reason,
                ]);
                $storesDeactivated++;
            }
        }

        Log::warning('Stores deactivated due to subscription issue', [
            'merchant_id' => $merchant->id,
            'stores_deactivated' => $storesDeactivated,
            'reason' => $reason,
        ]);

        return $storesDeactivated;
    }

    /**
     * Reactivate stores after payment settlement.
     *
     * @param Merchant $merchant
     * @return int Number of stores reactivated
     */
    public function reactivateStores(Merchant $merchant): int
    {
        // Verify payment is settled
        if (!$this->hasSettledPayment($merchant)) {
            Log::warning('Cannot reactivate stores - payment not settled', [
                'merchant_id' => $merchant->id,
            ]);
            return 0;
        }

        $storesReactivated = 0;

        foreach ($merchant->stores as $store) {
            if (!$store->is_active && in_array($store->deactivation_reason, ['subscription_overdue', 'payment_failed'])) {
                $store->update([
                    'is_active' => true,
                    'deactivated_at' => null,
                    'deactivation_reason' => null,
                ]);
                $storesReactivated++;
            }
        }

        Log::info('Stores reactivated after payment settlement', [
            'merchant_id' => $merchant->id,
            'stores_reactivated' => $storesReactivated,
        ]);

        return $storesReactivated;
    }

    /**
     * Check if merchant has a settled payment (subscription is active or trialing).
     *
     * @param Merchant $merchant
     * @return bool
     */
    public function hasSettledPayment(Merchant $merchant): bool
    {
        return in_array($merchant->subscription_status, ['active', 'trialing']);
    }
}
