<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoreSelectionController extends Controller
{
    /**
     * Display store selection page.
     */
    public function show(): Response
    {
        $user = auth()->user();

        $stores = $user->isOwner()
            ? $user->merchant->stores
            : $user->stores;

        return Inertia::render('Auth/StoreSelection', [
            'stores' => $stores,
        ]);
    }

    /**
     * Handle store selection.
     */
    public function store(Request $request)
    {
        $request->validate([
            'store_id' => ['required', 'integer', 'exists:stores,id'],
        ]);

        $user = auth()->user();
        $storeId = $request->input('store_id');

        // Verify user has access to this store
        $hasAccess = $user->isOwner()
            ? $user->merchant->stores()->where('stores.id', $storeId)->exists()
            : $user->stores()->where('stores.id', $storeId)->exists();

        if (!$hasAccess) {
            abort(403, 'You do not have access to this store.');
        }

        session(['store_id' => $storeId]);

        return redirect()->route('dashboard');
    }
}
