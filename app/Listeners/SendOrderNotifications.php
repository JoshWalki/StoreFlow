<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Http\Controllers\PushNotificationController;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderNotifications implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;
        $store = $order->store;

        if (!$store) {
            return;
        }

        // Get all users who have access to this store
        $users = $store->users()->get();

        foreach ($users as $user) {
            // Determine sound based on order type
            $sound = $order->fulfilment_type === 'shipping'
                ? '/sounds/bell.mp3'
                : '/sounds/notification.wav';

            // Send push notification
            PushNotificationController::sendPushNotification(
                $user->id,
                'New Order!',
                "Order #{$order->public_id} from {$order->customer_name}",
                [
                    'order_id' => $order->id,
                    'public_id' => $order->public_id,
                    'url' => '/dashboard',
                    'sound' => $sound,
                ]
            );
        }
    }
}
