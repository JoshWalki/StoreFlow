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
            DB::raw('COALESCE((SELECT MAX(created_at) FROM orders WHERE orders.customer_id = customers.id), customers.created_at) as last_order_at')
        ]);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%");
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
                $query->orderBy('last_order_at', 'desc');
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
                'mobile' => $customer->mobile,
                'orders_count' => $customer->orders_count,
                'lifetime_value' => $customer->lifetime_value_cents / 100,
                'loyalty_points' => $customer->loyaltyAccount->points ?? 0,
                'last_order_at' => $customer->last_order_at,
                'created_at' => $customer->created_at,
            ];
        });

        return Inertia::render('Customers/Index', [
            'store' => $store,
            'user' => $user,
            'customers' => $customers,
            'filters' => $request->only(['search', 'sort']),
        ]);
    }
}
