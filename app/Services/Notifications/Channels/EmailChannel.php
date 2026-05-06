<?php

declare(strict_types=1);

namespace App\Services\Notifications\Channels;

use App\Models\NotificationLog;
use App\Models\Order;
use App\Services\Notifications\NotificationChannel;
use Illuminate\Support\Facades\Mail;

class EmailChannel implements NotificationChannel
{
    public function id(): string
    {
        return NotificationLog::CHANNEL_EMAIL;
    }

    public function canSend(Order $order, ?string $recipient = null): bool
    {
        $email = $recipient ?? $this->defaultRecipient($order);

        return ! empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function defaultRecipient(Order $order): ?string
    {
        return $order->customer_email;
    }

    public function send(Order $order, string $recipient, string $event, array $context = []): array
    {
        $statusLabel = $order->statusLabel();
        $previousStatus = $context['previous_status'] ?? null;

        $emoji = match ($order->status) {
            Order::STATUS_PREPARADO => '📦',
            Order::STATUS_EN_REPARTO => '🚚',
            Order::STATUS_ENTREGADO => '✅',
            default => '📋',
        };

        $subject = "{$emoji} Tu pedido {$order->order_number} — {$statusLabel}";

        $statusMessage = match ($order->status) {
            Order::STATUS_PREPARADO => 'Tu pedido está listo y preparado para el reparto.',
            Order::STATUS_EN_REPARTO => 'Tu pedido está en camino. ¡Pronto lo recibirás!',
            Order::STATUS_ENTREGADO => 'Tu pedido ha sido entregado. ¡Gracias por tu compra!',
            default => 'El estado de tu pedido ha sido actualizado.',
        };

        $body = implode("\n", [
            "¡Hola, {$order->customer_name}!",
            '',
            "Tu pedido {$order->order_number} ha cambiado de estado.",
            '',
            "Nuevo estado: {$statusLabel}",
            $statusMessage,
            '',
            "Dirección de entrega: {$order->delivery_address}, CP {$order->postal_code}",
            "Total: " . number_format((float) $order->total, 2, ',', '.') . ' €',
            '',
            'Un saludo, BarcodePapel',
        ]);

        Mail::raw($body, function ($message) use ($recipient, $subject, $order) {
            $message->to($recipient)
                ->subject($subject);
        });

        return [
            'subject' => $subject,
            'body' => $body,
        ];
    }
}
