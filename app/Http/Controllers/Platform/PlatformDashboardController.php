<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Models\Merchant;
use App\Models\User;
use App\Models\Store;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

class PlatformDashboardController extends Controller
{
    /**
     * Display the platform dashboard.
     */
    public function index(Request $request): Response
    {
        $stats = $this->getDashboardStats();

        return Inertia::render('Platform/Dashboard', [
            'stats' => $stats,
        ]);
    }

    /**
     * Get all merchants with details.
     */
    public function merchants(Request $request): Response
    {
        $query = Merchant::with(['owner:id,name,email', 'stores', 'users']);

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($q) use ($search) {
                        $q->where('email', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                    });
            });
        }

        $merchants = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(function ($merchant) {
                return [
                    'id' => $merchant->id,
                    'name' => $merchant->name,
                    'slug' => $merchant->slug,
                    'owner' => $merchant->owner ? [
                        'id' => $merchant->owner->id,
                        'name' => $merchant->owner->name,
                        'email' => $merchant->owner->email,
                    ] : null,
                    'stores_count' => $merchant->stores->count(),
                    'users_count' => $merchant->users->count(),
                    'created_at' => $merchant->created_at,
                ];
            });

        return Inertia::render('Platform/Merchants', [
            'merchants' => $merchants,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show detailed view of a merchant.
     */
    public function showMerchant(Merchant $merchant): Response
    {
        $merchant->load(['owner', 'stores', 'users']);

        // Get merchant statistics
        $ordersCount = Order::whereHas('store', function ($q) use ($merchant) {
            $q->where('merchant_id', $merchant->id);
        })->count();

        $customersCount = Customer::where('merchant_id', $merchant->id)->count();

        $productsCount = DB::table('products')
            ->join('stores', 'products.store_id', '=', 'stores.id')
            ->where('stores.merchant_id', $merchant->id)
            ->count();

        return Inertia::render('Platform/MerchantDetails', [
            'merchant' => [
                'id' => $merchant->id,
                'name' => $merchant->name,
                'slug' => $merchant->slug,
                'owner' => $merchant->owner ? [
                    'id' => $merchant->owner->id,
                    'name' => $merchant->owner->name,
                    'email' => $merchant->owner->email,
                    'username' => $merchant->owner->username,
                ] : null,
                'stores' => $merchant->stores->map(fn($store) => [
                    'id' => $store->id,
                    'name' => $store->name,
                    'slug' => $store->slug,
                    'status' => $store->status,
                ]),
                'users' => $merchant->users->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                    'role' => $user->role,
                ]),
                'created_at' => $merchant->created_at,
                'updated_at' => $merchant->updated_at,
            ],
            'stats' => [
                'orders_count' => $ordersCount,
                'customers_count' => $customersCount,
                'products_count' => $productsCount,
            ],
        ]);
    }

    /**
     * Calculate dashboard statistics.
     */
    private function getDashboardStats(): array
    {
        $totalMerchants = Merchant::count();
        $totalStores = Store::count();
        $totalUsers = User::count();
        $totalOrders = Order::count();
        $totalCustomers = Customer::count();

        // Merchants created this month
        $merchantsThisMonth = Merchant::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        // Merchants created last month
        $merchantsLastMonth = Merchant::whereYear('created_at', now()->subMonth()->year)
            ->whereMonth('created_at', now()->subMonth()->month)
            ->count();

        // Calculate growth rate
        $growthRate = $merchantsLastMonth > 0
            ? (($merchantsThisMonth - $merchantsLastMonth) / $merchantsLastMonth) * 100
            : 0;

        // Recent merchants (last 5)
        $recentMerchants = Merchant::with(['owner:id,name,email', 'stores'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($merchant) {
                return [
                    'id' => $merchant->id,
                    'name' => $merchant->name,
                    'slug' => $merchant->slug,
                    'owner_email' => $merchant->owner->email ?? 'N/A',
                    'stores_count' => $merchant->stores->count(),
                    'created_at' => $merchant->created_at,
                ];
            });

        // Merchant growth trend (last 6 months)
        $growthTrend = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $count = Merchant::whereYear('created_at', '<=', $date->year)
                ->where(function ($q) use ($date) {
                    $q->whereYear('created_at', '<', $date->year)
                        ->orWhere(function ($q) use ($date) {
                            $q->whereYear('created_at', '=', $date->year)
                                ->whereMonth('created_at', '<=', $date->month);
                        });
                })
                ->count();

            $growthTrend[] = [
                'month' => $date->format('M Y'),
                'count' => $count,
            ];
        }

        // Orders by status (using actual Order statuses)
        $ordersByStatus = [
            'pending' => Order::where('status', 'pending')->count(),
            'accepted' => Order::where('status', 'accepted')->count(),
            'ready' => Order::where('status', 'ready')->count(),
            'delivered' => Order::where('status', 'delivered')->count(),
            'picked_up' => Order::where('status', 'picked_up')->count(),
            'cancelled' => Order::where('status', 'cancelled')->count(),
        ];

        // Total revenue (in cents, convert to dollars)
        // Include completed orders: delivered, picked_up, ready
        $totalRevenueCents = Order::whereIn('status', ['delivered', 'picked_up', 'ready', 'accepted', 'in_progress'])
            ->sum('total_cents');
        $totalRevenue = $totalRevenueCents / 100;

        return [
            'total_merchants' => $totalMerchants,
            'total_stores' => $totalStores,
            'total_users' => $totalUsers,
            'total_orders' => $totalOrders,
            'total_customers' => $totalCustomers,
            'total_revenue' => number_format($totalRevenue, 2),
            'merchants_this_month' => $merchantsThisMonth,
            'merchants_last_month' => $merchantsLastMonth,
            'growth_rate' => round($growthRate, 1),
            'recent_merchants' => $recentMerchants,
            'growth_trend' => $growthTrend,
            'orders_by_status' => $ordersByStatus,
        ];
    }
}
