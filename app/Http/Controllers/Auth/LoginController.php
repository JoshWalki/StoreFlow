<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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
        ]);

        if (!Auth::attempt($request->only('username', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'username' => __('These credentials do not match our records.'),
            ]);
        }

        $request->session()->regenerate();

        $user = Auth::user();

        // Check if user has multiple stores
        $stores = $user->stores()->get();

        if ($user->isOwner()) {
            // Owners have access to all merchant stores
            $stores = $user->merchant->stores;
        }

        if ($stores->count() > 1) {
            // Redirect to store selection
            return redirect()->route('store-selection');
        } elseif ($stores->count() === 1) {
            // Set the store and redirect to dashboard
            session(['store_id' => $stores->first()->id]);
            return redirect()->intended(route('dashboard'));
        } else {
            // No stores assigned
            Auth::logout();
            throw ValidationException::withMessages([
                'username' => __('No stores assigned to this account.'),
            ]);
        }
    }
}
