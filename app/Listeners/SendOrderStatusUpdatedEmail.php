<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusUpdatedEmail implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderStatusUpdated $event): void
    {
        // Load necessary relationships
        $event->order->load(['customer', 'store', 'items.product']);

        // Only send email for important status changes
        $emailableStatuses = [
            Order::STATUS_READY_FOR_PICKUP,
            Order::STATUS_SHIPPED,
            Order::STATUS_DELIVERED,
            Order::STATUS_PICKED_UP,
            Order::STATUS_CANCELLED,
        ];

        if (in_array($event->newStatus, $emailableStatuses)) {
            Mail::to($event->order->customer->email)
                ->send(new OrderStatusUpdatedMail(
                    $event->order,
                    $event->oldStatus,
                    $event->newStatus
                ));
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderStatusUpdated $event, \Throwable $exception): void
    {
        // Log the failure or notify administrators
        \Log::error('Failed to send order status updated email', [
            'order_id' => $event->order->id,
            'customer_email' => $event->order->customer->email,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
            'error' => $exception->getMessage(),
        ]);
    }
}
