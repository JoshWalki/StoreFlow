<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionService;
use App\Services\StripeService;
use App\Mail\SubscriptionPurchasedMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;
    protected StripeService $stripeService;

    public function __construct(SubscriptionService $subscriptionService, StripeService $stripeService)
    {
        $this->subscriptionService = $subscriptionService;
        $this->stripeService = $stripeService;
    }

    /**
     * Test endpoint to diagnose checkout issues.
     */
    public function testCheckout(Request $request)
    {
        $diagnostics = [];
        $user = $request->user();

        // Test 1: User exists
        $diagnostics['user'] = [
            'exists' => $user ? true : false,
            'id' => $user?->id,
            'email' => $user?->email,
        ];

        // Test 2: Merchant exists
        $merchant = $user?->merchant;
        $diagnostics['merchant'] = [
            'exists' => $merchant ? true : false,
            'id' => $merchant?->id,
            'name' => $merchant?->name,
            'owner_user_id' => $merchant?->owner_user_id ?? null,
        ];

        // Test 3: Owner relationship
        if ($merchant) {
            try {
                $owner = $merchant->owner;
                $diagnostics['owner'] = [
                    'exists' => $owner ? true : false,
                    'loaded' => $merchant->relationLoaded('owner'),
                    'email' => $owner?->email,
                ];
            } catch (\Exception $e) {
                $diagnostics['owner'] = [
                    'error' => $e->getMessage(),
                ];
            }
        }

        // Test 4: Stripe configuration
        $diagnostics['stripe'] = [
            'secret_key_set' => config('services.stripe.secret') ? 'yes (hidden)' : 'no',
            'price_basic' => config('services.stripe.price_basic'),
            'webhook_secret_set' => config('services.stripe.webhook_secret') ? 'yes (hidden)' : 'no',
        ];

        // Test 5: Try to get or create customer
        if ($merchant) {
            try {
                $stripeService = app(StripeService::class);
                $customer = $stripeService->getOrCreateCustomer($merchant);
                $diagnostics['stripe_customer'] = [
                    'success' => true,
                    'customer_id' => $customer->id,
                    'email' => $customer->email,
                ];
            } catch (\Exception $e) {
                $diagnostics['stripe_customer'] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ];
            }
        }

        return response()->json([
            'status' => 'diagnostics_complete',
            'diagnostics' => $diagnostics,
        ]);
    }

    /**
     * Display available subscription plans.
     */
    public function index(Request $request)
    {
        $plans = SubscriptionPlan::active()
            ->get();

        $merchant = $request->user()->merchant;
        $currentSubscription = null;

        if ($merchant) {
            $currentSubscription = [
                'status' => $merchant->subscription_status,
                'plan_id' => $merchant->subscription_plan_id,
                'current_period_end' => $merchant->subscription_current_period_end,
                'trial_end' => $merchant->subscription_trial_end,
                'can_accept_payments' => $this->subscriptionService->canAcceptPayments($merchant),
            ];
        }

        return Inertia::render('Subscriptions/Index', [
            'plans' => $plans,
            'currentSubscription' => $currentSubscription,
        ]);
    }

    /**
     * Get current subscription status and metrics.
     */
    public function status(Request $request)
    {
        $merchant = $request->user()->merchant;

        if (!$merchant) {
            return response()->json(['error' => 'No merchant found'], 404);
        }

        $metrics = $this->subscriptionService->getSubscriptionMetrics($merchant);

        return response()->json([
            'subscription' => [
                'status' => $merchant->subscription_status,
                'plan_id' => $merchant->subscription_plan_id,
                'stripe_subscription_id' => $merchant->stripe_subscription_id,
                'current_period_start' => $merchant->subscription_current_period_start,
                'current_period_end' => $merchant->subscription_current_period_end,
                'trial_end' => $merchant->subscription_trial_end,
                'ended_at' => $merchant->subscription_ended_at,
            ],
            'metrics' => $metrics,
        ]);
    }

    /**
     * Create a Stripe Checkout Session for subscription.
     *
     * This creates a hosted checkout page where customers can enter payment details.
     * Preferred method for PCI compliance as payment data never touches your server.
     */
    public function createCheckout(Request $request)
    {
        Log::info('Checkout request received', [
            'user_id' => $request->user()?->id,
            'has_merchant' => $request->user()?->merchant ? 'yes' : 'no',
        ]);

        $validated = $request->validate([
            'plan_id' => 'nullable|string|exists:subscription_plans,plan_id',
        ]);

        $merchant = $request->user()->merchant;

        if (!$merchant) {
            Log::error('Checkout failed: No merchant found', [
                'user_id' => $request->user()?->id,
            ]);
            return response()->json(['error' => 'No merchant found'], 404);
        }

        Log::info('Merchant found for checkout', [
            'merchant_id' => $merchant->id,
            'merchant_name' => $merchant->name,
            'has_owner' => $merchant->relationLoaded('owner') ? 'loaded' : 'not_loaded',
        ]);

        // Check if already has active subscription
        // Allow checkout for:
        // 1. Trial ended (trialing status but trial_end in past)
        // 2. Incomplete subscriptions that need payment
        // Block only if truly active or trial still has time remaining
        $isTrialEnded = $merchant->subscription_status === 'trialing'
            && $merchant->subscription_trial_end
            && $merchant->subscription_trial_end->isPast();

        $needsPayment = in_array($merchant->subscription_status, ['incomplete', 'incomplete_expired']);

        // Check if merchant has EVER used a trial before (not just current status)
        // If they have subscription_trial_end or stripe_subscription_id, they've had a subscription/trial
        $hasUsedTrialBefore = $merchant->subscription_trial_end !== null ||
                              $merchant->stripe_subscription_id !== null;

        if ($merchant->subscription_status === 'active' ||
            ($merchant->subscription_status === 'trialing' && !$isTrialEnded)) {
            return response()->json([
                'error' => 'Merchant already has an active subscription',
                'subscription_status' => $merchant->subscription_status,
            ], 400);
        }

        try {
            // Get price ID from config
            // Use price without trial for returning users, price with trial for new users
            if ($hasUsedTrialBefore) {
                $priceId = config('services.stripe.price_basic_no_trial')
                    ?? env('STRIPE_PRICE_BASIC_NO_TRIAL')
                    ?? config('services.stripe.price_basic')
                    ?? env('STRIPE_PRICE_BASIC');
            } else {
                $priceId = config('services.stripe.price_basic') ?? env('STRIPE_PRICE_BASIC');
            }

            Log::info('Creating checkout session', [
                'merchant_id' => $merchant->id,
                'price_id' => $priceId,
                'has_used_trial_before' => $hasUsedTrialBefore,
                'subscription_trial_end' => $merchant->subscription_trial_end?->toDateTimeString(),
                'stripe_subscription_id' => $merchant->stripe_subscription_id,
            ]);

            // Prepare checkout options
            $checkoutOptions = [
                'success_url' => route('subscriptions.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('subscriptions.index'),
            ];

            // Don't offer trial if:
            // 1. They've used a trial before (cancelled/expired subscriptions)
            // 2. Current trial has ended
            // 3. Payment is incomplete
            // Note: We omit trial_period_days entirely to skip trial (Stripe doesn't accept 0)
            if ($hasUsedTrialBefore || $isTrialEnded || $needsPayment) {
                $checkoutOptions['subscription_data'] = [
                    // No trial_period_days = immediate payment
                    'metadata' => [
                        'merchant_id' => $merchant->id,
                        'merchant_slug' => $merchant->slug,
                        'trial_already_used' => $hasUsedTrialBefore ? 'true' : 'false',
                        'reason' => $hasUsedTrialBefore ? 'previous_subscription_existed' :
                                   ($isTrialEnded ? 'trial_ended' : 'payment_incomplete'),
                    ],
                ];
            }

            // Create Checkout Session via StripeService
            $session = $this->stripeService->createCheckoutSession($merchant, $priceId, $checkoutOptions);

            Log::info('Checkout session created', [
                'merchant_id' => $merchant->id,
                'session_id' => $session->id,
            ]);

            return response()->json([
                'checkout_url' => $session->url,
                'session_id' => $session->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Checkout session creation failed', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'error' => 'Failed to create checkout session',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred',
                'debug' => config('app.debug') ? [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                ] : null,
            ], 500);
        }
    }

    /**
     * Handle successful checkout (redirect from Stripe).
     */
    public function checkoutSuccess(Request $request)
    {
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('subscriptions.index')
                ->with('error', 'Invalid checkout session');
        }

        try {
            // Retrieve session from Stripe
            $session = $this->stripeService->getClient()->checkout->sessions->retrieve($sessionId);

            Log::info('Checkout completed successfully', [
                'session_id' => $sessionId,
                'subscription_id' => $session->subscription ?? null,
                'customer_id' => $session->customer ?? null,
            ]);

            // Sync subscription data immediately (fallback if webhook hasn't processed yet)
            if ($session->subscription) {
                $merchant = $request->user()->merchant;

                // Retrieve full subscription from Stripe
                $subscription = $this->stripeService->getClient()->subscriptions->retrieve($session->subscription);

                // Sync subscription data to merchant
                $this->subscriptionService->syncSubscriptionFromStripe($merchant, $subscription->toArray());

                // Reactivate stores if they were deactivated
                $storesReactivated = $this->subscriptionService->reactivateStores($merchant);

                // Send subscription purchased email
                try {
                    if ($merchant->owner && $merchant->owner->email) {
                        Mail::to($merchant->owner->email)->send(new SubscriptionPurchasedMail($merchant));
                        Log::info('Subscription purchased email sent', [
                            'merchant_id' => $merchant->id,
                            'email' => $merchant->owner->email,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to send subscription purchased email', [
                        'merchant_id' => $merchant->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                Log::info('Subscription synced in checkout success', [
                    'merchant_id' => $merchant->id,
                    'subscription_id' => $subscription->id,
                    'status' => $subscription->status,
                    'trial_end' => $subscription->trial_end ?? null,
                    'stores_reactivated' => $storesReactivated,
                ]);
            }

            return redirect()->route('store.settings', ['store' => $request->user()->merchant->stores->first()->id])
                ->with('success', 'Subscription activated successfully! Welcome to StoreFlow.');

        } catch (\Exception $e) {
            Log::error('Checkout success handling failed', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->route('subscriptions.index')
                ->with('error', 'Failed to verify checkout. Please contact support.');
        }
    }

    /**
     * Get billing history (invoices) for the merchant.
     */
    public function invoices(Request $request)
    {
        $merchant = $request->user()->merchant;

        if (!$merchant || !$merchant->stripe_subscription_id) {
            return response()->json(['invoices' => []], 200);
        }

        try {
            $invoices = $this->stripeService->getSubscriptionInvoices(
                $merchant->stripe_subscription_id,
                20 // Get last 20 invoices
            );

            $formattedInvoices = collect($invoices->data)->map(function ($invoice) {
                return [
                    'id' => $invoice->id,
                    'amount' => $invoice->amount_paid / 100, // Convert from cents
                    'currency' => strtoupper($invoice->currency),
                    'status' => $invoice->status,
                    'date' => date('Y-m-d', $invoice->created),
                    'invoice_pdf' => $invoice->invoice_pdf,
                    'hosted_invoice_url' => $invoice->hosted_invoice_url,
                    'period_start' => $invoice->period_start ? date('Y-m-d', $invoice->period_start) : null,
                    'period_end' => $invoice->period_end ? date('Y-m-d', $invoice->period_end) : null,
                ];
            });

            return response()->json([
                'invoices' => $formattedInvoices,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve invoices', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to retrieve invoices',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new subscription with Stripe.
     *
     * Creates a Stripe subscription using the STRIPE_PRICE_BASIC configuration.
     * Automatically creates Stripe Customer if needed and attaches payment method.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|string|exists:subscription_plans,plan_id',
            'payment_method_id' => 'required|string', // Stripe payment method ID
        ]);

        $merchant = $request->user()->merchant;

        if (!$merchant) {
            return response()->json(['error' => 'No merchant found'], 404);
        }

        // Check if already has active subscription
        if ($merchant->hasActiveSubscription()) {
            return response()->json([
                'error' => 'Merchant already has an active subscription',
                'subscription_status' => $merchant->subscription_status,
            ], 400);
        }

        try {
            // Create subscription via service
            $subscription = $this->subscriptionService->createSubscription(
                $merchant,
                $validated['plan_id'],
                $validated['payment_method_id']
            );

            // Reactivate stores if they were deactivated
            $storesReactivated = $this->subscriptionService->reactivateStores($merchant);

            // Send subscription purchased email
            try {
                if ($merchant->owner && $merchant->owner->email) {
                    Mail::to($merchant->owner->email)->send(new SubscriptionPurchasedMail($merchant));
                    Log::info('Subscription purchased email sent', [
                        'merchant_id' => $merchant->id,
                        'email' => $merchant->owner->email,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to send subscription purchased email', [
                    'merchant_id' => $merchant->id,
                    'error' => $e->getMessage(),
                ]);
            }

            Log::info('Subscription created successfully', [
                'merchant_id' => $merchant->id,
                'subscription_id' => $subscription['id'],
                'stores_reactivated' => $storesReactivated,
            ]);

            return response()->json([
                'message' => 'Subscription created successfully',
                'subscription' => [
                    'id' => $subscription['id'],
                    'status' => $subscription['status'],
                    'trial_end' => $subscription['trial_end'] ?? null,
                    'current_period_end' => $subscription['current_period_end'] ?? null,
                ],
                'stores_reactivated' => $storesReactivated,
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription creation failed', [
                'merchant_id' => $merchant->id,
                'plan_id' => $validated['plan_id'],
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to create subscription',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel subscription.
     */
    public function cancel(Request $request)
    {
        $merchant = $request->user()->merchant;

        if (!$merchant) {
            return response()->json(['error' => 'No merchant found'], 404);
        }

        if (!$merchant->stripe_subscription_id) {
            return response()->json(['error' => 'No active subscription found'], 404);
        }

        // Get cancellation feedback (optional)
        $reason = $request->input('reason');
        $feedback = $request->input('feedback');

        try {
            // Cancel subscription at period end (don't immediately revoke access)
            $subscription = $this->stripeService->cancelSubscription($merchant->stripe_subscription_id);

            // Sync changes
            $this->subscriptionService->syncSubscriptionFromStripe($merchant, $subscription->toArray());

            // Log cancellation with feedback
            Log::info('Subscription canceled', [
                'merchant_id' => $merchant->id,
                'merchant_name' => $merchant->name,
                'subscription_id' => $merchant->stripe_subscription_id,
                'plan_id' => $merchant->subscription_plan_id,
                'cancel_at' => $merchant->subscription_current_period_end,
                'cancellation_reason' => $reason,
                'cancellation_feedback' => $feedback,
            ]);

            return response()->json([
                'message' => 'Subscription will be canceled at period end',
                'cancel_at' => $merchant->subscription_current_period_end,
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription cancellation failed', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to cancel subscription',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Resume a canceled subscription.
     */
    public function resume(Request $request)
    {
        $merchant = $request->user()->merchant;

        if (!$merchant) {
            return response()->json(['error' => 'No merchant found'], 404);
        }

        if (!$merchant->stripe_subscription_id) {
            return response()->json(['error' => 'No subscription found'], 404);
        }

        try {
            // Remove cancel_at_period_end flag
            $subscription = $this->stripeService->resumeSubscription($merchant->stripe_subscription_id);

            // Sync changes
            $this->subscriptionService->syncSubscriptionFromStripe($merchant, $subscription->toArray());

            Log::info('Subscription resumed', [
                'merchant_id' => $merchant->id,
                'subscription_id' => $merchant->stripe_subscription_id,
            ]);

            return response()->json([
                'message' => 'Subscription resumed successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription resume failed', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to resume subscription',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create Stripe Customer Portal session for managing payment methods.
     *
     * Stripe handles all payment method updates securely - no card data touches your server.
     */
    public function createPortalSession(Request $request)
    {
        $merchant = $request->user()->merchant;

        if (!$merchant) {
            return response()->json(['error' => 'No merchant found'], 404);
        }

        if (!$merchant->stripe_customer_id) {
            return response()->json(['error' => 'No Stripe customer found. Please subscribe first.'], 404);
        }

        try {
            // First, verify the customer exists in Stripe
            $customer = $this->stripeService->getClient()->customers->retrieve($merchant->stripe_customer_id);

            if (!$customer || $customer->deleted) {
                return response()->json([
                    'error' => 'Customer not found in Stripe',
                    'message' => 'Your Stripe customer record is invalid. Please contact support.',
                ], 404);
            }

            // Create Stripe Billing Portal session
            $session = $this->stripeService->getClient()->billingPortal->sessions->create([
                'customer' => $merchant->stripe_customer_id,
                'return_url' => route('store.settings', ['store' => $merchant->stores->first()->id]) . '#subscription',
            ]);

            Log::info('Billing portal session created', [
                'merchant_id' => $merchant->id,
                'session_id' => $session->id,
            ]);

            return response()->json([
                'url' => $session->url,
            ]);

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            $errorMessage = $e->getMessage();

            // Check if this is a "Customer Portal not activated" error
            if (str_contains($errorMessage, 'customer portal') || str_contains($errorMessage, 'not activated')) {
                Log::error('Stripe Customer Portal not activated', [
                    'merchant_id' => $merchant->id,
                    'error' => $errorMessage,
                ]);

                return response()->json([
                    'error' => 'Customer Portal not activated',
                    'message' => 'The Stripe Customer Portal needs to be activated. Please contact support or activate it in your Stripe Dashboard: Settings → Billing → Customer Portal.',
                    'setup_url' => 'https://dashboard.stripe.com/settings/billing/portal',
                ], 400);
            }

            Log::error('Billing portal session creation failed', [
                'merchant_id' => $merchant->id,
                'customer_id' => $merchant->stripe_customer_id,
                'error' => $errorMessage,
                'error_type' => get_class($e),
            ]);

            return response()->json([
                'error' => 'Failed to create billing portal session',
                'message' => config('app.debug') ? $errorMessage : 'Unable to access payment settings. Please try again or contact support.',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Billing portal session creation failed', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
                'error_type' => get_class($e),
            ]);

            return response()->json([
                'error' => 'Failed to create billing portal session',
                'message' => config('app.debug') ? $e->getMessage() : 'An error occurred',
            ], 500);
        }
    }

    /**
     * Update subscription (change plan).
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'plan_id' => 'required|string|exists:subscription_plans,plan_id',
        ]);

        $merchant = $request->user()->merchant;

        if (!$merchant) {
            return response()->json(['error' => 'No merchant found'], 404);
        }

        if (!$merchant->stripe_subscription_id) {
            return response()->json(['error' => 'No active subscription found'], 404);
        }

        try {
            $newPlan = SubscriptionPlan::where('plan_id', $validated['plan_id'])->firstOrFail();

            // Update subscription to new price via StripeService
            $updated = $this->stripeService->updateSubscriptionPrice(
                $merchant->stripe_subscription_id,
                $newPlan->stripe_price_id
            );

            // Sync changes
            $this->subscriptionService->syncSubscriptionFromStripe($merchant, $updated->toArray());

            Log::info('Subscription plan changed', [
                'merchant_id' => $merchant->id,
                'old_plan' => $merchant->subscription_plan_id,
                'new_plan' => $validated['plan_id'],
            ]);

            return response()->json([
                'message' => 'Subscription plan updated successfully',
                'new_plan' => $newPlan,
            ]);

        } catch (\Exception $e) {
            Log::error('Subscription plan change failed', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to update subscription',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
