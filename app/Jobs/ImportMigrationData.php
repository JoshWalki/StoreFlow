<?php

namespace App\Jobs;

use App\Models\Category;
use App\Models\DataMigration;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\Products\ProductDisplayService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportMigrationData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(
        public DataMigration $migration
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Update status
            $this->migration->update(['status' => 'importing']);

            Log::info('Starting import job', [
                'migration_id' => $this->migration->id,
                'store_id' => $this->migration->store_id,
            ]);

            $store = $this->migration->store;
            $merchantId = $store->merchant_id;

            // Get approved products that haven't been imported yet
            $tempProducts = $this->migration->tempProducts()
                ->approved()
                ->notImported()
                ->get();

            if ($tempProducts->isEmpty()) {
                Log::warning('No approved products to import', [
                    'migration_id' => $this->migration->id,
                ]);
                $this->migration->update(['status' => 'completed']);
                return;
            }

            $importedCount = 0;
            $categoryCache = [];

            DB::transaction(function () use ($tempProducts, $merchantId, $store, &$importedCount, &$categoryCache) {
                foreach ($tempProducts as $tempProduct) {
                    try {
                        // Get or create category
                        $categoryId = $this->getOrCreateCategory(
                            $tempProduct->category,
                            $merchantId,
                            $categoryCache
                        );

                        // Create product
                        $product = Product::create([
                            'merchant_id' => $merchantId,
                            'store_id' => $store->id,
                            'category_id' => $categoryId,
                            'name' => $tempProduct->name,
                            'description' => $tempProduct->description,
                            'price_cents' => $tempProduct->price_cents,
                            'is_active' => true,
                            'addon_data' => $tempProduct->addons,
                        ]);

                        // Download and store product image if available
                        $imageUrl = $tempProduct->image_url;
                        if ($imageUrl && filter_var($imageUrl, FILTER_VALIDATE_URL)) {
                            try {
                                // Download image
                                $imageContents = @file_get_contents($imageUrl);

                                if ($imageContents !== false) {
                                    // Generate unique filename
                                    $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                                    if (empty($extension) || !in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                        $extension = 'jpg'; // Default extension
                                    }

                                    $filename = uniqid('product_') . '.' . $extension;
                                    $storagePath = 'products/' . $filename;

                                    // Store the image in storage/app/public/products
                                    Storage::disk('public')->put($storagePath, $imageContents);

                                    // Create ProductImage record with local path
                                    ProductImage::create([
                                        'product_id' => $product->id,
                                        'image_path' => $storagePath,
                                        'is_primary' => 1,
                                    ]);

                                    Log::info('Image downloaded', [
                                        'product_id' => $product->id,
                                        'source_url' => $imageUrl,
                                        'stored_path' => $storagePath,
                                    ]);
                                } else {
                                    Log::warning('Failed to download image', [
                                        'product_id' => $product->id,
                                        'image_url' => $imageUrl,
                                    ]);
                                }
                            } catch (\Exception $imgException) {
                                Log::error('Image download error', [
                                    'product_id' => $product->id,
                                    'image_url' => $imageUrl,
                                    'error' => $imgException->getMessage(),
                                ]);
                            }
                        }

                        // Mark temp product as imported
                        $tempProduct->update([
                            'imported' => true,
                            'imported_product_id' => $product->id,
                        ]);

                        $importedCount++;

                        Log::info('Product imported', [
                            'migration_id' => $this->migration->id,
                            'temp_product_id' => $tempProduct->id,
                            'product_id' => $product->id,
                            'product_name' => $product->name,
                        ]);
                    } catch (\Exception $e) {
                        Log::error('Failed to import product', [
                            'migration_id' => $this->migration->id,
                            'temp_product_id' => $tempProduct->id,
                            'error' => $e->getMessage(),
                        ]);
                        // Continue with next product
                    }
                }
            });

            // Update migration with results
            $this->migration->update([
                'status' => 'completed',
                'products_imported' => $importedCount,
                'import_results' => [
                    'imported_count' => $importedCount,
                    'failed_count' => $tempProducts->count() - $importedCount,
                    'categories_created' => count($categoryCache),
                    'imported_at' => now()->toIso8601String(),
                ],
            ]);

            // Clear storefront cache so new products appear immediately
            $productDisplayService = app(ProductDisplayService::class);
            $productDisplayService->clearStorefrontCache($merchantId, $store->id);

            Log::info('Import completed', [
                'migration_id' => $this->migration->id,
                'products_imported' => $importedCount,
                'categories_created' => count($categoryCache),
                'cache_cleared' => true,
            ]);
        } catch (\Exception $e) {
            Log::error('Import job failed', [
                'migration_id' => $this->migration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->migration->update([
                'status' => 'failed',
                'error_message' => 'Import failed: ' . $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Get or create category
     */
    protected function getOrCreateCategory(string $categoryName, int $merchantId, array &$cache): int
    {
        // Check cache first
        $cacheKey = strtolower(trim($categoryName));
        if (isset($cache[$cacheKey])) {
            return $cache[$cacheKey];
        }

        // Try to find existing category by name
        $category = Category::where('merchant_id', $merchantId)
            ->where('name', $categoryName)
            ->first();

        if (!$category) {
            // Create new category
            $category = Category::create([
                'merchant_id' => $merchantId,
                'name' => $categoryName,
                'slug' => Str::slug($categoryName),
                'is_active' => true,
                'sort_order' => 0,
            ]);

            Log::info('Category created', [
                'migration_id' => $this->migration->id,
                'category_id' => $category->id,
                'category_name' => $categoryName,
            ]);
        }

        // Cache the result
        $cache[$cacheKey] = $category->id;

        return $category->id;
    }
}
