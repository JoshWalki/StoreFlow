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
        ]);

        try {
            // Update shipping information
            $updatedOrder = $this->orderService->updateShippingInfo(
                $order,
                $request->input('shipping_status'),
                $request->input('tracking_code'),
                $request->input('tracking_url')
            );

            return back()->with('success', 'Shipping information updated successfully.');
        } catch (\Exception $e) {
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

        // Load all relationships
        $order->load(['customer', 'items.product']);

        // Format the response
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
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price_cents' => $item->price_cents,
                    'total_cents' => $item->total_cents,
                ];
            }),

            // Timestamps
            'created_at' => $order->created_at,
            'updated_at' => $order->updated_at,
        ]);
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
            'total_revenue' => number_format($allOrders->sum('total_cents') / 100, 2),
            'average_order' => $allOrders->count() > 0
                ? number_format($allOrders->avg('total_cents') / 100, 2)
                : '0.00',
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
