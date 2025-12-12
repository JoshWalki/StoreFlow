<?php

namespace App\Http\Controllers;

use App\Models\Merchant;
use App\Services\StripeConnectService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class StripeConnectController extends Controller
{
    protected StripeConnectService $stripeConnectService;

    public function __construct(StripeConnectService $stripeConnectService)
    {
        $this->stripeConnectService = $stripeConnectService;
    }

    /**
     * Initiate Stripe Connect onboarding for a merchant.
     */
    public function initiate(Request $request)
    {
        $user = $request->user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json(['error' => 'No merchant associated with user'], 403);
        }

        // Check if already onboarded
        if ($merchant->stripe_onboarding_complete) {
            return response()->json([
                'message' => 'Stripe Connect already set up',
                'account_id' => $merchant->stripe_connect_account_id,
            ]);
        }

        try {
            // Create Stripe Connect account if not exists
            if (!$merchant->stripe_connect_account_id) {
                $account = $this->stripeConnectService->createConnectAccount($merchant);
                $merchant->update([
                    'stripe_connect_account_id' => $account['id'],
                ]);

                Log::info('Stripe Connect account created', [
                    'merchant_id' => $merchant->id,
                    'stripe_account_id' => $account['id'],
                ]);
            }

            // Generate account link for onboarding
            $returnUrl = route('stripe.connect.return');
            $refreshUrl = route('stripe.connect.refresh');

            $accountLink = $this->stripeConnectService->createAccountLink(
                $merchant->stripe_connect_account_id,
                $returnUrl,
                $refreshUrl
            );

            Log::info('Stripe Connect onboarding initiated', [
                'merchant_id' => $merchant->id,
                'stripe_account_id' => $merchant->stripe_connect_account_id,
            ]);

            return response()->json([
                'url' => $accountLink['url'],
                'account_id' => $merchant->stripe_connect_account_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe Connect onboarding failed', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to initiate Stripe onboarding',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle return from Stripe Connect onboarding.
     */
    public function handleReturn(Request $request)
    {
        $user = $request->user();
        $merchant = $user->merchant;

        if (!$merchant || !$merchant->stripe_connect_account_id) {
            return redirect()->route('dashboard')->with('error', 'No Stripe Connect account found');
        }

        try {
            // Sync account data to local database (this also retrieves account)
            $merchant = $this->stripeConnectService->syncAccountData($merchant);

            // Retrieve account details to check onboarding status
            $account = $this->stripeConnectService->retrieveAccount($merchant->stripe_connect_account_id);

            // Check if onboarding is complete
            $isReady = $this->stripeConnectService->isAccountReady($account);

            if ($isReady) {
                Log::info('Stripe Connect onboarding completed', [
                    'merchant_id' => $merchant->id,
                    'stripe_account_id' => $merchant->stripe_connect_account_id,
                ]);

                return redirect()->route('dashboard')->with('success', 'Stripe Connect setup completed! You can now accept payments.');
            } else {
                // Onboarding not complete - missing requirements
                $requirements = $account['requirements']['currently_due'] ?? [];

                Log::warning('Stripe Connect onboarding incomplete', [
                    'merchant_id' => $merchant->id,
                    'stripe_account_id' => $merchant->stripe_connect_account_id,
                    'requirements' => $requirements,
                ]);

                return redirect()->route('dashboard')->with('warning', 'Stripe Connect setup is incomplete. Please complete all required information.');
            }

        } catch (\Exception $e) {
            Log::error('Stripe Connect return handling failed', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('dashboard')->with('error', 'Failed to complete Stripe onboarding');
        }
    }

    /**
     * Handle refresh when user exits onboarding early.
     */
    public function handleRefresh(Request $request)
    {
        $user = $request->user();
        $merchant = $user->merchant;

        if (!$merchant || !$merchant->stripe_connect_account_id) {
            return redirect()->route('dashboard')->with('error', 'No Stripe Connect account found');
        }

        try {
            // Generate new account link
            $accountLink = $this->stripeConnectService->createAccountLink(
                $merchant->stripe_connect_account_id,
                route('stripe.connect.return'),
                route('stripe.connect.refresh')
            );

            Log::info('Stripe Connect onboarding refreshed', [
                'merchant_id' => $merchant->id,
                'stripe_account_id' => $merchant->stripe_connect_account_id,
            ]);

            // Redirect to new onboarding link
            return redirect($accountLink['url']);

        } catch (\Exception $e) {
            Log::error('Stripe Connect refresh failed', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('dashboard')->with('error', 'Failed to refresh Stripe onboarding');
        }
    }

    /**
     * Generate Stripe Express Dashboard login link.
     */
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $merchant = $user->merchant;

        if (!$merchant || !$merchant->stripe_connect_account_id) {
            return response()->json(['error' => 'No Stripe Connect account found'], 404);
        }

        if (!$merchant->stripe_onboarding_complete) {
            return response()->json(['error' => 'Stripe Connect onboarding not complete'], 403);
        }

        try {
            // Create dashboard login link
            $loginLink = $this->stripeConnectService->createDashboardLink($merchant->stripe_connect_account_id);

            Log::info('Stripe Express Dashboard accessed', [
                'merchant_id' => $merchant->id,
                'stripe_account_id' => $merchant->stripe_connect_account_id,
            ]);

            return response()->json([
                'url' => $loginLink,
            ]);

        } catch (\Exception $e) {
            Log::error('Stripe Dashboard access failed', [
                'merchant_id' => $merchant->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Failed to access Stripe dashboard',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get current Stripe Connect account status.
     */
    public function status(Request $request)
    {
        $user = $request->user();
        $merchant = $user->merchant;

        if (!$merchant) {
            return response()->json(['error' => 'No merchant found'], 404);
        }

        return response()->json([
            'connected' => (bool) $merchant->stripe_connect_account_id,
            'onboarding_complete' => $merchant->stripe_onboarding_complete,
            'charges_enabled' => $merchant->stripe_charges_enabled,
            'payouts_enabled' => $merchant->stripe_payouts_enabled,
            'details_submitted' => $merchant->stripe_details_submitted,
            'account_id' => $merchant->stripe_connect_account_id,
        ]);
    }
}
