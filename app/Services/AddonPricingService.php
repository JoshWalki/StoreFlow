<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductAddon;
use App\Models\OrderItem;
use App\Models\OrderItemAddon;
use Illuminate\Support\Collection;

class AddonPricingService
{
    /**
     * Calculate the total price for a cart item including addons.
     *
     * @param int $productId
     * @param int $quantity
     * @param array $addonSelections [['addon_id' => int, 'quantity' => int], ...]
     * @return array ['base_price_cents' => int, 'addons_price_cents' => int, 'total_cents' => int, 'addons_breakdown' => array]
     */
    public function calculateCartItemPrice(int $productId, int $quantity, array $addonSelections = []): array
    {
        $product = Product::findOrFail($productId);
        $basePriceCents = $product->price_cents * $quantity;
        $addonsPriceCents = 0;
        $addonsBreakdown = [];

        if (!empty($addonSelections)) {
            $addonIds = collect($addonSelections)->pluck('addon_id')->unique()->toArray();
            $addons = ProductAddon::whereIn('id', $addonIds)
                ->where('is_active', true)
                ->get()
                ->keyBy('id');

            foreach ($addonSelections as $selection) {
                $addonId = $selection['addon_id'];
                $addonQty = $selection['quantity'] ?? 1;

                if ($addons->has($addonId)) {
                    $addon = $addons->get($addonId);
                    $addonTotalCents = $addon->price_cents * $addonQty * $quantity;
                    $addonsPriceCents += $addonTotalCents;

                    $addonsBreakdown[] = [
                        'addon_id' => $addon->id,
                        'name' => $addon->name,
                        'quantity' => $addonQty,
                        'unit_price_cents' => $addon->price_cents,
                        'total_price_cents' => $addonTotalCents,
                    ];
                }
            }
        }

        return [
            'base_price_cents' => $basePriceCents,
            'addons_price_cents' => $addonsPriceCents,
            'total_cents' => $basePriceCents + $addonsPriceCents,
            'addons_breakdown' => $addonsBreakdown,
        ];
    }

    /**
     * Calculate total for multiple cart items.
     *
     * @param array $cartItems [['product_id' => int, 'quantity' => int, 'addons' => array], ...]
     * @return array ['items_total_cents' => int, 'addons_total_cents' => int, 'cart_total_cents' => int, 'items' => array]
     */
    public function calculateCartTotal(array $cartItems): array
    {
        $itemsTotalCents = 0;
        $addonsTotalCents = 0;
        $itemsBreakdown = [];

        foreach ($cartItems as $item) {
            $pricing = $this->calculateCartItemPrice(
                $item['product_id'],
                $item['quantity'],
                $item['addons'] ?? []
            );

            $itemsTotalCents += $pricing['base_price_cents'];
            $addonsTotalCents += $pricing['addons_price_cents'];

            $itemsBreakdown[] = [
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'base_price_cents' => $pricing['base_price_cents'],
                'addons_price_cents' => $pricing['addons_price_cents'],
                'total_cents' => $pricing['total_cents'],
                'addons' => $pricing['addons_breakdown'],
            ];
        }

        return [
            'items_total_cents' => $itemsTotalCents,
            'addons_total_cents' => $addonsTotalCents,
            'cart_total_cents' => $itemsTotalCents + $addonsTotalCents,
            'items' => $itemsBreakdown,
        ];
    }

    /**
     * Store addon selections for an order item.
     *
     * @param OrderItem $orderItem
     * @param array $addonSelections [['addon_id' => int, 'quantity' => int], ...]
     * @return Collection
     */
    public function storeOrderItemAddons(OrderItem $orderItem, array $addonSelections): Collection
    {
        $savedAddons = collect();

        if (empty($addonSelections)) {
            return $savedAddons;
        }

        $addonIds = collect($addonSelections)->pluck('addon_id')->unique()->toArray();
        $addons = ProductAddon::whereIn('id', $addonIds)->get()->keyBy('id');

        foreach ($addonSelections as $selection) {
            $addonId = $selection['addon_id'];
            $quantity = $selection['quantity'] ?? 1;

            if ($addons->has($addonId)) {
                $addon = $addons->get($addonId);

                // Create frozen snapshot of addon
                $orderItemAddon = OrderItemAddon::create([
                    'order_item_id' => $orderItem->id,
                    'product_addon_id' => $addon->id,
                    'name' => $addon->name,
                    'description' => $addon->description,
                    'quantity' => $quantity,
                    'unit_price_cents' => $addon->price_cents,
                    'total_price_cents' => $addon->price_cents * $quantity,
                ]);

                $savedAddons->push($orderItemAddon);
            }
        }

        return $savedAddons;
    }

    /**
     * Validate addon selections for a product.
     *
     * @param int $productId
     * @param array $addonSelections
     * @return array ['valid' => bool, 'errors' => array]
     */
    public function validateAddonSelections(int $productId, array $addonSelections): array
    {
        $errors = [];

        if (empty($addonSelections)) {
            return ['valid' => true, 'errors' => []];
        }

        $product = Product::with('activeAddons')->findOrFail($productId);
        $availableAddonIds = $product->activeAddons->pluck('id')->toArray();

        $addonIds = collect($addonSelections)->pluck('addon_id')->unique()->toArray();

        // Check if all selected addons are available for this product
        foreach ($addonIds as $addonId) {
            if (!in_array($addonId, $availableAddonIds)) {
                $errors[] = "Addon ID {$addonId} is not available for this product.";
            }
        }

        // Validate quantities
        foreach ($addonSelections as $selection) {
            $quantity = $selection['quantity'] ?? 1;
            if ($quantity < 1) {
                $errors[] = "Addon quantity must be at least 1.";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get formatted price display.
     *
     * @param int $priceCents
     * @return string
     */
    public function formatPrice(int $priceCents): string
    {
        return '$' . number_format($priceCents / 100, 2);
    }

    /**
     * Calculate addons subtotal for an order item.
     *
     * @param OrderItem $orderItem
     * @return int
     */
    public function calculateOrderItemAddonsTotal(OrderItem $orderItem): int
    {
        return $orderItem->addons()->sum('total_price_cents');
    }
}
