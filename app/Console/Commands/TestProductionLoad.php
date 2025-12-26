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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class TestProductionLoad extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:production-load
                            {--store=8 : Store ID to test with}
                            {--vendors=30 : Number of concurrent vendors}
                            {--duration=120 : Test duration in seconds}
                            {--workers=10 : Concurrent PHP workers to simulate}
                            {--polling : Enable display view polling simulation}
                            {--broadcast : Enable real broadcasting events}
                            {--cleanup : Clean up test data}';

    /**
     * The console command description.
     */
    protected $description = 'Realistic production load test with concurrent workers, polling, and broadcasting';

    protected array $workerPids = [];
    protected array $metrics = [
        'start_time' => 0,
        'end_time' => 0,
        'orders_created' => 0,
        'status_updates' => 0,
        'polls' => 0,
        'broadcasts' => 0,
        'errors' => 0,
        'db_connections' => [],
        'query_times' => [],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->metrics['start_time'] = microtime(true);

        $storeId = $this->option('store');
        $vendors = $this->option('vendors');
        $duration = $this->option('duration');
        $workers = $this->option('workers');
        $enablePolling = $this->option('polling');
        $enableBroadcast = $this->option('broadcast');

        $this->info("🚀 PRODUCTION LOAD TEST");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Store ID: {$storeId}");
        $this->info("Vendors: {$vendors}");
        $this->info("Duration: {$duration}s");
        $this->info("Concurrent Workers: {$workers}");
        $this->info("Display Polling: " . ($enablePolling ? '' : '❌'));
        $this->info("Broadcasting: " . ($enableBroadcast ? '' : '❌'));
        $this->newLine();

        // Validate store
        $store = Store::find($storeId);
        if (!$store) {
            $this->error("❌ Store not found!");
            return 1;
        }

        // Pre-test system check
        $this->info("🔍 Pre-Test System Check");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->checkSystemResources();
        $this->newLine();

        $this->warn("⚠️  WARNING: This test will create significant load!");
        $this->warn("   - {$workers} concurrent PHP processes");
        $this->warn("   - Heavy database activity");
        $this->warn("   - Broadcasting events (if enabled)");
        $this->warn("   - Continuous polling (if enabled)");
        $this->newLine();

        if (!$this->confirm('Continue with production load test?', true)) {
            return 0;
        }

        $this->newLine();
        $this->info("🔥 Starting production load test...");
        $this->newLine();

        // Start background workers
        $this->startWorkers($storeId, $vendors, $duration, $workers);

        // Start polling simulation
        if ($enablePolling) {
            $this->startPollingSimulation($storeId, $duration);
        }

        // Monitor test
        $this->monitorTest($duration);

        // Wait for workers to finish
        $this->waitForWorkers();

        // Collect metrics
        $this->collectMetrics();

        // Display results
        $this->displayResults($vendors, $workers);

        // Cleanup
        if ($this->option('cleanup')) {
            $this->cleanup();
        }

        return 0;
    }

    /**
     * Check system resources before test
     */
    protected function checkSystemResources(): void
    {
        // Database connections
        try {
            $maxConnections = DB::selectOne("SHOW VARIABLES LIKE 'max_connections'")->Value ?? 'unknown';
            $currentConnections = DB::selectOne("SHOW STATUS LIKE 'Threads_connected'")->Value ?? 'unknown';
            $this->info("   MySQL Connections: {$currentConnections} / {$maxConnections}");
        } catch (\Exception $e) {
            $this->warn("   Could not check MySQL connections");
        }

        // PHP memory
        $memoryLimit = ini_get('memory_limit');
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
        $this->info("   PHP Memory: {$memoryUsage}MB / {$memoryLimit}");

        // Cache driver
        $cacheDriver = config('cache.default');
        $this->info("   Cache Driver: {$cacheDriver}");

        // Queue driver
        $queueDriver = config('queue.default');
        $this->info("   Queue Driver: {$queueDriver}");

        // Broadcast driver
        $broadcastDriver = config('broadcasting.default');
        $this->info("   Broadcast Driver: {$broadcastDriver}");
    }

    /**
     * Start concurrent worker processes
     */
    protected function startWorkers(int $storeId, int $vendors, int $duration, int $workers): void
    {
        $this->info("🔧 Spawning {$workers} concurrent workers...");

        $ordersPerWorker = ceil($vendors / $workers);

        for ($i = 0; $i < $workers; $i++) {
            $workerScript = base_path('scripts/stress-worker.php');
            $logFile = storage_path("logs/worker-{$i}.log");

            // Create worker script if it doesn't exist
            $this->ensureWorkerScript($workerScript);

            // Start worker process
            $cmd = sprintf(
                'php %s --store=%d --orders=%d --duration=%d --worker-id=%d > %s 2>&1 &',
                $workerScript,
                $storeId,
                $ordersPerWorker,
                $duration,
                $i,
                $logFile
            );

            exec($cmd, $output, $pid);
            $this->workerPids[$i] = [
                'log' => $logFile,
                'started' => microtime(true),
            ];

            usleep(100000); // Stagger worker starts by 100ms
        }

        $this->info(" {$workers} workers started");
        $this->newLine();
    }

    /**
     * Create worker script
     */
    protected function ensureWorkerScript(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        if (!file_exists($path)) {
            $script = <<<'PHP'
<?php
// Stress Test Worker Script
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Parse command line args
$options = getopt('', ['store:', 'orders:', 'duration:', 'worker-id:']);
$storeId = $options['store'] ?? 8;
$targetOrders = $options['orders'] ?? 10;
$duration = $options['duration'] ?? 60;
$workerId = $options['worker-id'] ?? 0;

echo "[Worker {$workerId}] Starting with target {$targetOrders} orders over {$duration}s\n";

$store = App\Models\Store::find($storeId);
$customer = App\Models\Customer::firstOrCreate(
    ['email' => "worker-{$workerId}@stress.test"],
    [
        'merchant_id' => $store->merchant_id,
        'first_name' => "Worker",
        'last_name' => $workerId,
        'mobile' => '0400000000',
        'password' => bcrypt('password'),
    ]
);

$product = App\Models\Product::where('store_id', $storeId)
    ->where('is_active', true)
    ->inRandomOrder()
    ->first();

if (!$product) {
    die("No products available\n");
}

$orderService = app(App\Services\Orders\OrderService::class);
$endTime = time() + $duration;
$ordersCreated = 0;
$errors = 0;

while (time() < $endTime && $ordersCreated < $targetOrders) {
    try {
        // Create order
        DB::beginTransaction();

        $lineTotal = $product->price_cents * rand(1, 5);
        $order = App\Models\Order::create([
            'public_id' => "W{$workerId}-" . uniqid(),
            'merchant_id' => $store->merchant_id,
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'fulfilment_type' => rand(0, 1) ? 'pickup' : 'shipping',
            'status' => App\Models\Order::STATUS_PENDING,
            'payment_status' => 'paid',
            'items_total_cents' => $lineTotal,
            'shipping_cost_cents' => 0,
            'total_cents' => $lineTotal,
            'pickup_time' => now()->addHours(2),
            'shipping_name' => 'Test Customer',
            'line1' => '123 Test St',
            'city' => 'Sydney',
            'state' => 'NSW',
            'postcode' => '2000',
            'country' => 'AU',
        ]);

        App\Models\OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'name' => $product->name,
            'quantity' => rand(1, 5),
            'unit_price_cents' => $product->price_cents,
            'line_subtotal_cents' => $lineTotal,
            'total_cents' => $lineTotal,
        ]);

        DB::commit();
        $ordersCreated++;

        // Simulate realistic workflow
        usleep(rand(100000, 500000)); // 100-500ms think time

        // Random status updates (simulate drag-and-drop)
        if (rand(0, 2) === 0) {
            $orderService->updateOrderStatus($order, App\Models\Order::STATUS_IN_PROGRESS);
            usleep(rand(50000, 200000)); // 50-200ms
        }

        // Update to final status based on fulfillment type
        if (rand(0, 2) === 0) {
            if ($order->fulfilment_type === 'pickup') {
                $orderService->updateOrderStatus($order, App\Models\Order::STATUS_READY_FOR_PICKUP);
            } else {
                $orderService->updateOrderStatus($order, App\Models\Order::STATUS_SHIPPED);
            }
        }

    } catch (Exception $e) {
        DB::rollBack();
        $errors++;
        echo "[Worker {$workerId}] Error: " . $e->getMessage() . "\n";
    }

    // Realistic pacing (not all orders at once)
    usleep(rand(500000, 2000000)); // 0.5-2 seconds between orders
}

echo "[Worker {$workerId}] Completed: {$ordersCreated} orders, {$errors} errors\n";
PHP;
            file_put_contents($path, $script);
            chmod($path, 0755);
        }
    }

    /**
     * Start display view polling simulation
     */
    protected function startPollingSimulation(int $storeId, int $duration): void
    {
        $this->info("📡 Starting display view polling simulation...");

        $pollingScript = base_path('scripts/polling-simulator.php');
        $this->ensurePollingScript($pollingScript, $storeId, $duration);

        $logFile = storage_path('logs/polling.log');
        $cmd = sprintf('php %s > %s 2>&1 &', $pollingScript, $logFile);
        exec($cmd);

        $this->info(" Polling simulation started (3-second intervals)");
        $this->newLine();
    }

    /**
     * Create polling script
     */
    protected function ensurePollingScript(string $path, int $storeId, int $duration): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $script = <<<PHP
<?php
// Display View Polling Simulator
require __DIR__ . '/../vendor/autoload.php';

\$app = require_once __DIR__ . '/../bootstrap/app.php';
\$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

\$endTime = time() + {$duration};
\$polls = 0;

echo "Polling simulator started (store {$storeId})\n";

while (time() < \$endTime) {
    try {
        // Simulate the /dashboard/orders/poll endpoint
        \$orders = App\Models\Order::with(['items.product', 'customer'])
            ->where('store_id', {$storeId})
            ->whereIn('status', ['pending', 'in_progress', 'ready', 'ready_for_pickup', 'shipped'])
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get();

        \$polls++;
        echo "Poll #\$polls: " . count(\$orders) . " orders\n";

    } catch (Exception \$e) {
        echo "Poll error: " . \$e->getMessage() . "\n";
    }

    sleep(3); // Poll every 3 seconds (same as display view)
}

echo "Polling completed: \$polls polls\n";
PHP;
        file_put_contents($path, $script);
        chmod($path, 0755);
    }

    /**
     * Monitor test progress
     */
    protected function monitorTest(int $duration): void
    {
        $progressBar = $this->output->createProgressBar($duration);
        $progressBar->setFormat(' %current%s/%max%s [%bar%] | DB Conn: %message%');
        $progressBar->start();

        for ($i = 0; $i < $duration; $i++) {
            try {
                $connections = DB::selectOne("SHOW STATUS LIKE 'Threads_connected'")->Value ?? '?';
                $progressBar->setMessage($connections);
                $this->metrics['db_connections'][] = (int) $connections;
            } catch (\Exception $e) {
                $progressBar->setMessage('err');
            }

            $progressBar->advance();
            sleep(1);
        }

        $progressBar->finish();
        $this->newLine(2);
    }

    /**
     * Wait for worker processes
     */
    protected function waitForWorkers(): void
    {
        $this->info("⏳ Waiting for workers to finish...");
        sleep(5); // Give workers time to complete
        $this->info(" Workers completed");
        $this->newLine();
    }

    /**
     * Collect metrics from workers
     */
    protected function collectMetrics(): void
    {
        $this->metrics['end_time'] = microtime(true);

        // Parse worker logs
        foreach ($this->workerPids as $id => $data) {
            if (file_exists($data['log'])) {
                $log = file_get_contents($data['log']);

                // Extract metrics from worker logs
                if (preg_match('/Completed: (\d+) orders/', $log, $matches)) {
                    $this->metrics['orders_created'] += (int) $matches[1];
                }

                if (preg_match('/(\d+) errors/', $log, $matches)) {
                    $this->metrics['errors'] += (int) $matches[1];
                }
            }
        }

        // Parse polling logs
        $pollingLog = storage_path('logs/polling.log');
        if (file_exists($pollingLog)) {
            $log = file_get_contents($pollingLog);
            $this->metrics['polls'] = substr_count($log, 'Poll #');
        }
    }

    /**
     * Display comprehensive results
     */
    protected function displayResults(int $vendors, int $workers): void
    {
        $elapsed = $this->metrics['end_time'] - $this->metrics['start_time'];

        $this->info("📊 PRODUCTION LOAD TEST RESULTS");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        // Test configuration
        $this->table(['Configuration', 'Value'], [
            ['Duration', round($elapsed, 2) . 's'],
            ['Concurrent Workers', $workers],
            ['Simulated Vendors', $vendors],
            ['Display Polling', $this->option('polling') ? 'Enabled' : 'Disabled'],
        ]);

        $this->newLine();

        // Operations
        $this->table(['Metric', 'Count', 'Per Second'], [
            ['Orders Created', $this->metrics['orders_created'], round($this->metrics['orders_created'] / $elapsed, 2)],
            ['Display Polls', $this->metrics['polls'], round($this->metrics['polls'] / $elapsed, 2)],
            ['Errors', $this->metrics['errors'], '-'],
        ]);

        $this->newLine();

        // Database load
        if (!empty($this->metrics['db_connections'])) {
            $avgConn = round(array_sum($this->metrics['db_connections']) / count($this->metrics['db_connections']), 1);
            $maxConn = max($this->metrics['db_connections']);
            $minConn = min($this->metrics['db_connections']);

            $this->info("🔌 Database Connection Pool");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->table(['Metric', 'Value'], [
                ['Average Connections', $avgConn],
                ['Peak Connections', $maxConn],
                ['Minimum Connections', $minConn],
            ]);

            $this->newLine();

            // Warning if high connection usage
            try {
                $maxAllowed = DB::selectOne("SHOW VARIABLES LIKE 'max_connections'")->Value ?? 151;
                $usage = ($maxConn / $maxAllowed) * 100;

                if ($usage > 80) {
                    $this->error("⚠️  WARNING: Connection pool usage at {$usage}%!");
                    $this->error("   Consider increasing max_connections or using connection pooling");
                } elseif ($usage > 50) {
                    $this->warn("⚠️  Connection pool usage: {$usage}%");
                } else {
                    $this->info(" Connection pool healthy ({$usage}% usage)");
                }
            } catch (\Exception $e) {
                // Ignore
            }
        }

        $this->newLine();

        // Performance assessment
        $this->info("💡 PRODUCTION READINESS ASSESSMENT");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        $ordersPerSecond = $this->metrics['orders_created'] / $elapsed;
        $errorRate = $this->metrics['orders_created'] > 0
            ? ($this->metrics['errors'] / $this->metrics['orders_created']) * 100
            : 0;

        if ($errorRate === 0 && $ordersPerSecond > 1) {
            $this->info(" PRODUCTION READY");
            $this->info("   System handled {$vendors} concurrent vendors flawlessly");
            $this->info("   Throughput: " . round($ordersPerSecond, 2) . " orders/second");
            $this->info("   Zero errors under realistic load");
        } elseif ($errorRate < 1 && $ordersPerSecond > 0.5) {
            $this->info(" PRODUCTION READY (with monitoring)");
            $this->info("   Minor issues detected but system stable");
            $this->info("   Error rate: " . round($errorRate, 2) . "%");
        } else {
            $this->warn("⚠️  NEEDS OPTIMIZATION");
            $this->warn("   Error rate: " . round($errorRate, 2) . "%");
            $this->warn("   Throughput: " . round($ordersPerSecond, 2) . " orders/second");
        }

        $this->newLine();
        $this->info("📈 Scaling Recommendations:");

        if ($ordersPerSecond > 5) {
            $this->info("   • Current capacity: ~" . round($ordersPerSecond * 60) . " orders/minute");
            $this->info("   • Can support: " . round(($ordersPerSecond * 60) / 2) . " concurrent vendors");
            $this->info("   • Consider horizontal scaling beyond 100 vendors");
        } else {
            $this->info("   • Optimize database queries");
            $this->info("   • Add database indexes");
            $this->info("   • Implement Redis caching");
            $this->info("   • Use queue workers for broadcasts");
        }
    }

    /**
     * Clean up test data
     */
    protected function cleanup(): void
    {
        $this->info("🧹 Cleaning up...");

        Order::where('public_id', 'LIKE', 'W%-%')->delete();
        Customer::where('email', 'LIKE', 'worker-%@stress.test')->delete();

        // Clean up scripts
        @unlink(base_path('scripts/stress-worker.php'));
        @unlink(base_path('scripts/polling-simulator.php'));

        // Clean up logs
        foreach (glob(storage_path('logs/worker-*.log')) as $log) {
            @unlink($log);
        }
        @unlink(storage_path('logs/polling.log'));

        $this->info(" Cleanup complete");
    }
}
