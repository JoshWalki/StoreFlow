<?php

namespace App\Traits;

use App\Services\AuditLogger;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    protected static function bootAuditable()
    {
        // Log when a model is created
        static::created(function ($model) {
            if (method_exists($model, 'shouldAuditCreate') && !$model->shouldAuditCreate()) {
                return;
            }

            $meta = $model->getAuditMetadata();
            AuditLogger::logCreated(
                class_basename($model),
                $model->id,
                $meta
            );
        });

        // Log when a model is updated
        static::updated(function ($model) {
            if (method_exists($model, 'shouldAuditUpdate') && !$model->shouldAuditUpdate()) {
                return;
            }

            $changes = [];
            $auditableFields = $model->getAuditableFields();

            foreach ($model->getDirty() as $key => $newValue) {
                // Skip if not in auditable fields (if defined)
                if (!empty($auditableFields) && !in_array($key, $auditableFields)) {
                    continue;
                }

                // Skip timestamps if not explicitly included
                if (in_array($key, ['created_at', 'updated_at']) && !in_array($key, $auditableFields)) {
                    continue;
                }

                $oldValue = $model->getOriginal($key);

                if ($oldValue != $newValue) {
                    $changes[$key] = [
                        'old' => $oldValue,
                        'new' => $newValue,
                    ];

                    // Special handling for status changes
                    if ($key === 'status' || str_ends_with($key, '_status')) {
                        AuditLogger::logStatusChange(
                            class_basename($model),
                            $model->id,
                            $oldValue,
                            $newValue,
                            array_merge(['field' => $key], $model->getAuditMetadata())
                        );
                    }
                }
            }

            // Log general update if there were other changes
            if (!empty($changes)) {
                AuditLogger::logUpdated(
                    class_basename($model),
                    $model->id,
                    $changes
                );
            }
        });

        // Log when a model is deleted
        static::deleted(function ($model) {
            if (method_exists($model, 'shouldAuditDelete') && !$model->shouldAuditDelete()) {
                return;
            }

            $meta = $model->getAuditMetadata();
            AuditLogger::logDeleted(
                class_basename($model),
                $model->id,
                $meta
            );
        });
    }

    /**
     * Get metadata to include in audit logs.
     * Override this method in your model to customize.
     *
     * @return array
     */
    protected function getAuditMetadata(): array
    {
        return [];
    }

    /**
     * Get the fields that should be audited.
     * Return empty array to audit all fields.
     * Override this method in your model to customize.
     *
     * @return array
     */
    protected function getAuditableFields(): array
    {
        return [];
    }
}
