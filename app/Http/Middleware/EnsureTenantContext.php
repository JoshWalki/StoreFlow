<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantContext
{
    /**
     * Handle an incoming request.
     *
     * This middleware sets up global query scopes to prevent cross-tenant data leakage.
     * It adds automatic WHERE clauses to queries based on the authenticated user's context.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->merchant_id) {
            // Set merchant context for this request
            app()->instance('tenant.merchant_id', $user->merchant_id);

            // Set store context if selected
            if (session()->has('store_id')) {
                app()->instance('tenant.store_id', session('store_id'));
            }

            // Add global query scopes for tenant isolation
            $this->applyGlobalScopes($user);
        }

        return $next($request);
    }

    /**
     * Apply global query scopes for tenant isolation.
     *
     * This ensures all queries automatically filter by merchant_id.
     * Note: This is a safety net. Models should still have explicit scopes.
     */
    protected function applyGlobalScopes($user): void
    {
        // Listen to Eloquent queries and add merchant_id filter
        // This is a safety mechanism - models should have their own scopes
        $models = [
            \App\Models\Store::class,
            \App\Models\Product::class,
            \App\Models\Category::class,
            \App\Models\Order::class,
            \App\Models\Customer::class,
            \App\Models\ShippingZone::class,
            \App\Models\ShippingMethod::class,
            \App\Models\LoyaltyConfig::class,
            \App\Models\LoyaltyAccount::class,
        ];

        foreach ($models as $model) {
            $model::addGlobalScope('tenant', function ($query) use ($user) {
                // Only apply if the table has merchant_id column
                if (in_array('merchant_id', $query->getModel()->getFillable())) {
                    $query->where($query->qualifyColumn('merchant_id'), $user->merchant_id);
                }
            });
        }
    }

    /**
     * Clean up global scopes after request.
     */
    public function terminate(Request $request, Response $response): void
    {
        // Clean up any global scopes
        $models = [
            \App\Models\Store::class,
            \App\Models\Product::class,
            \App\Models\Category::class,
            \App\Models\Order::class,
            \App\Models\Customer::class,
            \App\Models\ShippingZone::class,
            \App\Models\ShippingMethod::class,
            \App\Models\LoyaltyConfig::class,
            \App\Models\LoyaltyAccount::class,
        ];

        foreach ($models as $model) {
            // Remove the global scope
            $instance = new $model();
            $globalScopes = $instance->getGlobalScopes();
            if (isset($globalScopes['tenant'])) {
                unset($globalScopes['tenant']);
            }
        }
    }
}
