<?php

declare(strict_types=1);

namespace App\Services\Notifications\Channels;

use App\Models\NotificationLog;
use App\Models\Order;
use App\Services\Notifications\NotificationChannel;
use Illuminate\Support\Facades\Mail;

/**
 * Correos de pedido, maquetados sobre el layout común (<x-mail.layout>).
 *
 * Cada mensaje se envía en HTML y en texto plano: el texto es además lo que se
 * guarda en `notification_logs`, para que el historial del pedido se pueda leer
 * sin renderizar nada.
 */
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
        $mensaje = match ($event) {
            NotificationLog::EVENT_STORE_COPY    => $this->buildStoreCopy($order),
            NotificationLog::EVENT_ORDER_CREATED => $this->buildOrderCreated($order),
            default                              => $this->buildStatusChanged($order),
        };

        Mail::send(
            ['html' => $mensaje['view'], 'text' => 'emails.plain'],
            $mensaje['data'] + ['texto' => $mensaje['body']],
            function ($message) use ($recipient, $mensaje) {
                $message->to($recipient)
                    ->subject($mensaje['subject'])
                    // El remitente puede ser la cuenta de correo desde la que se
                    // envía; las respuestas del cliente van al buzón público.
                    ->replyTo(config('tienda.email'), config('tienda.nombre'));
            },
        );

        return [
            'subject' => $mensaje['subject'],
            'body'    => $mensaje['body'],
        ];
    }

    /**
     * Acuse de recibo al cliente, con el detalle de las líneas.
     *
     * @return array{subject: string, view: string, data: array<string, mixed>, body: string}
     */
    private function buildOrderCreated(Order $order): array
    {
        $lineas = $order->items->map(function ($item) {
            $nombre = $item->product?->name ?? 'Producto';

            return "  · {$item->quantity} × {$nombre} — ".$this->money((float) $item->total);
        })->all();

        $body = implode("\n", array_merge([
            "¡Hola, {$order->customer_name}!",
            '',
            "Hemos recibido tu pedido {$order->order_number}. Lo estamos revisando y te avisaremos cuando esté preparado para el reparto.",
            ...$this->fechaPrevista($order),
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
            $this->firma(),
        ]));

        return [
            'subject' => "🧾 Hemos recibido tu pedido {$order->order_number}",
            'view'    => 'emails.orders.created',
            'data'    => ['order' => $order],
            'body'    => $body,
        ];
    }

    /**
     * Aviso de cambio de estado. El pedido entregado tiene su propio mensaje.
     *
     * @return array{subject: string, view: string, data: array<string, mixed>, body: string}
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

        $titulo = match ($order->status) {
            Order::STATUS_PREPARADO  => 'Tu pedido ya está preparado',
            Order::STATUS_EN_REPARTO => 'Tu pedido va de camino',
            Order::STATUS_ENTREGADO  => 'Pedido entregado, ¡gracias!',
            default                  => 'Tu pedido ha cambiado de estado',
        };

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
            'Un saludo,',
            $this->firma(),
        ]);

        return [
            'subject' => "{$emoji} Tu pedido {$order->order_number} — {$statusLabel}",
            'view'    => 'emails.orders.status',
            'data'    => ['order' => $order, 'titulo' => $titulo, 'mensaje' => $statusMessage],
            'body'    => $body,
        ];
    }

    /**
     * Copia interna para la librería cuando entra un pedido por la web.
     *
     * @return array{subject: string, view: string, data: array<string, mixed>, body: string}
     */
    private function buildStoreCopy(Order $order): array
    {
        $lineas = $order->items->map(function ($item) {
            $nombre = $item->product?->name ?? 'Producto';

            return "  · {$item->quantity} × {$nombre} — ".$this->money((float) $item->total);
        })->all();

        $body = implode("\n", array_merge([
            "Nuevo pedido web: {$order->order_number}",
            '',
            "Cliente: {$order->customer_name}",
            "Teléfono: {$order->customer_phone}",
            'Email: '.($order->customer_email ?: 'no facilitado — avisar por teléfono'),
            '',
            "Entrega: {$order->delivery_address}, CP {$order->postal_code}",
            ...$this->fechaPrevista($order, 'Fecha prevista: '),
            $order->notes ? "Indicaciones: {$order->notes}" : '',
            '',
            'Líneas:',
        ], $lineas, [
            '',
            'Total: '.$this->money((float) $order->total),
            '',
            'El pedido entra en Verial al marcarlo como preparado.',
        ]));

        return [
            'subject' => "🛎️ Nuevo pedido {$order->order_number} — {$order->customer_name}",
            'view'    => 'emails.orders.store-copy',
            'data'    => ['order' => $order],
            'body'    => $body,
        ];
    }

    /**
     * Día de reparto anunciado al confirmar el pedido, si la zona tiene uno.
     *
     * @return list<string>
     */
    private function fechaPrevista(Order $order, ?string $prefijo = null): array
    {
        $fecha = $order->formattedEstimatedDelivery();

        if ($fecha === null) {
            return [];
        }

        return $prefijo !== null
            ? [$prefijo.$fecha]
            : ['', "Según los días de reparto de tu zona, te lo llevamos el {$fecha}."];
    }

    /**
     * Firma con los datos de la tienda (config/tienda.php).
     */
    private function firma(): string
    {
        return implode("\n", [
            (string) config('tienda.nombre'),
            config('tienda.direccion.calle').' · '.config('tienda.direccion.codigo_postal').' '.config('tienda.direccion.ciudad'),
            'Tel. '.config('tienda.telefono.display'),
        ]);
    }

    private function money(float $amount): string
    {
        return number_format($amount, 2, ',', '.').' €';
    }
}
