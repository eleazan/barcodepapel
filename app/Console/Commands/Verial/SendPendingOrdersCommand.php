<?php

declare(strict_types=1);

namespace App\Console\Commands\Verial;

use App\Jobs\Verial\SendOrderToVerialJob;
use App\Models\Order;
use App\Services\Verial\VerialClient;
use Illuminate\Console\Command;

class SendPendingOrdersCommand extends Command
{
    protected $signature = 'verial:send-pending-orders';

    protected $description = 'Envía a Verial los pedidos pendientes de sincronizar';

    public function handle(VerialClient $client): int
    {
        if (! $client->isConfigured()) {
            $this->warn('Verial no está configurado. Define VERIAL_HOST y VERIAL_SESSION en .env.');

            return self::SUCCESS;
        }

        $pedidos = Order::whereNull('verial_enviado_at')
            ->whereIn('status', [
                Order::STATUS_PREPARADO,
                Order::STATUS_EN_REPARTO,
                Order::STATUS_ENTREGADO,
            ])
            ->get();

        if ($pedidos->isEmpty()) {
            $this->info('No hay pedidos pendientes de enviar a Verial.');

            return self::SUCCESS;
        }

        $this->info("Despachando {$pedidos->count()} pedido(s) a Verial...");

        foreach ($pedidos as $order) {
            SendOrderToVerialJob::dispatch($order);
            $this->line("  - Pedido #{$order->order_number} despachado.");
        }

        $this->info('Jobs encolados correctamente.');

        return self::SUCCESS;
    }
}
