<?php

namespace App\Jobs;

use App\Models\DataMigration;
use App\Models\MigrationProductTemp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ScrapeRestaurantMenu implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300; // 5 minutes

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
            $this->migration->update(['status' => 'scraping']);

            Log::info('Starting scrape job', [
                'migration_id' => $this->migration->id,
                'platform' => $this->migration->platform,
                'url' => $this->migration->source_url,
            ]);

            // Call scraper service
            $scraperUrl = config('services.scraper.url', env('SCRAPER_SERVICE_URL', 'http://localhost:3000'));
            $response = Http::timeout(300)->post($scraperUrl . '/scrape', [
                'platform' => $this->migration->platform,
                'url' => $this->migration->source_url,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Scraper service returned error: ' . $response->body());
            }

            $data = $response->json();

            if (!$data['success']) {
                throw new \Exception($data['error'] ?? 'Scraping failed');
            }

            $menuData = $data['data'];

            Log::info('Scrape completed', [
                'migration_id' => $this->migration->id,
                'products_found' => count($menuData['products'] ?? []),
                'categories_found' => count($menuData['categories'] ?? []),
            ]);

            // Store extracted data
            $this->migration->update([
                'extracted_data' => $menuData,
                'products_found' => count($menuData['products'] ?? []),
                'categories_found' => count($menuData['categories'] ?? []),
            ]);

            // Create temporary product records
            $this->createTempProducts($menuData['products'] ?? []);

            // Update status to preview
            $this->migration->update(['status' => 'preview']);

            Log::info('Migration ready for preview', [
                'migration_id' => $this->migration->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Scrape job failed', [
                'migration_id' => $this->migration->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->migration->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Create temporary product records
     */
    protected function createTempProducts(array $products): void
    {
        foreach ($products as $product) {
            MigrationProductTemp::create([
                'data_migration_id' => $this->migration->id,
                'name' => $product['name'] ?? 'Unknown Product',
                'description' => $product['description'] ?? '',
                'price_cents' => $product['price_cents'] ?? 0,
                'category' => $product['category'] ?? 'Uncategorized',
                'image_url' => $product['image_url'] ?? null,
                'addons' => $product['addons'] ?? [],
                'variations' => $product['variations'] ?? [],
                'user_approved' => true, // Auto-approve for MVP
            ]);
        }

        Log::info('Created temp products', [
            'migration_id' => $this->migration->id,
            'count' => count($products),
        ]);
    }
}
