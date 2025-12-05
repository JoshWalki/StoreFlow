<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {}

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('store.' . $this->order->store_id . '.orders'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'OrderStatusUpdated';
    }

    /**
     * Get the data to broadcast.
     */
    public function broadcastWith(): array
    {
        return [
            'order' => [
                'id' => $this->order->id,
                'public_id' => $this->order->public_id,
                'status' => $this->order->status,
                'fulfilment_type' => $this->order->fulfilment_type,
                'payment_status' => $this->order->payment_status,
                'total_cents' => $this->order->total_cents,
                'items_total_cents' => $this->order->items_total_cents,
                'shipping_cost_cents' => $this->order->shipping_cost_cents,
                'customer_name' => $this->order->customer->first_name . ' ' . $this->order->customer->last_name,
                'customer_email' => $this->order->customer->email,
                'customer_phone' => $this->order->customer->phone ?? '',
                'pickup_time' => $this->order->pickup_time,
                'shipping_name' => $this->order->shipping_name,
                'line1' => $this->order->line1,
                'line2' => $this->order->line2,
                'city' => $this->order->city,
                'state' => $this->order->state,
                'postcode' => $this->order->postcode,
                'country' => $this->order->country,
                'tracking_code' => $this->order->tracking_code,
                'tracking_url' => $this->order->tracking_url,
                'items' => $this->order->items->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'product_id' => $item->product_id,
                        'product_name' => $item->product->name ?? 'Unknown Product',
                        'quantity' => $item->qty,
                        'price_cents' => $item->unit_price_cents,
                        'total_cents' => $item->qty * $item->unit_price_cents,
                    ];
                }),
                'created_at' => $this->order->created_at,
                'updated_at' => $this->order->updated_at,
            ],
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
        ];
    }
}
