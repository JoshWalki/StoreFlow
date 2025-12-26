<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\Auth\CustomerPasswordResetMail;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class CustomerPasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotForm(Request $request, $store): Response
    {
        $storeModel = \App\Models\Store::findOrFail($store);

        return Inertia::render('Storefront/Auth/ForgotPassword', [
            'store' => $storeModel,
        ]);
    }

    /**
     * Handle forgot password request.
     */
    public function sendResetLink(Request $request, $store)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $storeModel = \App\Models\Store::findOrFail($store);

        // Find customer by email
        $customer = Customer::where('email', $request->email)->first();

        // Generate and send reset link only if customer exists
        if ($customer) {
            // Generate secure random token
            $token = Str::random(64);

            // Set token expiry to 10 minutes from now
            $expiresAt = now()->addMinutes(10);

            // Update customer with reset token
            $customer->update([
                'reset_token' => hash('sha256', $token),
                'reset_token_expires_at' => $expiresAt,
            ]);

            // Send reset email with store context
            Mail::to($customer->email)->send(new CustomerPasswordResetMail($customer, $token, $storeModel));
        }

        // Always show the same message (security: don't reveal if account exists)
        return back()->with('success', 'If an account with that email exists, a password reset link has been sent.');
    }

    /**
     * Show the reset password form.
     */
    public function showResetForm(Request $request, $store, string $token): Response
    {
        $storeModel = \App\Models\Store::findOrFail($store);

        return Inertia::render('Storefront/Auth/ResetPassword', [
            'store' => $storeModel,
            'token' => $token,
        ]);
    }

    /**
     * Handle password reset.
     */
    public function resetPassword(Request $request, $store)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $storeModel = \App\Models\Store::findOrFail($store);

        // Hash the token to compare with database
        $hashedToken = hash('sha256', $request->token);

        // Find customer with valid reset token
        $customer = Customer::where('reset_token', $hashedToken)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (!$customer) {
            return back()->withErrors([
                'token' => 'This password reset link is invalid or has expired.',
            ]);
        }

        // Update password and clear reset token
        $customer->update([
            'password' => Hash::make($request->password),
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ]);

        return redirect()->route('customer.login', ['store' => $storeModel->id])->with('success', 'Your password has been reset successfully. You can now login with your new password.');
    }
}
