<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShippingQuoteRequest;
use App\Models\Product;
use App\Models\Store;
use App\Services\Shipping\ShippingEngine;
use Illuminate\Http\JsonResponse;

class ShippingQuoteController extends Controller
{
    protected ShippingEngine $shippingEngine;

    public function __construct(ShippingEngine $shippingEngine)
    {
        $this->shippingEngine = $shippingEngine;
    }

    /**
     * Calculate shipping quotes for the given items and destination.
     *
     * @param ShippingQuoteRequest $request
     * @param Store $store
     * @return JsonResponse
     */
    public function calculate(ShippingQuoteRequest $request, Store $store): JsonResponse
    {
        $validated = $request->validated();

        // Check if shipping is enabled for this store
        if (!$store->shipping_enabled) {
            return response()->json([
                'message' => 'Shipping is not enabled for this store.',
            ], 422);
        }

        // Get product details for cart items
        $productIds = collect($validated['items'])->pluck('product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Build cart items array for ShippingEngine
        $cartItems = collect($validated['items'])->map(function ($item) use ($products) {
            $product = $products->get($item['product_id']);
            if (!$product) {
                return null;
            }

            return [
                'product_id' => $product->id,
                'quantity' => $item['qty'],
                'price_cents' => $product->price_cents,
                'weight_grams' => $product->weight_grams ?? 0,
            ];
        })->filter()->values()->all();

        // Calculate shipping quotes
        $quotes = $this->shippingEngine->calculateShippingOptions(
            $store->merchant_id,
            $store->id,
            $cartItems,
            $validated['country'],
            $validated['state'] ?? null,
            $validated['postcode'] ?? null
        );

        if ($quotes->isEmpty()) {
            return response()->json([
                'message' => 'No shipping options available for this destination.',
                'shipping_options' => [],
            ], 200);
        }

        // Format response to match A1.6 specification
        $shippingOptions = $quotes->map(function ($quote) {
            return [
                'id' => $quote['shipping_method_id'],
                'name' => $quote['shipping_method_name'],
                'description' => $quote['description'],
                'carrier' => $quote['carrier'],
                'service_code' => $quote['service_code'],
                'price_cents' => $quote['cost_cents'],
                'delivery_estimate' => $quote['delivery_estimate'],
            ];
        });

        return response()->json([
            'shipping_options' => $shippingOptions->values()->all(),
        ], 200);
    }
}
