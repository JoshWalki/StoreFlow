<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Store;
use App\Models\Merchant;
use App\Notifications\VerifyEmailNotification;
use App\Mail\WelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Illuminate\Support\Str;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     */
    public function create()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle merchant registration
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::min(8)],
            'terms' => ['accepted']
        ], [
            'name.required' => 'Your name is required',
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email is already registered',
            'password.required' => 'Password is required',
            'password.confirmed' => 'Passwords do not match',
            'password.min' => 'Password must be at least 8 characters',
            'terms.accepted' => 'You must accept the terms and conditions'
        ]);

        try {
            DB::beginTransaction();

            // Create the merchant - will complete onboarding to set up store
            $merchant = Merchant::create([
                'name' => $validated['name'], // Set to user's name, can be changed in onboarding
                'slug' => Str::slug($validated['name']) . '-' . Str::random(6),
                'status' => 'active',
                'onboarding_complete' => false, // User will complete onboarding
            ]);

            // Create the user (owner)
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'username' => $validated['email'], // Username is set to email
                'password' => Hash::make($validated['password']),
                'merchant_id' => $merchant->id,
                'role' => 'owner',
            ]);

            // Set the owner_user_id on the merchant
            $merchant->update(['owner_user_id' => $user->id]);

            // Store will be created during onboarding

            DB::commit();

            // Send welcome email
            try {
                Mail::to($user->email)->send(new WelcomeMail($user));
            } catch (\Exception $e) {
                // Log the error but don't fail registration
                \Log::error('Failed to send welcome email: ' . $e->getMessage());
            }

            // Log the user in immediately
            auth()->login($user);

            // Redirect to dashboard - onboarding modal will appear
            return redirect()->route('dashboard')->with('success', 'Welcome to StoreFlow! Let\'s get your store set up.');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Registration failed. Please try again. ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Verify email with token
     */
    public function verifyEmail($token)
    {
        $user = User::where('email_verification_token', $token)->first();

        if (!$user) {
            return redirect()->route('login')->withErrors([
                'error' => 'Invalid verification link.'
            ]);
        }

        if ($user->email_verified_at) {
            return redirect()->route('login')->with('success', 'Email already verified. Please login.');
        }

        // Verify the email
        $user->update([
            'email_verified_at' => now(),
            'email_verification_token' => null,
        ]);

        // Activate the merchant
        $merchant = $user->merchant;
        if ($merchant) {
            $merchant->update(['status' => 'active']);

            // Activate the default store
            if ($merchant->defaultStore) {
                $merchant->defaultStore->update(['status' => 'active']);
            }
        }

        return redirect()->route('login')->with('success', 'Email verified successfully! You can now login to your account.');
    }

    /**
     * Resend verification email
     */
    public function resendVerification(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email']
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->email_verified_at) {
            return back()->withErrors([
                'error' => 'Email is already verified.'
            ]);
        }

        // Generate new token
        $token = Str::random(60);
        $user->update(['email_verification_token' => $token]);

        // Resend verification email
        $user->notify(new VerifyEmailNotification($token));

        return back()->with('success', 'Verification email sent! Please check your inbox.');
    }
}
