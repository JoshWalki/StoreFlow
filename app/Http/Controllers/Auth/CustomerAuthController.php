<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Store;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class CustomerAuthController extends Controller
{
    /**
     * Display the customer login page.
     */
    public function showLogin(Request $request, Store $store): Response
    {
        return Inertia::render('Storefront/Auth/Login', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'merchant_id' => $store->merchant_id,
                'theme' => $store->theme_key ?? 'classic',
            ],
        ]);
    }

    /**
     * Display the customer registration page.
     */
    public function showRegister(Request $request, Store $store): Response
    {
        return Inertia::render('Storefront/Auth/Register', [
            'store' => [
                'id' => $store->id,
                'name' => $store->name,
                'merchant_id' => $store->merchant_id,
                'theme' => $store->theme_key ?? 'classic',
            ],
        ]);
    }

    /**
     * Handle customer login with rate limiting.
     */
    public function login(Request $request, Store $store): RedirectResponse
    {
        // Rate limiting key based on email and IP
        $key = 'customer-login:' . $request->ip() . ':' . $request->input('email');

        // Check rate limit (5 attempts per minute)
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.'],
            ]);
        }

        // Validate input with sanitization
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        // Sanitize email
        $credentials['email'] = filter_var($credentials['email'], FILTER_SANITIZE_EMAIL);

        // Find customer by email and merchant_id (customers are unique per merchant)
        $customer = Customer::where('email', $credentials['email'])
            ->where('merchant_id', $store->merchant_id)
            ->first();

        // Verify customer exists and password is correct
        if (!$customer || !Hash::check($credentials['password'], $customer->password)) {
            // Increment rate limiter on failed attempt
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'email' => ['The provided credentials do not match our records.'],
            ]);
        }

        // Verify customer has a password (is registered)
        if (empty($customer->password)) {
            throw ValidationException::withMessages([
                'email' => ['This account is not set up for login. Please register first.'],
            ]);
        }

        // Clear rate limiter on successful login
        RateLimiter::clear($key);

        // Log in the customer using the customer guard
        Auth::guard('customer')->login($customer, $request->boolean('remember'));

        // Regenerate session to prevent session fixation
        $request->session()->regenerate();

        // Store the store_id in session for customer context
        session(['customer_store_id' => $store->id]);

        return redirect()->route('customer.profile', ['store' => $store->id])
            ->with('success', 'Welcome back, ' . $customer->full_name . '!');
    }

    /**
     * Handle customer registration with secure password hashing.
     */
    public function register(Request $request, Store $store): RedirectResponse
    {
        // Rate limiting key for registration
        $key = 'customer-register:' . $request->ip();

        // Check rate limit (3 registrations per hour)
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'email' => ['Too many registration attempts. Please try again in ' . ceil($seconds / 60) . ' minutes.'],
            ]);
        }

        // Validate input with sanitization
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        // Sanitize inputs
        $validated['first_name'] = strip_tags(trim($validated['first_name']));
        $validated['last_name'] = strip_tags(trim($validated['last_name']));
        $validated['email'] = filter_var($validated['email'], FILTER_SANITIZE_EMAIL);
        if (isset($validated['mobile'])) {
            $validated['mobile'] = preg_replace('/[^0-9+\-() ]/', '', $validated['mobile']);
        }

        // Check if email already exists for this merchant
        $existingCustomer = Customer::where('email', $validated['email'])
            ->where('merchant_id', $store->merchant_id)
            ->first();

        if ($existingCustomer) {
            // Increment rate limiter
            RateLimiter::hit($key, 3600);

            throw ValidationException::withMessages([
                'email' => ['An account with this email already exists.'],
            ]);
        }

        // Create customer with bcrypt hashed password (via Hash::make)
        $customer = Customer::create([
            'merchant_id' => $store->merchant_id,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'] ?? null,
            'password' => Hash::make($validated['password']), // Uses bcrypt by default
        ]);

        // Increment rate limiter on successful registration
        RateLimiter::hit($key, 3600);

        // Log in the customer automatically
        Auth::guard('customer')->login($customer);

        // Regenerate session
        $request->session()->regenerate();

        // Store the store_id in session
        session(['customer_store_id' => $store->id]);

        return redirect()->route('customer.profile', ['store' => $store->id])
            ->with('success', 'Welcome, ' . $customer->full_name . '! Your account has been created.');
    }

    /**
     * Handle customer logout.
     */
    public function logout(Request $request): RedirectResponse
    {
        $store = null;

        // Get store from session before logout
        if ($storeId = session('customer_store_id')) {
            $store = Store::find($storeId);
        }

        // Log out the customer
        Auth::guard('customer')->logout();

        // Invalidate the session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        // Remove customer store session
        session()->forget('customer_store_id');

        // Redirect to store or homepage
        if ($store) {
            return redirect()->route('storefront.index', ['store' => $store->id])
                ->with('success', 'You have been logged out successfully.');
        }

        return redirect()->route('login')
            ->with('success', 'You have been logged out successfully.');
    }
}
