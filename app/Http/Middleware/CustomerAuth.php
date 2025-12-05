<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CustomerAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if customer is authenticated using customer guard
        if (!Auth::guard('customer')->check()) {
            // Store intended URL for redirect after login
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Unauthenticated. Please log in to continue.'
                ], 401);
            }

            // Get store from route parameter for redirect
            $store = $request->route('store');

            if ($store && is_object($store)) {
                return redirect()->route('customer.login', ['store' => $store->id])
                    ->with('error', 'Please log in to access this page.');
            }

            // Fallback to home if no store context (shouldn't happen in normal flow)
            return redirect('/')
                ->with('error', 'Please log in to access this page.');
        }

        // Verify customer belongs to the store's merchant
        $customer = Auth::guard('customer')->user();
        $store = $request->route('store');

        if ($store && is_object($store)) {
            if ($customer->merchant_id !== $store->merchant_id) {
                Auth::guard('customer')->logout();
                $request->session()->invalidate();

                return redirect()->route('storefront.index', ['store' => $store->id])
                    ->with('error', 'Access denied. Your account is not associated with this store.');
            }
        }

        return $next($request);
    }
}
