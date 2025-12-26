<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RefundProcessedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public Order $order,
        public OrderItem $refundedItem,
        public string $refundId,
        public int $refundAmountCents
    ) {
        // Load necessary relationships including product images
        $this->order->load(['store', 'customer']);
        $this->refundedItem->load(['product.primaryImage', 'product.images', 'addons']);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Refund Processed - Order #' . $this->order->public_id . ' - ' . $this->order->store->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.orders.refund-processed',
            with: [
                'order' => $this->order,
                'store' => $this->order->store,
                'refundedItem' => $this->refundedItem,
                'refundId' => $this->refundId,
                'refundAmount' => $this->refundAmountCents / 100,
                'refundReason' => $this->refundedItem->refund_reason,
                'headerSubtitle' => 'Refund Processed',
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
