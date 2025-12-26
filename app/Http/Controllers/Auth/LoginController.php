<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Rules\ValidTurnstile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    /**
     * Display the login page.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login');
    }

    /**
     * Handle the login request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'turnstile_token' => ['required', 'string', new ValidTurnstile()],
        ]);

        if (!Auth::attempt($request->only('username', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'username' => __('These credentials do not match our records.'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // Check if owner needs onboarding (no stores created yet)
        if ($user->isOwner() && $user->merchant && !$user->merchant->onboarding_complete) {
            // Redirect to dashboard where onboarding modal will appear
            return redirect()->route('dashboard');
        }

        // Get user's stores
        if ($user->isOwner()) {
            // Owners have access to all merchant stores
            $stores = $user->merchant->stores;
        } else {
            // Staff members have specific store assignments
            $stores = $user->stores()->get();
        }

        // Check store count and redirect accordingly
        if ($stores->count() > 1) {
            // Multiple stores - redirect to store selection page
            return redirect()->route('store-selection');
        } elseif ($stores->count() === 1) {
            // Single store - auto-select and redirect to dashboard
            session(['store_id' => $stores->first()->id]);
            return redirect()->intended(route('dashboard'));
        } else {
            // No stores assigned - logout and show error
            Auth::logout();
            throw ValidationException::withMessages([
                'username' => __('No stores assigned to this account.'),
            ]);
        }
    }
}
