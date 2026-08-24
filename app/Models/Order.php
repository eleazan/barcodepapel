<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasAudit, HasFactory;

    public const STATUS_PENDIENTE  = 'pendiente';
    public const STATUS_PREPARADO  = 'preparado';
    public const STATUS_EN_REPARTO = 'en_reparto';
    public const STATUS_ENTREGADO  = 'entregado';

    public const STATUSES = [
        self::STATUS_PENDIENTE  => 'Pendiente',
        self::STATUS_PREPARADO  => 'Preparado',
        self::STATUS_EN_REPARTO => 'En reparto',
        self::STATUS_ENTREGADO  => 'Entregado',
    ];

    public const STATUS_COLORS = [
        self::STATUS_PENDIENTE  => 'yellow',
        self::STATUS_PREPARADO  => 'blue',
        self::STATUS_EN_REPARTO => 'purple',
        self::STATUS_ENTREGADO  => 'green',
    ];

    protected $fillable = [
        'order_number',
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'delivery_address',
        'postal_code',
        'status',
        'subtotal',
        'delivery_fee',
        'total',
        'estimated_delivery_date',
        'notes',
        'verial_pedido_id',
        'verial_referencia',
        'verial_estado',
        'verial_enviado_at',
    ];

    protected function casts(): array
    {
        return [
            'subtotal'                => 'decimal:2',
            'delivery_fee'            => 'decimal:2',
            'total'                   => 'decimal:2',
            'estimated_delivery_date' => 'date',
            'verial_enviado_at'       => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = 'BP-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -5));
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'gray';
    }

    public function formattedTotal(): string
    {
        return number_format((float) $this->total, 2, ',', '.').' €';
    }

    public function formattedSubtotal(): string
    {
        return number_format((float) $this->subtotal, 2, ',', '.').' €';
    }

    public function formattedDeliveryFee(): string
    {
        return number_format((float) $this->delivery_fee, 2, ',', '.').' €';
    }

    /**
     * Día de reparto anunciado al cliente: «jueves, 27 de agosto».
     */
    public function formattedEstimatedDelivery(): ?string
    {
        return $this->estimated_delivery_date?->translatedFormat('l, j \d\e F');
    }

    public function isEnviadoAVerial(): bool
    {
        return $this->verial_pedido_id !== null;
    }
}
