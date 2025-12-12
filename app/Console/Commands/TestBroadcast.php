<?php

namespace App\Console\Commands;

use App\Events\OrderCreated;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestBroadcast extends Command
{
    protected $signature = 'test:broadcast {order_id}';
    protected $description = 'Test broadcasting an OrderCreated event';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        $order = Order::with(['items.product', 'items.addons', 'customer'])->find($orderId);

        if (!$order) {
            $this->error("Order {$orderId} not found!");
            return 1;
        }

        $this->info("Broadcasting OrderCreated for order {$orderId} on channel: private-store.{$order->store_id}.orders");

        Log::info('Manual test broadcast', [
            'order_id' => $order->id,
            'store_id' => $order->store_id,
            'channel' => "private-store.{$order->store_id}.orders"
        ]);

        event(new OrderCreated($order));

        $this->info("Event broadcast complete! Check dashboard for real-time update.");

        return 0;
    }
}
