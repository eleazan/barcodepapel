<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class VerialSyncLog extends Model
{
    protected $table = 'verial_sync_log';

    public $timestamps = false;

    const UPDATED_AT = null;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'operation',
        'verial_method',
        'status',
        'verial_response',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'verial_response' => 'array',
            'created_at'      => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (VerialSyncLog $log) {
            if (empty($log->created_at)) {
                $log->created_at = now();
            }
        });
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'error');
    }

    public function scopeForEntity(Builder $query, string $type): Builder
    {
        return $query->where('entity_type', $type);
    }

    public static function record(
        string $entityType,
        ?int $entityId,
        string $operation,
        string $verialMethod,
        array $response = [],
        ?string $error = null
    ): self {
        $log = new self([
            'entity_type'     => $entityType,
            'entity_id'       => $entityId,
            'operation'       => $operation,
            'verial_method'   => $verialMethod,
            'status'          => $error === null ? 'ok' : 'error',
            'verial_response' => $response,
            'error_message'   => $error,
        ]);

        $log->save();

        return $log;
    }
}
