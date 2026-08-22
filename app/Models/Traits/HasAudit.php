<?php

declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait HasAudit
 *
 * Registers model events to automatically log creation, updates and deletions.
 * Attach to any model to track its full history.
 *
 * Usage:
 *   use HasAudit;
 *
 * Optionally override in your model:
 *   protected array $auditExclude = ['updated_at', 'remember_token'];
 *   protected array $auditInclude = ['name', 'status']; // if set, ONLY these fields are audited
 */
trait HasAudit
{
    public static function bootHasAudit(): void
    {
        static::created(function ($model) {
            $model->logAudit(AuditLog::EVENT_CREATED, [], $model->auditableAttributes());
        });

        static::updated(function ($model) {
            $changed = $model->auditableChanges();
            if (! empty($changed['old']) || ! empty($changed['new'])) {
                $model->logAudit(AuditLog::EVENT_UPDATED, $changed['old'], $changed['new']);
            }
        });

        static::deleted(function ($model) {
            $model->logAudit(AuditLog::EVENT_DELETED, $model->auditableAttributes(), []);
        });
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable')->latest('created_at');
    }

    /**
     * Fields to exclude from audit. Override in your model to customize.
     */
    protected function auditExcludedFields(): array
    {
        return array_merge(
            ['password', 'remember_token', 'updated_at', 'created_at'],
            property_exists($this, 'auditExclude') ? $this->auditExclude : [],
        );
    }

    /**
     * If set, ONLY these fields will be audited. Override in model.
     */
    protected function auditIncludedFields(): ?array
    {
        return property_exists($this, 'auditInclude') ? $this->auditInclude : null;
    }

    protected function auditableAttributes(): array
    {
        $attributes = $this->getAttributes();

        return $this->filterAuditFields($attributes);
    }

    protected function auditableChanges(): array
    {
        $dirty    = $this->getDirty();
        $filtered = $this->filterAuditFields($dirty);

        if (empty($filtered)) {
            return ['old' => [], 'new' => []];
        }

        $old = [];
        $new = [];

        foreach ($filtered as $key => $value) {
            $old[$key] = $this->getOriginal($key);
            $new[$key] = $value;
        }

        return ['old' => $old, 'new' => $new];
    }

    protected function filterAuditFields(array $fields): array
    {
        $include = $this->auditIncludedFields();
        $exclude = $this->auditExcludedFields();

        if ($include !== null) {
            $fields = array_intersect_key($fields, array_flip($include));
        }

        return array_diff_key($fields, array_flip($exclude));
    }

    protected function logAudit(string $event, array $oldValues, array $newValues): void
    {
        $request = request();

        AuditLog::create([
            'auditable_type' => $this->getMorphClass(),
            'auditable_id'   => $this->getKey(),
            'user_id'        => auth()->id(),
            'event'          => $event,
            'old_values'     => ! empty($oldValues) ? $oldValues : null,
            'new_values'     => ! empty($newValues) ? $newValues : null,
            'ip_address'     => $request?->ip(),
            'user_agent'     => $request?->userAgent() ? substr($request->userAgent(), 0, 255) : null,
            'created_at'     => now(),
        ]);
    }
}
