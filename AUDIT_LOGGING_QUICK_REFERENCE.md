# Audit Logging - Quick Reference Card

## Quick Start

### 1. Run Migration
```bash
php artisan migrate
```

### 2. Add to Model (Automatic Logging)
```php
use App\Traits\Auditable;

class YourModel extends Model
{
    use Auditable;
}
```

### 3. Or Use Service Directly (Manual Logging)
```php
use App\Services\AuditLogger;

AuditLogger::log('Order', $orderId, 'shipped', ['tracking' => 'ABC123']);
```

## Common Patterns

### Product CRUD
```php
// Create
$product = Product::create($data);
AuditLogger::logCreated('Product', $product->id, [
    'name' => $product->name,
    'price_cents' => $product->price_cents,
]);

// Update with change tracking
$changes = [];
foreach ($data as $key => $newValue) {
    $oldValue = $product->getOriginal($key);
    if ($oldValue != $newValue) {
        $changes[$key] = ['old' => $oldValue, 'new' => $newValue];
    }
}
$product->update($data);
AuditLogger::logUpdated('Product', $product->id, $changes);

// Delete
AuditLogger::logDeleted('Product', $product->id, ['name' => $product->name]);
$product->delete();
```

### Order Status Changes
```php
// Manual
AuditLogger::logStatusChange(
    'Order',
    $order->id,
    'pending',
    'shipped',
    ['tracking_code' => 'TRACK123']
);

// Or use Auditable trait (automatic)
$order->status = 'shipped'; // Old: 'pending'
$order->save(); // Automatically logged
```

### Configuration Changes
```php
AuditLogger::logConfigChange('ShippingZone', $zone->id, [
    'countries' => ['old' => ['US'], 'new' => ['US', 'CA']],
]);
```

## Query Audit Logs

### Get logs for specific entity
```php
// All logs for an order
$logs = AuditLogger::getLogsForEntity('Order', $orderId);

// All order logs
$logs = AuditLogger::getLogsForEntity('Order');
```

### Get recent merchant activity
```php
// Last 7 days
$logs = AuditLogger::getRecentLogsForMerchant($merchantId, 7, 100);
```

### Get user activity
```php
$logs = AuditLogger::getLogsForUser($userId, 50);
```

### Advanced queries
```php
use App\Models\AuditLog;

// Status changes only
$logs = AuditLog::forEntity('Order')
    ->where('action', 'status_changed')
    ->where('created_at', '>=', now()->subWeek())
    ->with('user')
    ->get();

// All deletions
$deletions = AuditLog::where('action', 'deleted')
    ->forMerchant($merchantId)
    ->with('user')
    ->get();
```

## Customize Model Auditing

```php
use App\Traits\Auditable;

class Order extends Model
{
    use Auditable;

    // Which fields to audit (empty = all)
    protected function getAuditableFields(): array
    {
        return ['status', 'payment_status', 'total_cents'];
    }

    // Metadata to include in logs
    protected function getAuditMetadata(): array
    {
        return [
            'public_id' => $this->public_id,
            'customer_id' => $this->customer_id,
        ];
    }

    // Conditional logging
    protected function shouldAuditUpdate(): bool
    {
        return $this->isDirty(['status', 'payment_status']);
    }
}
```

## API Methods

### AuditLogger Service
- `log($entity, $entityId, $action, $meta)`
- `logCreated($entity, $entityId, $meta)`
- `logUpdated($entity, $entityId, $changes)`
- `logDeleted($entity, $entityId, $meta)`
- `logStatusChange($entity, $entityId, $oldStatus, $newStatus, $meta)`
- `logConfigChange($entity, $entityId, $changes)`
- `getLogsForEntity($entity, $entityId, $limit)`
- `getRecentLogsForMerchant($merchantId, $days, $limit)`
- `getLogsForUser($userId, $limit)`

### AuditLog Model Scopes
- `forEntity($entity, $entityId)`
- `forMerchant($merchantId)`
- `byUser($userId)`
- `recent($days)`

## Important Rules

1. **Audit logs are IMMUTABLE** - Cannot be updated or deleted
2. **Always scoped to merchant_id** - Multi-tenancy built-in
3. **Captures user context automatically** - No manual user tracking needed
4. **Never throws exceptions** - Audit failures won't break operations
5. **Use consistent entity names** - 'Order' not 'Orders', 'Product' not 'product'

## Files to Know

- Migration: `database/migrations/2025_12_03_000003_create_audit_logs_table.php`
- Model: `app/Models/AuditLog.php`
- Service: `app/Services/AuditLogger.php`
- Trait: `app/Traits/Auditable.php`
- Controller: `app/Http/Controllers/AuditLogController.php` (optional UI)

## Full Documentation

See `/mnt/c/xampp/htdocs/StoreFlow/app/Services/AUDIT_LOGGING_USAGE.md` for complete documentation.
