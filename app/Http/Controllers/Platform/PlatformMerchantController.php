<?php

namespace App\Http\Controllers\Platform;

use App\Http\Controllers\Controller;
use App\Http\Requests\Platform\CreateMerchantRequest;
use App\Models\Merchant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PlatformMerchantController extends Controller
{
    /**
     * Show the form for creating a new merchant.
     */
    public function create(): Response
    {
        return Inertia::render('Platform/CreateMerchant');
    }

    /**
     * Store a newly created merchant with owner account.
     */
    public function store(CreateMerchantRequest $request)
    {
        try {
            $result = DB::transaction(function () use ($request) {
                $validated = $request->validated();

                // Create merchant
                $merchantSlug = $validated['merchant_slug'] ?? Str::slug($validated['merchant_name']);

                // Ensure unique merchant slug
                $count = 1;
                $originalSlug = $merchantSlug;
                while (Merchant::where('slug', $merchantSlug)->exists()) {
                    $merchantSlug = $originalSlug . '-' . $count++;
                }

                $merchant = Merchant::create([
                    'name' => $validated['merchant_name'],
                    'slug' => $merchantSlug,
                    'owner_user_id' => null, // Will be set after creating user
                ]);

                // Create owner user
                $user = User::create([
                    'merchant_id' => $merchant->id,
                    'name' => $validated['owner_name'],
                    'email' => $validated['owner_email'],
                    'username' => $validated['owner_username'],
                    'password' => Hash::make($validated['owner_password']),
                    'role' => 'owner',
                ]);

                // Update merchant with owner_user_id
                $merchant->update(['owner_user_id' => $user->id]);

                return [
                    'merchant' => $merchant,
                    'user' => $user,
                ];
            });

            return redirect()->route('platform.merchants')
                ->with('success', 'Merchant created successfully! Owner email: ' . $result['user']->email);
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create merchant: ' . $e->getMessage());
        }
    }
}
