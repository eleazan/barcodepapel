<?php

declare(strict_types=1);

namespace App\Services\Verial;

use App\Models\Order;
use App\Models\VerialSyncLog;
use Illuminate\Support\Facades\Log;

class SendOrderService
{
    public function __construct(
        private readonly VerialClient $client,
    ) {}

    /**
     * Envía un pedido al sistema Verial.
     *
     * @throws \RuntimeException
     */
    public function send(Order $order): void
    {
        if (! $this->client->isConfigured()) {
            throw new \RuntimeException(
                'Verial no configurado. Configura VERIAL_HOST y VERIAL_SESSION en .env.'
            );
        }

        $order->loadMissing(['items.product']);

        $payload = $this->buildPayload($order);

        try {
            $response = $this->client->post('NuevoDocClienteWS', $payload);

            $verialPedidoId = (int) ($response['CodigoPedido'] ?? $response['Codigo'] ?? 0);
            $verialReferencia = (string) ($response['Referencia'] ?? $response['referencia'] ?? '');

            $order->update([
                'verial_pedido_id'  => $verialPedidoId ?: null,
                'verial_referencia' => $verialReferencia ?: null,
                'verial_enviado_at' => now(),
            ]);

            VerialSyncLog::record(
                entityType: 'pedido',
                entityId: $order->id,
                operation: 'send_order',
                verialMethod: 'NuevoDocClienteWS',
                response: $response,
            );
        } catch (\Throwable $e) {
            Log::error('Error al enviar pedido a Verial', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            VerialSyncLog::record(
                entityType: 'pedido',
                entityId: $order->id,
                operation: 'send_order',
                verialMethod: 'NuevoDocClienteWS',
                response: [],
                error: $e->getMessage(),
            );

            throw new \RuntimeException(
                'Error al enviar pedido a Verial: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    private function buildPayload(Order $order): array
    {
        $clienteId = 0;
        if ($order->relationLoaded('user') && $order->user !== null) {
            $clienteId = $order->user->verial_cliente_id ?? 0;
        }

        $lineas = $order->items->map(function ($item) {
            return [
                'CodigoArticulo'  => $item->product?->verial_id ?? 0,
                'Cantidad'        => $item->quantity,
                'PrecioUnitario'  => (float) $item->unit_price,
            ];
        })->toArray();

        return [
            'CodigoCliente'   => $clienteId,
            'NombreCliente'   => $order->customer_name,
            'EmailCliente'    => $order->customer_email,
            'TelefonoCliente' => $order->customer_phone,
            'DireccionEntrega' => $order->delivery_address,
            'CodigoPostal'    => $order->postal_code,
            'Lineas'          => $lineas,
        ];
    }
}
