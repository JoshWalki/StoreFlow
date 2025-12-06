<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class PlatformAuthController extends Controller
{
    /**
     * Show the platform login form.
     */
    public function showLogin(): Response
    {
        return Inertia::render('Platform/Login');
    }

    /**
     * Handle platform login.
     */
    public function login(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $platformPassword = config('app.platform_password');

        if (empty($platformPassword)) {
            return back()->with('error', 'Platform password not configured. Please set PLATFORM_PASSWORD in .env');
        }

        // Check if password matches
        if ($request->password === $platformPassword) {
            session(['platform_authenticated' => true]);
            return redirect()->route('platform.dashboard');
        }

        return back()->with('error', 'Invalid password.');
    }

    /**
     * Handle platform logout.
     */
    public function logout(Request $request)
    {
        session()->forget('platform_authenticated');
        return redirect()->route('platform.login');
    }
}
