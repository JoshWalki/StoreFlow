<?php

namespace App\Services\Products;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Service for managing product display logic on the storefront
 * Handles frequent products calculation and category-based grouping
 */
class ProductDisplayService
{
    /**
     * Get the most frequently purchased products for a store
     * Based on quantity sold over the last 90 days
     *
     * @param int $merchantId
     * @param int $storeId
     * @param int $limit Number of products to return (default 4)
     * @param int $daysBack How far back to look for orders (default 90)
     * @return Collection<Product>
     */
    public function getFrequentProducts(
        int $merchantId,
        int $storeId,
        int $limit = 4,
        int $daysBack = 90
    ): Collection {
        $cacheKey = "storefront:frequent:{$merchantId}:{$storeId}";

        return Cache::remember($cacheKey, 3600, function () use ($merchantId, $storeId, $limit, $daysBack) {
            // Get product IDs ordered by quantity sold
            $productIds = DB::table('order_items')
                ->select('product_id', DB::raw('SUM(quantity) as total_quantity'))
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('orders.merchant_id', $merchantId)
                ->where('orders.store_id', $storeId)
                ->where('orders.payment_status', Order::PAYMENT_PAID)
                ->where('orders.created_at', '>=', now()->subDays($daysBack))
                ->where('products.is_active', true)
                ->whereIn('orders.status', [
                    Order::STATUS_DELIVERED,
                    Order::STATUS_PICKED_UP
                ])
                ->groupBy('product_id')
                ->orderByDesc('total_quantity')
                ->limit($limit)
                ->pluck('product_id');

            // Fallback: If insufficient products, add recent products
            if ($productIds->count() < $limit) {
                $fallbackIds = Product::where('merchant_id', $merchantId)
                    ->where(function ($q) use ($storeId) {
                        $q->where('store_id', $storeId)->orWhereNull('store_id');
                    })
                    ->where('is_active', true)
                    ->whereNotIn('id', $productIds)
                    ->orderBy('created_at', 'desc')
                    ->limit($limit - $productIds->count())
                    ->pluck('id');

                $productIds = $productIds->merge($fallbackIds);
            }

            // Fetch full product models with relationships
            return Product::with(['category', 'images'])
                ->whereIn('id', $productIds)
                ->get()
                ->sortBy(function ($product) use ($productIds) {
                    return $productIds->search($product->id);
                })
                ->values();
        });
    }

    /**
     * Get products grouped by category
     *
     * @param int $merchantId
     * @param int $storeId
     * @param int|null $productsPerCategory Limit products per category (null = all)
     * @return Collection<Category>
     */
    public function getProductsByCategory(
        int $merchantId,
        int $storeId,
        ?int $productsPerCategory = null
    ): Collection {
        $cacheKey = "storefront:categories:{$merchantId}:{$storeId}:" . ($productsPerCategory ?? 'all');

        return Cache::remember($cacheKey, 21600, function () use ($merchantId, $storeId, $productsPerCategory) {
            $query = Category::where('merchant_id', $merchantId)
                ->where('is_active', true)
                ->with(['products' => function ($query) use ($storeId, $productsPerCategory) {
                    $query->where(function ($q) use ($storeId) {
                        $q->where('store_id', $storeId)
                          ->orWhereNull('store_id');
                    })
                    ->where('is_active', true)
                    ->with('images')
                    ->orderBy('created_at', 'desc');

                    if ($productsPerCategory) {
                        $query->limit($productsPerCategory);
                    }
                }])
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get()
                ->filter(fn($category) => $category->products->isNotEmpty());

            return $query;
        });
    }

    /**
     * Get complete storefront data (frequent products + categorized products)
     *
     * @param \App\Models\Store $store
     * @return array
     */
    public function getStorefrontData($store): array
    {
        $frequentProducts = $this->getFrequentProducts(
            $store->merchant_id,
            $store->id,
            4
        );

        $categorizedProducts = $this->getProductsByCategory(
            $store->merchant_id,
            $store->id,
            12  // Show up to 12 products per category
        );

        return [
            'frequent_products' => $this->formatProductsForFrontend($frequentProducts),
            'categories' => $this->formatCategoriesForFrontend($categorizedProducts),
        ];
    }

    /**
     * Format products for frontend consumption
     *
     * @param Collection $products
     * @return Collection
     */
    private function formatProductsForFrontend(Collection $products): Collection
    {
        return $products->map(function ($product) {
            $primaryImage = $product->images->where('is_primary', true)->first();
            $allImages = $product->images->map(fn($img) => '/storage/' . $img->image_path);

            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price_cents' => $product->price_cents,
                'category' => $product->category?->name,
                'category_id' => $product->category?->id,
                'is_shippable' => $product->is_shippable,
                'image' => $primaryImage ? '/storage/' . $primaryImage->image_path : null,
                'images' => $allImages,
            ];
        });
    }

    /**
     * Format categories with products for frontend consumption
     *
     * @param Collection $categories
     * @return Collection
     */
    private function formatCategoriesForFrontend(Collection $categories): Collection
    {
        return $categories->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'products' => $this->formatProductsForFrontend($category->products),
            ];
        });
    }

    /**
     * Clear storefront cache for a specific store
     * Called when products/categories are updated
     *
     * @param int $merchantId
     * @param int $storeId
     * @return void
     */
    public function clearStorefrontCache(int $merchantId, int $storeId): void
    {
        Cache::forget("storefront:frequent:{$merchantId}:{$storeId}");

        // Clear all category cache variations
        $limits = [12, 'all'];
        foreach ($limits as $limit) {
            Cache::forget("storefront:categories:{$merchantId}:{$storeId}:{$limit}");
        }
    }
}
