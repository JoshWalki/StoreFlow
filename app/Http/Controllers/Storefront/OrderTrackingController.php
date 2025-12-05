<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderTrackingController extends Controller
{
    /**
     * Display the order tracking page.
     */
    public function show(Request $request): Response
    {
        return Inertia::render('Storefront/OrderTracking', [
            'publicId' => $request->query('order'),
        ]);
    }

    /**
     * Track an order by public ID.
     */
    public function track(Request $request): Response
    {
        $request->validate([
            'public_id' => 'required|string',
        ]);

        $publicId = $request->input('public_id');

        // Find order by public_id
        $order = Order::with(['store', 'customer', 'items'])
            ->where('public_id', $publicId)
            ->first();

        if (!$order) {
            return Inertia::render('Storefront/OrderTracking', [
                'error' => 'Order not found. Please check your order number and try again.',
                'publicId' => $publicId,
            ]);
        }

        // Return sanitized order data
        $orderData = [
            'public_id' => $order->public_id,
            'status' => $order->status,
            'fulfilment_type' => $order->fulfilment_type,
            'created_at' => $order->created_at,
            'store_name' => $order->store->name,
            'items_count' => $order->items->count(),
            'total_cents' => $order->total_cents,
            'payment_status' => $order->payment_status,
        ];

        // Add shipping info if available
        if ($order->fulfilment_type === 'shipping') {
            $orderData['shipping'] = [
                'status' => $order->shipping_status,
                'tracking_code' => $order->tracking_code,
                'tracking_url' => $order->tracking_url,
                'method' => $order->shipping_method,
            ];
        } else {
            $orderData['pickup_time'] = $order->pickup_time;
        }

        return Inertia::render('Storefront/OrderTracking', [
            'order' => $orderData,
            'publicId' => $publicId,
            'websocketChannel' => "customer.{$publicId}.order",
        ]);
    }

    /**
     * Get order status timeline.
     */
    private function getStatusTimeline(string $status, string $fulfilmentType): array
    {
        if ($fulfilmentType === 'shipping') {
            return [
                ['status' => 'pending', 'label' => 'Order Placed', 'completed' => true],
                ['status' => 'accepted', 'label' => 'Accepted', 'completed' => in_array($status, ['accepted', 'in_progress', 'ready', 'packing', 'shipped', 'delivered'])],
                ['status' => 'in_progress', 'label' => 'Processing', 'completed' => in_array($status, ['in_progress', 'ready', 'packing', 'shipped', 'delivered'])],
                ['status' => 'packing', 'label' => 'Packing', 'completed' => in_array($status, ['packing', 'shipped', 'delivered'])],
                ['status' => 'shipped', 'label' => 'Shipped', 'completed' => in_array($status, ['shipped', 'delivered'])],
                ['status' => 'delivered', 'label' => 'Delivered', 'completed' => $status === 'delivered'],
            ];
        } else {
            return [
                ['status' => 'pending', 'label' => 'Order Placed', 'completed' => true],
                ['status' => 'accepted', 'label' => 'Accepted', 'completed' => in_array($status, ['accepted', 'in_progress', 'ready_for_pickup', 'picked_up'])],
                ['status' => 'in_progress', 'label' => 'Preparing', 'completed' => in_array($status, ['in_progress', 'ready_for_pickup', 'picked_up'])],
                ['status' => 'ready_for_pickup', 'label' => 'Ready', 'completed' => in_array($status, ['ready_for_pickup', 'picked_up'])],
                ['status' => 'picked_up', 'label' => 'Picked Up', 'completed' => $status === 'picked_up'],
            ];
        }
    }
}
