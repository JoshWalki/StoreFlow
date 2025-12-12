<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Services\Orders\OrderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestOrderPerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:order-performance
                            {--store=8 : Store ID to test with}
                            {--orders=50 : Number of orders to create}
                            {--concurrent=10 : Number of concurrent status updates}
                            {--duration=60 : Test duration in seconds}
                            {--cleanup : Clean up test data after completion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test order system performance with concurrent operations';

    protected OrderService $orderService;
    protected array $metrics = [
        'orders_created' => 0,
        'status_updates' => 0,
        'errors' => 0,
        'update_times' => [],
        'inconsistencies' => [],
    ];

    protected array $testOrderIds = [];

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService)
    {
        $this->orderService = $orderService;

        $storeId = $this->option('store');
        $orderCount = $this->option('orders');
        $concurrent = $this->option('concurrent');
        $duration = $this->option('duration');

        $this->info("🧪 Starting Order Performance Test");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Store ID: {$storeId}");
        $this->info("Orders to create: {$orderCount}");
        $this->info("Concurrent updates: {$concurrent}");
        $this->info("Test duration: {$duration}s");
        $this->newLine();

        // Validate store exists
        $store = Store::find($storeId);
        if (!$store) {
            $this->error("❌ Store {$storeId} not found!");
            return 1;
        }

        // Phase 1: Create test orders
        $this->info("📦 Phase 1: Creating test orders...");
        $createStart = microtime(true);
        $this->createTestOrders($store, $orderCount);
        $createDuration = round((microtime(true) - $createStart) * 1000, 2);
        $this->info("✅ Created {$this->metrics['orders_created']} orders in {$createDuration}ms");

        if ($this->metrics['orders_created'] > 0) {
            $this->info("   Average: " . round($createDuration / $this->metrics['orders_created'], 2) . "ms per order");
        } else {
            $this->error("❌ No orders were created! Check errors below:");
            $this->error("   Errors encountered: {$this->metrics['errors']}");
            return 1;
        }

        $this->newLine();

        // Phase 2: Concurrent status updates
        $this->info("🔄 Phase 2: Testing concurrent status updates...");
        $this->info("   Running for {$duration} seconds with {$concurrent} concurrent operations...");
        $updateStart = microtime(true);
        $this->testConcurrentUpdates($duration, $concurrent);
        $updateDuration = round(microtime(true) - $updateStart, 2);
        $this->info("✅ Completed {$this->metrics['status_updates']} status updates in {$updateDuration}s");
        $this->info("   Average: " . round(($updateDuration * 1000) / $this->metrics['status_updates'], 2) . "ms per update");
        $this->info("   Throughput: " . round($this->metrics['status_updates'] / $updateDuration, 2) . " updates/second");
        $this->newLine();

        // Phase 3: Data consistency check
        $this->info("🔍 Phase 3: Checking data consistency...");
        $this->checkDataConsistency();
        $this->newLine();

        // Display results
        $this->displayResults();

        // Cleanup if requested
        if ($this->option('cleanup')) {
            $this->info("🧹 Cleaning up test data...");
            $this->cleanup();
            $this->info("✅ Cleanup complete");
        }

        return 0;
    }

    /**
     * Create test orders in the database
     */
    protected function createTestOrders(Store $store, int $count): void
    {
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        // Get or create test customer
        $customer = Customer::firstOrCreate(
            ['email' => 'test@performance.test'],
            [
                'merchant_id' => $store->merchant_id,
                'first_name' => 'Performance',
                'last_name' => 'Test',
                'mobile' => '0400000000',
                'password' => bcrypt('password'),
            ]
        );

        // Get a random product from the store
        $product = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (!$product) {
            $this->newLine();
            $this->error("❌ No active products found in store {$store->id}");

            // Show helpful information
            $totalProducts = Product::where('store_id', $store->id)->count();
            $activeProducts = Product::where('store_id', $store->id)->where('is_active', true)->count();

            $this->warn("   Total products in store: {$totalProducts}");
            $this->warn("   Active products: {$activeProducts}");

            if ($totalProducts > 0 && $activeProducts === 0) {
                $this->info("💡 Tip: Activate some products first:");
                $this->info("   UPDATE products SET is_active = 1 WHERE store_id = {$store->id} LIMIT 5;");
            } elseif ($totalProducts === 0) {
                $this->info("💡 Tip: Create some products first in the dashboard");
            }

            return;
        }

        for ($i = 0; $i < $count; $i++) {
            try {
                DB::beginTransaction();

                // Create order
                $order = Order::create([
                    'public_id' => 'PERF-' . str_pad($i, 5, '0', STR_PAD_LEFT) . '-' . time(),
                    'merchant_id' => $store->merchant_id,
                    'store_id' => $store->id,
                    'customer_id' => $customer->id,
                    'fulfilment_type' => 'pickup',
                    'status' => Order::STATUS_PENDING,
                    'payment_status' => 'paid',
                    'items_total_cents' => $product->price_cents * 2,
                    'shipping_cost_cents' => 0,
                    'total_cents' => $product->price_cents * 2,
                    'pickup_time' => now()->addHours(2),
                ]);

                // Create order item
                $lineTotal = $product->price_cents * 2;
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'name' => $product->name,
                    'quantity' => 2,
                    'unit_price_cents' => $product->price_cents,
                    'line_subtotal_cents' => $lineTotal,
                    'total_cents' => $lineTotal,
                ]);

                $this->testOrderIds[] = $order->id;
                $this->metrics['orders_created']++;

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                $this->metrics['errors']++;

                // Log detailed error for first few failures
                if ($this->metrics['errors'] <= 3) {
                    $this->newLine();
                    $this->error("Order creation error #{$this->metrics['errors']}:");
                    $this->error("  {$e->getMessage()}");
                }

                Log::error('Order creation failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'iteration' => $i,
                    'store_id' => $store->id,
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
    }

    /**
     * Test concurrent status updates
     */
    protected function testConcurrentUpdates(int $duration, int $concurrent): void
    {
        $startTime = time();
        $endTime = $startTime + $duration;
        $statuses = [
            Order::STATUS_PENDING,
            Order::STATUS_IN_PROGRESS,
            Order::STATUS_READY_FOR_PICKUP,
        ];

        $progressBar = $this->output->createProgressBar($duration);
        $progressBar->start();

        while (time() < $endTime) {
            // Simulate concurrent updates
            $operations = [];

            for ($i = 0; $i < $concurrent; $i++) {
                $orderId = $this->testOrderIds[array_rand($this->testOrderIds)];
                $newStatus = $statuses[array_rand($statuses)];

                $operations[] = [
                    'order_id' => $orderId,
                    'status' => $newStatus,
                ];
            }

            // Execute concurrent updates
            foreach ($operations as $operation) {
                $updateStart = microtime(true);

                try {
                    $order = Order::lockForUpdate()->find($operation['order_id']);

                    if (!$order) {
                        $this->metrics['errors']++;
                        continue;
                    }

                    // Use the service to update (respects business logic)
                    $this->orderService->updateOrderStatus($order, $operation['status']);

                    $updateTime = (microtime(true) - $updateStart) * 1000;
                    $this->metrics['update_times'][] = $updateTime;
                    $this->metrics['status_updates']++;
                } catch (\Exception $e) {
                    $this->metrics['errors']++;

                    // Log but don't stop the test
                    if (!str_contains($e->getMessage(), 'Invalid status transition')) {
                        Log::error('Status update failed', [
                            'error' => $e->getMessage(),
                            'order_id' => $operation['order_id'],
                            'status' => $operation['status'],
                        ]);
                    }
                }
            }

            $progressBar->advance();
            usleep(100000); // 100ms delay between batches
        }

        $progressBar->finish();
        $this->newLine();
    }

    /**
     * Check for data inconsistencies
     */
    protected function checkDataConsistency(): void
    {
        $inconsistencies = [];

        foreach ($this->testOrderIds as $orderId) {
            $order = Order::with('items')->find($orderId);

            if (!$order) {
                $inconsistencies[] = "Order {$orderId} not found (lost during test)";
                continue;
            }

            // Check if order has items
            if ($order->items->isEmpty()) {
                $inconsistencies[] = "Order {$order->public_id} has no items";
            }

            // Check status validity
            $validStatuses = [
                Order::STATUS_PENDING,
                Order::STATUS_IN_PROGRESS,
                Order::STATUS_READY_FOR_PICKUP,
            ];
            if (!in_array($order->status, $validStatuses)) {
                $inconsistencies[] = "Order {$order->public_id} has invalid status: {$order->status}";
            }

            // Check for orphaned items
            foreach ($order->items as $item) {
                if (!$item->product_id) {
                    $inconsistencies[] = "Order {$order->public_id} has item without product_id";
                }
            }
        }

        $this->metrics['inconsistencies'] = $inconsistencies;

        if (empty($inconsistencies)) {
            $this->info("✅ No data inconsistencies found!");
        } else {
            $this->warn("⚠️  Found " . count($inconsistencies) . " inconsistencies:");
            foreach ($inconsistencies as $issue) {
                $this->warn("   • {$issue}");
            }
        }
    }

    /**
     * Display test results
     */
    protected function displayResults(): void
    {
        $this->info("📊 Performance Test Results");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $this->table(
            ['Metric', 'Value'],
            [
                ['Orders Created', $this->metrics['orders_created']],
                ['Status Updates', $this->metrics['status_updates']],
                ['Total Operations', $this->metrics['orders_created'] + $this->metrics['status_updates']],
                ['Errors', $this->metrics['errors']],
                ['Error Rate', round(($this->metrics['errors'] / max(1, $this->metrics['status_updates'])) * 100, 2) . '%'],
                ['Data Inconsistencies', count($this->metrics['inconsistencies'])],
            ]
        );

        if (!empty($this->metrics['update_times'])) {
            $avgTime = round(array_sum($this->metrics['update_times']) / count($this->metrics['update_times']), 2);
            $minTime = round(min($this->metrics['update_times']), 2);
            $maxTime = round(max($this->metrics['update_times']), 2);
            $p95Time = round($this->percentile($this->metrics['update_times'], 95), 2);
            $p99Time = round($this->percentile($this->metrics['update_times'], 99), 2);

            $this->newLine();
            $this->info("⏱️  Update Time Statistics");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->table(
                ['Statistic', 'Time (ms)'],
                [
                    ['Average', $avgTime],
                    ['Min', $minTime],
                    ['Max', $maxTime],
                    ['P95', $p95Time],
                    ['P99', $p99Time],
                ]
            );
        }

        $this->newLine();

        // Performance assessment
        if ($this->metrics['errors'] === 0 && empty($this->metrics['inconsistencies'])) {
            $this->info("✅ System Performance: EXCELLENT");
            $this->info("   No errors or data inconsistencies detected!");
        } elseif ($this->metrics['errors'] < 5 && count($this->metrics['inconsistencies']) === 0) {
            $this->info("✅ System Performance: GOOD");
            $this->warn("   Minor errors detected but no data loss");
        } else {
            $this->warn("⚠️  System Performance: NEEDS ATTENTION");
            $this->warn("   Review errors and inconsistencies above");
        }
    }

    /**
     * Calculate percentile
     */
    protected function percentile(array $data, int $percentile): float
    {
        sort($data);
        $index = ceil(count($data) * $percentile / 100) - 1;
        return $data[max(0, $index)];
    }

    /**
     * Clean up test data
     */
    protected function cleanup(): void
    {
        foreach ($this->testOrderIds as $orderId) {
            try {
                $order = Order::find($orderId);
                if ($order) {
                    // Delete items first
                    OrderItem::where('order_id', $orderId)->delete();
                    // Delete order
                    $order->delete();
                }
            } catch (\Exception $e) {
                Log::error('Cleanup failed', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Delete test customer
        Customer::where('email', 'test@performance.test')->delete();
    }
}
