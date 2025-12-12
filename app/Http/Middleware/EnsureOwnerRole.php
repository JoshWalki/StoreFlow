<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Ensure Owner Role Middleware
 *
 * Restricts access to owner-only features like Stripe Connect and subscription management.
 * Prevents managers and staff from accessing sensitive billing/payment settings.
 */
class EnsureOwnerRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->isOwner()) {
            abort(403, 'Only store owners can access this feature.');
        }

        return $next($request);
    }
}
