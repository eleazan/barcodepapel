<?php

declare(strict_types=1);

namespace App\Services\Verial;

use App\Models\Order;
use App\Models\VerialSyncLog;
use Illuminate\Support\Facades\Log;

class SyncOrderStatusService
{
    public function __construct(
        private readonly VerialClient $client,
    ) {}

    public function sync(): SyncResult
    {
        $processed     = 0;
        $updated       = 0;
        $errors        = 0;
        $errorMessages = [];

        try {
            $response = $this->client->get('EstadoPedidosWS');
            $pedidos  = $response['Pedidos'] ?? $response['pedidos'] ?? $response;

            if (! is_array($pedidos)) {
                $pedidos = [];
            }

            foreach ($pedidos as $item) {
                $processed++;

                try {
                    $verialPedidoId = (int) ($item['CodigoPedido'] ?? $item['Codigo'] ?? 0);
                    $estado         = (string) ($item['Estado'] ?? '');

                    if ($verialPedidoId === 0) {
                        continue;
                    }

                    $rows = Order::where('verial_pedido_id', $verialPedidoId)
                        ->update(['verial_estado' => $estado]);

                    if ($rows > 0) {
                        $updated++;
                    }
                } catch (\Throwable $e) {
                    $errors++;
                    $errorMessages[] = $e->getMessage();
                    Log::warning('Error al actualizar estado de pedido Verial', [
                        'item'  => $item,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            VerialSyncLog::record(
                entityType: 'pedido',
                entityId: null,
                operation: 'sync_order_status',
                verialMethod: 'EstadoPedidosWS',
                response: ['processed' => $processed, 'updated' => $updated, 'errors' => $errors],
            );
        } catch (\Throwable $e) {
            $errors++;
            $errorMessages[] = $e->getMessage();

            VerialSyncLog::record(
                entityType: 'pedido',
                entityId: null,
                operation: 'sync_order_status',
                verialMethod: 'EstadoPedidosWS',
                response: [],
                error: $e->getMessage(),
            );
        }

        return new SyncResult($processed, 0, $updated, $errors, $errorMessages);
    }
}
