<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class SaleController extends Controller
{
    /**
     * Get all sales for the current store.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $storeId = session('store_id');
        $store = Store::findOrFail($storeId);

        // Ensure the user owns this store
        if ($store->merchant_id !== $user->merchant_id) {
            abort(403, 'Unauthorized access to sales.');
        }

        // Get all sales with product counts and products
        $sales = Sale::where('store_id', $storeId)
            ->withCount('products')
            ->with(['bogoProduct:id,name', 'products:id,name,price_cents'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'name' => $sale->name,
                    'type' => $sale->type,
                    'discount_value' => $sale->discount_value,
                    'discount_display' => $sale->discount_display,
                    'bogo_product_id' => $sale->bogo_product_id,
                    'bogo_product' => $sale->bogoProduct,
                    'is_active' => $sale->is_active,
                    'starts_at' => $sale->starts_at?->toISOString(),
                    'ends_at' => $sale->ends_at?->toISOString(),
                    'products_count' => $sale->products_count,
                    'products' => $sale->products->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'price' => '$' . number_format($product->price_cents / 100, 2),
                        ];
                    }),
                    'created_at' => $sale->created_at->toISOString(),
                ];
            });

        return response()->json([
            'sales' => $sales,
        ]);
    }

    /**
     * Store a new sale.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $storeId = session('store_id');
        $store = Store::findOrFail($storeId);

        // Ensure the user owns this store and has owner role
        if ($store->merchant_id !== $user->merchant_id || $user->role !== 'owner') {
            abort(403, 'Only store owners can create sales.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['price_discount', 'percent_discount', 'bogo_same', 'bogo_different'])],
            'discount_value' => ['nullable', 'integer', 'min:1'],
            'bogo_product_id' => ['nullable', 'exists:products,id'],
            'product_ids' => ['required', 'array', 'min:1'],
            'product_ids.*' => ['exists:products,id'],
            'is_active' => ['boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ]);

        // Validate discount_value based on type
        if (in_array($validated['type'], ['price_discount', 'percent_discount'])) {
            if (empty($validated['discount_value'])) {
                return response()->json([
                    'message' => 'Discount value is required for this sale type.',
                    'errors' => ['discount_value' => ['Discount value is required.']],
                ], 422);
            }

            if ($validated['type'] === 'percent_discount' && $validated['discount_value'] > 100) {
                return response()->json([
                    'message' => 'Percentage discount cannot exceed 100%.',
                    'errors' => ['discount_value' => ['Percentage cannot exceed 100.']],
                ], 422);
            }
        }

        // Validate BOGO product
        if ($validated['type'] === 'bogo_different' && empty($validated['bogo_product_id'])) {
            return response()->json([
                'message' => 'BOGO product is required for this sale type.',
                'errors' => ['bogo_product_id' => ['BOGO product is required.']],
            ], 422);
        }

        // Check if any selected products are already in other sales
        $productsInOtherSales = DB::table('product_sale')
            ->whereIn('product_id', $validated['product_ids'])
            ->pluck('product_id')
            ->toArray();

        if (!empty($productsInOtherSales)) {
            $products = Product::whereIn('id', $productsInOtherSales)->pluck('name')->toArray();
            return response()->json([
                'message' => 'Some products are already assigned to other sales.',
                'errors' => ['product_ids' => ['The following products are already in sales: ' . implode(', ', $products)]],
            ], 422);
        }

        // Create the sale
        $sale = Sale::create([
            'merchant_id' => $user->merchant_id,
            'store_id' => $storeId,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'discount_value' => $validated['discount_value'] ?? null,
            'bogo_product_id' => $validated['bogo_product_id'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
        ]);

        // Attach products to the sale
        $sale->products()->attach($validated['product_ids']);

        return response()->json([
            'message' => 'Sale created successfully.',
            'sale' => $sale->load('products'),
        ], 201);
    }

    /**
     * Update an existing sale.
     */
    public function update(Request $request, Sale $sale)
    {
        $user = $request->user();
        $storeId = session('store_id');
        $store = Store::findOrFail($storeId);

        // Ensure the user owns this store and has owner role
        if ($store->merchant_id !== $user->merchant_id || $user->role !== 'owner') {
            abort(403, 'Only store owners can update sales.');
        }

        // Ensure the sale belongs to this store
        if ($sale->store_id !== $storeId) {
            abort(403, 'Unauthorized access to this sale.');
        }

        $validated = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', Rule::in(['price_discount', 'percent_discount', 'bogo_same', 'bogo_different'])],
            'discount_value' => ['nullable', 'integer', 'min:1'],
            'bogo_product_id' => ['nullable', 'exists:products,id'],
            'product_ids' => ['sometimes', 'array', 'min:1'],
            'product_ids.*' => ['exists:products,id'],
            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
        ]);

        // Validate discount_value based on type
        $type = $validated['type'] ?? $sale->type;
        if (in_array($type, ['price_discount', 'percent_discount'])) {
            $discountValue = $validated['discount_value'] ?? $sale->discount_value;
            if (empty($discountValue)) {
                return response()->json([
                    'message' => 'Discount value is required for this sale type.',
                    'errors' => ['discount_value' => ['Discount value is required.']],
                ], 422);
            }

            if ($type === 'percent_discount' && $discountValue > 100) {
                return response()->json([
                    'message' => 'Percentage discount cannot exceed 100%.',
                    'errors' => ['discount_value' => ['Percentage cannot exceed 100.']],
                ], 422);
            }
        }

        // If updating products, check conflicts
        if (isset($validated['product_ids'])) {
            $productsInOtherSales = DB::table('product_sale')
                ->whereIn('product_id', $validated['product_ids'])
                ->where('sale_id', '!=', $sale->id)
                ->pluck('product_id')
                ->toArray();

            if (!empty($productsInOtherSales)) {
                $products = Product::whereIn('id', $productsInOtherSales)->pluck('name')->toArray();
                return response()->json([
                    'message' => 'Some products are already assigned to other sales.',
                    'errors' => ['product_ids' => ['The following products are already in sales: ' . implode(', ', $products)]],
                ], 422);
            }

            // Sync products
            $sale->products()->sync($validated['product_ids']);
        }

        // Update the sale
        $sale->update(array_filter($validated, fn($key) => $key !== 'product_ids', ARRAY_FILTER_USE_KEY));

        return response()->json([
            'message' => 'Sale updated successfully.',
            'sale' => $sale->fresh()->load('products'),
        ]);
    }

    /**
     * Delete a sale.
     */
    public function destroy(Request $request, Sale $sale)
    {
        $user = $request->user();
        $storeId = session('store_id');
        $store = Store::findOrFail($storeId);

        // Ensure the user owns this store and has owner role
        if ($store->merchant_id !== $user->merchant_id || $user->role !== 'owner') {
            abort(403, 'Only store owners can delete sales.');
        }

        // Ensure the sale belongs to this store
        if ($sale->store_id !== $storeId) {
            abort(403, 'Unauthorized access to this sale.');
        }

        $sale->delete();

        return response()->json([
            'message' => 'Sale deleted successfully.',
        ]);
    }

    /**
     * Get available products (not assigned to any sale).
     */
    public function availableProducts(Request $request)
    {
        $storeId = session('store_id');

        $excludeSaleId = $request->query('exclude_sale_id');

        $query = Product::where('store_id', $storeId)
            ->where('is_active', true)
            ->whereNotIn('id', function ($query) use ($excludeSaleId) {
                $query->select('product_id')
                    ->from('product_sale');
                if ($excludeSaleId) {
                    $query->where('sale_id', '!=', $excludeSaleId);
                }
            })
            ->select('id', 'name', 'price_cents');

        $products = $query->get()->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'price' => '$' . number_format($product->price_cents / 100, 2),
            ];
        });

        return response()->json([
            'products' => $products,
        ]);
    }
}
