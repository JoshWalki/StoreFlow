<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class StoreSettingsController extends Controller
{
    /**
     * Display the store settings page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $storeId = session('store_id');
        $store = Store::findOrFail($storeId);

        // Ensure the user owns this store
        if ($store->merchant_id !== $user->merchant_id) {
            abort(403, 'Unauthorized access to store settings.');
        }

        // Extended settings stored in JSON or separate table (for future use)
        // For now, we'll return defaults that can be customized
        $storeSettings = [
            'email' => $store->contact_email ?? '',
            'phone' => $store->contact_phone ?? '',
            'address' => $store->address_primary ?? '',
            'city' => $store->address_city ?? '',
            'state' => $store->address_state ?? '',
            'postcode' => $store->address_postcode ?? '',
            'currency' => 'AUD',
            'timezone' => $store->timezone ?? 'Australia/Sydney',
            'tax_rate' => 0,
            'auto_fulfill' => false,
        ];

        // Extend store model with additional fields for the form
        $storeData = [
            'id' => $store->id,
            'name' => $store->name,
            'subdomain' => $store->subdomain ?? '',
            'description' => $store->description,
            'is_active' => $store->is_active ?? true,
            'theme_key' => $store->theme_key,
            'timezone' => $store->timezone,
            'shipping_enabled' => $store->shipping_enabled,
            'logo_path' => $store->logo_path,
            'logo_url' => $store->logo_path ? asset('storage/' . $store->logo_path) : null,
        ];

        return Inertia::render('Store/Settings', [
            'store' => $storeData,
            'user' => $user,
            'storeSettings' => $storeSettings,
        ]);
    }

    /**
     * Update basic store information.
     */
    public function updateBasic(Request $request)
    {
        $storeId = session('store_id');
        $store = Store::findOrFail($storeId);

        // Ensure the user owns this store
        if ($store->merchant_id !== $request->user()->merchant_id) {
            abort(403, 'Unauthorized access to store settings.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'subdomain' => ['nullable', 'string', 'max:63'],
            'is_active' => ['boolean'],
        ]);

        // Update all fields
        $store->update([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'subdomain' => $validated['subdomain'],
            'is_active' => $validated['is_active'] ?? false,
        ]);

        return redirect()->route('store.settings')
            ->with('success', 'Basic information updated successfully.');
    }

    /**
     * Update contact information.
     */
    public function updateContact(Request $request)
    {
        $storeId = session('store_id');
        $store = Store::findOrFail($storeId);

        // Ensure the user owns this store
        if ($store->merchant_id !== $request->user()->merchant_id) {
            abort(403, 'Unauthorized access to store settings.');
        }

        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postcode' => ['nullable', 'string', 'max:20'],
        ]);

        // Update contact information and address
        $store->update([
            'contact_email' => $validated['email'],
            'contact_phone' => $validated['phone'],
            'address_primary' => $validated['address'],
            'address_city' => $validated['city'],
            'address_state' => $validated['state'],
            'address_postcode' => $validated['postcode'],
        ]);

        return redirect()->route('store.settings')
            ->with('success', 'Contact information updated successfully.');
    }

    /**
     * Update business settings.
     */
    public function updateBusiness(Request $request)
    {
        $storeId = session('store_id');
        $store = Store::findOrFail($storeId);

        // Ensure the user owns this store
        if ($store->merchant_id !== $request->user()->merchant_id) {
            abort(403, 'Unauthorized access to store settings.');
        }

        $validated = $request->validate([
            'currency' => ['required', 'string', 'in:USD,EUR,GBP,AUD,CAD'],
            'timezone' => ['required', 'string'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'auto_fulfill' => ['boolean'],
        ]);

        // Update timezone which exists in the database
        $store->update([
            'timezone' => $validated['timezone'],
        ]);

        // TODO: Store other settings (currency, tax_rate, auto_fulfill) in a separate store_settings table or JSON column

        return redirect()->route('store.settings')
            ->with('success', 'Business settings updated successfully.');
    }

    /**
     * Update shipping settings.
     */
    public function updateShipping(Request $request)
    {
        $storeId = session('store_id');
        $store = Store::findOrFail($storeId);

        // Ensure the user owns this store
        if ($store->merchant_id !== $request->user()->merchant_id) {
            abort(403, 'Unauthorized access to store settings.');
        }

        $validated = $request->validate([
            'shipping_enabled' => ['required', 'boolean'],
        ]);

        $store->update([
            'shipping_enabled' => $validated['shipping_enabled'],
        ]);

        return redirect()->route('store.settings')
            ->with('success', 'Shipping settings updated successfully.');
    }

    /**
     * Update storefront theme.
     */
    public function updateTheme(Request $request)
    {
        $storeId = session('store_id');
        $store = Store::findOrFail($storeId);

        // Ensure the user owns this store
        if ($store->merchant_id !== $request->user()->merchant_id) {
            abort(403, 'Unauthorized access to store settings.');
        }

        $validated = $request->validate([
            'theme_key' => ['required', 'string', 'in:classic,modern,bold'],
        ]);

        $store->update([
            'theme_key' => $validated['theme_key'],
        ]);

        return redirect()->route('store.settings')
            ->with('success', 'Storefront theme updated successfully.');
    }

    /**
     * Update store logo.
     */
    public function updateLogo(Request $request)
    {
        $storeId = session('store_id');
        $store = Store::findOrFail($storeId);

        // Ensure the user owns this store
        if ($store->merchant_id !== $request->user()->merchant_id) {
            abort(403, 'Unauthorized access to store settings.');
        }

        $validated = $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'], // 5MB max
        ]);

        // Delete old logo if exists
        if ($store->logo_path) {
            Storage::disk('public')->delete($store->logo_path);
        }

        // Store new logo in /storage/app/public/stores
        $path = $request->file('logo')->store('stores', 'public');

        $store->update([
            'logo_path' => $path,
        ]);

        return redirect()->route('store.settings')
            ->with('success', 'Store logo updated successfully.');
    }

    /**
     * Remove store logo.
     */
    public function removeLogo(Request $request)
    {
        $storeId = session('store_id');
        $store = Store::findOrFail($storeId);

        // Ensure the user owns this store
        if ($store->merchant_id !== $request->user()->merchant_id) {
            abort(403, 'Unauthorized access to store settings.');
        }

        // Delete logo file
        if ($store->logo_path) {
            Storage::disk('public')->delete($store->logo_path);
        }

        $store->update([
            'logo_path' => null,
        ]);

        return redirect()->route('store.settings')
            ->with('success', 'Store logo removed successfully.');
    }
}
