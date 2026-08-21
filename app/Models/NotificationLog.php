<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    public const STATUS_SENT    = 'sent';
    public const STATUS_FAILED  = 'failed';
    public const STATUS_PENDING = 'pending';

    public const CHANNEL_EMAIL    = 'email';
    public const CHANNEL_WHATSAPP = 'whatsapp';
    public const CHANNEL_TELEGRAM = 'telegram';
    public const CHANNEL_SMS      = 'sms';

    public const CHANNELS = [
        self::CHANNEL_EMAIL    => 'Email',
        self::CHANNEL_WHATSAPP => 'WhatsApp',
        self::CHANNEL_TELEGRAM => 'Telegram',
        self::CHANNEL_SMS      => 'SMS',
    ];

    public const EVENT_STATUS_CHANGED = 'status_changed';
    public const EVENT_MANUAL_RESEND  = 'manual_resend';
    public const EVENT_ORDER_CREATED  = 'order_created';

    public const EVENTS = [
        self::EVENT_ORDER_CREATED  => 'Pedido recibido',
        self::EVENT_STATUS_CHANGED => 'Cambio de estado',
        self::EVENT_MANUAL_RESEND  => 'Reenvío manual',
    ];

    protected $fillable = [
        'order_id',
        'channel',
        'recipient',
        'subject',
        'body',
        'status',
        'error_message',
        'event',
        'metadata',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'sent_at'  => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function channelLabel(): string
    {
        return self::CHANNELS[$this->channel] ?? $this->channel;
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_SENT    => 'Enviado',
            self::STATUS_FAILED  => 'Error',
            self::STATUS_PENDING => 'Pendiente',
            default              => $this->status,
        };
    }

    public function eventLabel(): string
    {
        return self::EVENTS[$this->event] ?? $this->event;
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            self::STATUS_SENT    => 'green',
            self::STATUS_FAILED  => 'red',
            self::STATUS_PENDING => 'yellow',
            default              => 'gray',
        };
    }
}
