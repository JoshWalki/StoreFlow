# Audit Logging System - Usage Guide

## Overview

The StoreFlow Audit Logging system provides a comprehensive, append-only audit trail for all critical operations in the system. The system follows the A1.2 specification and ensures that audit logs are NEVER deleted, only appended to.

## Architecture

### Components

1. **Migration**: `2025_12_03_000003_create_audit_logs_table.php`
   - Creates the `audit_logs` table with proper indexes
   - Fields: merchant_id, user_id, entity, entity_id, action, meta_json, created_at

2. **Model**: `App\Models\AuditLog`
   - Handles JSON casting for meta_json field
   - Prevents updates and deletions through model events
   - Provides relationships to Merchant and User

3. **Service**: `App\Services\AuditLogger`
   - Static methods for logging actions
   - Automatically captures current user and merchant from auth context

4. **Trait**: `App\Traits\Auditable`
   - Automatically logs model events (created, updated, deleted)
   - Configurable per-model metadata and auditable fields

## Basic Usage

### Manual Logging with AuditLogger Service

```php
use App\Services\AuditLogger;

// Basic log entry
AuditLogger::log('Order', $orderId, 'shipped', [
    'tracking_code' => 'TRACK123',
    'carrier' => 'DHL'
]);

// Log creation
AuditLogger::logCreated('Product', $product->id, [
    'name' => $product->name,
    'price_cents' => $product->price_cents
]);

// Log update with changes
AuditLogger::logUpdated('Product', $product->id, [
    'price_cents' => [
        'old' => 1000,
        'new' => 1200
    ]
]);

// Log deletion
AuditLogger::logDeleted('Product', $product->id, [
    'name' => $product->name,
    'reason' => 'discontinued'
]);

// Log status change
AuditLogger::logStatusChange(
    'Order',
    $order->id,
    'pending',
    'accepted',
    ['notes' => 'Auto-accepted by system']
);

// Log configuration change
AuditLogger::logConfigChange('ShippingZone', $zone->id, [
    'countries' => [
        'old' => ['US', 'CA'],
        'new' => ['US', 'CA', 'MX']
    ]
]);
```

### Automatic Logging with Auditable Trait

Add the `Auditable` trait to your model:

```php
use App\Traits\Auditable;

class Order extends Model
{
    use HasFactory, Auditable;

    // Define which fields should be audited
    protected function getAuditableFields(): array
    {
        return [
            'status',
            'payment_status',
            'shipping_status',
            'total_cents',
        ];
    }

    // Define metadata to include in audit logs
    protected function getAuditMetadata(): array
    {
        return [
            'public_id' => $this->public_id,
            'customer_id' => $this->customer_id,
            'total_cents' => $this->total_cents,
        ];
    }
}
```

Now the model will automatically log:
- **Creation**: When a new record is created
- **Updates**: When any auditable field changes
- **Status Changes**: Special logging for status fields
- **Deletion**: When the record is deleted

## Real-World Examples

### Example 1: Order Status Changes

```php
// In your OrderController or service
$order = Order::find($orderId);
$order->status = 'shipped'; // Old: 'accepted'
$order->tracking_code = 'TRACK123';
$order->save();

// The Auditable trait automatically creates:
// 1. A status_changed log entry with old/new status
// 2. An updated log entry with all changes including tracking_code
```

### Example 2: Product CRUD Operations

```php
// CREATE
$product = Product::create([
    'merchant_id' => $merchantId,
    'name' => 'New Widget',
    'price_cents' => 1999,
    'is_active' => true,
]);
// Automatically logged as: action='created'

// UPDATE
$product->price_cents = 2499;
$product->save();
// Automatically logged as: action='updated'
// meta_json contains: {"changes": {"price_cents": {"old": 1999, "new": 2499}}}

// DELETE
$product->delete();
// Automatically logged as: action='deleted'
```

### Example 3: Shipping Configuration Changes

```php
$zone = ShippingZone::find($zoneId);
$zone->countries = ['US', 'CA', 'MX']; // Added MX
$zone->priority = 10; // Changed from 5
$zone->save();

// Automatically logged with both changes:
// meta_json: {
//   "changes": {
//     "countries": {"old": ["US", "CA"], "new": ["US", "CA", "MX"]},
//     "priority": {"old": 5, "new": 10}
//   }
// }
```

### Example 4: Bulk Operations

```php
// When updating multiple orders
$orders = Order::where('status', 'pending')
    ->where('created_at', '<', now()->subHours(24))
    ->get();

foreach ($orders as $order) {
    $order->status = 'cancelled';
    $order->save();
    // Each order gets its own audit log entry
}
```

## Querying Audit Logs

### Get logs for specific entity

```php
use App\Services\AuditLogger;

// Get all logs for a specific order
$logs = AuditLogger::getLogsForEntity('Order', $orderId);

// Get all logs for Order entity (all orders)
$logs = AuditLogger::getLogsForEntity('Order');
```

### Get recent logs for merchant

```php
// Get last 7 days of activity
$logs = AuditLogger::getRecentLogsForMerchant($merchantId, 7, 100);

// Get last 30 days (default)
$logs = AuditLogger::getRecentLogsForMerchant($merchantId);
```

### Get logs for specific user

```php
$logs = AuditLogger::getLogsForUser($userId, 50);
```

### Advanced queries using the model directly

```php
use App\Models\AuditLog;

// Get all status changes for orders in the last week
$statusChanges = AuditLog::forEntity('Order')
    ->where('action', 'status_changed')
    ->where('created_at', '>=', now()->subWeek())
    ->with('user')
    ->orderBy('created_at', 'desc')
    ->get();

// Get all product deletions
$deletions = AuditLog::forEntity('Product')
    ->where('action', 'deleted')
    ->with(['user', 'merchant'])
    ->get();

// Get logs for specific merchant in date range
$logs = AuditLog::forMerchant($merchantId)
    ->whereBetween('created_at', [$startDate, $endDate])
    ->get();

// Find who changed a specific order
$changes = AuditLog::forEntity('Order', $orderId)
    ->where('action', 'updated')
    ->with('user:id,name,email')
    ->get();
```

## Display Audit Logs in UI

```php
// In your controller
public function auditLog(Request $request, Order $order)
{
    $logs = AuditLog::forEntity('Order', $order->id)
        ->with('user:id,name,email')
        ->orderBy('created_at', 'desc')
        ->paginate(20);

    return Inertia::render('Orders/AuditLog', [
        'order' => $order,
        'logs' => $logs,
    ]);
}
```

```vue
<!-- In your Vue component -->
<template>
  <div>
    <h2>Audit Log for Order #{{ order.public_id }}</h2>
    <div v-for="log in logs.data" :key="log.id">
      <div>
        <strong>{{ log.action }}</strong> by {{ log.user?.name || 'System' }}
        <span>{{ formatDate(log.created_at) }}</span>
      </div>
      <pre>{{ JSON.stringify(log.meta_json, null, 2) }}</pre>
    </div>
  </div>
</template>
```

## Configuration

### Customizing Auditable Fields

Only log specific fields by defining `getAuditableFields()`:

```php
protected function getAuditableFields(): array
{
    return ['status', 'price_cents', 'is_active'];
}
```

Return empty array to audit all fields:

```php
protected function getAuditableFields(): array
{
    return []; // Audits all fields
}
```

### Customizing Metadata

Include additional context in logs:

```php
protected function getAuditMetadata(): array
{
    return [
        'public_id' => $this->public_id,
        'store_id' => $this->store_id,
        'customer_name' => $this->customer?->name,
    ];
}
```

### Conditional Logging

Control when to audit:

```php
// Only audit updates when certain conditions are met
protected function shouldAuditUpdate(): bool
{
    return $this->isDirty(['status', 'payment_status']);
}

protected function shouldAuditDelete(): bool
{
    return true; // Always audit deletions
}

protected function shouldAuditCreate(): bool
{
    return !$this->is_draft; // Don't audit draft orders
}
```

## Best Practices

1. **Never Delete Audit Logs**: The system prevents this, but don't try to circumvent it
2. **Include Context**: Use metadata to include relevant context
3. **Meaningful Actions**: Use descriptive action names (e.g., 'status_changed', 'price_updated')
4. **Consistent Entity Names**: Use consistent entity names (e.g., always 'Order', not 'Orders')
5. **Security**: Audit logs can contain sensitive data - restrict access appropriately
6. **Performance**: Use indexes when querying audit logs for large datasets
7. **Retention**: Even though logs are append-only, plan for archival of very old logs if needed

## Security Considerations

- Audit logs are tied to merchant_id for multi-tenancy security
- User context is automatically captured (or NULL for system actions)
- All queries should be scoped to the current merchant
- Prevent unauthorized access to audit logs through proper authorization

## Troubleshooting

### Logs not being created

1. Check that user is authenticated: `Auth::check()`
2. Verify user has merchant_id: `Auth::user()->merchant_id`
3. Check Laravel logs for AuditLogger errors
4. Ensure trait is properly added to model

### Missing changes in logs

1. Verify fields are in `getAuditableFields()` array
2. Check that model is actually being saved (not just updated in DB)
3. Ensure Auditable trait is included

### Performance issues

1. Use indexes on audit_logs table (already included in migration)
2. Limit result sets with pagination
3. Use `select()` to load only needed columns
4. Consider archiving old audit logs

## Integration Checklist

- [x] Migration created and run
- [x] AuditLog model created with relationships
- [x] AuditLogger service created
- [x] Auditable trait created
- [x] Integrated into Order model
- [x] Integrated into Product CRUD operations
- [x] Integrated into ShippingZone model
- [ ] Add audit log viewing UI (optional)
- [ ] Add audit log filtering/search (optional)
- [ ] Set up audit log monitoring/alerts (optional)

## API Reference

### AuditLogger Methods

- `log(string $entity, int $entityId, string $action, array $meta = []): ?AuditLog`
- `logCreated(string $entity, int $entityId, array $meta = []): ?AuditLog`
- `logUpdated(string $entity, int $entityId, array $changes = []): ?AuditLog`
- `logDeleted(string $entity, int $entityId, array $meta = []): ?AuditLog`
- `logStatusChange(string $entity, int $entityId, string $oldStatus, string $newStatus, array $additionalMeta = []): ?AuditLog`
- `logConfigChange(string $entity, int $entityId, array $changes = []): ?AuditLog`
- `getLogsForEntity(string $entity, ?int $entityId = null, int $limit = 50): Collection`
- `getRecentLogsForMerchant(int $merchantId, int $days = 7, int $limit = 100): Collection`
- `getLogsForUser(int $userId, int $limit = 50): Collection`

### AuditLog Model Scopes

- `forEntity(string $entity, ?int $entityId = null)`
- `forMerchant(int $merchantId)`
- `byUser(int $userId)`
- `recent(int $days = 30)`

### Auditable Trait Methods (Override in Model)

- `getAuditMetadata(): array` - Metadata to include in all logs
- `getAuditableFields(): array` - Fields to audit (empty = all fields)
- `shouldAuditCreate(): bool` - Whether to audit creation
- `shouldAuditUpdate(): bool` - Whether to audit updates
- `shouldAuditDelete(): bool` - Whether to audit deletion
