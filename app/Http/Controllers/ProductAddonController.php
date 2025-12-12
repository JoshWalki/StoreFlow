<?php

namespace App\Http\Controllers;

use App\Models\ProductAddon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductAddonController extends Controller
{
    /**
     * Display a listing of product addons.
     */
    public function index(Request $request)
    {
        $query = ProductAddon::query()
            ->forMerchant(auth()->user()->merchant_id)
            ->with(['store']);

        // Filter by store if provided
        if ($request->has('store_id')) {
            $query->forStore($request->store_id);
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $addons = $query->orderBy('sort_order')->orderBy('name')->paginate(20);

        return Inertia::render('ProductAddons/Index', [
            'addons' => $addons,
            'filters' => $request->only(['store_id', 'is_active', 'search']),
        ]);
    }

    /**
     * Show the form for creating a new addon.
     */
    public function create()
    {
        return Inertia::render('ProductAddons/Create');
    }

    /**
     * Store a newly created addon.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $addon = ProductAddon::create([
            'merchant_id' => auth()->user()->merchant_id,
            'store_id' => auth()->user()->currentStore->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price_cents' => (int) ($validated['price'] * 100),
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->route('product-addons.index')
            ->with('success', 'Product addon created successfully.');
    }

    /**
     * Display the specified addon.
     */
    public function show(ProductAddon $productAddon)
    {
        $this->authorize('view', $productAddon);

        $productAddon->load(['store', 'products']);

        return Inertia::render('ProductAddons/Show', [
            'addon' => $productAddon,
        ]);
    }

    /**
     * Show the form for editing the specified addon.
     */
    public function edit(ProductAddon $productAddon)
    {
        $this->authorize('update', $productAddon);

        return Inertia::render('ProductAddons/Edit', [
            'addon' => $productAddon,
        ]);
    }

    /**
     * Update the specified addon.
     */
    public function update(Request $request, ProductAddon $productAddon)
    {
        $this->authorize('update', $productAddon);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $productAddon->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'price_cents' => (int) ($validated['price'] * 100),
            'is_active' => $validated['is_active'] ?? $productAddon->is_active,
            'sort_order' => $validated['sort_order'] ?? $productAddon->sort_order,
        ]);

        return redirect()->route('product-addons.index')
            ->with('success', 'Product addon updated successfully.');
    }

    /**
     * Remove the specified addon.
     */
    public function destroy(ProductAddon $productAddon)
    {
        $this->authorize('delete', $productAddon);

        $productAddon->delete();

        return redirect()->route('product-addons.index')
            ->with('success', 'Product addon deleted successfully.');
    }

    /**
     * Attach addon to a product.
     */
    public function attachToProduct(Request $request, ProductAddon $productAddon)
    {
        $this->authorize('update', $productAddon);

        $validated = $request->validate([
            'product_id' => [
                'required',
                'exists:products,id',
                Rule::exists('products', 'id')->where(function ($query) use ($productAddon) {
                    $query->where('merchant_id', $productAddon->merchant_id);
                }),
            ],
            'sort_order' => 'integer|min:0',
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Attach if not already attached
        if (!$product->addons()->where('product_addon_id', $productAddon->id)->exists()) {
            $product->addons()->attach($productAddon->id, [
                'sort_order' => $validated['sort_order'] ?? 0,
            ]);
        }

        return back()->with('success', 'Addon attached to product successfully.');
    }

    /**
     * Detach addon from a product.
     */
    public function detachFromProduct(Request $request, ProductAddon $productAddon)
    {
        $this->authorize('update', $productAddon);

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $product->addons()->detach($productAddon->id);

        return back()->with('success', 'Addon detached from product successfully.');
    }

    /**
     * Bulk update addon sort order.
     */
    public function updateSortOrder(Request $request)
    {
        $validated = $request->validate([
            'addons' => 'required|array',
            'addons.*.id' => 'required|exists:product_addons,id',
            'addons.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['addons'] as $addonData) {
                ProductAddon::where('id', $addonData['id'])
                    ->where('merchant_id', auth()->user()->merchant_id)
                    ->update(['sort_order' => $addonData['sort_order']]);
            }
        });

        return back()->with('success', 'Sort order updated successfully.');
    }
}
