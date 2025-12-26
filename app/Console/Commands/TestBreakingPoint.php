<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestBreakingPoint extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:breaking-point
                            {--store=8 : Store ID to test with}
                            {--start-vendors=5 : Starting number of vendors}
                            {--increment=5 : Vendor increase per stage}
                            {--stage-duration=60 : Seconds per load stage}
                            {--polling : Enable display polling}
                            {--failure-threshold=10 : Error rate % to consider failure}
                            {--max-vendors=500 : Maximum vendors to test (safety limit)}';

    /**
     * The console command description.
     */
    protected $description = 'Ramp up load until system breaks - finds maximum capacity';

    protected array $stageMetrics = [];
    protected array $workerPids = [];
    protected bool $systemFailed = false;
    protected ?string $failureReason = null;
    protected int $peakVendors = 0;
    protected float $peakThroughput = 0;
    protected int $peakQPS = 0;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $storeId = $this->option('store');
        $startVendors = $this->option('start-vendors');
        $increment = $this->option('increment');
        $stageDuration = $this->option('stage-duration');
        $failureThreshold = $this->option('failure-threshold');
        $maxVendors = $this->option('max-vendors');

        $this->info("🔥 BREAKING POINT TEST");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("Strategy: Ramp up load until system fails");
        $this->info("Starting Load: {$startVendors} vendors");
        $this->info("Increment: +{$increment} vendors per stage");
        $this->info("Stage Duration: {$stageDuration}s");
        $this->info("Failure Threshold: {$failureThreshold}% error rate");
        $this->info("Safety Limit: {$maxVendors} vendors");
        $this->newLine();

        // Validate store
        $store = Store::find($storeId);
        if (!$store) {
            $this->error("❌ Store not found!");
            return 1;
        }

        // Check system resources
        $this->checkInitialResources();
        $this->newLine();

        $this->warn("⚠️  This test will push your system to failure!");
        $this->warn("   It may cause temporary system instability.");
        if (!$this->confirm('Continue?', false)) {
            return 0;
        }

        $this->newLine();
        $this->info("🚀 Starting ramp-up test...");
        $this->newLine();

        // Run ramp-up stages
        $currentVendors = $startVendors;
        $stage = 1;

        while ($currentVendors <= $maxVendors && !$this->systemFailed) {
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("📊 STAGE {$stage}: {$currentVendors} Concurrent Vendors");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

            // Calculate workers (1 worker per 3 vendors)
            $workers = max(2, ceil($currentVendors / 3));

            // Run stage
            $metrics = $this->runStage(
                $store,
                $currentVendors,
                $workers,
                $stageDuration,
                $stage
            );

            // Store metrics
            $this->stageMetrics[$stage] = $metrics;

            // Check for failure
            if ($this->detectFailure($metrics, $failureThreshold)) {
                $this->systemFailed = true;
                $this->info("🔴 SYSTEM FAILURE DETECTED AT STAGE {$stage}");
                break;
            }

            // Update peaks
            if ($metrics['throughput'] > $this->peakThroughput) {
                $this->peakThroughput = $metrics['throughput'];
                $this->peakVendors = $currentVendors;
            }

            $this->info(" Stage {$stage} completed successfully");
            $this->newLine();

            // Next stage
            $currentVendors += $increment;
            $stage++;

            // Brief cooldown between stages
            sleep(5);
        }

        // Display results
        $this->displayBreakingPointAnalysis();

        // Cleanup
        $this->cleanup();

        return 0;
    }

    /**
     * Check initial system resources
     */
    protected function checkInitialResources(): void
    {
        $this->info("🔍 Initial System Check:");

        try {
            $maxConn = DB::selectOne("SHOW VARIABLES LIKE 'max_connections'")->Value ?? 151;
            $currentConn = DB::selectOne("SHOW STATUS LIKE 'Threads_connected'")->Value ?? 0;
            $this->info("   MySQL: {$currentConn}/{$maxConn} connections");

            $memLimit = ini_get('memory_limit');
            $memUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
            $this->info("   PHP Memory: {$memUsage}MB / {$memLimit}");
        } catch (\Exception $e) {
            $this->warn("   Could not check resources");
        }
    }

    /**
     * Run a single load stage
     */
    protected function runStage(Store $store, int $vendors, int $workers, int $duration, int $stage): array
    {
        $stageStart = microtime(true);

        // Start workers
        $this->startStageWorkers($store, $vendors, $workers, $duration, $stage);

        // Monitor stage
        $metrics = $this->monitorStage($duration);

        // Wait for workers
        sleep(5);

        // Collect results
        $results = $this->collectStageResults($stage, $workers);

        $stageEnd = microtime(true);
        $elapsed = $stageEnd - $stageStart;

        // Calculate metrics
        return [
            'vendors' => $vendors,
            'workers' => $workers,
            'duration' => $elapsed,
            'orders_created' => $results['orders'],
            'errors' => $results['errors'],
            'error_rate' => $results['orders'] > 0 ? ($results['errors'] / $results['orders']) * 100 : 0,
            'throughput' => $results['orders'] / $elapsed,
            'avg_connections' => !empty($metrics['connections']) ? array_sum($metrics['connections']) / count($metrics['connections']) : 0,
            'peak_connections' => !empty($metrics['connections']) ? max($metrics['connections']) : 0,
            'queries' => $results['queries'] ?? 0,
            'qps' => isset($results['queries']) ? $results['queries'] / $elapsed : 0,
        ];
    }

    /**
     * Start workers for this stage
     */
    protected function startStageWorkers(Store $store, int $vendors, int $workers, int $duration, int $stage): void
    {
        $ordersPerWorker = ceil($vendors / $workers);
        $this->workerPids = [];

        for ($i = 0; $i < $workers; $i++) {
            $workerScript = base_path('scripts/stress-worker.php');
            $logFile = storage_path("logs/stage-{$stage}-worker-{$i}.log");

            $this->ensureWorkerScript($workerScript);

            $cmd = sprintf(
                'php %s --store=%d --orders=%d --duration=%d --worker-id=%d > %s 2>&1 &',
                $workerScript,
                $store->id,
                $ordersPerWorker,
                $duration,
                $i,
                $logFile
            );

            exec($cmd);
            $this->workerPids[$i] = $logFile;
            usleep(50000); // Stagger by 50ms
        }
    }

    /**
     * Monitor stage with progress bar
     */
    protected function monitorStage(int $duration): array
    {
        $metrics = ['connections' => []];

        $progressBar = $this->output->createProgressBar($duration);
        $progressBar->setFormat(' %current%s/%max%s [%bar%] %percent:3s%% | Conn: %message%');
        $progressBar->start();

        for ($i = 0; $i < $duration; $i++) {
            try {
                $conn = DB::selectOne("SHOW STATUS LIKE 'Threads_connected'")->Value ?? 0;
                $metrics['connections'][] = (int) $conn;
                $progressBar->setMessage($conn);
            } catch (\Exception $e) {
                $progressBar->setMessage('err');
            }

            $progressBar->advance();
            sleep(1);
        }

        $progressBar->finish();
        $this->newLine();

        return $metrics;
    }

    /**
     * Collect results from worker logs
     */
    protected function collectStageResults(int $stage, int $workers): array
    {
        $orders = 0;
        $errors = 0;

        foreach ($this->workerPids as $id => $logFile) {
            if (file_exists($logFile)) {
                $log = file_get_contents($logFile);

                if (preg_match('/Completed: (\d+) orders/', $log, $matches)) {
                    $orders += (int) $matches[1];
                }

                if (preg_match('/(\d+) errors/', $log, $matches)) {
                    $errors += (int) $matches[1];
                }
            }
        }

        return [
            'orders' => $orders,
            'errors' => $errors,
        ];
    }

    /**
     * Detect if system has failed
     */
    protected function detectFailure(array $metrics, float $threshold): bool
    {
        // Check error rate
        if ($metrics['error_rate'] > $threshold) {
            $this->failureReason = sprintf(
                "High error rate: %.1f%% (threshold: %.1f%%)",
                $metrics['error_rate'],
                $threshold
            );
            return true;
        }

        // Check connection pool exhaustion
        try {
            $maxConn = DB::selectOne("SHOW VARIABLES LIKE 'max_connections'")->Value ?? 151;
            $usage = ($metrics['peak_connections'] / $maxConn) * 100;

            if ($usage > 95) {
                $this->failureReason = sprintf(
                    "Connection pool exhausted: %.1f%% usage",
                    $usage
                );
                return true;
            }
        } catch (\Exception $e) {
            // Ignore
        }

        // Check throughput degradation (if we have previous stages)
        if (count($this->stageMetrics) >= 2) {
            $stages = array_values($this->stageMetrics);
            $previous = end($stages);
            $current = $metrics;

            // If throughput drops by more than 30%, system is struggling
            if ($previous['throughput'] > 0) {
                $degradation = (($previous['throughput'] - $current['throughput']) / $previous['throughput']) * 100;

                if ($degradation > 30) {
                    $this->failureReason = sprintf(
                        "Throughput degradation: %.1f%% drop from previous stage",
                        $degradation
                    );
                    return true;
                }
            }
        }

        // Check if no orders completed (complete failure)
        if ($metrics['orders_created'] === 0) {
            $this->failureReason = "Zero orders completed - system unresponsive";
            return true;
        }

        return false;
    }

    /**
     * Display comprehensive breaking point analysis
     */
    protected function displayBreakingPointAnalysis(): void
    {
        $this->newLine(2);
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->info("🎯 BREAKING POINT ANALYSIS");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->newLine();

        // Stage-by-stage results
        $tableData = [];
        foreach ($this->stageMetrics as $stage => $metrics) {
            $status = $metrics['error_rate'] > 0 ? '⚠️' : '';
            $tableData[] = [
                "Stage {$stage}",
                $metrics['vendors'],
                $metrics['workers'],
                round($metrics['throughput'], 2) . '/s',
                round($metrics['error_rate'], 1) . '%',
                round($metrics['peak_connections']),
                $status,
            ];
        }

        $this->table(
            ['Stage', 'Vendors', 'Workers', 'Throughput', 'Error Rate', 'Peak Conn', 'Status'],
            $tableData
        );

        $this->newLine();

        // Breaking point summary
        if ($this->systemFailed) {
            $failedStage = end($this->stageMetrics);

            $this->error("🔴 SYSTEM BREAKING POINT REACHED");
            $this->error("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->error("   Failed at: {$failedStage['vendors']} concurrent vendors");
            $this->error("   Reason: {$this->failureReason}");
            $this->error("   Error Rate: " . round($failedStage['error_rate'], 1) . "%");
            $this->error("   Throughput: " . round($failedStage['throughput'], 2) . " orders/s");
            $this->newLine();

            $this->info(" MAXIMUM SAFE CAPACITY");
            $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
            $this->info("   Vendors: {$this->peakVendors} (safe operational limit)");
            $this->info("   Peak Throughput: " . round($this->peakThroughput, 2) . " orders/s");
            $this->info("   Recommended Max: " . floor($this->peakVendors * 0.7) . " vendors (70% capacity)");
        } else {
            $this->info(" NO BREAKING POINT FOUND");
            $this->info("   System stable up to {$this->option('max-vendors')} vendors");
            $this->info("   Peak Throughput: " . round($this->peakThroughput, 2) . " orders/s");
        }

        $this->newLine();

        // Performance curve
        $this->info("📈 PERFORMANCE CURVE");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        foreach ($this->stageMetrics as $stage => $metrics) {
            $bar = str_repeat('█', (int) ($metrics['throughput'] * 10));
            $this->line(sprintf(
                "  %3d vendors: %s %.2f/s",
                $metrics['vendors'],
                $bar,
                $metrics['throughput']
            ));
        }

        $this->newLine();

        // Recommendations
        $this->info("💡 CAPACITY PLANNING RECOMMENDATIONS");
        $this->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");

        if ($this->peakVendors < 30) {
            $this->warn("   ⚠️  System capacity is limited");
            $this->info("   1. Optimize database queries (add indexes)");
            $this->info("   2. Implement Redis caching");
            $this->info("   3. Use queue workers for background jobs");
            $this->info("   4. Consider database read replicas");
        } elseif ($this->peakVendors < 100) {
            $this->info("    Good capacity for small-medium deployment");
            $this->info("   • Safe for up to " . floor($this->peakVendors * 0.7) . " concurrent vendors");
            $this->info("   • Monitor during peak hours");
            $this->info("   • Plan horizontal scaling beyond " . $this->peakVendors . " vendors");
        } else {
            $this->info("    EXCELLENT - Production ready for large scale");
            $this->info("   • Supports 100+ concurrent vendors");
            $this->info("   • System is well-optimized");
            $this->info("   • Can handle significant growth");
        }
    }

    /**
     * Ensure worker script exists
     */
    protected function ensureWorkerScript(string $path): void
    {
        if (file_exists($path)) {
            return;
        }

        // Use the same worker script as TestProductionLoad
        $command = new \App\Console\Commands\TestProductionLoad();
        $reflection = new \ReflectionClass($command);
        $method = $reflection->getMethod('ensureWorkerScript');
        $method->setAccessible(true);
        $method->invoke($command, $path);
    }

    /**
     * Cleanup test data and logs
     */
    protected function cleanup(): void
    {
        $this->info("🧹 Cleaning up...");

        // Clean orders
        Order::where('public_id', 'LIKE', 'W%-%')->delete();

        // Clean customers
        Customer::where('email', 'LIKE', 'worker-%@stress.test')->delete();

        // Clean logs
        foreach (glob(storage_path('logs/stage-*.log')) as $log) {
            @unlink($log);
        }

        $this->info(" Cleanup complete");
    }
}
