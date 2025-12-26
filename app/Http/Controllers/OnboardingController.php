<?php

namespace App\Http\Controllers;

use App\Http\Requests\OnboardingStoreRequest;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    /**
     * Complete onboarding by creating the first store.
     */
    public function completeOnboarding(OnboardingStoreRequest $request)
    {
        \Log::info('=== ONBOARDING START ===');

        $user = $request->user();
        $merchant = $user->merchant;

        \Log::info('User ID: ' . $user->id);
        \Log::info('Merchant ID: ' . $merchant->id);
        \Log::info('Owner User ID: ' . $merchant->owner_user_id);
        \Log::info('Onboarding Complete: ' . ($merchant->onboarding_complete ? 'true' : 'false'));

        // Verify user is the owner
        if ($user->id !== $merchant->owner_user_id) {
            \Log::error('User is not the owner');
            return back()->with('error', 'Only the merchant owner can complete onboarding.');
        }

        // Verify onboarding is not already complete
        if ($merchant->onboarding_complete) {
            \Log::error('Onboarding already complete');
            return back()->with('error', 'Onboarding has already been completed.');
        }

        try {
            \Log::info('Starting transaction...');

            $result = DB::transaction(function () use ($request, $user, $merchant) {
                $validated = $request->validated();
                \Log::info('Validated data:', $validated);

                // Handle logo upload if provided
                $logoPath = null;
                if ($request->hasFile('logo')) {
                    \Log::info('Logo file detected');
                    $logoPath = $request->file('logo')->store('stores', 'public');
                    \Log::info('Logo uploaded to: ' . $logoPath);
                }

                // Create the store
                \Log::info('Creating store with data:', [
                    'merchant_id' => $merchant->id,
                    'name' => $validated['name'],
                    'description' => $validated['description'] ?? null,
                    'contact_email' => $validated['email'] ?? null,
                    'contact_phone' => $validated['phone'] ?? null,
                    'address_primary' => $validated['address'] ?? null,
                    'address_city' => $validated['city'] ?? null,
                    'address_state' => $validated['state'] ?? null,
                    'address_postcode' => $validated['postcode'] ?? null,
                    'logo_path' => $logoPath,
                    'is_active' => true,
                ]);

                // Generate subdomain from store name
                $subdomain = Str::slug($validated['name']);
                $count = 1;
                $originalSubdomain = $subdomain;
                while (Store::where('subdomain', $subdomain)->exists()) {
                    $subdomain = $originalSubdomain . '-' . $count++;
                }

                $store = Store::create([
                    'merchant_id' => $merchant->id,
                    'name' => $validated['name'],
                    'subdomain' => $subdomain,
                    'description' => $validated['description'] ?? null,
                    'contact_email' => $validated['email'] ?? null,
                    'contact_phone' => $validated['phone'] ?? null,
                    'address_primary' => $validated['address'] ?? null,
                    'address_city' => $validated['city'] ?? null,
                    'address_state' => $validated['state'] ?? null,
                    'address_postcode' => $validated['postcode'] ?? null,
                    'logo_path' => $logoPath,
                    'is_active' => true,
                ]);

                \Log::info('Store created with ID: ' . $store->id);

                // Link owner to the store with owner role
                \Log::info('Attaching user to store...');
                $store->users()->attach($user->id, [
                    'role' => 'owner',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                \Log::info('User attached to store');

                // Mark onboarding as complete
                \Log::info('Marking onboarding as complete...');
                $merchant->update(['onboarding_complete' => true]);
                \Log::info('Onboarding marked complete');

                // Set this store as the selected store in session
                session(['store_id' => $store->id]);
                \Log::info('Session store_id set to: ' . $store->id);

                return $store;
            });

            \Log::info('Transaction completed successfully');
            \Log::info('Redirecting to store settings subscription...');

            return redirect()->to('/store/settings#subscription')
                ->with('success', 'Welcome! Your store has been created successfully. Start your free trial to activate your store.');
        } catch (\Exception $e) {
            \Log::error('Exception caught: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());

            return back()
                ->withInput()
                ->with('error', 'Failed to create store: ' . $e->getMessage());
        }
    }
}
