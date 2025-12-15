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
        $orderNumber = $request->query('order');

        // If order number is provided in URL, show tracking info directly
        if ($orderNumber) {
            return $this->showOrderTracking($orderNumber);
        }

        // Otherwise show the search form
        return Inertia::render('Storefront/OrderTracking', [
            'publicId' => null,
        ]);
    }

    /**
     * Track an order by public ID.
     */
    public function track(Request $request)
    {
        $request->validate([
            'public_id' => 'required|string',
        ]);

        $publicId = $request->input('public_id');

        // Redirect to GET route with order number in URL
        return redirect()->route('storefront.track', ['order' => $publicId]);
    }

    /**
     * Display tracking information for a specific order.
     */
    private function showOrderTracking(string $publicId)
    {
        // Find order by public_id with shipping method relationship
        $order = Order::with(['store', 'customer', 'items', 'shippingMethodRelation'])
            ->where('public_id', $publicId)
            ->first();

        if (!$order) {
            return Inertia::render('Storefront/OrderTracking', [
                'error' => 'Order not found. Please check your order number and try again.',
                'publicId' => $publicId,
            ]);
        }

        // Get shipping method name (handle both ID and name stored in shipping_method field)
        $shippingMethodName = null;
        if ($order->shipping_method) {
            // Check if it's numeric (ID) and we have a relationship
            if (is_numeric($order->shipping_method) && $order->shippingMethodRelation) {
                $shippingMethodName = $order->shippingMethodRelation->name;
            } else {
                // It's already a name string
                $shippingMethodName = $order->shipping_method;
            }
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
            'items_total_cents' => $order->items_total_cents,
            'shipping_cost_cents' => $order->shipping_cost_cents,
            'discount_cents' => $order->discount_cents,
            'payment_status' => $order->payment_status,
            'tracking_number' => $order->tracking_number,
            'courier_company' => $order->courier_company,
            'customer_email' => $order->customer_email,
            'customer_mobile' => $order->customer_mobile,
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'unit_price_cents' => $item->unit_price_cents,
                    'total_cents' => $item->total_cents,
                    'customizations' => $item->customizations,
                    'addons' => $item->addons,
                    'special_instructions' => $item->special_instructions,
                ];
            }),
        ];

        // Add shipping info if available
        if ($order->fulfilment_type === 'shipping' || $order->fulfilment_type === 'delivery') {
            $orderData['shipping'] = [
                'status' => $order->shipping_status,
                'tracking_code' => $order->tracking_code,
                'tracking_url' => $order->tracking_url,
                'method' => $shippingMethodName,
            ];

            // Delivery address (masked - full address shown after verification)
            $orderData['shipping_address'] = [
                'name' => $order->shipping_name,
                'line1' => $order->shipping_line1,
                'line2' => $order->shipping_line2,
                'city' => $order->city,
                'state' => $order->state,
                'postcode' => $order->postcode,
                'country' => $order->country,
            ];
        } else {
            $orderData['pickup_time'] = $order->pickup_time;
            $orderData['pickup_notes'] = $order->pickup_notes;
        }

        return Inertia::render('Storefront/OrderTracking', [
            'order' => $orderData,
            'publicId' => $publicId,
            'websocketChannel' => "customer.{$publicId}.order",
            'store' => [
                'name' => $order->store->name,
                'address_primary' => $order->store->address_primary,
                'address_city' => $order->store->address_city,
                'address_state' => $order->store->address_state,
                'address_postcode' => $order->store->address_postcode,
            ],
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
