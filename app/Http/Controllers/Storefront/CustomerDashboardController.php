<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CustomerDashboardController extends Controller
{
    /**
     * Display the customer dashboard.
     */
    public function index(Request $request, Store $store): Response
    {
        // Get authenticated customer using customer guard
        $customer = Auth::guard('customer')->user();

        // Ensure customer is authenticated (middleware should handle this)
        if (!$customer) {
            abort(401, 'Unauthorized');
        }

        // Verify customer belongs to this store's merchant
        if ($customer->merchant_id !== $store->merchant_id) {
            abort(403, 'Access denied');
        }

        // Load customer data with relationships
        $customer->load([
            'orders' => function ($query) {
                $query->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->with('items.product');
            },
            'loyaltyAccount',
        ]);

        // Check if loyalty program is active for this merchant
        $loyaltyConfig = \App\Models\LoyaltyConfig::where('merchant_id', $customer->merchant_id)
            ->where('is_active', true)
            ->first();

        // Calculate customer statistics
        $stats = [
            'total_orders' => $customer->orders()->count(),
            'total_spent' => $customer->orders()->sum('total_cents') / 100,
            'loyalty_points' => ($loyaltyConfig && $customer->loyaltyAccount) ? $customer->loyaltyAccount->points_balance : 0,
            'member_since' => $customer->created_at->format('F Y'),
        ];

        // Format recent orders for display
        $recentOrders = $customer->orders->map(function ($order) {
            return [
                'id' => $order->id,
                'public_id' => $order->public_id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total_cents / 100,
                'currency' => $order->currency,
                'created_at' => $order->created_at,
                'items_count' => $order->items->count(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product->name ?? 'Product Removed',
                        'quantity' => $item->quantity,
                        'price' => $item->unit_price_cents / 100,
                    ];
                }),
            ];
        });

        return Inertia::render('Storefront/Customer/Dashboard', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'merchant_id' => $store->merchant_id,
                'theme' => $store->theme_key ?? 'classic',
            ],
            'customer' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'mobile' => $customer->mobile,
                'created_at' => $customer->created_at,
            ],
            'stats' => $stats,
            'recent_orders' => $recentOrders,
        ]);
    }

    /**
     * Display the customer orders page.
     */
    public function orders(Request $request, Store $store): Response
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer || $customer->merchant_id !== $store->merchant_id) {
            abort(403, 'Access denied');
        }

        // Get all customer orders with pagination
        $orders = $customer->orders()
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Format orders for display
        $orders->through(function ($order) {
            return [
                'id' => $order->id,
                'public_id' => $order->public_id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total_cents / 100,
                'currency' => $order->currency,
                'created_at' => $order->created_at,
                'items_count' => $order->items->count(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product->name ?? 'Product Removed',
                        'quantity' => $item->quantity,
                        'price' => $item->unit_price_cents / 100,
                    ];
                }),
            ];
        });

        return Inertia::render('Storefront/Customer/Orders', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'theme' => $store->theme_key ?? 'classic',
            ],
            'customer' => [
                'id' => $customer->id,
                'full_name' => $customer->full_name,
            ],
            'orders' => $orders,
        ]);
    }

    /**
     * Display the customer profile page (combined dashboard + profile).
     */
    public function profile(Request $request, Store $store): Response
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer || $customer->merchant_id !== $store->merchant_id) {
            abort(403, 'Access denied');
        }

        // Load customer data with relationships
        $customer->load([
            'orders' => function ($query) {
                $query->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->with('items.product');
            },
            'loyaltyAccount',
        ]);

        // Check if loyalty program is active for this merchant
        $loyaltyConfig = \App\Models\LoyaltyConfig::where('merchant_id', $customer->merchant_id)
            ->where('is_active', true)
            ->first();

        // Calculate customer statistics
        $stats = [
            'total_orders' => $customer->orders()->count(),
            'total_spent' => $customer->orders()->sum('total_cents') / 100,
            'loyalty_points' => ($loyaltyConfig && $customer->loyaltyAccount) ? $customer->loyaltyAccount->points_balance : 0,
            'member_since' => $customer->created_at->format('F Y'),
        ];

        // Get detailed loyalty information (only if loyalty program is active)
        $loyaltyDetails = null;
        if ($loyaltyConfig && $customer->loyaltyAccount) {
            $loyaltyDetails = [
                'points_balance' => $customer->loyaltyAccount->points_balance,
                'points_earned' => $customer->loyaltyAccount->points_earned,
                'points_redeemed' => $customer->loyaltyAccount->points_redeemed,
                'lifetime_points' => $customer->loyaltyAccount->lifetime_points,
                'last_activity_at' => $customer->loyaltyAccount->last_activity_at,
            ];
        }

        // Format recent orders for display
        $recentOrders = $customer->orders->map(function ($order) {
            return [
                'id' => $order->id,
                'public_id' => $order->public_id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total_cents / 100,
                'currency' => $order->currency,
                'created_at' => $order->created_at,
                'items_count' => $order->items->count(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'product_name' => $item->product->name ?? 'Product Removed',
                        'quantity' => $item->quantity,
                        'price' => $item->unit_price_cents / 100,
                    ];
                }),
            ];
        });

        return Inertia::render('Storefront/Customer/Profile', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'theme' => $store->theme_key ?? 'classic',
            ],
            'customer' => [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'full_name' => $customer->full_name,
                'email' => $customer->email,
                'mobile' => $customer->mobile,
                'address_line1' => $customer->address_line1,
                'address_line2' => $customer->address_line2,
                'address_city' => $customer->address_city,
                'address_state' => $customer->address_state,
                'address_postcode' => $customer->address_postcode,
                'address_country' => $customer->address_country,
                'created_at' => $customer->created_at,
            ],
            'stats' => $stats,
            'loyalty' => $loyaltyDetails,
            'recent_orders' => $recentOrders,
        ]);
    }

    /**
     * Update customer profile information.
     */
    public function updateProfile(Request $request, Store $store)
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer || $customer->merchant_id !== $store->merchant_id) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'address_line1' => ['nullable', 'string', 'max:255'],
            'address_line2' => ['nullable', 'string', 'max:255'],
            'address_city' => ['nullable', 'string', 'max:100'],
            'address_state' => ['nullable', 'string', 'max:100'],
            'address_postcode' => ['nullable', 'string', 'max:20'],
            'address_country' => ['nullable', 'string', 'max:100'],
        ]);

        $customer->update([
            'first_name' => strip_tags(trim($validated['first_name'])),
            'last_name' => strip_tags(trim($validated['last_name'])),
            'mobile' => isset($validated['mobile']) ? preg_replace('/[^0-9+\-() ]/', '', $validated['mobile']) : null,
            'address_line1' => isset($validated['address_line1']) ? strip_tags(trim($validated['address_line1'])) : null,
            'address_line2' => isset($validated['address_line2']) ? strip_tags(trim($validated['address_line2'])) : null,
            'address_city' => isset($validated['address_city']) ? strip_tags(trim($validated['address_city'])) : null,
            'address_state' => isset($validated['address_state']) ? strip_tags(trim($validated['address_state'])) : null,
            'address_postcode' => isset($validated['address_postcode']) ? strip_tags(trim($validated['address_postcode'])) : null,
            'address_country' => isset($validated['address_country']) ? strip_tags(trim($validated['address_country'])) : null,
        ]);

        return redirect()->route('customer.profile', ['store' => $store->id])
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Update customer password.
     */
    public function updatePassword(Request $request, Store $store)
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer || $customer->merchant_id !== $store->merchant_id) {
            abort(403, 'Access denied');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Check if current password is correct
        if (!\Illuminate\Support\Facades\Hash::check($validated['current_password'], $customer->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        // Update password
        $customer->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return redirect()->route('customer.profile', ['store' => $store->id])
            ->with('success', 'Password updated successfully!');
    }

    /**
     * Get full order details for modal display.
     */
    public function getOrderDetails(Request $request, Store $store, $orderId)
    {
        $customer = Auth::guard('customer')->user();

        if (!$customer || $customer->merchant_id !== $store->merchant_id) {
            abort(403, 'Access denied');
        }

        // Find the order and ensure it belongs to the customer
        $order = \App\Models\Order::where('id', $orderId)
            ->where('customer_id', $customer->id)
            ->where('store_id', $store->id)
            ->with('items')
            ->first();

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Format order details for JSON response
        return response()->json([
            'id' => $order->id,
            'public_id' => $order->public_id,
            'status' => $order->status,
            'fulfilment_type' => $order->fulfilment_type,
            'total_cents' => $order->total_cents,
            'items_total_cents' => $order->items_total_cents,
            'shipping_cost_cents' => $order->shipping_cost_cents,
            'discount_cents' => $order->discount_cents ?? 0,
            'tracking_code' => $order->tracking_code,
            'tracking_url' => $order->tracking_url,
            'created_at' => $order->created_at,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price_cents' => $item->price_cents,
                    'total_cents' => $item->total_cents,
                ];
            }),
        ]);
    }
}
