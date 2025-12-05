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
            'points_per_dollar' => ['required', 'numeric', 'min:0', 'max:100'],
            'threshold' => ['required', 'integer', 'min:1'],
            'reward_json' => ['required', 'array'],
            'reward_json.type' => ['required', 'in:percentage,fixed_amount,free_product'],
            'reward_json.value' => ['required', 'numeric', 'min:0'],
            'reward_json.description' => ['required', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $loyaltyConfig = LoyaltyConfig::where('merchant_id', $merchantId)->firstOrFail();
        $loyaltyConfig->update($validated);

        return redirect()->route('loyalty.index')
            ->with('success', 'Loyalty program updated successfully.');
    }
}
