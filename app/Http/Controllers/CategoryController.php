<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CategoryController extends Controller
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

        $query = Category::where('merchant_id', $merchantId)
            ->withCount('products');

        // Search
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $categories = $query->orderBy('sort_order')->orderBy('name')->paginate(15);

        return Inertia::render('Categories/Index', [
            'store' => $store,
            'user' => $user,
            'categories' => $categories,
            'filters' => $request->only(['search', 'is_active']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        $user = $request->user();
        $storeId = session('store_id');
        $store = \App\Models\Store::find($storeId);

        // Load all products for this merchant/store to allow assignment
        $products = \App\Models\Product::where('merchant_id', $user->merchant_id)
            ->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)->orWhereNull('store_id');
            })
            ->with(['category:id,name', 'images' => function ($query) {
                $query->where('is_primary', true);
            }])
            ->select('id', 'name', 'category_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('Categories/Create', [
            'store' => $store,
            'user' => $user,
            'products' => $products,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $validated['merchant_id'] = $request->user()->merchant_id;

        $category = Category::create($validated);

        // Assign selected products to this category
        if (!empty($validated['product_ids'])) {
            \App\Models\Product::whereIn('id', $validated['product_ids'])
                ->where('merchant_id', $request->user()->merchant_id)
                ->update(['category_id' => $category->id]);
        }

        return redirect()->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Category $category): Response
    {
        $user = $request->user();
        $storeId = session('store_id');
        $store = \App\Models\Store::find($storeId);

        // Ensure category belongs to user's merchant
        if ($category->merchant_id !== $user->merchant_id) {
            abort(403);
        }

        $category->load('products:id,name,category_id');

        // Load all products for this merchant/store to allow reassignment
        $allProducts = \App\Models\Product::where('merchant_id', $user->merchant_id)
            ->where(function ($q) use ($storeId) {
                $q->where('store_id', $storeId)->orWhereNull('store_id');
            })
            ->with(['category:id,name', 'images' => function ($query) {
                $query->where('is_primary', true);
            }])
            ->select('id', 'name', 'category_id')
            ->orderBy('name')
            ->get();

        return Inertia::render('Categories/Edit', [
            'store' => $store,
            'user' => $user,
            'category' => $category,
            'products' => $allProducts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        // Ensure category belongs to user's merchant
        if ($category->merchant_id !== $request->user()->merchant_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
            'product_ids' => ['nullable', 'array'],
            'product_ids.*' => ['integer', 'exists:products,id'],
        ]);

        $category->update($validated);

        // Update product assignments
        // First, remove this category from products that are no longer selected
        \App\Models\Product::where('category_id', $category->id)
            ->where('merchant_id', $request->user()->merchant_id)
            ->whereNotIn('id', $validated['product_ids'] ?? [])
            ->update(['category_id' => null]);

        // Then, assign selected products to this category
        if (!empty($validated['product_ids'])) {
            \App\Models\Product::whereIn('id', $validated['product_ids'])
                ->where('merchant_id', $request->user()->merchant_id)
                ->update(['category_id' => $category->id]);
        }

        return redirect()->route('categories.index')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Category $category)
    {
        // Ensure category belongs to user's merchant
        if ($category->merchant_id !== $request->user()->merchant_id) {
            abort(403);
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Category deleted successfully.');
    }

    /**
     * Reorder categories.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['required', 'integer', 'exists:categories,id'],
        ]);

        $merchantId = $request->user()->merchant_id;
        $categoryIds = $validated['category_ids'];

        // Verify all categories belong to the merchant
        $categories = Category::whereIn('id', $categoryIds)
            ->where('merchant_id', $merchantId)
            ->get();

        if ($categories->count() !== count($categoryIds)) {
            return back()->with('error', 'Some categories could not be found or do not belong to your merchant.');
        }

        // Update sort_order for each category based on position in array
        foreach ($categoryIds as $index => $categoryId) {
            Category::where('id', $categoryId)
                ->where('merchant_id', $merchantId)
                ->update(['sort_order' => $index]);
        }

        return back()->with('success', 'Categories reordered successfully.');
    }
}
