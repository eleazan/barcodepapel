<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Models\NotificationLog;
use App\Models\Order;

class OrderNotificationService
{
    /** @var array<string, NotificationChannel> */
    private array $channels = [];

    /**
     * Register a notification channel.
     */
    public function registerChannel(NotificationChannel $channel): void
    {
        $this->channels[$channel->id()] = $channel;
    }

    /**
     * Get a registered channel by id.
     */
    public function channel(string $id): ?NotificationChannel
    {
        return $this->channels[$id] ?? null;
    }

    /**
     * Get all registered channels.
     *
     * @return array<string, NotificationChannel>
     */
    public function channels(): array
    {
        return $this->channels;
    }

    /**
     * Get channels that can send to this order.
     *
     * @return array<string, NotificationChannel>
     */
    public function availableChannels(Order $order): array
    {
        return array_filter(
            $this->channels,
            fn (NotificationChannel $ch) => $ch->canSend($order),
        );
    }

    /**
     * Send notification through a specific channel and log the result.
     */
    public function send(
        Order $order,
        string $channelId,
        string $event = NotificationLog::EVENT_STATUS_CHANGED,
        ?string $recipient = null,
        array $context = [],
    ): NotificationLog {
        $channel = $this->channels[$channelId] ?? null;

        if (! $channel) {
            return $this->createLog($order, $channelId, $recipient ?? '', $event, [
                'status'        => NotificationLog::STATUS_FAILED,
                'error_message' => "Canal '{$channelId}' no registrado.",
            ]);
        }

        $recipient = $recipient ?? $channel->defaultRecipient($order);

        if (! $recipient || ! $channel->canSend($order, $recipient)) {
            return $this->createLog($order, $channelId, $recipient ?? '', $event, [
                'status'        => NotificationLog::STATUS_FAILED,
                'error_message' => 'Destinatario no válido o no disponible.',
            ]);
        }

        try {
            $result = $channel->send($order, $recipient, $event, $context);

            return $this->createLog($order, $channelId, $recipient, $event, [
                'subject'  => $result['subject'] ?? null,
                'body'     => $result['body'],
                'status'   => NotificationLog::STATUS_SENT,
                'sent_at'  => now(),
                'metadata' => $context['metadata'] ?? null,
            ]);
        } catch (\Throwable $e) {
            return $this->createLog($order, $channelId, $recipient, $event, [
                'body'          => 'Error al enviar.',
                'status'        => NotificationLog::STATUS_FAILED,
                'error_message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send through all available channels for the order.
     *
     * @return NotificationLog[]
     */
    public function sendAll(
        Order $order,
        string $event = NotificationLog::EVENT_STATUS_CHANGED,
        array $context = [],
    ): array {
        $logs = [];

        foreach ($this->availableChannels($order) as $channel) {
            $logs[] = $this->send($order, $channel->id(), $event, null, $context);
        }

        return $logs;
    }

    /**
     * Aviso interno a la librería de que ha entrado un pedido por la web.
     *
     * Va al buzón de config/tienda.php, no al del cliente, y queda registrado
     * en el historial del pedido como cualquier otro envío.
     */
    public function notifyStore(Order $order): ?NotificationLog
    {
        $buzon = config('tienda.email');

        if (empty($buzon)) {
            return null;
        }

        return $this->send(
            order: $order,
            channelId: NotificationLog::CHANNEL_EMAIL,
            event: NotificationLog::EVENT_STORE_COPY,
            recipient: $buzon,
        );
    }

    /**
     * Resend a previous notification, optionally with a corrected recipient.
     */
    public function resend(NotificationLog $log, ?string $newRecipient = null): NotificationLog
    {
        return $this->send(
            order: $log->order,
            channelId: $log->channel,
            event: NotificationLog::EVENT_MANUAL_RESEND,
            recipient: $newRecipient ?? $log->recipient,
            context: ['metadata' => ['resent_from' => $log->id]],
        );
    }

    private function createLog(Order $order, string $channel, string $recipient, string $event, array $data): NotificationLog
    {
        return NotificationLog::create([
            'order_id' => $order->id,
            // Si el pedido es de un cliente registrado, el aviso aparece también
            // en su ficha.
            'user_id'       => $order->user_id,
            'channel'       => $channel,
            'recipient'     => $recipient,
            'event'         => $event,
            'subject'       => $data['subject'] ?? null,
            'body'          => $data['body']    ?? '',
            'status'        => $data['status'],
            'error_message' => $data['error_message'] ?? null,
            'metadata'      => $data['metadata']      ?? null,
            'sent_at'       => $data['sent_at']       ?? null,
        ]);
    }
}
