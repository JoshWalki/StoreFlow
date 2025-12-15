<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the operations dashboard.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $storeId = session('store_id');
        $store = Store::find($storeId);

        // Get active orders (not completed or cancelled)
        $orders = Order::with(['customer', 'items.product', 'items.addons', 'items.options.customizationOption'])
            ->where('store_id', $storeId)
            ->whereNotIn('status', ['delivered', 'picked_up', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'public_id' => $order->public_id,
                    'status' => $order->status,
                    'fulfilment_type' => $order->fulfilment_type,
                    'payment_status' => $order->payment_status,
                    'total_cents' => $order->total_cents,
                    'items_total_cents' => $order->items_total_cents,
                    'shipping_cost_cents' => $order->shipping_cost_cents,
                    'customer_name' => $order->customer->first_name . ' ' . $order->customer->last_name,
                    'customer_email' => $order->customer->email,
                    'customer_phone' => $order->customer->phone ?? '',
                    'customer_order_count' => Order::where('customer_id', $order->customer_id)
                        ->where('store_id', $order->store_id)
                        ->where('status', '!=', 'cancelled')
                        ->count(),
                    'pickup_time' => $order->pickup_time,
                    'shipping_name' => $order->shipping_name,
                    'line1' => $order->line1,
                    'line2' => $order->line2,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postcode' => $order->postcode,
                    'country' => $order->country,
                    'tracking_code' => $order->tracking_code,
                    'tracking_url' => $order->tracking_url,
                    'tracking_number' => $order->tracking_number,
                    'courier_company' => $order->courier_company,
                    'items_count' => $order->items->count(),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name ?? 'Unknown Product',
                            'quantity' => $item->quantity,
                            'price_cents' => $item->unit_price_cents,
                            'total_cents' => $item->total_cents,
                            'special_instructions' => $item->special_instructions,
                            'addons' => $item->addons->map(function ($addon) {
                                return [
                                    'name' => $addon->name,
                                    'quantity' => $addon->quantity,
                                    'unit_price_cents' => $addon->unit_price_cents,
                                    'total_price_cents' => $addon->total_price_cents,
                                ];
                            })->toArray(),
                        ];
                    }),
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ];
            });

        return Inertia::render('Dashboard/Index', [
            'store' => $store,
            'user' => $user,
            'orders' => $orders,
        ]);
    }

    /**
     * Poll for order updates (JSON endpoint for real-time updates).
     */
    public function pollOrders(Request $request, Store $store): JsonResponse
    {
        // Get active orders (not completed or cancelled)
        $orders = Order::with(['customer', 'items.product', 'items.addons', 'items.options.customizationOption'])
            ->where('store_id', $store->id)
            ->whereNotIn('status', ['delivered', 'picked_up', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'public_id' => $order->public_id,
                    'status' => $order->status,
                    'fulfilment_type' => $order->fulfilment_type,
                    'payment_status' => $order->payment_status,
                    'total_cents' => $order->total_cents,
                    'items_total_cents' => $order->items_total_cents,
                    'shipping_cost_cents' => $order->shipping_cost_cents,
                    'customer_name' => $order->customer->first_name . ' ' . $order->customer->last_name,
                    'customer_email' => $order->customer->email,
                    'customer_phone' => $order->customer->phone ?? '',
                    'customer_order_count' => Order::where('customer_id', $order->customer_id)
                        ->where('store_id', $order->store_id)
                        ->where('status', '!=', 'cancelled')
                        ->count(),
                    'pickup_time' => $order->pickup_time,
                    'shipping_name' => $order->shipping_name,
                    'line1' => $order->line1,
                    'line2' => $order->line2,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postcode' => $order->postcode,
                    'country' => $order->country,
                    'tracking_code' => $order->tracking_code,
                    'tracking_url' => $order->tracking_url,
                    'tracking_number' => $order->tracking_number,
                    'courier_company' => $order->courier_company,
                    'items_count' => $order->items->count(),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name ?? 'Unknown Product',
                            'quantity' => $item->quantity,
                            'price_cents' => $item->unit_price_cents,
                            'total_cents' => $item->total_cents,
                            'special_instructions' => $item->special_instructions,
                            'addons' => $item->addons->map(function ($addon) {
                                return [
                                    'name' => $addon->name,
                                    'quantity' => $addon->quantity,
                                    'unit_price_cents' => $addon->unit_price_cents,
                                    'total_price_cents' => $addon->total_price_cents,
                                ];
                            })->toArray(),
                        ];
                    }),
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ];
            });

        return response()->json([
            'orders' => $orders,
        ]);
    }

    /**
     * Check if store should be closed based on operating hours.
     */
    public function checkOperatingHours(Request $request): JsonResponse
    {
        $storeId = session('store_id');
        $store = Store::find($storeId);

        if (!$store || !$store->close_time) {
            return response()->json(['status' => 'no_closing_time']);
        }

        // Get current time
        $now = Carbon::now();

        // Parse the store's closing time
        $closeTime = Carbon::parse($store->close_time);

        // Check if current time is past closing time
        if ($now->format('H:i') >= $closeTime->format('H:i')) {
            // Close the store
            $store->update(['is_active' => false]);

            return response()->json([
                'status' => 'closed',
                'is_active' => false,
            ]);
        }

        return response()->json([
            'status' => 'open',
            'is_active' => $store->is_active,
        ]);
    }

    /**
     * Display the full-screen display view (for secondary monitors/displays).
     */
    public function displayView(Request $request): Response
    {
        $user = $request->user();
        $storeId = session('store_id');
        $store = Store::find($storeId);

        // Get active orders (not completed or cancelled)
        $orders = Order::with(['customer', 'items.product', 'items.addons', 'items.options.customizationOption'])
            ->where('store_id', $storeId)
            ->whereNotIn('status', ['delivered', 'picked_up', 'cancelled'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'public_id' => $order->public_id,
                    'status' => $order->status,
                    'fulfilment_type' => $order->fulfilment_type,
                    'payment_status' => $order->payment_status,
                    'total_cents' => $order->total_cents,
                    'items_total_cents' => $order->items_total_cents,
                    'shipping_cost_cents' => $order->shipping_cost_cents,
                    'customer_name' => $order->customer->first_name . ' ' . $order->customer->last_name,
                    'customer_email' => $order->customer->email,
                    'customer_phone' => $order->customer->phone ?? '',
                    'customer_order_count' => Order::where('customer_id', $order->customer_id)
                        ->where('store_id', $order->store_id)
                        ->where('status', '!=', 'cancelled')
                        ->count(),
                    'pickup_time' => $order->pickup_time,
                    'shipping_name' => $order->shipping_name,
                    'line1' => $order->line1,
                    'line2' => $order->line2,
                    'city' => $order->city,
                    'state' => $order->state,
                    'postcode' => $order->postcode,
                    'country' => $order->country,
                    'tracking_code' => $order->tracking_code,
                    'tracking_url' => $order->tracking_url,
                    'tracking_number' => $order->tracking_number,
                    'courier_company' => $order->courier_company,
                    'items_count' => $order->items->count(),
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name ?? 'Unknown Product',
                            'quantity' => $item->quantity,
                            'price_cents' => $item->unit_price_cents,
                            'total_cents' => $item->total_cents,
                            'special_instructions' => $item->special_instructions,
                            'addons' => $item->addons->map(function ($addon) {
                                return [
                                    'name' => $addon->name,
                                    'quantity' => $addon->quantity,
                                    'unit_price_cents' => $addon->unit_price_cents,
                                    'total_price_cents' => $addon->total_price_cents,
                                ];
                            })->toArray(),
                        ];
                    }),
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ];
            });

        return Inertia::render('Dashboard/DisplayView', [
            'store' => $store,
            'orders' => $orders,
        ]);
    }
}
