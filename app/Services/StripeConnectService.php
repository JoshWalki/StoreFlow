<?php

namespace App\Services;

use App\Models\Merchant;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

/**
 * Stripe Connect Service
 *
 * Handles Stripe Connect Express account management:
 * - Account creation and onboarding
 * - Account link generation
 * - Account status verification
 * - Payment intent creation with direct charges
 * - Platform fee calculation
 * - Webhook signature verification
 */
class StripeConnectService
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $apiKey = config('services.stripe.secret');

        if (empty($apiKey)) {
            throw new \Exception('Stripe API key not configured. Check config/services.php and .env file.');
        }

        $this->stripe = new StripeClient($apiKey);
    }

    /**
     * Create a Stripe Connect Express account for a merchant.
     *
     * @param Merchant $merchant
     * @return array Account data
     * @throws ApiErrorException
     */
    public function createConnectAccount(Merchant $merchant): array
    {
        try {
            // Get the first store for the merchant or use app URL
            $firstStore = $merchant->stores()->first();

            // Generate business URL - ensure it's a fully qualified URL
            if ($firstStore) {
                $businessUrl = route('storefront.index', ['store' => $firstStore->id]);
            } else {
                $businessUrl = config('app.url');
            }

            // Ensure URL has a valid scheme
            if (!str_starts_with($businessUrl, 'http://') && !str_starts_with($businessUrl, 'https://')) {
                $businessUrl = 'https://' . $businessUrl;
            }

            // Validate URL format
            if (!filter_var($businessUrl, FILTER_VALIDATE_URL)) {
                Log::warning('Invalid business URL generated, using app URL', [
                    'merchant_id' => $merchant->id,
                    'generated_url' => $businessUrl,
                ]);
                $businessUrl = config('app.url', 'https://example.com');
            }

            // Default to AU (Australia) for this merchant
            $country = $merchant->stripe_country ?? 'AU';

            // Prepare business profile - only include URL if it's not localhost/development
            // Stripe validates URLs and rejects localhost/example.com
            $businessProfile = ['name' => $merchant->name];

            $isLocalhost = str_contains($businessUrl, 'localhost')
                || str_contains($businessUrl, '127.0.0.1')
                || str_contains($businessUrl, '192.168.')
                || str_contains($businessUrl, 'example.com')
                || str_contains($businessUrl, '.local')
                || str_contains($businessUrl, '.test');

            // Only include URL if it's valid and not localhost
            if (!$isLocalhost && !empty($businessUrl) && strlen($businessUrl) > 10) {
                // Additional validation: ensure URL is at least 10 chars and has valid format
                $parsedUrl = parse_url($businessUrl);
                if (isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
                    $businessProfile['url'] = $businessUrl;
                } else {
                    Log::warning('Business URL failed parsing validation', [
                        'merchant_id' => $merchant->id,
                        'url' => $businessUrl,
                        'parsed' => $parsedUrl,
                    ]);
                }
            }

            // Log the URL being sent for debugging
            Log::info('Creating Stripe Connect account', [
                'merchant_id' => $merchant->id,
                'business_url' => $businessUrl,
                'url_included' => !$isLocalhost,
                'is_localhost' => $isLocalhost,
            ]);

            // Prepare account data
            $accountData = [
                'type' => 'express',
                'country' => $country,
                'capabilities' => [
                    'card_payments' => ['requested' => true],
                    'transfers' => ['requested' => true],
                ],
                'business_profile' => $businessProfile,
                'metadata' => [
                    'merchant_id' => $merchant->id,
                    'platform' => config('app.name'),
                ],
            ];

            // Only add email if owner exists and has email
            if ($merchant->owner && $merchant->owner->email) {
                $accountData['email'] = $merchant->owner->email;
            }

            $account = $this->stripe->accounts->create($accountData);

            Log::info('Stripe Connect account created', [
                'merchant_id' => $merchant->id,
                'account_id' => $account->id,
            ]);

            return $account->toArray();
        } catch (ApiErrorException $e) {
            Log::error('Failed to create Stripe Connect account', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
                'http_status' => $e->getHttpStatus(),
                'stripe_code' => $e->getError()->code ?? null,
                'stripe_param' => $e->getError()->param ?? null,
                'stripe_type' => $e->getError()->type ?? null,
                'full_response' => $e->getJsonBody(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate an account link for Stripe onboarding.
     *
     * @param string $accountId Stripe Connect account ID
     * @param string $returnUrl URL to redirect after onboarding
     * @param string $refreshUrl URL to redirect if link expires
     * @return array Account link data
     * @throws ApiErrorException
     */
    public function createAccountLink(string $accountId, string $returnUrl, string $refreshUrl): array
    {
        try {
            $accountLink = $this->stripe->accountLinks->create([
                'account' => $accountId,
                'refresh_url' => $refreshUrl,
                'return_url' => $returnUrl,
                'type' => 'account_onboarding',
            ]);

            return $accountLink->toArray();
        } catch (ApiErrorException $e) {
            Log::error('Failed to create account link', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Retrieve Stripe Connect account details.
     *
     * @param string $accountId
     * @return array Account data
     * @throws ApiErrorException
     */
    public function retrieveAccount(string $accountId): array
    {
        try {
            $account = $this->stripe->accounts->retrieve($accountId);
            return $account->toArray();
        } catch (ApiErrorException $e) {
            Log::error('Failed to retrieve Stripe account', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if Stripe Connect account is ready to accept payments.
     *
     * @param array $account Stripe account data
     * @return bool
     */
    public function isAccountReady(array $account): bool
    {
        return isset($account['charges_enabled'])
            && $account['charges_enabled'] === true
            && isset($account['details_submitted'])
            && $account['details_submitted'] === true;
    }

    /**
     * Create a Stripe Customer for recurring customer.
     *
     * @param Merchant $merchant
     * @param array $customerData Customer data (email, name, etc.)
     * @return array Stripe Customer data
     * @throws ApiErrorException
     */
    public function createStripeCustomer(Merchant $merchant, array $customerData): array
    {
        try {
            $customer = $this->stripe->customers->create([
                'email' => $customerData['email'],
                'name' => trim(($customerData['first_name'] ?? '') . ' ' . ($customerData['last_name'] ?? '')),
                'phone' => $customerData['mobile'] ?? null,
                'metadata' => [
                    'merchant_id' => $merchant->id,
                    'platform' => config('app.name'),
                ],
            ], [
                'stripe_account' => $merchant->stripe_connect_account_id,
            ]);

            Log::info('Stripe Customer created', [
                'merchant_id' => $merchant->id,
                'customer_id' => $customer->id,
                'email' => $customerData['email'],
            ]);

            return $customer->toArray();
        } catch (ApiErrorException $e) {
            Log::error('Failed to create Stripe Customer', [
                'merchant_id' => $merchant->id,
                'email' => $customerData['email'] ?? null,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a payment intent with direct charge to merchant's connected account.
     * Uses Stripe Direct Charges where the merchant pays Stripe fees and platform collects application fee.
     *
     * @param Merchant $merchant
     * @param int $amountCents Total amount in cents
     * @param array $metadata Additional metadata
     * @param string|null $stripeCustomerId Optional Stripe Customer ID
     * @return array PaymentIntent data
     * @throws ApiErrorException
     */
    public function createPaymentIntent(Merchant $merchant, int $amountCents, array $metadata = [], ?string $stripeCustomerId = null): array
    {
        $platformFee = $this->calculateApplicationFee($merchant, $amountCents);

        // Determine currency based on merchant's Stripe country
        $currency = match($merchant->stripe_country ?? 'AU') {
            'AU' => 'aud',
            'US' => 'usd',
            'GB' => 'gbp',
            'CA' => 'cad',
            'NZ' => 'nzd',
            'SG' => 'sgd',
            default => 'aud', // Default to AUD
        };

        try {
            $paymentIntentData = [
                'amount' => $amountCents,
                'currency' => $currency,
                'payment_method_types' => ['card'],
                'application_fee_amount' => $platformFee,
                'metadata' => array_merge([
                    'merchant_id' => $merchant->id,
                    'platform' => config('app.name'),
                ], $metadata),
            ];

            // Add customer ID if provided (for registered customers)
            if ($stripeCustomerId) {
                $paymentIntentData['customer'] = $stripeCustomerId;
            }

            // Create payment intent using Direct Charges (charges directly on merchant's connected account)
            // The stripe_account header makes this a Direct Charge where merchant pays Stripe fees
            $paymentIntent = $this->stripe->paymentIntents->create(
                $paymentIntentData,
                ['stripe_account' => $merchant->stripe_connect_account_id]
            );

            return $paymentIntent->toArray();
        } catch (ApiErrorException $e) {
            Log::error('Failed to create payment intent', [
                'merchant_id' => $merchant->id,
                'amount' => $amountCents,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Calculate application fee (platform fee) for an amount.
     *
     * @param Merchant $merchant
     * @param int $amountCents
     * @return int Platform fee in cents
     */
    public function calculateApplicationFee(Merchant $merchant, int $amountCents): int
    {
        return $merchant->calculatePlatformFee($amountCents);
    }

    /**
     * Retrieve payment intent details.
     * For Direct Charges, must specify the connected account.
     *
     * @param string $paymentIntentId
     * @param string|null $stripeAccountId Stripe Connect account ID (required for Direct Charges)
     * @return array PaymentIntent data
     * @throws ApiErrorException
     */
    public function retrievePaymentIntent(string $paymentIntentId, ?string $stripeAccountId = null): array
    {
        try {
            // For Direct Charges, retrieve from the connected account
            $options = $stripeAccountId ? ['stripe_account' => $stripeAccountId] : [];
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId, [], $options);
            return $paymentIntent->toArray();
        } catch (ApiErrorException $e) {
            Log::error('Failed to retrieve payment intent', [
                'payment_intent_id' => $paymentIntentId,
                'stripe_account_id' => $stripeAccountId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a refund for a payment intent.
     * For Direct Charges, must specify the connected account.
     *
     * @param string $paymentIntentId
     * @param int|null $amountCents Amount to refund (null = full refund)
     * @param string|null $reason Refund reason
     * @param string|null $stripeAccountId Stripe Connect account ID (required for Direct Charges)
     * @return array Refund data
     * @throws ApiErrorException
     */
    public function createRefund(string $paymentIntentId, ?int $amountCents = null, ?string $reason = null, ?string $stripeAccountId = null): array
    {
        try {
            $params = [
                'payment_intent' => $paymentIntentId,
            ];

            if ($amountCents !== null) {
                $params['amount'] = $amountCents;
            }

            if ($reason !== null) {
                $params['reason'] = $reason;
            }

            // For Direct Charges, create refund on the connected account
            $options = $stripeAccountId ? ['stripe_account' => $stripeAccountId] : [];
            $refund = $this->stripe->refunds->create($params, $options);

            Log::info('Refund created', [
                'payment_intent_id' => $paymentIntentId,
                'refund_id' => $refund->id,
                'amount' => $amountCents,
            ]);

            return $refund->toArray();
        } catch (ApiErrorException $e) {
            Log::error('Failed to create refund', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Verify Stripe webhook signature.
     *
     * @param string $payload Request body
     * @param string $signature Stripe-Signature header
     * @param string $secret Webhook signing secret
     * @return array Event data
     * @throws \UnexpectedValueException
     * @throws \Stripe\Exception\SignatureVerificationException
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $secret): array
    {
        try {
            $event = \Stripe\Webhook::constructEvent($payload, $signature, $secret);
            return $event->toArray();
        } catch (\UnexpectedValueException $e) {
            Log::warning('Invalid webhook payload', ['error' => $e->getMessage()]);
            throw $e;
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::warning('Invalid webhook signature', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a login link to Stripe Express Dashboard.
     *
     * @param string $accountId
     * @return string Dashboard URL
     * @throws ApiErrorException
     */
    public function createDashboardLink(string $accountId): string
    {
        try {
            $loginLink = $this->stripe->accounts->createLoginLink($accountId);
            return $loginLink->url;
        } catch (ApiErrorException $e) {
            Log::error('Failed to create dashboard link', [
                'account_id' => $accountId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Sync Stripe account data to local merchant record.
     *
     * @param Merchant $merchant
     * @return Merchant Updated merchant
     */
    public function syncAccountData(Merchant $merchant): Merchant
    {
        if (!$merchant->stripe_connect_account_id) {
            return $merchant;
        }

        try {
            $account = $this->retrieveAccount($merchant->stripe_connect_account_id);

            $merchant->update([
                'stripe_charges_enabled' => $account['charges_enabled'] ?? false,
                'stripe_payouts_enabled' => $account['payouts_enabled'] ?? false,
                'stripe_details_submitted' => $account['details_submitted'] ?? false,
                'stripe_requirements' => [
                    'currently_due' => $account['requirements']['currently_due'] ?? [],
                    'eventually_due' => $account['requirements']['eventually_due'] ?? [],
                    'past_due' => $account['requirements']['past_due'] ?? [],
                ],
            ]);

            if ($this->isAccountReady($account) && !$merchant->stripe_onboarding_complete) {
                $merchant->update([
                    'stripe_onboarding_complete' => true,
                    'stripe_verified_at' => now(),
                ]);
            }

            Log::info('Synced Stripe account data', [
                'merchant_id' => $merchant->id,
                'charges_enabled' => $merchant->stripe_charges_enabled,
            ]);

            return $merchant->fresh();
        } catch (ApiErrorException $e) {
            Log::error('Failed to sync account data', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
            ]);
            return $merchant;
        }
    }
}
