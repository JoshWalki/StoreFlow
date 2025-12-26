<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\Orders\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}

    /**
     * Update the order status.
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Authorize using policy
        $this->authorize('updateStatus', $order);

        // Validate the request
        $request->validate([
            'status' => 'required|string|in:pending,in_progress,ready,packing,shipped,delivered,ready_for_pickup,picked_up,cancelled',
        ]);

        try {
            // Update the order status using the service
            $updatedOrder = $this->orderService->updateOrderStatus($order, $request->status);

            return back()->with('success', 'Order status updated successfully.');
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }
    }

    /**
     * Update shipping information for an order.
     */
    public function updateShipping(Request $request, Order $order)
    {
        // Authorize using policy
        $this->authorize('updateShipping', $order);

        // Validate the request
        $request->validate([
            'shipping_status' => 'nullable|string',
            'tracking_code' => 'nullable|string',
            'tracking_url' => 'nullable|url',
            'tracking_number' => 'nullable|string|max:255',
            'courier_company' => 'nullable|string|max:255',
        ]);

        try {
            // Update shipping information
            $updatedOrder = $this->orderService->updateShippingInfo(
                $order,
                $request->input('shipping_status'),
                $request->input('tracking_code'),
                $request->input('tracking_url'),
                $request->input('tracking_number'),
                $request->input('courier_company')
            );

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Shipping information updated successfully.',
                    'order' => $updatedOrder,
                ]);
            }

            return back()->with('success', 'Shipping information updated successfully.');
        } catch (\Exception $e) {
            // Return JSON error for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Update pickup ETA for an order.
     */
    public function updatePickupEta(Request $request, Order $order)
    {
        // Authorize using policy (same as updateShipping)
        $this->authorize('updateShipping', $order);

        // Validate the request
        $validated = $request->validate([
            'pickup_eta' => 'required|date',
        ]);

        try {
            // Update pickup ETA
            $order->update([
                'pickup_eta' => $validated['pickup_eta'],
            ]);

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Pickup ETA updated successfully.',
                    'order' => $order->fresh(),
                ]);
            }

            return back()->with('success', 'Pickup ETA updated successfully.');
        } catch (\Exception $e) {
            // Return JSON error for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Get full order details for modal view.
     */
    public function show(Request $request, Order $order)
    {
        // Authorize using policy
        $this->authorize('view', $order);

        // Load all relationships including addons for refund functionality
        $order->load(['customer', 'items.product', 'items.addons', 'store']);

        // Format the response - add no-cache headers
        return response()->json([
            'id' => $order->id,
            'public_id' => $order->public_id,
            'order_number' => $order->public_id,
            'status' => $order->status,
            'payment_status' => $order->payment_status,
            'fulfilment_type' => $order->fulfilment_type,

            // Financial details
            'total_cents' => $order->total_cents,
            'total' => $order->total_cents / 100,
            'items_total_cents' => $order->items_total_cents,
            'shipping_cost_cents' => $order->shipping_cost_cents,
            'discount_cents' => $order->discount_cents,
            'tax_cents' => $order->tax_cents,

            // Customer information
            'customer_name' => $order->customer->first_name . ' ' . $order->customer->last_name,
            'customer_email' => $order->customer->email,
            'customer_phone' => $order->customer->mobile ?? '',
            'customer_order_count' => Order::where('customer_id', $order->customer_id)
                ->where('store_id', $order->store_id)
                ->where('status', '!=', 'cancelled')
                ->count(),

            // Pickup details
            'pickup_time' => $order->pickup_time,
            'pickup_notes' => $order->pickup_notes,

            // Shipping details
            'shipping_name' => $order->shipping_name,
            'line1' => $order->shipping_line1,
            'line2' => $order->shipping_line2,
            'city' => $order->city,
            'state' => $order->state,
            'postcode' => $order->postcode,
            'country' => $order->country,
            'tracking_code' => $order->tracking_code,
            'tracking_url' => $order->tracking_url,

            // Order items
            'items' => $order->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_name' => $item->name ?? $item->product_name,
                    'quantity' => $item->quantity,
                    'unit_price_cents' => $item->unit_price_cents,
                    'price_cents' => $item->unit_price_cents,
                    'total_cents' => $item->total_cents,
                    'product' => $item->product ? [
                        'id' => $item->product->id,
                        'name' => $item->product->name,
                        'price_cents' => $item->product->price_cents,
                    ] : null,
                    'addons' => $item->addons->map(function ($addon) {
                        return [
                            'name' => $addon->name,
                            'quantity' => $addon->quantity,
                            'total_price_cents' => $addon->total_price_cents,
                        ];
                    }),
                    'special_instructions' => $item->special_instructions,
                    'is_refunded' => (bool) $item->is_refunded,
                    'refund_date' => $item->refund_date,
                    'refund_reason' => $item->refund_reason,
                ];
            }),

            // Timestamps
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }

    /**
     * Refund an order item.
     */
    public function refundItem(Request $request, Order $order, $itemId)
    {
        // Authorize using policy
        $this->authorize('updateStatus', $order);

        // Validate the request
        $validated = $request->validate([
            'refund_reason' => 'required|string|max:255',
        ]);

        try {
            // Load order with relationships
            $order->load(['items', 'store.merchant']);

            // Find the order item
            $orderItem = $order->items()->findOrFail($itemId);

            // Check if already refunded
            if ($orderItem->is_refunded) {
                return response()->json([
                    'message' => 'This item has already been refunded.',
                ], 422);
            }

            // Check if order was paid via Stripe
            if ($order->payment_status !== 'paid' || empty($order->payment_reference)) {
                return response()->json([
                    'message' => 'Cannot process refund. Order was not paid or payment reference is missing.',
                ], 422);
            }

            // Calculate refund amount (item total including addons)
            $refundAmount = $orderItem->calculateTotalWithAddons();

            // Process Stripe refund
            $stripeService = app(\App\Services\StripeConnectService::class);
            $merchant = $order->store->merchant;

            // Map our refund reason to Stripe's accepted values
            // Stripe only accepts: duplicate, fraudulent, or requested_by_customer
            // We store the full reason in our database, but send 'requested_by_customer' to Stripe
            $stripeReason = 'requested_by_customer';

            \Log::info('Processing Stripe refund', [
                'order_id' => $order->id,
                'item_id' => $orderItem->id,
                'payment_intent_id' => $order->payment_reference,
                'refund_amount' => $refundAmount,
                'stripe_account_id' => $merchant->stripe_connect_account_id,
                'user_reason' => $validated['refund_reason'],
                'stripe_reason' => $stripeReason,
            ]);

            $stripeRefund = $stripeService->createRefund(
                $order->payment_reference,
                $refundAmount,
                $stripeReason,
                $merchant->stripe_connect_account_id
            );

            // Update the order item only after successful Stripe refund
            $orderItem->update([
                'is_refunded' => true,
                'refund_date' => now(),
                'refund_reason' => $validated['refund_reason'],
            ]);

            \Log::info('Item refunded successfully', [
                'order_id' => $order->id,
                'item_id' => $orderItem->id,
                'refund_id' => $stripeRefund['id'],
                'amount' => $refundAmount,
            ]);

            // Send refund email to customer
            try {
                \Mail::to($order->customer_email)->send(
                    new \App\Mail\RefundProcessedMail(
                        $order,
                        $orderItem,
                        $stripeRefund['id'],
                        $refundAmount
                    )
                );

                \Log::info('Refund email sent', [
                    'order_id' => $order->id,
                    'customer_email' => $order->customer_email,
                ]);
            } catch (\Exception $e) {
                // Log email error but don't fail the refund
                \Log::error('Failed to send refund email', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Return JSON response for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Item refunded successfully via Stripe. Confirmation email sent to customer.',
                    'item' => $orderItem->fresh(),
                    'refund_id' => $stripeRefund['id'],
                ]);
            }

            return back()->with('success', 'Item refunded successfully via Stripe. Confirmation email sent to customer.');
        } catch (\Stripe\Exception\ApiErrorException $e) {
            \Log::error('Stripe refund failed', [
                'order_id' => $order->id,
                'item_id' => $itemId,
                'error' => $e->getMessage(),
            ]);

            // Return JSON error for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => 'Stripe refund failed: ' . $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Stripe refund failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Refund processing error', [
                'order_id' => $order->id,
                'item_id' => $itemId,
                'error' => $e->getMessage(),
            ]);

            // Return JSON error for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Display the order history page.
     */
    public function history(Request $request)
    {
        $user = $request->user();
        $storeId = session('store_id');
        $store = \App\Models\Store::find($storeId);

        // Build the orders query
        $query = Order::with(['customer', 'items.product'])
            ->where('store_id', $storeId);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('public_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by status
        if ($request->has('status') && $request->input('status') !== '' && $request->input('status') !== 'all') {
            $query->where('status', $request->input('status'));
        }

        // Filter by period
        if ($request->has('period') && $request->input('period') !== 'all') {
            $period = $request->input('period');

            switch ($period) {
                case 'today':
                    $query->whereDate('created_at', now()->toDateString());
                    break;
                case 'week':
                    $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $query->whereMonth('created_at', now()->month)
                          ->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Filter by date range (if custom dates are provided)
        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        // Sort by most recent first
        $orders = $query->orderBy('created_at', 'desc')
            ->paginate(25)
            ->through(function ($order) {
                return [
                    'id' => $order->id,
                    'public_id' => $order->public_id,
                    'order_number' => $order->public_id,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'fulfilment_type' => $order->fulfilment_type,
                    'total_cents' => $order->total_cents,
                    'total' => $order->total_cents / 100,
                    'items_total_cents' => $order->items_total_cents,
                    'shipping_cost_cents' => $order->shipping_cost_cents,
                    'items_count' => $order->items->count(),
                    'customer_name' => $order->customer->first_name . ' ' . $order->customer->last_name,
                    'customer_email' => $order->customer->email,
                    'customer_phone' => $order->customer->mobile ?? '',
                    'customer_order_count' => Order::where('customer_id', $order->customer_id)
                        ->where('store_id', $order->store_id)
                        ->where('status', '!=', 'cancelled')
                        ->count(),
                    'created_at' => $order->created_at,
                    'updated_at' => $order->updated_at,
                ];
            });

        // Calculate statistics for the filtered results
        $allOrders = Order::where('store_id', $storeId);

        // Apply same filters to stats
        if ($request->filled('search')) {
            $search = $request->input('search');
            $allOrders->where(function($q) use ($search) {
                $q->where('public_id', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($customerQuery) use ($search) {
                      $customerQuery->where('first_name', 'like', "%{$search}%")
                          ->orWhere('last_name', 'like', "%{$search}%")
                          ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        if ($request->has('status') && $request->input('status') !== '' && $request->input('status') !== 'all') {
            $allOrders->where('status', $request->input('status'));
        }
        if ($request->has('period') && $request->input('period') !== 'all') {
            $period = $request->input('period');

            switch ($period) {
                case 'today':
                    $allOrders->whereDate('created_at', now()->toDateString());
                    break;
                case 'week':
                    $allOrders->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    break;
                case 'month':
                    $allOrders->whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year);
                    break;
                case 'year':
                    $allOrders->whereYear('created_at', now()->year);
                    break;
            }
        }
        if ($request->has('date_from')) {
            $allOrders->whereDate('created_at', '>=', $request->input('date_from'));
        }
        if ($request->has('date_to')) {
            $allOrders->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $stats = [
            'total_orders' => $allOrders->count(),
            'total_revenue' => $allOrders->sum('total_cents') / 100,
            'average_order' => $allOrders->count() > 0
                ? $allOrders->avg('total_cents') / 100
                : 0,
            'cancelled_orders' => Order::where('store_id', $storeId)
                ->where('status', 'cancelled')
                ->count(),
        ];

        return \Inertia\Inertia::render('Orders/History', [
            'store' => $store,
            'user' => $user,
            'orders' => $orders,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'period', 'date_from', 'date_to']),
            'statuses' => [
                'all' => 'All Orders',
                'pending' => 'Pending',
                'in_progress' => 'In Progress',
                'ready' => 'Ready',
                'packing' => 'Packing',
                'shipped' => 'Shipped',
                'delivered' => 'Delivered',
                'picked_up' => 'Picked Up',
                'cancelled' => 'Cancelled',
            ],
        ]);
    }
}
