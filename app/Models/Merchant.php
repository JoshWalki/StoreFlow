<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'owner_user_id',
        'onboarding_complete',
        // Stripe Connect fields
        'stripe_account_id',
        'stripe_connect_account_id',
        'stripe_onboarding_complete',
        'stripe_charges_enabled',
        'stripe_payouts_enabled',
        'stripe_details_submitted',
        'stripe_requirements',
        'stripe_country',
        'stripe_verified_at',
        // Subscription fields
        'subscription_status',
        'stripe_subscription_id',
        'stripe_customer_id',
        'subscription_plan_id',
        'subscription_current_period_start',
        'subscription_current_period_end',
        'subscription_trial_end',
        'subscription_cancel_at_period_end',
        'subscription_canceled_at',
        'subscription_ended_at',
        // Platform fees
        'platform_fee_percentage',
        'platform_fee_fixed_cents',
    ];

    protected $casts = [
        'onboarding_complete' => 'boolean',
        'stripe_onboarding_complete' => 'boolean',
        'stripe_charges_enabled' => 'boolean',
        'stripe_payouts_enabled' => 'boolean',
        'stripe_details_submitted' => 'boolean',
        'stripe_requirements' => 'array',
        'stripe_verified_at' => 'datetime',
        'subscription_current_period_start' => 'datetime',
        'subscription_current_period_end' => 'datetime',
        'subscription_trial_end' => 'datetime',
        'subscription_cancel_at_period_end' => 'boolean',
        'subscription_canceled_at' => 'datetime',
        'subscription_ended_at' => 'datetime',
        'platform_fee_percentage' => 'decimal:2',
    ];

    /**
     * Get the owner of the merchant.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    /**
     * Get all stores for this merchant.
     */
    public function stores(): HasMany
    {
        return $this->hasMany(Store::class);
    }

    /**
     * Get all users for this merchant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // === STRIPE CONNECT HELPER METHODS ===

    /**
     * Check if merchant can accept payments right now.
     */
    public function canAcceptPayments(): bool
    {
        return $this->stripe_charges_enabled
            && $this->stripe_payouts_enabled
            && $this->hasActiveSubscription();
    }

    /**
     * Check if subscription is expired or inactive.
     */
    public function isSubscriptionExpired(): bool
    {
        // No subscription status = never had a subscription (not expired, just new user)
        if (!$this->subscription_status) {
            return false;
        }

        // These statuses mean subscription existed but is now expired
        $expiredStatuses = ['canceled', 'unpaid', 'incomplete_expired'];
        if (in_array($this->subscription_status, $expiredStatuses)) {
            return true;
        }

        // Subscription ended in the past
        if ($this->subscription_ended_at && $this->subscription_ended_at->isPast()) {
            return true;
        }

        return false;
    }

    /**
     * Check if merchant needs Stripe Connect onboarding.
     */
    public function needsStripeOnboarding(): bool
    {
        return empty($this->stripe_connect_account_id) || !$this->stripe_onboarding_complete;
    }

    /**
     * Check if subscription is active or in trial.
     */
    public function hasActiveSubscription(): bool
    {
        return in_array($this->subscription_status, ['active', 'trialing']);
    }

    /**
     * Check if subscription is in grace period (past_due).
     */
    public function isInGracePeriod(): bool
    {
        if ($this->subscription_status !== 'past_due') {
            return false;
        }

        if (!$this->subscription_current_period_end) {
            return false;
        }

        // 3-day grace period after period end (as per business requirements)
        $gracePeriodEnd = $this->subscription_current_period_end->addDays(3);
        return $gracePeriodEnd->isFuture();
    }

    /**
     * Calculate platform fee for an order amount.
     *
     * @param int $amountCents Order amount in cents
     * @return int Platform fee in cents
     */
    public function calculatePlatformFee(int $amountCents): int
    {
        $percentageFee = (int) ($amountCents * ($this->platform_fee_percentage / 100));
        return $percentageFee + $this->platform_fee_fixed_cents;
    }

    /**
     * Get subscription status message for UI display.
     */
    public function getSubscriptionStatusMessage(): string
    {
        if ($this->isSubscriptionExpired()) {
            return 'Your subscription has expired. Please update your payment method to continue.';
        }

        if ($this->subscription_status === 'past_due') {
            if ($this->isInGracePeriod()) {
                $daysRemaining = now()->diffInDays(
                    $this->subscription_current_period_end->addDays(3),
                    false
                );
                return "Payment failed. You have {$daysRemaining} days to update your payment method.";
            }
            return 'Payment failed. Please update your payment method immediately.';
        }

        if ($this->subscription_status === 'trialing' && $this->subscription_trial_end) {
            $daysRemaining = now()->diffInDays($this->subscription_trial_end, false);
            return "Free trial: {$daysRemaining} days remaining.";
        }

        if ($this->subscription_status === 'active') {
            return 'Subscription active';
        }

        return 'No active subscription';
    }
}
