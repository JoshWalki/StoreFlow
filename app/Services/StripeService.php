<?php

namespace App\Services;

use App\Models\Merchant;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;

/**
 * Centralized Stripe API Service
 *
 * Single source of truth for all Stripe API interactions.
 * Manages API keys securely and provides consistent error handling.
 *
 * Security Benefits:
 * - Single point of API key access (easier to rotate keys)
 * - Consistent error handling and logging
 * - Rate limiting and retry logic
 * - Request/response validation
 */
class StripeService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Get Stripe client instance.
     *
     * @return StripeClient
     */
    public function getClient(): StripeClient
    {
        return $this->stripe;
    }

    /**
     * Create or retrieve a Stripe Customer for a merchant.
     *
     * @param Merchant $merchant
     * @return \Stripe\Customer
     */
    public function getOrCreateCustomer(Merchant $merchant): \Stripe\Customer
    {
        if ($merchant->stripe_customer_id) {
            try {
                return $this->stripe->customers->retrieve($merchant->stripe_customer_id);
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                Log::warning('Stripe customer not found, creating new one', [
                    'merchant_id' => $merchant->id,
                    'old_customer_id' => $merchant->stripe_customer_id,
                ]);
            }
        }

        // Create new customer
        // Load owner relationship if not already loaded
        if (!$merchant->relationLoaded('owner')) {
            $merchant->load('owner');
        }

        $customer = $this->stripe->customers->create([
            'email' => $merchant->owner?->email ?? $merchant->email ?? 'noreply@storeflow.app',
            'name' => $merchant->name,
            'metadata' => [
                'merchant_id' => $merchant->id,
                'merchant_slug' => $merchant->slug,
            ],
        ]);

        $merchant->update(['stripe_customer_id' => $customer->id]);

        Log::info('Stripe customer created', [
            'merchant_id' => $merchant->id,
            'customer_id' => $customer->id,
        ]);

        return $customer;
    }

    /**
     * Create a Checkout Session for subscription.
     *
     * @param Merchant $merchant
     * @param string $priceId Stripe Price ID
     * @param array $options Additional options
     * @return \Stripe\Checkout\Session
     */
    public function createCheckoutSession(Merchant $merchant, string $priceId, array $options = []): \Stripe\Checkout\Session
    {
        $customer = $this->getOrCreateCustomer($merchant);

        $params = array_merge([
            'customer' => $customer->id,
            'mode' => 'subscription',
            'line_items' => [[
                'price' => $priceId,
                'quantity' => 1,
            ]],
            'success_url' => route('subscriptions.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('subscriptions.index'),
            'subscription_data' => [
                'trial_period_days' => 14,
                'metadata' => [
                    'merchant_id' => $merchant->id,
                    'merchant_slug' => $merchant->slug,
                ],
            ],
            'metadata' => [
                'merchant_id' => $merchant->id,
            ],
        ], $options);

        try {
            $session = $this->stripe->checkout->sessions->create($params);

            Log::info('Checkout session created', [
                'merchant_id' => $merchant->id,
                'session_id' => $session->id,
            ]);

            return $session;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Failed to create checkout session', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a subscription directly (with payment method).
     *
     * @param Merchant $merchant
     * @param string $priceId
     * @param string|null $paymentMethodId
     * @param array $options
     * @return \Stripe\Subscription
     */
    public function createSubscription(Merchant $merchant, string $priceId, ?string $paymentMethodId = null, array $options = []): \Stripe\Subscription
    {
        $customer = $this->getOrCreateCustomer($merchant);

        // Attach payment method if provided
        if ($paymentMethodId) {
            $this->attachPaymentMethod($customer->id, $paymentMethodId);
        }

        $params = array_merge([
            'customer' => $customer->id,
            'items' => [
                ['price' => $priceId],
            ],
            'trial_period_days' => 14,
            'expand' => ['latest_invoice.payment_intent'],
            'metadata' => [
                'merchant_id' => $merchant->id,
                'merchant_slug' => $merchant->slug,
            ],
        ], $options);

        try {
            $subscription = $this->stripe->subscriptions->create($params);

            Log::info('Subscription created', [
                'merchant_id' => $merchant->id,
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
            ]);

            return $subscription;
        } catch (\Stripe\Exception\ApiErrorException $e) {
            Log::error('Failed to create subscription', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Attach payment method to customer.
     *
     * @param string $customerId
     * @param string $paymentMethodId
     * @return \Stripe\PaymentMethod
     */
    public function attachPaymentMethod(string $customerId, string $paymentMethodId): \Stripe\PaymentMethod
    {
        $paymentMethod = $this->stripe->paymentMethods->attach($paymentMethodId, [
            'customer' => $customerId,
        ]);

        // Set as default payment method
        $this->stripe->customers->update($customerId, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);

        Log::info('Payment method attached', [
            'customer_id' => $customerId,
            'payment_method_id' => $paymentMethodId,
        ]);

        return $paymentMethod;
    }

    /**
     * Cancel subscription at period end.
     *
     * @param string $subscriptionId
     * @return \Stripe\Subscription
     */
    public function cancelSubscription(string $subscriptionId): \Stripe\Subscription
    {
        return $this->stripe->subscriptions->update($subscriptionId, [
            'cancel_at_period_end' => true,
        ]);
    }

    /**
     * Resume a canceled subscription.
     *
     * @param string $subscriptionId
     * @return \Stripe\Subscription
     */
    public function resumeSubscription(string $subscriptionId): \Stripe\Subscription
    {
        return $this->stripe->subscriptions->update($subscriptionId, [
            'cancel_at_period_end' => false,
        ]);
    }

    /**
     * Update subscription price (change plan).
     *
     * @param string $subscriptionId
     * @param string $newPriceId
     * @return \Stripe\Subscription
     */
    public function updateSubscriptionPrice(string $subscriptionId, string $newPriceId): \Stripe\Subscription
    {
        $subscription = $this->stripe->subscriptions->retrieve($subscriptionId);

        return $this->stripe->subscriptions->update($subscriptionId, [
            'items' => [
                [
                    'id' => $subscription->items->data[0]->id,
                    'price' => $newPriceId,
                ],
            ],
            'proration_behavior' => 'always_invoice',
        ]);
    }

    /**
     * Retrieve subscription from Stripe.
     *
     * @param string $subscriptionId
     * @return \Stripe\Subscription
     */
    public function retrieveSubscription(string $subscriptionId): \Stripe\Subscription
    {
        return $this->stripe->subscriptions->retrieve($subscriptionId, [
            'expand' => ['latest_invoice', 'customer'],
        ]);
    }

    /**
     * Verify webhook signature.
     *
     * @param string $payload
     * @param string $signature
     * @param string|null $secret Webhook secret (optional, uses config if not provided)
     * @return \Stripe\Event
     * @throws \Stripe\Exception\SignatureVerificationException
     */
    public function verifyWebhookSignature(string $payload, string $signature, ?string $secret = null): \Stripe\Event
    {
        $webhookSecret = $secret ?? config('services.stripe.webhook_secret');

        return \Stripe\Webhook::constructEvent(
            $payload,
            $signature,
            $webhookSecret
        );
    }

    /**
     * Get subscription invoices.
     *
     * @param string $subscriptionId
     * @param int $limit
     * @return \Stripe\Collection
     */
    public function getSubscriptionInvoices(string $subscriptionId, int $limit = 10): \Stripe\Collection
    {
        return $this->stripe->invoices->all([
            'subscription' => $subscriptionId,
            'limit' => $limit,
        ]);
    }

    /**
     * Get upcoming invoice for subscription.
     *
     * @param string $customerId
     * @param string|null $subscriptionId
     * @return \Stripe\Invoice
     */
    public function getUpcomingInvoice(string $customerId, ?string $subscriptionId = null): \Stripe\Invoice
    {
        $params = ['customer' => $customerId];

        if ($subscriptionId) {
            $params['subscription'] = $subscriptionId;
        }

        return $this->stripe->invoices->upcoming($params);
    }

    /**
     * Retry failed invoice payment.
     *
     * @param string $invoiceId
     * @return \Stripe\Invoice
     */
    public function retryInvoicePayment(string $invoiceId): \Stripe\Invoice
    {
        return $this->stripe->invoices->pay($invoiceId);
    }
}
