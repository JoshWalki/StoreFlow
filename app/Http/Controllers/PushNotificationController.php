<?php

namespace App\Http\Controllers;

use App\Models\PushSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PushNotificationController extends Controller
{
    /**
     * Get the VAPID public key for push subscription
     */
    public function getVapidKey()
    {
        return response()->json([
            'public_key' => config('webpush.vapid.public_key'),
        ]);
    }

    /**
     * Subscribe user to push notifications
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'subscription' => 'required|array',
            'subscription.endpoint' => 'required|string',
            'subscription.keys' => 'required|array',
            'subscription.keys.p256dh' => 'required|string',
            'subscription.keys.auth' => 'required|string',
        ]);

        $subscriptionData = $request->input('subscription');

        // Create or update subscription
        PushSubscription::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'endpoint' => $subscriptionData['endpoint'],
            ],
            [
                'public_key' => $subscriptionData['keys']['p256dh'],
                'auth_token' => $subscriptionData['keys']['auth'],
                'content_encoding' => $subscriptionData['expirationTime'] ?? 'aesgcm',
            ]
        );

        return response()->json(['message' => 'Subscription saved successfully']);
    }

    /**
     * Unsubscribe user from push notifications
     */
    public function unsubscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::where('user_id', Auth::id())
            ->where('endpoint', $request->input('endpoint'))
            ->delete();

        return response()->json(['message' => 'Unsubscribed successfully']);
    }

    /**
     * Send a push notification to a user
     */
    public static function sendPushNotification($userId, $title, $body, $data = [])
    {
        try {
            $subscriptions = PushSubscription::where('user_id', $userId)->get();

            if ($subscriptions->isEmpty()) {
                return false;
            }

            $auth = [
                'VAPID' => [
                    'subject' => config('app.url'),
                    'publicKey' => config('webpush.vapid.public_key'),
                    'privateKey' => config('webpush.vapid.private_key'),
                ],
            ];

            $webPush = new WebPush($auth);

            $payload = json_encode([
                'title' => $title,
                'body' => $body,
                'data' => $data,
                'tag' => 'storeflow-' . ($data['order_id'] ?? time()),
                'sound' => $data['sound'] ?? '/sounds/notification.wav',
            ]);

            foreach ($subscriptions as $sub) {
                $subscription = Subscription::create($sub->getSubscriptionArray());
                $webPush->queueNotification($subscription, $payload);
            }

            // Send all queued notifications
            $results = $webPush->flush();

            // Remove expired subscriptions
            foreach ($results as $result) {
                if (!$result->isSuccess() && $result->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $result->getEndpoint())->delete();
                }
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Push notification error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test push notification (for development)
     */
    public function test()
    {
        self::sendPushNotification(
            Auth::id(),
            'Test Notification',
            'This is a test push notification from StoreFlow',
            ['url' => '/dashboard']
        );

        return response()->json(['message' => 'Test notification sent']);
    }
}
