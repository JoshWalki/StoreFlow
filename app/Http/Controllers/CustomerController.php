<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers for the merchant's store.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $merchantId = $user->merchant_id;
        $storeId = session('store_id');
        $store = Store::find($storeId);

        // Build the customer query with order statistics
        $query = Customer::where('merchant_id', $merchantId)
            ->withCount('orders')
            ->with('loyaltyAccount');

        // Add order value aggregation
        $query->addSelect([
            'customers.*',
            DB::raw('COALESCE((SELECT SUM(total_cents) FROM orders WHERE orders.customer_id = customers.id), 0) as lifetime_value_cents'),
            DB::raw('COALESCE((SELECT MAX(created_at) FROM orders WHERE orders.customer_id = customers.id), customers.created_at) as last_order_date')
        ]);

        // Search functionality - including phone number
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('status') && $request->input('status') !== 'all') {
            $status = $request->input('status');
            if ($status === 'active') {
                // Customers with orders in last 90 days
                $query->whereHas('orders', function($q) {
                    $q->where('created_at', '>=', now()->subDays(90));
                });
            } elseif ($status === 'inactive') {
                // Customers with no orders in last 90 days
                $query->where(function($q) {
                    $q->whereDoesntHave('orders')
                      ->orWhereHas('orders', function($subQ) {
                          $subQ->where('created_at', '<', now()->subDays(90));
                      });
                });
            }
        }

        // Date range filter
        if ($request->has('date_from')) {
            $query->whereHas('orders', function($q) use ($request) {
                $q->where('created_at', '>=', $request->input('date_from'));
            });
        }
        if ($request->has('date_to')) {
            $query->whereHas('orders', function($q) use ($request) {
                $q->where('created_at', '<=', $request->input('date_to'));
            });
        }

        // Sorting
        $sort = $request->input('sort', 'recent');
        switch ($sort) {
            case 'lifetime_value':
                $query->orderBy('lifetime_value_cents', 'desc');
                break;
            case 'name':
                $query->orderBy('first_name')->orderBy('last_name');
                break;
            case 'recent':
            default:
                $query->orderBy('last_order_date', 'desc');
                break;
        }

        $customers = $query->paginate(20);

        // Format the customer data for the frontend
        $customers->through(function ($customer) {
            return [
                'id' => $customer->id,
                'first_name' => $customer->first_name,
                'last_name' => $customer->last_name,
                'name' => trim($customer->first_name . ' ' . $customer->last_name),
                'email' => $customer->email,
                'phone' => $customer->mobile, // Map mobile to phone for frontend
                'orders_count' => $customer->orders_count,
                'lifetime_value' => $customer->lifetime_value_cents / 100,
                'loyalty_points' => $customer->loyaltyAccount->points ?? 0,
                'last_order_date' => $customer->last_order_date,
                'created_at' => $customer->created_at,
            ];
        });

        return Inertia::render('Customers/Index', [
            'store' => $store,
            'user' => $user,
            'customers' => $customers,
            'filters' => $request->only(['search', 'sort', 'status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Get orders for a specific customer.
     */
    public function orders(Request $request, Customer $customer)
    {
        $user = $request->user();

        // Ensure the customer belongs to the merchant
        if ($customer->merchant_id !== $user->merchant_id) {
            abort(403, 'Unauthorized access to customer data');
        }

        $orders = $customer->orders()
            ->with(['items.product', 'items.addons'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'public_id' => $order->public_id,
                    'status' => $order->status,
                    'payment_status' => $order->payment_status,
                    'fulfilment_type' => $order->fulfilment_type,
                    'items_total' => $order->items_total_cents / 100,
                    'tax' => $order->tax_cents / 100,
                    'shipping_cost' => $order->shipping_cost_cents / 100,
                    'total' => $order->total_cents / 100,
                    'created_at' => $order->created_at,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'name' => $item->name,
                            'quantity' => $item->quantity,
                            'price' => $item->unit_price_cents / 100,
                            'total' => $item->total_cents / 100,
                            'addons' => $item->addons->map(function ($addon) {
                                return [
                                    'name' => $addon->name,
                                    'quantity' => $addon->quantity,
                                    'price' => $addon->unit_price_cents / 100,
                                    'total' => $addon->total_price_cents / 100,
                                ];
                            }),
                        ];
                    }),
                ];
            });

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->full_name,
                'email' => $customer->email,
                'phone' => $customer->mobile,
            ],
            'orders' => $orders,
        ]);
    }
}
