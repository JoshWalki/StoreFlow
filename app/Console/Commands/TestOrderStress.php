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

class TestOrderStress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:order-stress
                            {--store=8 : Store ID to test with}
                            {--duration=60 : Test duration in seconds}
                            {--vendors=30 : Number of concurrent vendors to simulate}
                            {--rate=1 : Orders per vendor per minute}
                            {--cleanup : Clean up test data after completion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Continuous stress test with order creation/deletion and vendor simulation';

    protected OrderService $orderService;
    protected array $metrics = [
        'total_operations' => 0,
        'orders_created' => 0,
        'orders_deleted' => 0,
        'status_updates' => 0,
        'total_queries' => 0,
        'errors' => 0,
        'query_breakdown' => [
            'create' => [],
            'update' => [],
            'delete' => [],
        ],
    ];

    protected array $testOrderIds = [];
    protected $startTime;

    /**
     * Execute the console command.
     */
    public function handle(OrderService $orderService)
    {
        $this->orderService = $orderService;
        $this->startTime = microtime(true);

        $storeId = $this->option('store');
        $duration = $this->option('duration');
        $vendors = $this->option('vendors');
        $rate = $this->option('rate');

        $this->info("🔥 Order System Stress Test");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Store ID: {$storeId}");
        $this->info("Test Duration: {$duration}s");
        $this->info("Simulated Vendors: {$vendors}");
        $this->info("Order Rate: {$rate} orders/vendor/minute");
        $this->newLine();

        // Validate store exists
        $store = Store::find($storeId);
        if (!$store) {
            $this->error("❌ Store {$storeId} not found!");
            return 1;
        }

        // Get test product
        $product = $this->getTestProduct($store);
        if (!$product) {
            return 1;
        }

        // Get test customer
        $customer = $this->getTestCustomer($store);

        // Calculate target operations
        $ordersPerSecond = ($vendors * $rate) / 60;
        $targetOperations = $ordersPerSecond * $duration;

        $this->info("📊 Expected Load:");
        $this->info("   Orders/second: " . round($ordersPerSecond, 2));
        $this->info("   Target operations: " . round($targetOperations));
        $this->newLine();

        // Enable query logging
        DB::enableQueryLog();

        // Run stress test
        $this->info("🚀 Starting continuous stress test...");
        $this->info("   Creating → Updating → Deleting orders in rapid succession");
        $this->newLine();

        $this->runStressTest($store, $customer, $product, $duration, $ordersPerSecond);

        // Display results
        $this->displayResults($duration, $vendors, $rate);

        // Cleanup if requested
        if ($this->option('cleanup')) {
            $this->cleanup();
        }

        return 0;
    }

    /**
     * Run continuous stress test
     */
    protected function runStressTest(Store $store, Customer $customer, Product $product, int $duration, float $ordersPerSecond): void
    {
        $endTime = time() + $duration;
        $delayMicroseconds = (int) (1000000 / max(1, $ordersPerSecond));

        $progressBar = $this->output->createProgressBar($duration);
        $progressBar->setFormat(' %current%s/%max%s [%bar%] %percent:3s%% | Orders: %message%');
        $progressBar->setMessage('0');
        $progressBar->start();

        while (time() < $endTime) {
            try {
                // CREATE ORDER
                $queryCountBefore = count(DB::getQueryLog());
                $order = $this->createOrder($store, $customer, $product);
                $createQueries = count(DB::getQueryLog()) - $queryCountBefore;

                if ($order) {
                    $this->metrics['orders_created']++;
                    $this->metrics['query_breakdown']['create'][] = $createQueries;
                    $this->testOrderIds[] = $order->id;

                    // UPDATE STATUS (simulate drag-and-drop)
                    $queryCountBefore = count(DB::getQueryLog());
                    $this->updateOrderStatus($order);
                    $updateQueries = count(DB::getQueryLog()) - $queryCountBefore;
                    $this->metrics['query_breakdown']['update'][] = $updateQueries;

                    // DELETE ORDER (clean up immediately)
                    $queryCountBefore = count(DB::getQueryLog());
                    $this->deleteOrder($order);
                    $deleteQueries = count(DB::getQueryLog()) - $queryCountBefore;
                    $this->metrics['query_breakdown']['delete'][] = $deleteQueries;

                    $this->metrics['total_operations'] += 3; // create + update + delete
                    $progressBar->setMessage((string) $this->metrics['orders_created']);
                }
            } catch (\Exception $e) {
                $this->metrics['errors']++;
                Log::error('Stress test operation failed', [
                    'error' => $e->getMessage(),
                    'operation' => 'full_cycle',
                ]);
            }

            // Rate limiting
            usleep($delayMicroseconds);
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Capture total queries
        $this->metrics['total_queries'] = count(DB::getQueryLog());
    }

    /**
     * Create a test order
     */
    protected function createOrder(Store $store, Customer $customer, Product $product): ?Order
    {
        try {
            DB::beginTransaction();

            $lineTotal = $product->price_cents * 2;

            $order = Order::create([
                'public_id' => 'STRESS-' . uniqid(),
                'merchant_id' => $store->merchant_id,
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'fulfilment_type' => 'pickup',
                'status' => Order::STATUS_PENDING,
                'payment_status' => 'paid',
                'items_total_cents' => $lineTotal,
                'shipping_cost_cents' => 0,
                'total_cents' => $lineTotal,
                'pickup_time' => now()->addHours(2),
            ]);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => 2,
                'unit_price_cents' => $product->price_cents,
                'line_subtotal_cents' => $lineTotal,
                'total_cents' => $lineTotal,
            ]);

            DB::commit();
            return $order;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->metrics['errors']++;
            return null;
        }
    }

    /**
     * Update order status
     */
    protected function updateOrderStatus(Order $order): void
    {
        try {
            $this->orderService->updateOrderStatus($order, Order::STATUS_IN_PROGRESS);
            $this->metrics['status_updates']++;
        } catch (\Exception $e) {
            $this->metrics['errors']++;
        }
    }

    /**
     * Delete order and items
     */
    protected function deleteOrder(Order $order): void
    {
        try {
            OrderItem::where('order_id', $order->id)->delete();
            $order->delete();
            $this->metrics['orders_deleted']++;
        } catch (\Exception $e) {
            $this->metrics['errors']++;
        }
    }

    /**
     * Get or create test product
     */
    protected function getTestProduct(Store $store): ?Product
    {
        $product = Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (!$product) {
            $this->error("❌ No active products found in store {$store->id}");
            $totalProducts = Product::where('store_id', $store->id)->count();
            $this->warn("   Total products: {$totalProducts}");
            $this->info("💡 Activate products: UPDATE products SET is_active = 1 WHERE store_id = {$store->id};");
            return null;
        }

        return $product;
    }

    /**
     * Get or create test customer
     */
    protected function getTestCustomer(Store $store): Customer
    {
        return Customer::firstOrCreate(
            ['email' => 'stress@test.local'],
            [
                'merchant_id' => $store->merchant_id,
                'first_name' => 'Stress',
                'last_name' => 'Test',
                'mobile' => '0400000000',
                'password' => bcrypt('password'),
            ]
        );
    }

    /**
     * Display comprehensive results
     */
    protected function displayResults(int $duration, int $vendors, float $rate): void
    {
        $elapsed = microtime(true) - $this->startTime;

        $this->info("📊 Stress Test Results");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        // Basic metrics
        $this->table(
            ['Metric', 'Value'],
            [
                ['Duration', round($elapsed, 2) . 's'],
                ['Orders Created', $this->metrics['orders_created']],
                ['Status Updates', $this->metrics['status_updates']],
                ['Orders Deleted', $this->metrics['orders_deleted']],
                ['Total Operations', $this->metrics['total_operations']],
                ['Errors', $this->metrics['errors']],
                ['Operations/second', round($this->metrics['total_operations'] / $elapsed, 2)],
            ]
        );

        $this->newLine();

        // Query analysis
        $avgCreateQueries = !empty($this->metrics['query_breakdown']['create'])
            ? round(array_sum($this->metrics['query_breakdown']['create']) / count($this->metrics['query_breakdown']['create']), 1)
            : 0;

        $avgUpdateQueries = !empty($this->metrics['query_breakdown']['update'])
            ? round(array_sum($this->metrics['query_breakdown']['update']) / count($this->metrics['query_breakdown']['update']), 1)
            : 0;

        $avgDeleteQueries = !empty($this->metrics['query_breakdown']['delete'])
            ? round(array_sum($this->metrics['query_breakdown']['delete']) / count($this->metrics['query_breakdown']['delete']), 1)
            : 0;

        $totalQueriesPerCycle = $avgCreateQueries + $avgUpdateQueries + $avgDeleteQueries;
        $queriesPerSecond = round($this->metrics['total_queries'] / $elapsed, 2);

        $this->info("🔍 Database Query Analysis");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->table(
            ['Operation', 'Queries', 'Per Operation'],
            [
                ['Create Order', array_sum($this->metrics['query_breakdown']['create']), $avgCreateQueries],
                ['Update Status', array_sum($this->metrics['query_breakdown']['update']), $avgUpdateQueries],
                ['Delete Order', array_sum($this->metrics['query_breakdown']['delete']), $avgDeleteQueries],
                ['TOTAL', $this->metrics['total_queries'], $totalQueriesPerCycle],
            ]
        );

        $this->newLine();
        $this->info("   Queries/second (QPS): {$queriesPerSecond}");
        $this->info("   Full cycle queries: {$totalQueriesPerCycle}");
        $this->newLine();

        // Vendor simulation
        $this->info("🏪 Multi-Vendor Load Projection");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $ordersPerVendorPerMin = $rate;
        $ordersPerVendorPerSec = $ordersPerVendorPerMin / 60;

        // Calculate for different vendor counts
        $vendorCounts = [1, 5, 10, 30, 50, 100];
        $projections = [];

        foreach ($vendorCounts as $count) {
            $ordersPerSec = $ordersPerVendorPerSec * $count;
            $operationsPerSec = $ordersPerSec * 3; // create + update + delete
            $qps = $operationsPerSec * $totalQueriesPerCycle;

            $projections[] = [
                $count . ' vendors',
                round($ordersPerSec, 2) . '/s',
                round($operationsPerSec, 2) . '/s',
                round($qps, 0) . ' QPS',
            ];
        }

        $this->table(
            ['Vendors', 'Orders/sec', 'Operations/sec', 'Database Load'],
            $projections
        );

        $this->newLine();

        // Recommendations based on current performance
        $this->info("💡 Performance Assessment");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $targetVendorQPS = ($vendors * $ordersPerVendorPerSec * 3 * $totalQueriesPerCycle);

        if ($queriesPerSecond < 100) {
            $this->info(" EXCELLENT - System can easily handle {$vendors}+ concurrent vendors");
            $this->info("   Current QPS: {$queriesPerSecond}");
            $this->info("   Capacity: ~" . round(1000 / $totalQueriesPerCycle / 3 * 60) . " orders/min system-wide");
        } elseif ($queriesPerSecond < 500) {
            $this->info(" GOOD - System performing well");
            $this->info("   Current QPS: {$queriesPerSecond}");
            $this->info("   Can handle {$vendors} vendors with current load");
        } elseif ($queriesPerSecond < 1000) {
            $this->warn("⚠️  MODERATE - Consider optimization for {$vendors}+ vendors");
            $this->warn("   Current QPS: {$queriesPerSecond}");
            $this->warn("   May experience slowdown during peak traffic");
        } else {
            $this->error("❌ HIGH LOAD - Optimization required");
            $this->error("   Current QPS: {$queriesPerSecond}");
            $this->error("   Will struggle with {$vendors} concurrent vendors");
        }

        $this->newLine();
        $this->info("📈 Database Optimization Tips:");
        $this->info("   1. Add indexes: store_id, status, customer_id, created_at");
        $this->info("   2. Use eager loading: ->with(['items.product', 'customer'])");
        $this->info("   3. Queue background jobs: broadcasting, notifications");
        $this->info("   4. Consider read replicas for high read loads");
        $this->info("   5. Implement Redis caching for frequent queries");
    }

    /**
     * Clean up test data
     */
    protected function cleanup(): void
    {
        $this->info("🧹 Cleaning up test data...");

        // Clean up any remaining test orders
        Order::where('public_id', 'LIKE', 'STRESS-%')->delete();
        Customer::where('email', 'stress@test.local')->delete();

        $this->info(" Cleanup complete");
    }
}
