<?php

namespace Database\Factories;

use App\Models\Merchant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class MerchantFactory extends Factory
{
    protected $model = Merchant::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'slug' => Str::slug($this->faker->unique()->company()),
            'owner_user_id' => null, // Set via withOwner() to avoid circular dependency
            'onboarding_complete' => false,
            'platform_fee_percentage' => 2.90,
            'platform_fee_fixed_cents' => 30,
        ];
    }

    /**
     * Create a merchant with an owner user.
     */
    public function withOwner(): static
    {
        return $this->afterCreating(function (Merchant $merchant) {
            $owner = User::factory()->owner()->forMerchant($merchant)->create();
            $merchant->update(['owner_user_id' => $owner->id]);
        });
    }

    /**
     * Indicate that the merchant has completed onboarding.
     */
    public function onboarded(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_complete' => true,
        ]);
    }

    /**
     * Indicate that the merchant has Stripe Connect setup.
     */
    public function withStripeConnect(): static
    {
        return $this->state(fn (array $attributes) => [
            'stripe_connect_account_id' => 'acct_' . Str::random(16),
            'stripe_onboarding_complete' => true,
            'stripe_charges_enabled' => true,
            'stripe_payouts_enabled' => true,
            'stripe_details_submitted' => true,
            'stripe_country' => 'US',
            'stripe_verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the merchant has an active subscription.
     */
    public function withActiveSubscription(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_status' => 'active',
            'stripe_subscription_id' => 'sub_' . Str::random(16),
            'stripe_customer_id' => 'cus_' . Str::random(16),
            'subscription_plan_id' => 'pro',
            'subscription_current_period_start' => now()->startOfMonth(),
            'subscription_current_period_end' => now()->endOfMonth(),
        ]);
    }

    /**
     * Indicate that the merchant has a trialing subscription.
     */
    public function withTrialSubscription(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_status' => 'trialing',
            'stripe_subscription_id' => 'sub_' . Str::random(16),
            'stripe_customer_id' => 'cus_' . Str::random(16),
            'subscription_plan_id' => 'basic',
            'subscription_trial_end' => now()->addDays(14),
            'subscription_current_period_start' => now(),
            'subscription_current_period_end' => now()->addDays(14),
        ]);
    }

    /**
     * Indicate that the merchant has an expired subscription.
     */
    public function withExpiredSubscription(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_status' => 'canceled',
            'stripe_subscription_id' => 'sub_' . Str::random(16),
            'stripe_customer_id' => 'cus_' . Str::random(16),
            'subscription_plan_id' => 'basic',
            'subscription_canceled_at' => now()->subDays(10),
            'subscription_ended_at' => now()->subDays(5),
        ]);
    }

    /**
     * Indicate that the merchant subscription is past due.
     */
    public function withPastDueSubscription(): static
    {
        return $this->state(fn (array $attributes) => [
            'subscription_status' => 'past_due',
            'stripe_subscription_id' => 'sub_' . Str::random(16),
            'stripe_customer_id' => 'cus_' . Str::random(16),
            'subscription_plan_id' => 'pro',
            'subscription_current_period_start' => now()->subMonth(),
            'subscription_current_period_end' => now()->subDays(3),
        ]);
    }

    /**
     * Fully configured merchant ready to accept payments.
     */
    public function fullyConfigured(): static
    {
        return $this->onboarded()
            ->withStripeConnect()
            ->withActiveSubscription();
    }
}
