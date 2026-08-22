<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\Order;

interface NotificationChannel
{
    /**
     * Unique channel identifier (email, whatsapp, telegram, sms...).
     */
    public function id(): string;

    /**
     * Determine if this channel can send to the given order.
     * e.g., email channel needs customer_email, whatsapp needs customer_phone.
     */
    public function canSend(Order $order, ?string $recipient = null): bool;

    /**
     * Get the default recipient for this channel from the order.
     */
    public function defaultRecipient(Order $order): ?string;

    /**
     * Send the notification. Returns [subject, body] on success.
     *
     * @return array{subject: ?string, body: string}
     *
     * @throws \Exception on failure
     */
    public function send(Order $order, string $recipient, string $event, array $context = []): array;
}
