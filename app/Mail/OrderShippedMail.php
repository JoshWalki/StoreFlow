<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShippedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order
    ) {
        // Load necessary relationships including product images
        $this->order->load([
            'store',
            'items.product.primaryImage',
            'items.product.images',
            'items.addons',
            'customer'
        ]);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Order Shipped - ' . $this->order->store->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.shipped',
            with: [
                'order' => $this->order,
                'store' => $this->order->store,
                'items' => $this->order->items,
                'subtotal' => $this->order->items_total_cents / 100,
                'discount' => $this->order->discount_cents / 100,
                'tax' => $this->order->tax_cents / 100,
                'shipping' => $this->order->shipping_cost_cents / 100,
                'total' => $this->order->total_cents / 100,
                'trackingCode' => $this->order->tracking_code,
                'trackingUrl' => $this->order->tracking_url,
                'headerSubtitle' => 'Order Shipped',
            ],
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
