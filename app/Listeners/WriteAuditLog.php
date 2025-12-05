<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Events\OrderStatusUpdated;
use App\Events\ShippingStatusUpdated;
use App\Services\AuditLogger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class WriteAuditLog implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle OrderCreated event.
     *
     * @param OrderCreated $event
     * @return void
     */
    public function handleOrderCreated(OrderCreated $event): void
    {
        AuditLogger::log(
            'Order',
            $event->order->id,
            'order_created',
            [
                'public_id' => $event->order->public_id,
                'customer_id' => $event->order->customer_id,
                'store_id' => $event->order->store_id,
                'fulfilment_type' => $event->order->fulfilment_type,
                'status' => $event->order->status,
                'total_cents' => $event->order->total_cents,
            ]
        );
    }

    /**
     * Handle OrderStatusUpdated event.
     *
     * @param OrderStatusUpdated $event
     * @return void
     */
    public function handleOrderStatusUpdated(OrderStatusUpdated $event): void
    {
        AuditLogger::logStatusChange(
            'Order',
            $event->order->id,
            $event->oldStatus,
            $event->newStatus,
            [
                'public_id' => $event->order->public_id,
                'customer_id' => $event->order->customer_id,
                'store_id' => $event->order->store_id,
                'fulfilment_type' => $event->order->fulfilment_type,
            ]
        );
    }

    /**
     * Handle ShippingStatusUpdated event.
     *
     * @param ShippingStatusUpdated $event
     * @return void
     */
    public function handleShippingStatusUpdated(ShippingStatusUpdated $event): void
    {
        AuditLogger::log(
            'Order',
            $event->order->id,
            'shipping_status_updated',
            [
                'public_id' => $event->order->public_id,
                'old_shipping_status' => $event->oldShippingStatus,
                'new_shipping_status' => $event->newShippingStatus,
                'tracking_code' => $event->order->tracking_code,
                'tracking_url' => $event->order->tracking_url,
                'shipping_method' => $event->order->shipping_method,
            ]
        );
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param \Illuminate\Events\Dispatcher $events
     * @return array
     */
    public function subscribe($events): array
    {
        return [
            OrderCreated::class => 'handleOrderCreated',
            OrderStatusUpdated::class => 'handleOrderStatusUpdated',
            ShippingStatusUpdated::class => 'handleShippingStatusUpdated',
        ];
    }
}
