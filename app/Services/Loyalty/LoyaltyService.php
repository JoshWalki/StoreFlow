<?php

namespace App\Services\Loyalty;

use App\Models\Customer;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyConfig;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LoyaltyService
{
    /**
     * Calculate points for an order based on merchant's loyalty config
     *
     * @param Order $order
     * @return int Points to be awarded
     */
    public function calculatePointsForOrder(Order $order): int
    {
        // Get merchant's loyalty config
        $config = LoyaltyConfig::where('merchant_id', $order->merchant_id)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            return 0;
        }

        // Calculate points based on order total (convert cents to dollars)
        $orderAmountInDollars = $order->total_cents / 100;
        $points = floor($orderAmountInDollars * $config->points_per_dollar);

        return (int) $points;
    }

    /**
     * Award points to a customer
     *
     * @param Customer $customer
     * @param int $points
     * @return LoyaltyAccount
     * @throws \Exception
     */
    public function awardPoints(Customer $customer, int $points): LoyaltyAccount
    {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Points must be greater than zero');
        }

        return DB::transaction(function () use ($customer, $points) {
            // Get or create loyalty account
            $account = LoyaltyAccount::firstOrCreate(
                [
                    'customer_id' => $customer->id,
                    'merchant_id' => $customer->merchant_id,
                ],
                [
                    'points_balance' => 0,
                    'points_earned' => 0,
                    'points_redeemed' => 0,
                    'lifetime_points' => 0,
                ]
            );

            // Update points
            $account->points_balance += $points;
            $account->points_earned += $points;
            $account->lifetime_points += $points;
            $account->last_activity_at = now();
            $account->save();

            Log::info('Points awarded', [
                'customer_id' => $customer->id,
                'merchant_id' => $customer->merchant_id,
                'points' => $points,
                'new_balance' => $account->points_balance,
            ]);

            return $account;
        });
    }

    /**
     * Redeem points from a customer's account
     *
     * @param Customer $customer
     * @param int $points
     * @return LoyaltyAccount
     * @throws \Exception
     */
    public function redeemPoints(Customer $customer, int $points): LoyaltyAccount
    {
        if ($points <= 0) {
            throw new \InvalidArgumentException('Points must be greater than zero');
        }

        return DB::transaction(function () use ($customer, $points) {
            $account = LoyaltyAccount::where('customer_id', $customer->id)
                ->where('merchant_id', $customer->merchant_id)
                ->lockForUpdate()
                ->first();

            if (!$account) {
                throw new \Exception('Loyalty account not found');
            }

            if (!$account->hasSufficientPoints($points)) {
                throw new \Exception('Insufficient points balance');
            }

            // Update points
            $account->points_balance -= $points;
            $account->points_redeemed += $points;
            $account->last_activity_at = now();
            $account->save();

            Log::info('Points redeemed', [
                'customer_id' => $customer->id,
                'merchant_id' => $customer->merchant_id,
                'points' => $points,
                'new_balance' => $account->points_balance,
            ]);

            return $account;
        });
    }

    /**
     * Check if customer has reached threshold and issue reward
     *
     * @param Customer $customer
     * @return array|null Returns reward info if threshold reached, null otherwise
     */
    public function checkThresholdAndIssueReward(Customer $customer): ?array
    {
        // Get merchant's loyalty config
        $config = LoyaltyConfig::where('merchant_id', $customer->merchant_id)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            return null;
        }

        // Get customer's loyalty account
        $account = LoyaltyAccount::where('customer_id', $customer->id)
            ->where('merchant_id', $customer->merchant_id)
            ->first();

        if (!$account) {
            return null;
        }

        // Check if threshold is reached
        if (!$account->hasReachedThreshold($config->threshold)) {
            return null;
        }

        // Threshold reached - prepare reward information
        $rewardConfig = $config->getRewardConfig();

        $reward = [
            'threshold_reached' => true,
            'points_balance' => $account->points_balance,
            'threshold' => $config->threshold,
            'reward_config' => $rewardConfig,
            'customer_id' => $customer->id,
            'merchant_id' => $customer->merchant_id,
        ];

        Log::info('Loyalty threshold reached', $reward);

        return $reward;
    }

    /**
     * Get loyalty account for a customer
     *
     * @param Customer $customer
     * @return LoyaltyAccount|null
     */
    public function getLoyaltyAccount(Customer $customer): ?LoyaltyAccount
    {
        return LoyaltyAccount::where('customer_id', $customer->id)
            ->where('merchant_id', $customer->merchant_id)
            ->first();
    }

    /**
     * Get or create loyalty account for a customer
     *
     * @param Customer $customer
     * @return LoyaltyAccount
     */
    public function getOrCreateLoyaltyAccount(Customer $customer): LoyaltyAccount
    {
        return LoyaltyAccount::firstOrCreate(
            [
                'customer_id' => $customer->id,
                'merchant_id' => $customer->merchant_id,
            ],
            [
                'points_balance' => 0,
                'points_earned' => 0,
                'points_redeemed' => 0,
                'lifetime_points' => 0,
            ]
        );
    }

    /**
     * Process loyalty points for a completed order
     * This combines calculate and award, plus checks for threshold
     *
     * @param Order $order
     * @return array Contains account and reward info
     */
    public function processOrderLoyalty(Order $order): array
    {
        // Only process if order has a customer
        if (!$order->customer_id) {
            return [
                'success' => false,
                'message' => 'No customer associated with order',
            ];
        }

        $customer = $order->customer;

        // Calculate points
        $points = $this->calculatePointsForOrder($order);

        if ($points <= 0) {
            return [
                'success' => false,
                'message' => 'No points to award',
            ];
        }

        // Award points
        $account = $this->awardPoints($customer, $points);

        // Check for threshold reward
        $reward = $this->checkThresholdAndIssueReward($customer);

        return [
            'success' => true,
            'points_awarded' => $points,
            'account' => $account,
            'reward' => $reward,
        ];
    }
}
