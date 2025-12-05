<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\OrderPlacedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderPlacedEmail implements ShouldQueue
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
            // Ensure the order has a customer email
            if (empty($event->order->customer_email)) {
                Log::warning('Order placed email not sent: No customer email', [
                    'order_id' => $event->order->id,
                    'public_id' => $event->order->public_id,
                ]);
                return;
            }

            // Send the order placed email
            Mail::to($event->order->customer_email)
                ->send(new OrderPlacedMail($event->order));

            Log::info('Order placed email sent successfully', [
                'order_id' => $event->order->id,
                'public_id' => $event->order->public_id,
                'customer_email' => $event->order->customer_email,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order placed email', [
                'order_id' => $event->order->id,
                'public_id' => $event->order->public_id,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to allow queue retry mechanism
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderCreated $event, \Throwable $exception): void
    {
        Log::error('Order placed email job failed permanently', [
            'order_id' => $event->order->id,
            'public_id' => $event->order->public_id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
