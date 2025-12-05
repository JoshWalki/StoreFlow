<?php

namespace App\Listeners;

use App\Events\OrderStatusUpdated;
use App\Mail\OrderCompletedMail;
use App\Mail\OrderReadyForPickupMail;
use App\Mail\OrderShippedMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderStatusEmail implements ShouldQueue
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
        try {
            // Ensure the order has a customer email
            if (empty($event->order->customer_email)) {
                Log::warning('Order status email not sent: No customer email', [
                    'order_id' => $event->order->id,
                    'public_id' => $event->order->public_id,
                    'status' => $event->newStatus,
                ]);
                return;
            }

            // Determine which email to send based on the new status
            $mailable = $this->getMailableForStatus($event->order, $event->newStatus);

            if (!$mailable) {
                Log::debug('No email configured for order status', [
                    'order_id' => $event->order->id,
                    'public_id' => $event->order->public_id,
                    'old_status' => $event->oldStatus,
                    'new_status' => $event->newStatus,
                ]);
                return;
            }

            // Send the appropriate email
            Mail::to($event->order->customer_email)
                ->send($mailable);

            Log::info('Order status email sent successfully', [
                'order_id' => $event->order->id,
                'public_id' => $event->order->public_id,
                'customer_email' => $event->order->customer_email,
                'status' => $event->newStatus,
                'email_type' => class_basename($mailable),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send order status email', [
                'order_id' => $event->order->id,
                'public_id' => $event->order->public_id,
                'status' => $event->newStatus,
                'error' => $e->getMessage(),
            ]);

            // Re-throw to allow queue retry mechanism
            throw $e;
        }
    }

    /**
     * Get the appropriate mailable for the order status.
     *
     * @param Order $order
     * @param string $status
     * @return \Illuminate\Mail\Mailable|null
     */
    protected function getMailableForStatus(Order $order, string $status): ?\Illuminate\Mail\Mailable
    {
        return match ($status) {
            Order::STATUS_READY_FOR_PICKUP => new OrderReadyForPickupMail($order),
            Order::STATUS_SHIPPED => new OrderShippedMail($order),
            Order::STATUS_PICKED_UP, Order::STATUS_DELIVERED => new OrderCompletedMail($order),
            default => null,
        };
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderStatusUpdated $event, \Throwable $exception): void
    {
        Log::error('Order status email job failed permanently', [
            'order_id' => $event->order->id,
            'public_id' => $event->order->public_id,
            'status' => $event->newStatus,
            'exception' => $exception->getMessage(),
        ]);
    }
}
