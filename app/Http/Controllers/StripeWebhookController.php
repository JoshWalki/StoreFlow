<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\Order;
use App\Models\StripeWebhookEvent;
use App\Services\StripeConnectService;
use App\Services\StripePaymentService;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    protected StripeConnectService $stripeConnectService;
    protected SubscriptionService $subscriptionService;
    protected StripePaymentService $paymentService;

    public function __construct(
        StripeConnectService $stripeConnectService,
        SubscriptionService $subscriptionService,
        StripePaymentService $paymentService
    ) {
        $this->stripeConnectService = $stripeConnectService;
        $this->subscriptionService = $subscriptionService;
        $this->paymentService = $paymentService;
    }

    /**
     * Handle incoming Stripe webhook.
     */
    public function handleWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook_secret');

        try {
            // Verify webhook signature
            $event = $this->stripeConnectService->verifyWebhookSignature($payload, $signature, $secret);

            // Check for duplicate webhook (idempotency)
            if (StripeWebhookEvent::where('event_id', $event['id'])->exists()) {
                Log::info('Duplicate webhook received', ['event_id' => $event['id']]);
                return response()->json(['message' => 'Webhook already processed'], 200);
            }

            // Log webhook event
            $webhookRecord = StripeWebhookEvent::create([
                'event_id' => $event['id'],
                'type' => $event['type'],
                'payload' => $event,
                'processed' => false,
            ]);

            // Route to appropriate handler
            $result = $this->routeWebhookEvent($event);

            // Mark as processed
            $webhookRecord->markAsProcessed();

            Log::info('Webhook processed successfully', [
                'event_id' => $event['id'],
                'type' => $event['type'],
            ]);

            return response()->json(['message' => 'Webhook processed'], 200);

        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Webhook signature verification failed', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Invalid signature'], 400);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (isset($webhookRecord)) {
                $webhookRecord->markAsFailed($e->getMessage());
            }

            return response()->json(['error' => 'Webhook processing failed'], 500);
        }
    }

    /**
     * Route webhook event to appropriate handler.
     */
    protected function routeWebhookEvent(array $event): mixed
    {
        return match ($event['type']) {
            // Connect Account events
            'account.updated' => $this->handleAccountUpdated($event),

            // Checkout events
            'checkout.session.completed' => $this->handleCheckoutSessionCompleted($event),

            // Subscription events
            'customer.subscription.created' => $this->handleSubscriptionCreated($event),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            'customer.subscription.trial_will_end' => $this->handleSubscriptionTrialWillEnd($event),

            // Invoice events
            'invoice.payment_succeeded' => $this->handleInvoicePaymentSucceeded($event),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event),

            // Payment events
            'payment_intent.succeeded' => $this->handlePaymentSucceeded($event),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event),

            // Charge events (for refunds)
            'charge.refunded' => $this->handleChargeRefunded($event),

            default => Log::info('Unhandled webhook event type', ['type' => $event['type']]),
        };
    }

    /**
     * Handle account.updated webhook.
     */
    protected function handleAccountUpdated(array $event): void
    {
        $accountData = $event['data']['object'];
        $accountId = $accountData['id'];

        $merchant = Merchant::where('stripe_connect_account_id', $accountId)->first();

        if (!$merchant) {
            Log::warning('Account updated webhook for unknown merchant', ['account_id' => $accountId]);
            return;
        }

        // Sync account data
        $this->stripeConnectService->syncAccountData($merchant, $accountData);

        Log::info('Merchant Stripe account synced', [
            'merchant_id' => $merchant->id,
            'account_id' => $accountId,
            'charges_enabled' => $merchant->stripe_charges_enabled,
            'payouts_enabled' => $merchant->stripe_payouts_enabled,
        ]);
    }

    /**
     * Handle checkout.session.completed webhook.
     *
     * This is the FIRST event after a successful checkout.
     * We fetch the full subscription from Stripe API to ensure we get all fields.
     */
    protected function handleCheckoutSessionCompleted(array $event): void
    {
        $session = $event['data']['object'];
        $customerId = $session['customer'] ?? null;
        $subscriptionId = $session['subscription'] ?? null;

        if (!$customerId || !$subscriptionId) {
            Log::warning('Checkout session completed without customer or subscription', [
                'session_id' => $session['id'],
                'has_customer' => isset($session['customer']),
                'has_subscription' => isset($session['subscription']),
            ]);
            return;
        }

        $merchant = Merchant::where('stripe_customer_id', $customerId)->first();

        if (!$merchant) {
            Log::warning('Checkout completed for unknown customer', ['customer_id' => $customerId]);
            return;
        }

        // Fetch FULL subscription object from Stripe API to ensure complete data
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $fullSubscription = $stripe->subscriptions->retrieve($subscriptionId);

            Log::info('Fetched full subscription from Stripe API', [
                'subscription_id' => $subscriptionId,
                'status' => $fullSubscription->status,
                'has_current_period_start' => isset($fullSubscription->current_period_start),
                'has_current_period_end' => isset($fullSubscription->current_period_end),
                'current_period_start' => $fullSubscription->current_period_start ?? null,
                'current_period_end' => $fullSubscription->current_period_end ?? null,
            ]);

            // Check if trial should be skipped (for returning users)
            $metadata = $fullSubscription->metadata->toArray();
            if (isset($metadata['trial_already_used']) && $metadata['trial_already_used'] === 'true') {
                // Cancel trial immediately for returning users AND reset billing cycle
                try {
                    $now = time();
                    $stripe->subscriptions->update($subscriptionId, [
                        'trial_end' => 'now',
                        'billing_cycle_anchor' => $now,  // Reset billing period to now
                        'proration_behavior' => 'none',  // Don't prorate
                    ]);

                    Log::info('Trial cancelled and billing cycle reset for returning user', [
                        'subscription_id' => $subscriptionId,
                        'merchant_id' => $merchant->id,
                        'billing_cycle_anchor' => $now,
                    ]);

                    // Fetch updated subscription data
                    $fullSubscription = $stripe->subscriptions->retrieve($subscriptionId);

                    Log::debug('Subscription after trial cancellation', [
                        'subscription_id' => $subscriptionId,
                        'current_period_start' => $fullSubscription->current_period_start,
                        'current_period_end' => $fullSubscription->current_period_end,
                        'trial_end' => $fullSubscription->trial_end ?? null,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to cancel trial', [
                        'subscription_id' => $subscriptionId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Sync with full data from API
            $this->subscriptionService->syncSubscriptionFromStripe($merchant, $fullSubscription->toArray());

            Log::info('Checkout session processed successfully', [
                'merchant_id' => $merchant->id,
                'session_id' => $session['id'],
                'subscription_id' => $subscriptionId,
                'status' => $fullSubscription->status,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to fetch subscription from Stripe API', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle customer.subscription.created webhook.
     */
    protected function handleSubscriptionCreated(array $event): void
    {
        $subscription = $event['data']['object'];
        $customerId = $subscription['customer'];
        $subscriptionId = $subscription['id'];

        $merchant = Merchant::where('stripe_customer_id', $customerId)->first();

        if (!$merchant) {
            Log::warning('Subscription created for unknown customer', ['customer_id' => $customerId]);
            return;
        }

        // Fetch full subscription from API to ensure complete data
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $fullSubscription = $stripe->subscriptions->retrieve($subscriptionId);

            Log::debug('Subscription created - API data', [
                'subscription_id' => $subscriptionId,
                'has_current_period_start' => isset($fullSubscription->current_period_start),
                'has_current_period_end' => isset($fullSubscription->current_period_end),
                'current_period_start' => $fullSubscription->current_period_start ?? null,
                'current_period_end' => $fullSubscription->current_period_end ?? null,
            ]);

            $this->subscriptionService->syncSubscriptionFromStripe($merchant, $fullSubscription->toArray());

        } catch (\Exception $e) {
            Log::warning('Failed to fetch full subscription, using webhook data', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);
            // Fallback to webhook data if API call fails
            $this->subscriptionService->syncSubscriptionFromStripe($merchant, $subscription);
        }

        Log::info('Subscription created', [
            'merchant_id' => $merchant->id,
            'subscription_id' => $subscriptionId,
            'status' => $subscription['status'],
        ]);
    }

    /**
     * Handle customer.subscription.updated webhook.
     */
    protected function handleSubscriptionUpdated(array $event): void
    {
        $subscription = $event['data']['object'];
        $subscriptionId = $subscription['id'];

        $merchant = Merchant::where('stripe_subscription_id', $subscriptionId)->first();

        if (!$merchant) {
            Log::warning('Subscription updated for unknown merchant', ['subscription_id' => $subscriptionId]);
            return;
        }

        // Fetch full subscription from API to ensure complete data
        try {
            $stripe = new \Stripe\StripeClient(config('services.stripe.secret'));
            $fullSubscription = $stripe->subscriptions->retrieve($subscriptionId);

            Log::debug('Subscription updated - API data', [
                'subscription_id' => $subscriptionId,
                'has_current_period_start' => isset($fullSubscription->current_period_start),
                'has_current_period_end' => isset($fullSubscription->current_period_end),
                'current_period_start' => $fullSubscription->current_period_start ?? null,
                'current_period_end' => $fullSubscription->current_period_end ?? null,
            ]);

            $this->subscriptionService->syncSubscriptionFromStripe($merchant, $fullSubscription->toArray());

        } catch (\Exception $e) {
            Log::warning('Failed to fetch full subscription, using webhook data', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);
            // Fallback to webhook data if API call fails
            $this->subscriptionService->syncSubscriptionFromStripe($merchant, $subscription);
        }

        Log::info('Subscription updated', [
            'merchant_id' => $merchant->id,
            'subscription_id' => $subscriptionId,
            'status' => $subscription['status'],
        ]);
    }

    /**
     * Handle customer.subscription.deleted webhook.
     */
    protected function handleSubscriptionDeleted(array $event): void
    {
        $subscription = $event['data']['object'];
        $subscriptionId = $subscription['id'];

        $merchant = Merchant::where('stripe_subscription_id', $subscriptionId)->first();

        if (!$merchant) {
            Log::warning('Subscription deleted for unknown merchant', ['subscription_id' => $subscriptionId]);
            return;
        }

        $this->subscriptionService->handleSubscriptionCanceled($merchant);

        Log::info('Subscription canceled', [
            'merchant_id' => $merchant->id,
            'subscription_id' => $subscriptionId,
        ]);
    }

    /**
     * Handle customer.subscription.trial_will_end webhook.
     */
    protected function handleSubscriptionTrialWillEnd(array $event): void
    {
        $subscription = $event['data']['object'];
        $subscriptionId = $subscription['id'];

        $merchant = Merchant::where('stripe_subscription_id', $subscriptionId)->first();

        if (!$merchant) {
            return;
        }

        $this->subscriptionService->handleTrialWillEnd($merchant);

        Log::info('Trial ending soon', [
            'merchant_id' => $merchant->id,
            'subscription_id' => $subscriptionId,
            'trial_end' => $subscription['trial_end'],
        ]);
    }

    /**
     * Handle invoice.payment_succeeded webhook.
     */
    protected function handleInvoicePaymentSucceeded(array $event): void
    {
        $invoice = $event['data']['object'];
        $subscriptionId = $invoice['subscription'] ?? null;

        if (!$subscriptionId) {
            return;
        }

        $merchant = Merchant::where('stripe_subscription_id', $subscriptionId)->first();

        if (!$merchant) {
            return;
        }

        $this->subscriptionService->handlePaymentSuccess($merchant);

        Log::info('Subscription payment succeeded', [
            'merchant_id' => $merchant->id,
            'subscription_id' => $subscriptionId,
            'amount' => $invoice['amount_paid'],
        ]);
    }

    /**
     * Handle invoice.payment_failed webhook.
     */
    protected function handleInvoicePaymentFailed(array $event): void
    {
        $invoice = $event['data']['object'];
        $subscriptionId = $invoice['subscription'] ?? null;

        if (!$subscriptionId) {
            return;
        }

        $merchant = Merchant::where('stripe_subscription_id', $subscriptionId)->first();

        if (!$merchant) {
            return;
        }

        $this->subscriptionService->handlePaymentFailure($merchant, $invoice['attempt_count'] ?? 1);

        Log::warning('Subscription payment failed', [
            'merchant_id' => $merchant->id,
            'subscription_id' => $subscriptionId,
            'attempt' => $invoice['attempt_count'] ?? 1,
        ]);
    }

    /**
     * Handle payment_intent.succeeded webhook.
     */
    protected function handlePaymentSucceeded(array $event): void
    {
        $paymentIntent = $event['data']['object'];
        $paymentIntentId = $paymentIntent['id'];

        $order = $this->paymentService->handlePaymentSucceeded($paymentIntentId);

        if ($order) {
            Log::info('Order payment succeeded', [
                'order_id' => $order->id,
                'payment_intent' => $paymentIntentId,
                'amount' => $paymentIntent['amount'],
            ]);
        }
    }

    /**
     * Handle payment_intent.payment_failed webhook.
     */
    protected function handlePaymentFailed(array $event): void
    {
        $paymentIntent = $event['data']['object'];
        $paymentIntentId = $paymentIntent['id'];

        $order = $this->paymentService->handlePaymentFailed($paymentIntentId);

        if ($order) {
            Log::warning('Order payment failed', [
                'order_id' => $order->id,
                'payment_intent' => $paymentIntentId,
                'error' => $paymentIntent['last_payment_error']['message'] ?? 'Unknown error',
            ]);
        }
    }

    /**
     * Handle charge.refunded webhook.
     */
    protected function handleChargeRefunded(array $event): void
    {
        $charge = $event['data']['object'];
        $paymentIntentId = $charge['payment_intent'] ?? null;

        if (!$paymentIntentId) {
            return;
        }

        $order = Order::where('payment_reference', $paymentIntentId)->first();

        if ($order) {
            Log::info('Order refunded via webhook', [
                'order_id' => $order->id,
                'payment_intent' => $paymentIntentId,
                'amount_refunded' => $charge['amount_refunded'],
            ]);
        }
    }
}
