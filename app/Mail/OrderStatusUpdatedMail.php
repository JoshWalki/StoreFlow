<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = match ($this->newStatus) {
            Order::STATUS_READY_FOR_PICKUP => 'Order Ready for Pickup - #' . $this->order->public_id,
            Order::STATUS_SHIPPED => 'Order Shipped - #' . $this->order->public_id,
            Order::STATUS_DELIVERED => 'Order Delivered - #' . $this->order->public_id,
            Order::STATUS_PICKED_UP => 'Order Picked Up - #' . $this->order->public_id,
            Order::STATUS_CANCELLED => 'Order Cancelled - #' . $this->order->public_id,
            default => 'Order Status Updated - #' . $this->order->public_id,
        };

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.status-updated',
            with: [
                'order' => $this->order,
                'store' => $this->order->store,
                'customer' => $this->order->customer,
                'items' => $this->order->items()->with('product')->get(),
                'oldStatus' => $this->oldStatus,
                'newStatus' => $this->newStatus,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
