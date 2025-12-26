<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\Auth\UserPasswordResetMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetController extends Controller
{
    /**
     * Show the forgot password form.
     */
    public function showForgotForm(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'type' => 'user',
        ]);
    }

    /**
     * Handle forgot password request.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'username' => ['required', 'string'],
        ]);

        // Find user by username
        $user = User::where('username', $request->username)->first();

        // Generate and send reset link only if user exists
        if ($user) {
            // Generate secure random token
            $token = Str::random(64);

            // Set token expiry to 10 minutes from now
            $expiresAt = now()->addMinutes(10);

            // Update user with reset token
            $user->update([
                'reset_token' => hash('sha256', $token),
                'reset_token_expires_at' => $expiresAt,
            ]);

            // Send reset email
            Mail::to($user->email)->send(new UserPasswordResetMail($user, $token));
        }

        // Always show the same message (security: don't reveal if account exists)
        return back()->with('success', 'If an account with that username exists, a password reset link has been sent to the associated email address.');
    }

    /**
     * Show the reset password form.
     */
    public function showResetForm(Request $request, string $token): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'type' => 'user',
        ]);
    }

    /**
     * Handle password reset.
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        // Hash the token to compare with database
        $hashedToken = hash('sha256', $request->token);

        // Find user with valid reset token
        $user = User::where('reset_token', $hashedToken)
            ->where('reset_token_expires_at', '>', now())
            ->first();

        if (!$user) {
            return back()->withErrors([
                'token' => 'This password reset link is invalid or has expired.',
            ]);
        }

        // Update password and clear reset token
        $user->update([
            'password' => Hash::make($request->password),
            'reset_token' => null,
            'reset_token_expires_at' => null,
        ]);

        return redirect()->route('login')->with('success', 'Your password has been reset successfully. You can now login with your new password.');
    }
}
