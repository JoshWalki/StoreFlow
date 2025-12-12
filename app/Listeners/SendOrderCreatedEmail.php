<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderCreatedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class SendOrderCreatedEmail implements ShouldQueue
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
    public function handle(OrderCreated $event): void
    {
        try {
            // Load necessary relationships
            $event->order->load(['customer', 'store', 'items.product']);

            // Send email to customer
            Mail::to($event->order->customer->email)
                ->send(new OrderCreatedMail($event->order));

            \Log::info('Order created email sent successfully', [
                'order_id' => $event->order->id,
                'customer_email' => $event->order->customer->email,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send order created email', [
                'order_id' => $event->order->id,
                'customer_email' => $event->order->customer->email ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            // Don't re-throw - log error but don't break checkout flow
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        // Log the failure or notify administrators
        \Log::error('Failed to send order created email', [
            'order_id' => $event->order->id,
            'customer_email' => $event->order->customer->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
