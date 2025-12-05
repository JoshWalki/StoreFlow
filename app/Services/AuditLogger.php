<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    /**
     * Log an audit event.
     *
     * @param string $entity The entity type (e.g., 'Order', 'Product', 'ShippingConfig')
     * @param int $entityId The ID of the entity
     * @param string $action The action performed (e.g., 'created', 'updated', 'deleted', 'status_changed')
     * @param array $meta Additional metadata about the action
     * @return AuditLog|null
     */
    public static function log(string $entity, int $entityId, string $action, array $meta = []): ?AuditLog
    {
        try {
            $user = Auth::user();

            // Ensure we have a merchant context
            if (!$user || !$user->merchant_id) {
                // If no authenticated user or merchant, log warning but don't fail
                \Log::warning('AuditLogger: No authenticated user or merchant context', [
                    'entity' => $entity,
                    'entity_id' => $entityId,
                    'action' => $action,
                ]);
                return null;
            }

            return AuditLog::create([
                'merchant_id' => $user->merchant_id,
                'user_id' => $user->id,
                'entity' => $entity,
                'entity_id' => $entityId,
                'action' => $action,
                'meta_json' => $meta,
            ]);
        } catch (\Exception $e) {
            // Log the error but don't let audit logging break the main operation
            \Log::error('AuditLogger: Failed to create audit log', [
                'entity' => $entity,
                'entity_id' => $entityId,
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Log a creation event.
     *
     * @param string $entity
     * @param int $entityId
     * @param array $meta
     * @return AuditLog|null
     */
    public static function logCreated(string $entity, int $entityId, array $meta = []): ?AuditLog
    {
        return self::log($entity, $entityId, 'created', $meta);
    }

    /**
     * Log an update event.
     *
     * @param string $entity
     * @param int $entityId
     * @param array $changes Array of changed attributes (old and new values)
     * @return AuditLog|null
     */
    public static function logUpdated(string $entity, int $entityId, array $changes = []): ?AuditLog
    {
        return self::log($entity, $entityId, 'updated', ['changes' => $changes]);
    }

    /**
     * Log a deletion event.
     *
     * @param string $entity
     * @param int $entityId
     * @param array $meta
     * @return AuditLog|null
     */
    public static function logDeleted(string $entity, int $entityId, array $meta = []): ?AuditLog
    {
        return self::log($entity, $entityId, 'deleted', $meta);
    }

    /**
     * Log a status change event.
     *
     * @param string $entity
     * @param int $entityId
     * @param string $oldStatus
     * @param string $newStatus
     * @param array $additionalMeta
     * @return AuditLog|null
     */
    public static function logStatusChange(
        string $entity,
        int $entityId,
        string $oldStatus,
        string $newStatus,
        array $additionalMeta = []
    ): ?AuditLog {
        return self::log($entity, $entityId, 'status_changed', array_merge([
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ], $additionalMeta));
    }

    /**
     * Log a configuration change event.
     *
     * @param string $entity
     * @param int $entityId
     * @param array $changes
     * @return AuditLog|null
     */
    public static function logConfigChange(string $entity, int $entityId, array $changes = []): ?AuditLog
    {
        return self::log($entity, $entityId, 'config_changed', ['changes' => $changes]);
    }

    /**
     * Get audit logs for a specific entity.
     *
     * @param string $entity
     * @param int|null $entityId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLogsForEntity(string $entity, ?int $entityId = null, int $limit = 50)
    {
        $query = AuditLog::forEntity($entity, $entityId)
            ->with(['user:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        return $query->get();
    }

    /**
     * Get recent audit logs for a merchant.
     *
     * @param int $merchantId
     * @param int $days
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getRecentLogsForMerchant(int $merchantId, int $days = 7, int $limit = 100)
    {
        return AuditLog::forMerchant($merchantId)
            ->recent($days)
            ->with(['user:id,name,email'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get audit logs for a specific user.
     *
     * @param int $userId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLogsForUser(int $userId, int $limit = 50)
    {
        return AuditLog::byUser($userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
