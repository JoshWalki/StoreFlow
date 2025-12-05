# Order Lifecycle Quick Reference

## Quick Start

```php
use App\Services\Orders\OrderService;
use App\Models\Order;

$orderService = app(OrderService::class);
```

## Create Order

```php
// Shipping Order
$order = $orderService->createOrder([
    'merchant_id' => $merchantId,
    'store_id' => $storeId,
    'customer_id' => $customerId,
    'fulfilment_type' => 'shipping',
    'items' => [
        ['product_id' => 1, 'qty' => 2, 'unit_price_cents' => 1000],
    ],
    'shipping_cost_cents' => 500,
    'shipping_name' => 'John Doe',
    'line1' => '123 Main St',
    'city' => 'Sydney',
    'state' => 'NSW',
    'postcode' => '2000',
    'country' => 'AU',
]);

// Pickup Order
$order = $orderService->createOrder([
    'merchant_id' => $merchantId,
    'store_id' => $storeId,
    'customer_id' => $customerId,
    'fulfilment_type' => 'pickup',
    'pickup_time' => now()->addHours(2),
    'items' => [
        ['product_id' => 1, 'qty' => 1, 'unit_price_cents' => 1500],
    ],
]);
```

## Update Status

```php
// Simple status update
$order = $orderService->updateOrderStatus($order, Order::STATUS_ACCEPTED);

// With additional data
$order = $orderService->updateOrderStatus(
    $order,
    Order::STATUS_ACCEPTED,
    ['accepted_at' => now(), 'accepted_by' => $userId]
);
```

## Status Constants

```php
Order::STATUS_PENDING
Order::STATUS_ACCEPTED
Order::STATUS_IN_PROGRESS
Order::STATUS_READY
Order::STATUS_PACKING              // Shipping only
Order::STATUS_SHIPPED              // Shipping only
Order::STATUS_DELIVERED            // Shipping only
Order::STATUS_READY_FOR_PICKUP     // Pickup only
Order::STATUS_PICKED_UP            // Pickup only
Order::STATUS_CANCELLED
```

## Shipping Updates

```php
$order = $orderService->updateShippingStatus(
    $order,
    'in_transit',
    [
        'tracking_code' => 'ABC123',
        'tracking_url' => 'https://track.example.com/ABC123',
    ]
);
```

## Cancel Order

```php
$order = $orderService->cancelOrder($order, 'Customer requested cancellation');
```

## Validation

```php
// Check if transition is valid
if ($orderService->validateStatusTransition($currentStatus, $newStatus)) {
    $order = $orderService->updateOrderStatus($order, $newStatus);
}

// Get allowed next statuses
$allowedStatuses = $orderService->getAllowedNextStatuses($order->status);

// Check if can be cancelled
if ($orderService->canBeCancelled($order)) {
    $order = $orderService->cancelOrder($order);
}
```

## Status Flows

### Shipping: `pending → accepted → in_progress → ready → packing → shipped → delivered`

### Pickup: `pending → accepted → in_progress → ready → ready_for_pickup → picked_up`

### Cancel: Any status except `delivered`, `picked_up`, or `cancelled` → `cancelled`

## Events

All events are automatically broadcast and logged:

- `App\Events\OrderCreated`
- `App\Events\OrderStatusUpdated`
- `App\Events\ShippingStatusUpdated`

## Error Handling

```php
try {
    $order = $orderService->updateOrderStatus($order, $newStatus);
} catch (\InvalidArgumentException $e) {
    // Invalid transition or business rule violation
    logger()->error('Order status update failed', [
        'order_id' => $order->id,
        'error' => $e->getMessage()
    ]);

    return back()->withErrors(['status' => $e->getMessage()]);
}
```

## Common Workflows

### Accept and Start Processing Order

```php
$order = $orderService->updateOrderStatus($order, Order::STATUS_ACCEPTED);
$order = $orderService->updateOrderStatus($order, Order::STATUS_IN_PROGRESS);
```

### Complete Shipping Order

```php
$order = $orderService->updateOrderStatus($order, Order::STATUS_READY);
$order = $orderService->updateOrderStatus($order, Order::STATUS_PACKING);
$order = $orderService->updateShippingStatus($order, 'label_created', [
    'tracking_code' => $trackingCode,
    'tracking_url' => $trackingUrl,
]);
$order = $orderService->updateOrderStatus($order, Order::STATUS_SHIPPED);
// ... later
$order = $orderService->updateOrderStatus($order, Order::STATUS_DELIVERED);
```

### Complete Pickup Order

```php
$order = $orderService->updateOrderStatus($order, Order::STATUS_READY);
$order = $orderService->updateOrderStatus($order, Order::STATUS_READY_FOR_PICKUP);
// Customer notified...
$order = $orderService->updateOrderStatus($order, Order::STATUS_PICKED_UP);
```

## Utilities

```php
// Generate unique public ID
$publicId = $orderService->generatePublicId(); // Returns: SF-12345

// Calculate totals
$totals = $orderService->calculateOrderTotals($items);
// Returns: ['items_total_cents' => 2500, 'total_cents' => 2500]
```

## Broadcasting Channels

- `merchant.{merchant_id}` - All merchant orders
- `store.{store_id}` - All store orders
- `order.{order_id}` - Specific order updates

## Frontend Listening

```javascript
// Vue/React example
Echo.channel(`store.${storeId}`)
    .listen('.order.created', (event) => {
        // New order notification
        showNotification(`New order ${event.public_id}`);
        refreshOrdersList();
    });

Echo.channel(`order.${orderId}`)
    .listen('.order.status.updated', (event) => {
        // Update order status in UI
        updateOrderStatus(event.old_status, event.new_status);
    });
```

## Testing

```php
// In your tests
use App\Services\Orders\OrderService;
use Illuminate\Support\Facades\Event;

Event::fake([OrderCreated::class, OrderStatusUpdated::class]);

$orderService = new OrderService();
$order = $orderService->createOrder($orderData);

Event::assertDispatched(OrderCreated::class);

$order = $orderService->updateOrderStatus($order, Order::STATUS_ACCEPTED);

Event::assertDispatched(OrderStatusUpdated::class, function ($event) use ($order) {
    return $event->order->id === $order->id
        && $event->newStatus === Order::STATUS_ACCEPTED;
});
```

## Audit Trail

All operations are automatically logged to `audit_logs` table:

```php
// Query audit logs
$logs = AuditLog::forEntity('Order', $orderId)
    ->orderBy('created_at', 'desc')
    ->get();

// View specific action logs
$statusChanges = AuditLog::where('entity', 'Order')
    ->where('action', 'status_changed')
    ->where('entity_id', $orderId)
    ->get();
```
