<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Store;
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
        $orders = Order::with(['customer', 'items.product'])
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
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name ?? 'Unknown Product',
                            'quantity' => $item->qty,
                            'price_cents' => $item->unit_price_cents,
                            'total_cents' => $item->qty * $item->unit_price_cents,
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
        $orders = Order::with(['customer', 'items.product'])
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
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_id' => $item->product_id,
                            'product_name' => $item->product->name ?? 'Unknown Product',
                            'quantity' => $item->qty,
                            'price_cents' => $item->unit_price_cents,
                            'total_cents' => $item->qty * $item->unit_price_cents,
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
}
