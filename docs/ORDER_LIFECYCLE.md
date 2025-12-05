# Order Lifecycle Management

This document describes the Order Lifecycle Management system implemented for StoreFlow, based on the A1.4 Shipping & Fulfilment specification.

## Overview

The Order Lifecycle Management system provides a robust, event-driven approach to managing orders from creation through completion. It includes:

- **Status state machine** with validated transitions
- **Event broadcasting** for real-time updates
- **Automatic audit logging** for compliance and tracking
- **Multi-tenancy support** with proper scoping
- **Separate workflows** for pickup and shipping orders

## Architecture

### Core Components

1. **OrderService** (`app/Services/Orders/OrderService.php`)
   - Central service for all order operations
   - Validates business rules and state transitions
   - Fires domain events
   - Handles calculations and ID generation

2. **Events** (`app/Events/`)
   - `OrderCreated` - Fired when a new order is created
   - `OrderStatusUpdated` - Fired when order status changes
   - `ShippingStatusUpdated` - Fired when shipping status changes
   - All implement `ShouldBroadcast` for real-time updates

3. **Listeners** (`app/Listeners/`)
   - `WriteAuditLog` - Automatically logs all order events to audit_logs table

4. **Order Model** (`app/Models/Order.php`)
   - Status constants for type safety
   - Auditable trait for automatic logging
   - Proper relationships and casts

## Order Status State Machine

### Status Flow for Shipping Orders

```
pending → accepted → in_progress → ready → packing → shipped → delivered
   ↓         ↓           ↓           ↓
cancelled  cancelled  cancelled  cancelled
```

### Status Flow for Pickup Orders

```
pending → accepted → in_progress → ready → ready_for_pickup → picked_up
   ↓         ↓           ↓           ↓
cancelled  cancelled  cancelled  cancelled
```

### Status Definitions

| Status | Description | Valid For |
|--------|-------------|-----------|
| `pending` | Order created, awaiting merchant acceptance | Both |
| `accepted` | Merchant accepted the order | Both |
| `in_progress` | Order is being prepared | Both |
| `ready` | Order is ready for next step | Both |
| `packing` | Order is being packed for shipment | Shipping only |
| `shipped` | Order has been shipped | Shipping only |
| `delivered` | Order delivered to customer | Shipping only |
| `ready_for_pickup` | Order ready for customer pickup | Pickup only |
| `picked_up` | Customer has picked up the order | Pickup only |
| `cancelled` | Order was cancelled | Both |

### Allowed Status Transitions

The system enforces the following transition rules:

```php
'pending' => ['accepted', 'cancelled']
'accepted' => ['in_progress', 'cancelled']
'in_progress' => ['ready', 'cancelled']
'ready' => ['packing', 'ready_for_pickup', 'cancelled']
'packing' => ['shipped']
'shipped' => ['delivered']
'ready_for_pickup' => ['picked_up']
'delivered' => [] // Terminal state
'picked_up' => [] // Terminal state
'cancelled' => [] // Terminal state
```

## Usage Examples

### Creating an Order

#### Shipping Order

```php
use App\Services\Orders\OrderService;

$orderService = new OrderService();

$orderData = [
    'merchant_id' => 1,
    'store_id' => 1,
    'customer_id' => 123,
    'fulfilment_type' => 'shipping',
    'items' => [
        [
            'product_id' => 456,
            'qty' => 2,
            'unit_price_cents' => 1000,
        ],
        [
            'product_id' => 789,
            'qty' => 1,
            'unit_price_cents' => 1500,
        ],
    ],
    'shipping_cost_cents' => 500,
    'shipping_name' => 'John Doe',
    'line1' => '123 Main St',
    'city' => 'Sydney',
    'state' => 'NSW',
    'postcode' => '2000',
    'country' => 'AU',
];

$order = $orderService->createOrder($orderData);
// Returns Order with status 'pending' and public_id like 'SF-12345'
```

#### Pickup Order

```php
$orderData = [
    'merchant_id' => 1,
    'store_id' => 1,
    'customer_id' => 123,
    'fulfilment_type' => 'pickup',
    'pickup_time' => now()->addHours(2),
    'items' => [
        [
            'product_id' => 456,
            'qty' => 1,
            'unit_price_cents' => 1500,
        ],
    ],
];

$order = $orderService->createOrder($orderData);
```

### Updating Order Status

```php
// Accept an order
$order = $orderService->updateOrderStatus($order, Order::STATUS_ACCEPTED);

// Move to in progress
$order = $orderService->updateOrderStatus($order, Order::STATUS_IN_PROGRESS);

// Complete preparation
$order = $orderService->updateOrderStatus($order, Order::STATUS_READY);

// For shipping orders: pack the order
$order = $orderService->updateOrderStatus($order, Order::STATUS_PACKING);

// For pickup orders: mark ready for pickup
$order = $orderService->updateOrderStatus($order, Order::STATUS_READY_FOR_PICKUP);
```

### Updating Shipping Information

```php
$order = $orderService->updateShippingStatus(
    $order,
    'in_transit',
    [
        'tracking_code' => 'ABC123456789',
        'tracking_url' => 'https://tracking.example.com/ABC123456789',
        'shipping_method' => 'Express Post',
    ]
);
```

### Cancelling an Order

```php
$order = $orderService->cancelOrder($order, 'Customer requested cancellation');
```

### Validating Status Transitions

```php
// Check if a transition is allowed
$isValid = $orderService->validateStatusTransition(
    Order::STATUS_PENDING,
    Order::STATUS_ACCEPTED
); // Returns true

$isValid = $orderService->validateStatusTransition(
    Order::STATUS_PENDING,
    Order::STATUS_SHIPPED
); // Returns false

// Get allowed next statuses
$allowedStatuses = $orderService->getAllowedNextStatuses(Order::STATUS_PENDING);
// Returns ['accepted', 'cancelled']
```

## Events and Broadcasting

### Event Broadcasting

All events broadcast to the following channels:

- `merchant.{merchant_id}` - Merchant-wide notifications
- `store.{store_id}` - Store-specific notifications
- `order.{order_id}` - Order-specific notifications (for StatusUpdated and ShippingUpdated)

### Event Payloads

#### OrderCreated

```json
{
    "order_id": 123,
    "public_id": "SF-12345",
    "merchant_id": 1,
    "store_id": 1,
    "customer_id": 456,
    "status": "pending",
    "fulfilment_type": "shipping",
    "total_cents": 2500,
    "created_at": "2025-12-03T10:30:00.000Z"
}
```

#### OrderStatusUpdated

```json
{
    "order_id": 123,
    "public_id": "SF-12345",
    "merchant_id": 1,
    "store_id": 1,
    "old_status": "pending",
    "new_status": "accepted",
    "fulfilment_type": "shipping",
    "updated_at": "2025-12-03T10:35:00.000Z"
}
```

#### ShippingStatusUpdated

```json
{
    "order_id": 123,
    "public_id": "SF-12345",
    "merchant_id": 1,
    "store_id": 1,
    "old_shipping_status": null,
    "new_shipping_status": "in_transit",
    "tracking_code": "ABC123456789",
    "tracking_url": "https://tracking.example.com/ABC123456789",
    "shipping_method": "Express Post",
    "updated_at": "2025-12-03T10:40:00.000Z"
}
```

### Listening to Events in Frontend

```javascript
// Listen for new orders
Echo.channel(`merchant.${merchantId}`)
    .listen('.order.created', (event) => {
        console.log('New order:', event.public_id);
        // Update UI, play notification sound, etc.
    });

// Listen for order updates
Echo.channel(`order.${orderId}`)
    .listen('.order.status.updated', (event) => {
        console.log(`Order ${event.public_id} status: ${event.old_status} → ${event.new_status}`);
        // Update order status in UI
    })
    .listen('.order.shipping.updated', (event) => {
        console.log('Shipping update:', event.new_shipping_status);
        // Update tracking information in UI
    });
```

## Audit Logging

All order events are automatically logged to the `audit_logs` table through the `WriteAuditLog` listener:

- `order_created` - When a new order is created
- `status_changed` - When order status changes
- `shipping_status_updated` - When shipping information is updated

Each audit log entry includes:
- `merchant_id` - For multi-tenancy
- `user_id` - Who made the change (if authenticated)
- `entity` - 'Order'
- `entity_id` - Order ID
- `action` - The action performed
- `meta_json` - Contextual data (old/new values, tracking info, etc.)
- `created_at` - Timestamp

## Validation Rules

### Order Creation Validation

Required fields for all orders:
- `merchant_id`
- `store_id`
- `customer_id`
- `fulfilment_type` (must be 'pickup' or 'shipping')
- `items` (array with at least one item)

Each item must have:
- `product_id`
- `qty` (must be > 0)
- `unit_price_cents` (must be >= 0)

Additional requirements for shipping orders:
- `shipping_name`
- `line1`
- `city`
- `state`
- `postcode`
- `country`

Additional requirements for pickup orders:
- `pickup_time`

### Status Transition Validation

- Transitions must follow the defined state machine
- `packing` status only valid for shipping orders
- `ready_for_pickup` and `picked_up` only valid for pickup orders
- Cannot cancel orders in terminal states (delivered, picked_up)

## Error Handling

The service throws `InvalidArgumentException` for:

- Invalid order data (missing required fields)
- Invalid status transitions
- Fulfilment-type-specific status violations
- Attempting to cancel orders in terminal states
- Updating shipping status for non-shipping orders

Example error handling:

```php
try {
    $order = $orderService->updateOrderStatus($order, $newStatus);
} catch (\InvalidArgumentException $e) {
    // Handle invalid transition
    return response()->json([
        'error' => $e->getMessage()
    ], 422);
}
```

## Integration with Other Systems

### Payment Integration

```php
// After successful payment
if ($payment->status === 'success') {
    $order->update(['payment_status' => Order::PAYMENT_PAID]);
    $orderService->updateOrderStatus($order, Order::STATUS_ACCEPTED);
}
```

### Shipping Provider Integration

```php
// After creating shipping label
$shippingLabel = $shippingProvider->createLabel($order);

$orderService->updateShippingStatus(
    $order,
    'label_created',
    [
        'tracking_code' => $shippingLabel->trackingCode,
        'tracking_url' => $shippingLabel->trackingUrl,
        'shipping_method' => $shippingLabel->service,
    ]
);

$orderService->updateOrderStatus($order, Order::STATUS_SHIPPED);
```

### Webhook Notifications

```php
// In webhook handler for shipping updates
$orderService->updateShippingStatus(
    $order,
    $webhookData['status'],
    [
        'tracking_code' => $webhookData['tracking_code'],
    ]
);

if ($webhookData['status'] === 'delivered') {
    $orderService->updateOrderStatus($order, Order::STATUS_DELIVERED);
}
```

## Database Schema

### Orders Table

See migration: `database/migrations/2025_12_02_095110_create_orders_table.php`

Key fields:
- `public_id` - Customer-facing tracking ID (SF-XXXXX)
- `status` - Order status (enum)
- `payment_status` - Payment status (enum)
- `fulfilment_type` - 'pickup' or 'shipping'
- `items_total_cents` - Sum of all items
- `shipping_cost_cents` - Shipping cost
- `total_cents` - Total order value
- Shipping address fields (for shipping orders)
- `pickup_time` (for pickup orders)
- Tracking information fields

### Audit Logs Table

See migration: `database/migrations/2025_12_03_000003_create_audit_logs_table.php`

Provides complete audit trail of all order operations.

## Testing

Comprehensive test suite available at `tests/Unit/OrderServiceTest.php`

Run tests:
```bash
php artisan test --filter OrderServiceTest
```

Tests cover:
- Order creation (shipping and pickup)
- Status transitions (valid and invalid)
- Fulfilment-type-specific validations
- Shipping status updates
- Order cancellation
- Public ID generation
- Total calculations
- Complete order lifecycles
- Event dispatching

## Best Practices

1. **Always use OrderService** - Never update order status directly on the model
2. **Handle exceptions** - Wrap service calls in try-catch blocks
3. **Check transition validity** - Use `validateStatusTransition()` before attempting updates
4. **Provide context** - Include reason when cancelling orders
5. **Monitor events** - Set up frontend listeners for real-time updates
6. **Review audit logs** - Use for debugging and compliance
7. **Respect terminal states** - Don't attempt to modify completed/cancelled orders

## Migration

To add the pickup statuses to existing database:

```bash
php artisan migrate
```

This will run: `2025_12_03_000004_add_pickup_statuses_to_orders_table.php`

## Future Enhancements

Potential improvements:
- Order workflow automation (auto-accept for trusted customers)
- SLA tracking and alerts
- Batch status updates
- Order modification before acceptance
- Split shipments support
- Return/refund workflow
- Customer notification system
- Estimated delivery date calculations
