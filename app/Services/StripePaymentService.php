<?php

namespace App\Services;

use App\Models\Merchant;
use App\Models\Order;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

/**
 * Stripe Payment Processing Service
 *
 * Handles customer payment processing through merchant Connect accounts:
 * - Payment intent creation
 * - Order payment tracking
 * - Platform fee management
 * - Refund processing
 */
class StripePaymentService
{
    protected StripeClient $stripe;
    protected StripeConnectService $connectService;

    public function __construct(
        ?StripeClient $stripe = null,
        ?StripeConnectService $connectService = null
    ) {
        $this->stripe = $stripe ?? new StripeClient(config('services.stripe.secret'));
        $this->connectService = $connectService ?? new StripeConnectService($this->stripe);
    }

    /**
     * Create payment intent for an order.
     *
     * @param Order $order
     * @param Merchant $merchant
     * @return array PaymentIntent data
     * @throws \Exception
     */
    public function createOrderPaymentIntent(Order $order, Merchant $merchant): array
    {
        // Verify merchant can accept payments
        if (!$merchant->canAcceptPayments()) {
            throw new \Exception('Merchant cannot accept payments. Subscription may be expired or Stripe Connect not configured.');
        }

        // Calculate amounts
        $totalCents = $order->total_cents;
        $platformFeeCents = $merchant->calculatePlatformFee($totalCents);
        $merchantNetCents = $totalCents - $platformFeeCents;

        try {
            $paymentIntent = $this->connectService->createPaymentIntent(
                $merchant,
                $totalCents,
                [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number ?? $order->id,
                    'customer_email' => $order->customer_email,
                    'merchant_name' => $merchant->name,
                ]
            );

            // Update order with payment tracking
            $order->update([
                'payment_reference' => $paymentIntent['id'],
                'platform_fee_cents' => $platformFeeCents,
                'merchant_net_cents' => $merchantNetCents,
                'stripe_metadata' => [
                    'payment_intent_id' => $paymentIntent['id'],
                    'client_secret' => $paymentIntent['client_secret'],
                ],
            ]);

            Log::info('Payment intent created for order', [
                'order_id' => $order->id,
                'merchant_id' => $merchant->id,
                'payment_intent_id' => $paymentIntent['id'],
                'amount' => $totalCents,
                'platform_fee' => $platformFeeCents,
            ]);

            return $paymentIntent;
        } catch (ApiErrorException $e) {
            Log::error('Failed to create payment intent for order', [
                'order_id' => $order->id,
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle successful payment webhook.
     *
     * @param string $paymentIntentId
     * @return Order|null
     */
    public function handlePaymentSucceeded(string $paymentIntentId): ?Order
    {
        $order = Order::where('payment_reference', $paymentIntentId)->first();

        if (!$order) {
            Log::warning('Payment succeeded for unknown order', [
                'payment_intent_id' => $paymentIntentId,
            ]);
            return null;
        }

        try {
            // Retrieve payment intent details from merchant's connected account (Direct Charges)
            $merchant = $order->store->merchant;
            $paymentIntent = $this->connectService->retrievePaymentIntent(
                $paymentIntentId,
                $merchant->stripe_connect_account_id
            );

            // Extract charge ID
            $chargeId = $paymentIntent['charges']['data'][0]['id'] ?? null;
            $transferId = $paymentIntent['charges']['data'][0]['transfer'] ?? null;

            // Update order
            $order->update([
                'payment_status' => Order::PAYMENT_PAID,
                'payment_method' => $paymentIntent['payment_method_types'][0] ?? 'card',
                'stripe_charge_id' => $chargeId,
                'stripe_transfer_id' => $transferId,
                'stripe_transferred_at' => now(),
            ]);

            Log::info('Order payment succeeded', [
                'order_id' => $order->id,
                'payment_intent_id' => $paymentIntentId,
                'charge_id' => $chargeId,
            ]);

            // TODO: Broadcast event for real-time dashboard update
            // broadcast(new OrderPaymentReceived($order));

            // TODO: Send order confirmation email
            // Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));

            return $order->fresh();
        } catch (\Exception $e) {
            Log::error('Failed to process payment success', [
                'order_id' => $order->id,
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Handle failed payment webhook.
     *
     * @param string $paymentIntentId
     * @param string|null $failureReason
     * @return Order|null
     */
    public function handlePaymentFailed(string $paymentIntentId, ?string $failureReason = null): ?Order
    {
        $order = Order::where('payment_reference', $paymentIntentId)->first();

        if (!$order) {
            Log::warning('Payment failed for unknown order', [
                'payment_intent_id' => $paymentIntentId,
            ]);
            return null;
        }

        $order->update([
            'payment_status' => Order::PAYMENT_UNPAID,
            'stripe_metadata' => array_merge($order->stripe_metadata ?? [], [
                'failure_reason' => $failureReason,
                'failed_at' => now()->toIso8601String(),
            ]),
        ]);

        Log::warning('Order payment failed', [
            'order_id' => $order->id,
            'payment_intent_id' => $paymentIntentId,
            'reason' => $failureReason,
        ]);

        // TODO: Send payment failure notification
        // Mail::to($order->customer_email)->send(new PaymentFailedMail($order));

        return $order->fresh();
    }

    /**
     * Process a refund for an order.
     *
     * @param Order $order
     * @param int|null $amountCents Amount to refund (null = full refund)
     * @param string|null $reason Refund reason
     * @return array Refund data
     * @throws \Exception
     */
    public function refundOrder(Order $order, ?int $amountCents = null, ?string $reason = null): array
    {
        if ($order->payment_status !== Order::PAYMENT_PAID) {
            throw new \Exception('Cannot refund unpaid order');
        }

        if (!$order->payment_reference) {
            throw new \Exception('Order has no payment reference');
        }

        try {
            // Create refund on merchant's connected account (Direct Charges)
            $merchant = $order->store->merchant;
            $refund = $this->connectService->createRefund(
                $order->payment_reference,
                $amountCents,
                $reason,
                $merchant->stripe_connect_account_id
            );

            // Update order status
            $isFullRefund = $amountCents === null || $amountCents >= $order->total_cents;

            if ($isFullRefund) {
                $order->update(['payment_status' => Order::PAYMENT_REFUNDED]);
            }

            // Store refund info in metadata
            $order->update([
                'stripe_metadata' => array_merge($order->stripe_metadata ?? [], [
                    'refund_id' => $refund['id'],
                    'refund_amount' => $refund['amount'],
                    'refund_reason' => $reason,
                    'refunded_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::info('Order refunded', [
                'order_id' => $order->id,
                'refund_id' => $refund['id'],
                'amount' => $amountCents ?? $order->total_cents,
                'full_refund' => $isFullRefund,
            ]);

            // TODO: Send refund confirmation email
            // Mail::to($order->customer_email)->send(new RefundConfirmationMail($order, $refund));

            return $refund;
        } catch (ApiErrorException $e) {
            Log::error('Failed to refund order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get payment method details for an order.
     *
     * @param Order $order
     * @return array|null
     */
    public function getPaymentMethodDetails(Order $order): ?array
    {
        if (!$order->payment_reference) {
            return null;
        }

        try {
            // Retrieve payment intent from merchant's connected account (Direct Charges)
            $merchant = $order->store->merchant;
            $paymentIntent = $this->connectService->retrievePaymentIntent(
                $order->payment_reference,
                $merchant->stripe_connect_account_id
            );

            if (!isset($paymentIntent['payment_method'])) {
                return null;
            }

            // Also need to retrieve payment method from connected account
            $paymentMethod = $this->stripe->paymentMethods->retrieve(
                $paymentIntent['payment_method'],
                [],
                ['stripe_account' => $merchant->stripe_connect_account_id]
            );

            return [
                'type' => $paymentMethod->type,
                'card' => [
                    'brand' => $paymentMethod->card->brand ?? null,
                    'last4' => $paymentMethod->card->last4 ?? null,
                    'exp_month' => $paymentMethod->card->exp_month ?? null,
                    'exp_year' => $paymentMethod->card->exp_year ?? null,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Failed to get payment method details', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Calculate platform fee breakdown for display.
     *
     * @param Merchant $merchant
     * @param int $amountCents
     * @return array
     */
    public function calculateFeeBreakdown(Merchant $merchant, int $amountCents): array
    {
        $platformFee = $merchant->calculatePlatformFee($amountCents);
        $merchantNet = $amountCents - $platformFee;

        $percentageFee = (int) ($amountCents * ($merchant->platform_fee_percentage / 100));
        $fixedFee = $merchant->platform_fee_fixed_cents;

        return [
            'total_cents' => $amountCents,
            'total_formatted' => '$' . number_format($amountCents / 100, 2),
            'platform_fee_cents' => $platformFee,
            'platform_fee_formatted' => '$' . number_format($platformFee / 100, 2),
            'platform_fee_percentage' => $merchant->platform_fee_percentage,
            'platform_fee_fixed_cents' => $fixedFee,
            'percentage_fee_cents' => $percentageFee,
            'fixed_fee_cents' => $fixedFee,
            'merchant_net_cents' => $merchantNet,
            'merchant_net_formatted' => '$' . number_format($merchantNet / 100, 2),
        ];
    }
}
