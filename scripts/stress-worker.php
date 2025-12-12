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