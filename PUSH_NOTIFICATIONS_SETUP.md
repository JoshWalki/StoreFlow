# Push Notifications Setup Guide for StoreFlow PWA

## Overview
This guide will help you set up push notifications for StoreFlow PWA so users receive order notifications even when the app is closed.

## Files Created

### Backend
1. ✅ `/app/Models/PushSubscription.php` - Model for storing push subscriptions
2. ✅ `/app/Http/Controllers/PushNotificationController.php` - Controller for managing push notifications  
3. ✅ `/database/migrations/2025_12_27_012030_create_push_subscriptions_table.php` - Database migration
4. ✅ `/config/webpush.php` - Configuration file for web push settings
5. ✅ Routes added to `/routes/web.php`

### Frontend
6. ✅ `/public/sw.js` - Updated service worker with push notification handlers
7. ✅ `/resources/js/Composables/usePushNotifications.js` - Vue composable for managing subscriptions
8. ⏳ `/public/manifest.json` - Already exists, no changes needed

## Setup Instructions

### Step 1: Install Dependencies

```bash
cd /mnt/c/xampp/htdocs/storeflow

# Install web-push PHP library
composer require minishlink/web-push
```

### Step 2: Generate VAPID Keys

```bash
# Run the setup script
./setup-push-notifications.sh

# OR manually generate keys:
php artisan tinker
> use Minishlink\WebPush\VAPID;
> $keys = VAPID::createVapidKeys();
> echo "VAPID_PUBLIC_KEY=" . $keys['publicKey'] . "\n";
> echo "VAPID_PRIVATE_KEY=" . $keys['privateKey'] . "\n";
```

### Step 3: Update .env Files

Add the generated VAPID keys to your `.env` files (both local and production):

```env
# Push Notifications
VAPID_PUBLIC_KEY=your_generated_public_key_here
VAPID_PRIVATE_KEY=your_generated_private_key_here
```

### Step 4: Run Migration

```bash
php artisan migrate
```

### Step 5: Integrate with Order Events

Update `/app/Listeners/SendOrderNotifications.php` (or create it) to send push notifications:

```php
<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Http\Controllers\PushNotificationController;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderNotifications implements ShouldQueue
{
    public function handle(OrderCreated $event)
    {
        $order = $event->order;
        $store = $order->store;
        
        // Get all users who have access to this store
        $users = $store->users; // Adjust based on your relationship
        
        foreach ($users as $user) {
            // Determine sound based on order type
            $sound = $order->fulfilment_type === 'shipping' 
                ? '/sounds/bell.mp3' 
                : '/sounds/notification.wav';
            
            PushNotificationController::sendPushNotification(
                $user->id,
                'New Order!',
                "Order #{$order->public_id} from {$order->customer_name}",
                [
                    'order_id' => $order->id,
                    'url' => '/dashboard',
                    'sound' => $sound,
                ]
            );
        }
    }
}
```

Register the listener in `/app/Providers/EventServiceProvider.php`:

```php
use App\Events\OrderCreated;
use App\Listeners\SendOrderNotifications;

protected $listen = [
    OrderCreated::class => [
        SendOrderNotifications::class,
    ],
];
```

### Step 6: Update Dashboard UI

Add push notification toggle to DashboardLayout.vue (around line 1600, near the sound notification toggle):

```vue
<template>
  <!-- Add after the sound notification toggle -->
  <div class="flex items-center justify-between mb-3 mt-4 pt-4 border-t border-gray-700">
    <div class="flex-1">
      <p class="text-sm text-gray-300">
        Push Notifications
      </p>
      <p class="text-xs text-gray-400">
        Receive notifications when app is closed
      </p>
    </div>

    <button
      @click="togglePushNotifications"
      :class="[
        pushEnabled ? 'bg-green-600' : 'bg-gray-600',
        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out'
      ]"
    >
      <span
        :class="[
          pushEnabled ? 'translate-x-6' : 'translate-x-1',
          'inline-block h-4 w-4 transform rounded-full bg-white transition duration-200 ease-in-out mt-1'
        ]"
      />
    </button>
  </div>
</template>

<script setup>
import { usePushNotifications } from '@/Composables/usePushNotifications';

const { 
  isSupported: pushSupported, 
  isSubscribed: pushEnabled, 
  subscribe: subscribePush,
  unsubscribe: unsubscribePush,
  checkSubscription 
} = usePushNotifications();

// Check subscription status on mount
onMounted(() => {
  checkSubscription();
});

const togglePushNotifications = async () => {
  try {
    if (pushEnabled.value) {
      await unsubscribePush();
      toast.success('Push notifications disabled');
    } else {
      await subscribePush();
      toast.success('Push notifications enabled!');
    }
  } catch (error) {
    toast.error('Failed to update push notifications: ' + error.message);
  }
};
</script>
```

### Step 7: Clear Caches

```bash
php artisan config:cache
php artisan route:cache
```

### Step 8: Rebuild Frontend

```bash
npm run build
```

## Testing

### Test Push Notification

1. Open dashboard and enable push notifications
2. Open browser console and run:

```javascript
fetch('/api/push/test', {
  method: 'POST',
  headers: {
    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
  },
}).then(r => r.json()).then(console.log);
```

3. You should receive a test notification

### Test with Real Order

1. Enable push notifications in dashboard
2. Close the PWA (or put in background)
3. Create a new order
4. Push notification should appear even though app is closed!

## Production Deployment

1. Update production .env with VAPID keys
2. Run migration: `php artisan migrate`
3. Install composer package: `composer require minishlink/web-push`
4. Deploy updated code
5. Clear caches: `php artisan config:cache && php artisan route:cache`

## Troubleshooting

### Notifications not appearing
- Check browser notification permissions: `Settings > Site Settings > Notifications`
- Verify VAPID keys are set in .env
- Check browser console for errors
- Ensure service worker is registered

### Subscription failing
- Check that HTTPS is enabled (required for push notifications)
- Verify `/api/push/vapid-key` returns a valid public key
- Check browser compatibility (Chrome 50+, Firefox 44+, Safari 16+)

### Push delivery failing
- Check Laravel logs: `storage/logs/laravel.log`
- Verify web-push library is installed: `composer show minishlink/web-push`
- Check that user has active subscriptions in `push_subscriptions` table

## Browser Compatibility

✅ Chrome/Edge 50+  
✅ Firefox 44+  
✅ Safari 16+ (macOS 13+, iOS 16.4+)  
✅ Opera 37+  
❌ IE (not supported)

## Security Notes

- VAPID keys should be kept secure (never commit to public repos)
- Push notifications only work over HTTPS
- Users must grant notification permission
- Subscriptions are tied to specific browsers/devices
