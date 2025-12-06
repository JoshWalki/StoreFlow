<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\LoyaltyAccount;
use App\Models\LoyaltyConfig;
use App\Models\Store;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LoyaltyController extends Controller
{
    /**
     * Display the loyalty program configuration and customer accounts.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $merchantId = $user->merchant_id;
        $storeId = session('store_id');
        $store = Store::find($storeId);

        // Get or create loyalty configuration for this merchant
        $loyaltyConfig = LoyaltyConfig::firstOrCreate(
            ['merchant_id' => $merchantId],
            [
                'points_per_dollar' => 1.0,
                'threshold' => 100,
                'reward_json' => [
                    'type' => 'percentage',
                    'value' => 10,
                    'description' => '10% off your next purchase'
                ],
                'is_active' => true,
            ]
        );

        // Get top loyalty customers for this merchant
        $topCustomers = Customer::where('merchant_id', $merchantId)
            ->with('loyaltyAccount')
            ->whereHas('loyaltyAccount')
            ->get()
            ->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => trim($customer->first_name . ' ' . $customer->last_name),
                    'email' => $customer->email,
                    'points' => $customer->loyaltyAccount->points_balance ?? 0,
                    'points_earned' => $customer->loyaltyAccount->points_earned ?? 0,
                    'points_redeemed' => $customer->loyaltyAccount->points_redeemed ?? 0,
                    'lifetime_points' => $customer->loyaltyAccount->lifetime_points ?? 0,
                    'tier' => 'member', // Simple tier for now
                    'joined_at' => $customer->loyaltyAccount->created_at ?? $customer->created_at,
                ];
            })
            ->sortByDesc('points')
            ->take(50)
            ->values();

        // Calculate loyalty program statistics
        $totalMembers = LoyaltyAccount::whereHas('customer', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })->count();

        $totalPointsIssued = LoyaltyAccount::whereHas('customer', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })->sum('lifetime_points');

        $totalRewardsClaimed = LoyaltyAccount::whereHas('customer', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })->sum('points_redeemed');

        return Inertia::render('Loyalty/Index', [
            'store' => $store,
            'user' => $user,
            'settings' => [
                'is_enabled' => $loyaltyConfig->is_active,
                'points_per_dollar' => $loyaltyConfig->points_per_dollar,
                'signup_bonus' => 100,
                'birthday_bonus' => 50,
                'points_for_redemption' => 100,
                'redemption_value' => 1,
                'min_redemption_points' => $loyaltyConfig->threshold,
                'enable_tiers' => false,
                'enable_expiration' => false,
                'expiration_months' => 12,
            ],
            'tiers' => [],
            'loyaltyConfig' => [
                'id' => $loyaltyConfig->id,
                'points_per_dollar' => $loyaltyConfig->points_per_dollar,
                'threshold' => $loyaltyConfig->threshold,
                'reward_json' => $loyaltyConfig->reward_json,
                'is_active' => $loyaltyConfig->is_active,
            ],
            'topCustomers' => $topCustomers,
            'stats' => [
                'total_members' => $totalMembers,
                'total_points_issued' => $totalPointsIssued,
                'total_rewards_claimed' => $totalRewardsClaimed,
            ],
        ]);
    }

    /**
     * Update the loyalty program configuration.
     */
    public function update(Request $request)
    {
        $merchantId = $request->user()->merchant_id;

        $validated = $request->validate([
            'is_enabled' => ['boolean'],
            'points_per_dollar' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'signup_bonus' => ['nullable', 'integer', 'min:0'],
            'birthday_bonus' => ['nullable', 'integer', 'min:0'],
            'min_redemption_points' => ['nullable', 'integer', 'min:1'],
            'reward_type' => ['nullable', 'string', 'in:percentage,fixed_amount'],
            'reward_value' => ['nullable', 'numeric', 'min:0'],
            'reward_description' => ['nullable', 'string', 'max:500'],
            'enable_tiers' => ['boolean'],
            'enable_expiration' => ['boolean'],
            'expiration_months' => ['nullable', 'integer', 'min:1', 'max:36'],
        ]);

        $loyaltyConfig = LoyaltyConfig::where('merchant_id', $merchantId)->firstOrFail();

        // Map frontend field names to database field names
        $updateData = [];

        if (isset($validated['is_enabled'])) {
            $updateData['is_active'] = $validated['is_enabled'];
        }

        if (isset($validated['points_per_dollar'])) {
            $updateData['points_per_dollar'] = $validated['points_per_dollar'];
        }

        if (isset($validated['min_redemption_points'])) {
            $updateData['threshold'] = $validated['min_redemption_points'];
        }

        // Build reward_json structure
        if (isset($validated['reward_type']) || isset($validated['reward_value']) || isset($validated['reward_description'])) {
            $updateData['reward_json'] = [
                'type' => $validated['reward_type'] ?? $loyaltyConfig->reward_json['type'] ?? 'percentage',
                'value' => $validated['reward_value'] ?? $loyaltyConfig->reward_json['value'] ?? 10,
                'description' => $validated['reward_description'] ?? $loyaltyConfig->reward_json['description'] ?? '10% off your next purchase',
            ];
        }

        $loyaltyConfig->update($updateData);

        return redirect()->route('loyalty.index')
            ->with('success', 'Loyalty program updated successfully.');
    }
}
