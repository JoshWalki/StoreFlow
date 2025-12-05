<?php

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// User channel (default Laravel)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// Merchant-level channel - only users belonging to this merchant
Broadcast::channel('merchant.{merchantId}', function (User $user, int $merchantId) {
    return (int) $user->merchant_id === (int) $merchantId;
});

// Store-level order channel - for dashboard real-time updates
Broadcast::channel('store.{storeId}.orders', function (User $user, int $storeId) {
    // Owner has access to all stores
    if ($user->role === 'owner' && $user->merchant_id) {
        return ['id' => $user->id, 'name' => $user->username, 'role' => $user->role];
    }

    // Manager/Staff must be assigned to this store
    $hasAccess = $user->stores()->where('stores.id', $storeId)->exists();

    if ($hasAccess) {
        return ['id' => $user->id, 'name' => $user->username, 'role' => $user->role];
    }

    return false;
});

// Specific order channel - for detailed order updates
Broadcast::channel('order.{orderId}', function (User $user, int $orderId) {
    $order = Order::find($orderId);

    if (!$order) {
        return false;
    }

    // Check user has access to this order's merchant
    if ((int) $user->merchant_id !== (int) $order->merchant_id) {
        return false;
    }

    // Owner always has access
    if ($user->role === 'owner') {
        return ['id' => $user->id, 'name' => $user->username];
    }

    // Manager/Staff must be assigned to the order's store
    $hasAccess = $user->stores()->where('stores.id', $order->store_id)->exists();

    return $hasAccess ? ['id' => $user->id, 'name' => $user->username] : false;
});

// Public customer order tracking channel - uses public_id, no auth required
Broadcast::channel('customer.{publicId}.order', function ($user, string $publicId) {
    // This is a public channel for customer order tracking
    // No authentication required - anyone with the public_id can listen
    // The public_id is already cryptographically secure (format: SF-XXXXX)
    return true;
});
