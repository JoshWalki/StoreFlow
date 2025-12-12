<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\Store;
use App\Services\AddonPricingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductAddonController extends Controller
{
    protected AddonPricingService $pricingService;

    public function __construct(AddonPricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Get all active addons for a specific product.
     *
     * @param Store $store
     * @param Product $product
     * @return JsonResponse
     */
    public function getProductAddons(Store $store, Product $product): JsonResponse
    {
        // Ensure product belongs to this store
        if ($product->store_id !== $store->id) {
            return response()->json([
                'error' => 'Product not found in this store'
            ], 404);
        }

        $addons = $product->activeAddons()
            ->orderBy('product_product_addon.sort_order')
            ->orderBy('product_addons.name')
            ->get()
            ->map(function ($addon) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'description' => $addon->description,
                    'price_cents' => $addon->price_cents,
                    'price' => $addon->price,
                    'formatted_price' => $addon->formatted_price,
                    'sort_order' => $addon->pivot->sort_order,
                ];
            });

        return response()->json([
            'data' => $addons
        ]);
    }

    /**
     * Get all active addons for a store.
     *
     * @param Store $store
     * @return JsonResponse
     */
    public function getStoreAddons(Store $store): JsonResponse
    {
        $addons = ProductAddon::forStore($store->id)
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function ($addon) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'description' => $addon->description,
                    'price_cents' => $addon->price_cents,
                    'price' => $addon->price,
                    'formatted_price' => $addon->formatted_price,
                ];
            });

        return response()->json([
            'data' => $addons
        ]);
    }

    /**
     * Calculate price for cart item with addons.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculatePrice(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'addons' => 'nullable|array',
            'addons.*.addon_id' => 'required|exists:product_addons,id',
            'addons.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            // Validate addon selections
            $validation = $this->pricingService->validateAddonSelections(
                $validated['product_id'],
                $validated['addons'] ?? []
            );

            if (!$validation['valid']) {
                return response()->json([
                    'error' => 'Invalid addon selections',
                    'validation_errors' => $validation['errors']
                ], 422);
            }

            // Calculate pricing
            $pricing = $this->pricingService->calculateCartItemPrice(
                $validated['product_id'],
                $validated['quantity'],
                $validated['addons'] ?? []
            );

            return response()->json([
                'data' => [
                    'base_price_cents' => $pricing['base_price_cents'],
                    'base_price' => $pricing['base_price_cents'] / 100,
                    'addons_price_cents' => $pricing['addons_price_cents'],
                    'addons_price' => $pricing['addons_price_cents'] / 100,
                    'total_cents' => $pricing['total_cents'],
                    'total' => $pricing['total_cents'] / 100,
                    'formatted_base_price' => $this->pricingService->formatPrice($pricing['base_price_cents']),
                    'formatted_addons_price' => $this->pricingService->formatPrice($pricing['addons_price_cents']),
                    'formatted_total' => $this->pricingService->formatPrice($pricing['total_cents']),
                    'addons_breakdown' => $pricing['addons_breakdown'],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to calculate price',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate total for entire cart with addons.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function calculateCartTotal(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.addons' => 'nullable|array',
            'items.*.addons.*.addon_id' => 'required|exists:product_addons,id',
            'items.*.addons.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            $cartTotal = $this->pricingService->calculateCartTotal($validated['items']);

            return response()->json([
                'data' => [
                    'items_total_cents' => $cartTotal['items_total_cents'],
                    'items_total' => $cartTotal['items_total_cents'] / 100,
                    'addons_total_cents' => $cartTotal['addons_total_cents'],
                    'addons_total' => $cartTotal['addons_total_cents'] / 100,
                    'cart_total_cents' => $cartTotal['cart_total_cents'],
                    'cart_total' => $cartTotal['cart_total_cents'] / 100,
                    'formatted_items_total' => $this->pricingService->formatPrice($cartTotal['items_total_cents']),
                    'formatted_addons_total' => $this->pricingService->formatPrice($cartTotal['addons_total_cents']),
                    'formatted_cart_total' => $this->pricingService->formatPrice($cartTotal['cart_total_cents']),
                    'items' => $cartTotal['items'],
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to calculate cart total',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
