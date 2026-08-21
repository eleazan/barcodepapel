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
        [$subject, $body] = $event === NotificationLog::EVENT_ORDER_CREATED
            ? $this->buildOrderCreated($order)
            : $this->buildStatusChanged($order);

        Mail::raw($body, function ($message) use ($recipient, $subject) {
            $message->to($recipient)
                ->subject($subject);
        });

        return [
            'subject' => $subject,
            'body'    => $body,
        ];
    }

    /**
     * Acuse de recibo del pedido, con el detalle de las líneas.
     *
     * @return array{0: string, 1: string}
     */
    private function buildOrderCreated(Order $order): array
    {
        $subject = "🧾 Hemos recibido tu pedido {$order->order_number}";

        $lineas = $order->items->map(function ($item) {
            $nombre = $item->product?->name ?? 'Producto';

            return "  · {$item->quantity} × {$nombre} — ".$this->money((float) $item->total);
        })->all();

        $body = implode("\n", array_merge([
            "¡Hola, {$order->customer_name}!",
            '',
            "Hemos recibido tu pedido {$order->order_number}. Lo estamos revisando y te avisaremos cuando esté preparado para el reparto.",
            '',
            'Detalle del pedido:',
        ], $lineas, [
            '',
            'Subtotal: '.$this->money((float) $order->subtotal),
            'Gastos de reparto: '.$this->money((float) $order->delivery_fee),
            'Total: '.$this->money((float) $order->total),
            '',
            "Dirección de entrega: {$order->delivery_address}, CP {$order->postal_code}",
            '',
            'El pago se realiza en el momento de la entrega. Repartimos nosotros mismos en toda la isla, sin paquetería externa.',
            '',
            'Gracias por comprar en tu librería de barrio.',
            'Barco de Papel — Ibiza',
        ]));

        return [$subject, $body];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function buildStatusChanged(Order $order): array
    {
        $statusLabel = $order->statusLabel();

        $emoji = match ($order->status) {
            Order::STATUS_PREPARADO  => '📦',
            Order::STATUS_EN_REPARTO => '🚚',
            Order::STATUS_ENTREGADO  => '✅',
            default                  => '📋',
        };

        $subject = "{$emoji} Tu pedido {$order->order_number} — {$statusLabel}";

        $statusMessage = match ($order->status) {
            Order::STATUS_PREPARADO  => 'Tu pedido está listo y preparado para el reparto.',
            Order::STATUS_EN_REPARTO => 'Tu pedido está en camino. ¡Pronto lo recibirás!',
            Order::STATUS_ENTREGADO  => 'Tu pedido ha sido entregado. ¡Gracias por tu compra!',
            default                  => 'El estado de tu pedido ha sido actualizado.',
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
            'Total: '.$this->money((float) $order->total),
            '',
            'Un saludo, Barco de Papel',
        ]);

        return [$subject, $body];
    }

    private function money(float $amount): string
    {
        return number_format($amount, 2, ',', '.').' €';
    }
}
