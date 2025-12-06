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
        $user = $request->user();

        // Allow users who need onboarding to proceed (they'll see the modal)
        if ($user && $user->isOwner() && $user->merchant) {
            $merchant = $user->merchant;
            $isOwner = $user->id === $merchant->owner_user_id;
            $needsOnboarding = !$merchant->onboarding_complete;

            if ($isOwner && $needsOnboarding) {
                // Skip store selection check for onboarding
                return $next($request);
            }
        }

        if (!session()->has('store_id')) {
            return redirect()->route('store-selection');
        }

        // Verify the store still exists and user has access
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
