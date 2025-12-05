# WebSocket Setup Guide for StoreFlow

This guide will help you enable real-time order notifications in the StoreFlow dashboard using Laravel Broadcasting with Pusher.

## Features

Once configured, the dashboard will:
- ✅ Show toast notifications when new orders are placed
- ✅ Auto-refresh the active orders list
- ✅ Play notification sounds (optional)
- ✅ Work across multiple browser tabs/windows

## Prerequisites

- Pusher account (free tier available)
- Node.js and NPM installed

## Step 1: Create Pusher Account

1. Go to [https://pusher.com/](https://pusher.com/)
2. Sign up for a free account
3. Create a new Channels app
4. Note down your credentials:
   - App ID
   - Key
   - Secret
   - Cluster

## Step 2: Install Required Packages

```bash
# Install Laravel Echo and Pusher JS
npm install --save-dev laravel-echo pusher-js

# Rebuild assets
npm run dev
```

## Step 3: Update Environment Variables

Edit your `.env` file:

```env
BROADCAST_DRIVER=pusher

PUSHER_APP_ID=your_app_id_here
PUSHER_APP_KEY=your_app_key_here
PUSHER_APP_SECRET=your_app_secret_here
PUSHER_APP_CLUSTER=your_cluster_here  # e.g., ap4 for Sydney
PUSHER_SCHEME=https
PUSHER_PORT=443
```

## Step 4: Update Broadcasting Configuration

The `config/broadcasting.php` is already configured. Verify it contains:

```php
'pusher' => [
    'driver' => 'pusher',
    'key' => env('PUSHER_APP_KEY'),
    'secret' => env('PUSHER_APP_SECRET'),
    'app_id' => env('PUSHER_APP_ID'),
    'options' => [
        'cluster' => env('PUSHER_APP_CLUSTER'),
        'encrypted' => true,
    ],
],
```

## Step 5: Setup Laravel Echo

Edit `resources/js/app.js` and add Laravel Echo configuration:

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
});
```

## Step 6: Add Vite Environment Variables

Edit your `.env` file to add Vite-specific variables:

```env
VITE_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
VITE_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

## Step 7: Rebuild Assets

```bash
npm run dev
```

Or for production:

```bash
npm run build
```

## Step 8: Test the Setup

1. Open the StoreFlow dashboard in your browser
2. Open browser console (F12)
3. You should see: "Setting up websocket listener for store: X"
4. Place a test order from the storefront
5. The dashboard should show a toast notification: "New Order! Order ORD-XXXX placed for Customer Name"

## Troubleshooting

### Echo is undefined

**Problem:** Console shows "WebSocket not configured"

**Solution:**
- Verify Laravel Echo is installed: `npm list laravel-echo`
- Check that `app.js` includes Echo configuration
- Rebuild assets: `npm run dev`
- Hard refresh browser (Ctrl+F5)

### No notifications appearing

**Checks:**
1. Verify Pusher credentials in `.env`
2. Check Pusher dashboard for connection activity
3. Open browser console for errors
4. Verify broadcast driver: `php artisan config:clear`

### CORS errors

**Solution:** Add your domain to Pusher allowed origins in the Pusher dashboard settings.

## Broadcasting Channels

StoreFlow uses these channels:

### `store.{storeId}.orders`
- Event: `order.created`
- Triggered: When a new order is placed
- Payload: Order details including public_id, customer_name, total

### `merchant.{merchantId}`
- Event: `order.created`
- Triggered: When any order is placed for this merchant
- Payload: Full order details

### `order.{orderId}`
- Events: `order.status.updated`, `shipping.updated`
- Triggered: When order status changes
- Payload: Updated order details

## Optional: Add Notification Sound

1. Add a sound file to `public/sounds/notification.mp3`
2. Uncomment the sound code in `DashboardLayout.vue`:

```javascript
const playNotificationSound = () => {
    const audio = new Audio('/sounds/notification.mp3');
    audio.play().catch(e => console.log('Could not play sound:', e));
};
```

## Alternative: Using Laravel WebSockets (Self-Hosted)

If you prefer a self-hosted solution instead of Pusher:

1. Install Laravel WebSockets package:
```bash
composer require beyondcode/laravel-websockets
php artisan vendor:publish --provider="BeyondCode\LaravelWebSockets\WebSocketsServiceProvider"
php artisan migrate
```

2. Update `.env`:
```env
BROADCAST_DRIVER=pusher
PUSHER_APP_ID=storeflow
PUSHER_APP_KEY=storeflowkey
PUSHER_APP_SECRET=storeflowsecret
PUSHER_HOST=127.0.0.1
PUSHER_PORT=6001
PUSHER_SCHEME=http
```

3. Update `resources/js/app.js`:
```javascript
window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? window.location.hostname,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 6001,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 6001,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});
```

4. Run the WebSocket server:
```bash
php artisan websockets:serve
```

5. Access dashboard at: `http://localhost:8000/laravel-websockets`

## Testing Without Pusher (Development)

For development/testing without Pusher:

The system will still work but without real-time updates. Orders will be created successfully, but you'll need to manually refresh the dashboard to see new orders.

Events are logged to `storage/logs/laravel.log` when `BROADCAST_DRIVER=log`.

## Production Checklist

- [ ] Pusher credentials added to production `.env`
- [ ] Broadcasting driver set to `pusher`
- [ ] Assets built with `npm run build`
- [ ] Config cached with `php artisan config:cache`
- [ ] Test order placement on production
- [ ] Verify dashboard receives notifications

## Support

For more information:
- Laravel Broadcasting: https://laravel.com/docs/broadcasting
- Pusher Documentation: https://pusher.com/docs
- Laravel Echo: https://github.com/laravel/echo
