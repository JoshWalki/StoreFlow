<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $merchantId = $user->merchant_id;
        $storeId = session('store_id');
        $store = \App\Models\Store::find($storeId);

        $query = Product::where('merchant_id', $merchantId)
            ->with(['category', 'images']);

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $products = $query->orderBy('created_at', 'desc')
            ->paginate(15)
            ->through(function ($product) {
                $primaryImage = $product->images->where('is_primary', true)->first();
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price_cents' => $product->price_cents,
                    'category_id' => $product->category_id,
                    'category' => $product->category,
                    'store_id' => $product->store_id,
                    'is_active' => $product->is_active,
                    'is_shippable' => $product->is_shippable,
                    'image' => $primaryImage ? '/storage/' . $primaryImage->image_path : null,
                    'images_count' => $product->images->count(),
                ];
            });

        $categories = Category::where('merchant_id', $merchantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Products/Index', [
            'store' => $store,
            'user' => $user,
            'products' => $products,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category_id', 'is_active']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $user = $request->user();

        // Only managers and owners can create products
        if ($user->role === 'staff') {
            abort(403, 'Staff members cannot create products.');
        }

        $merchantId = $user->merchant_id;
        $storeId = session('store_id');
        $store = \App\Models\Store::find($storeId);

        $categories = Category::where('merchant_id', $merchantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $stores = \App\Models\Store::where('merchant_id', $merchantId)
            ->orderBy('name')
            ->get();

        return Inertia::render('Products/Create', [
            'store' => $store,
            'user' => $user,
            'categories' => $categories,
            'stores' => $stores,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Only managers and owners can create products
        if ($request->user()->role === 'staff') {
            abort(403, 'Staff members cannot create products.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'store_id' => ['nullable', 'exists:stores,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'is_shippable' => ['boolean'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'length_cm' => ['nullable', 'integer', 'min:0'],
            'width_cm' => ['nullable', 'integer', 'min:0'],
            'height_cm' => ['nullable', 'integer', 'min:0'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
        ]);

        // Convert price to cents
        $validated['price_cents'] = (int) ($validated['price'] * 100);
        unset($validated['price']);

        // Remove images from validated data (handled separately)
        $images = $request->file('images');
        unset($validated['images']);

        $validated['merchant_id'] = $request->user()->merchant_id;

        $product = Product::create($validated);

        // Handle image uploads
        if ($images) {
            foreach ($images as $index => $image) {
                $path = $image->store('products', 'public');

                \App\Models\ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'sort_order' => $index,
                    'is_primary' => $index === 0,
                ]);
            }
        }

        // Log product creation
        AuditLogger::logCreated('Product', $product->id, [
            'name' => $product->name,
            'price_cents' => $product->price_cents,
            'category_id' => $product->category_id,
            'is_active' => $product->is_active,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Product $product): Response
    {
        $user = $request->user();

        // Only managers and owners can edit products
        if ($user->role === 'staff') {
            abort(403, 'Staff members cannot edit products.');
        }

        $merchantId = $user->merchant_id;
        $storeId = session('store_id');
        $store = \App\Models\Store::find($storeId);

        // Ensure product belongs to user's merchant
        if ($product->merchant_id !== $merchantId) {
            abort(403);
        }

        $categories = Category::where('merchant_id', $merchantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $stores = \App\Models\Store::where('merchant_id', $merchantId)
            ->orderBy('name')
            ->get();

        $product->load(['category', 'customizationGroups.options', 'images']);

        return Inertia::render('Products/Edit', [
            'store' => $store,
            'user' => $user,
            'product' => $product,
            'categories' => $categories,
            'stores' => $stores,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        // Only managers and owners can update products
        if ($request->user()->role === 'staff') {
            abort(403, 'Staff members cannot update products.');
        }

        // Ensure product belongs to user's merchant
        if ($product->merchant_id !== $request->user()->merchant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'store_id' => ['nullable', 'exists:stores,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'is_shippable' => ['boolean'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'length_cm' => ['nullable', 'integer', 'min:0'],
            'width_cm' => ['nullable', 'integer', 'min:0'],
            'height_cm' => ['nullable', 'integer', 'min:0'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['image', 'mimes:jpeg,jpg,png,gif,webp', 'max:2048'],
            'delete_images' => ['nullable', 'array'],
            'delete_images.*' => ['integer', 'exists:product_images,id'],
        ]);

        // Convert price to cents
        $validated['price_cents'] = (int) ($validated['price'] * 100);
        unset($validated['price']);

        // Remove images and delete_images from validated data (handled separately)
        $images = $request->file('images');
        $deleteImages = $request->input('delete_images', []);
        unset($validated['images'], $validated['delete_images']);

        // Track changes for audit log
        $changes = [];
        foreach ($validated as $key => $newValue) {
            $oldValue = $product->getOriginal($key);
            if ($oldValue != $newValue) {
                $changes[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        $product->update($validated);

        // Handle image deletions
        if (!empty($deleteImages)) {
            $imagesToDelete = \App\Models\ProductImage::whereIn('id', $deleteImages)
                ->where('product_id', $product->id)
                ->get();

            foreach ($imagesToDelete as $image) {
                \Storage::disk('public')->delete($image->image_path);
                $image->delete();
            }
        }

        // Handle new image uploads
        if ($images) {
            $currentMaxOrder = $product->images()->max('sort_order') ?? -1;

            foreach ($images as $index => $image) {
                $path = $image->store('products', 'public');

                \App\Models\ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'sort_order' => $currentMaxOrder + $index + 1,
                    'is_primary' => $product->images()->count() === 0 && $index === 0,
                ]);
            }
        }

        // Log product update if there were changes
        if (!empty($changes)) {
            AuditLogger::logUpdated('Product', $product->id, $changes);
        }

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product)
    {
        // Only managers and owners can delete products
        if ($request->user()->role === 'staff') {
            abort(403, 'Staff members cannot delete products.');
        }

        // Ensure product belongs to user's merchant
        if ($product->merchant_id !== $request->user()->merchant_id) {
            abort(403);
        }

        // Log product deletion before deleting
        AuditLogger::logDeleted('Product', $product->id, [
            'name' => $product->name,
            'price_cents' => $product->price_cents,
            'category_id' => $product->category_id,
        ]);

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
