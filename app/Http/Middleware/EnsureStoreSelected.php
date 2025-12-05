<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStoreSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!session()->has('store_id')) {
            return redirect()->route('store-selection');
        }

        // Verify the store still exists and user has access
        $user = $request->user();
        $storeId = session('store_id');

        $hasAccess = $user->isOwner()
            ? $user->merchant->stores()->where('stores.id', $storeId)->exists()
            : $user->stores()->where('stores.id', $storeId)->exists();

        if (!$hasAccess) {
            session()->forget('store_id');
            return redirect()->route('store-selection');
        }

        return $next($request);
    }
}
