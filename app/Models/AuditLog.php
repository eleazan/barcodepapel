<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    public $timestamps = false;

    public const EVENT_CREATED = 'created';
    public const EVENT_UPDATED = 'updated';
    public const EVENT_DELETED = 'deleted';

    protected $fillable = [
        'auditable_type',
        'auditable_id',
        'user_id',
        'event',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eventLabel(): string
    {
        return match ($this->event) {
            self::EVENT_CREATED => 'Creado',
            self::EVENT_UPDATED => 'Modificado',
            self::EVENT_DELETED => 'Eliminado',
            default => $this->event,
        };
    }

    public function eventColor(): string
    {
        return match ($this->event) {
            self::EVENT_CREATED => 'green',
            self::EVENT_UPDATED => 'blue',
            self::EVENT_DELETED => 'red',
            default => 'gray',
        };
    }

    /**
     * Get a human-readable summary of what changed.
     *
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function changes(): array
    {
        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];
        $changes = [];

        foreach ($new as $key => $value) {
            $oldVal = $old[$key] ?? null;
            if ($oldVal !== $value) {
                $changes[$key] = ['old' => $oldVal, 'new' => $value];
            }
        }

        return $changes;
    }
}
