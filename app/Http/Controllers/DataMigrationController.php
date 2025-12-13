<?php

namespace App\Http\Controllers;

use App\Jobs\ImportMigrationData;
use App\Jobs\ScrapeRestaurantMenu;
use App\Models\DataMigration;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DataMigrationController extends Controller
{
    /**
     * Start a new data migration
     */
    public function start(Request $request, Store $store)
    {
        $request->validate([
            'platform' => 'required|in:ubereats,doordash,menulog,deliveroo',
            'url' => 'required|url',
        ]);

        // Create migration record
        $migration = DataMigration::create([
            'store_id' => $store->id,
            'platform' => $request->platform,
            'source_url' => $request->url,
            'status' => 'pending',
        ]);

        // Dispatch scraping job
        ScrapeRestaurantMenu::dispatch($migration);

        return response()->json([
            'success' => true,
            'migration' => $migration,
            'message' => 'Migration started. This may take 1-2 minutes.',
        ]);
    }

    /**
     * Get migration status
     */
    public function status(DataMigration $migration)
    {
        $migration->load('tempProducts');

        return response()->json([
            'migration' => $migration,
            'products_preview' => $migration->tempProducts()->limit(10)->get(),
        ]);
    }

    /**
     * List all migrations for a store
     */
    public function index(Store $store)
    {
        $migrations = DataMigration::where('store_id', $store->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'migrations' => $migrations,
        ]);
    }

    /**
     * Get full preview data
     */
    public function preview(DataMigration $migration)
    {
        $migration->load('tempProducts');

        return response()->json([
            'migration' => $migration,
            'products' => $migration->tempProducts,
            'categories' => $migration->tempProducts->pluck('category')->unique()->values(),
        ]);
    }

    /**
     * Import migration data (create actual products)
     */
    public function import(DataMigration $migration)
    {
        // Check if migration is ready for import
        if (!in_array($migration->status, ['preview', 'failed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Migration is not ready for import. Current status: ' . $migration->status,
            ], 422);
        }

        // Check if there are products to import
        $productCount = $migration->tempProducts()->approved()->notImported()->count();
        if ($productCount === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No approved products to import.',
            ], 422);
        }

        // Dispatch import job
        ImportMigrationData::dispatch($migration);

        return response()->json([
            'success' => true,
            'message' => "Importing {$productCount} products. This may take a few minutes.",
            'migration' => $migration->fresh(),
        ]);
    }

    /**
     * Update product approval status
     */
    public function updateProductApproval(Request $request, DataMigration $migration)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'required|integer',
            'approved' => 'required|boolean',
        ]);

        $migration->tempProducts()
            ->whereIn('id', $request->product_ids)
            ->update(['user_approved' => $request->approved]);

        return response()->json([
            'success' => true,
            'message' => 'Product approval updated.',
        ]);
    }

    /**
     * Delete migration and temp products
     */
    public function destroy(DataMigration $migration)
    {
        $migration->delete();

        return response()->json([
            'success' => true,
            'message' => 'Migration deleted successfully.',
        ]);
    }

    /**
     * Simple test endpoint
     */
    public function test()
    {
        return response()->json([
            'success' => true,
            'message' => 'Data migration API is working!',
            'scraper_service' => config('services.scraper.url', env('SCRAPER_SERVICE_URL', 'http://localhost:3000')),
        ]);
    }
}
