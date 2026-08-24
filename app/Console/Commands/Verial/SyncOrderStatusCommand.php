<?php

declare(strict_types=1);

namespace App\Console\Commands\Verial;

use App\Services\Verial\SyncOrderStatusService;
use App\Services\Verial\VerialClient;
use Illuminate\Console\Command;

class SyncOrderStatusCommand extends Command
{
    protected $signature = 'verial:sync-order-status';

    protected $description = 'Actualiza el estado de pedidos enviados a Verial';

    public function handle(VerialClient $client, SyncOrderStatusService $service): int
    {
        if (! $client->isConfigured()) {
            $this->warn('Verial no está configurado. Define VERIAL_HOST y VERIAL_SESSION en .env.');

            return self::SUCCESS;
        }

        $this->info('Sincronizando estado de pedidos desde Verial...');

        $result = $service->sync();

        $this->info($result->summary());

        if (! $result->isOk()) {
            foreach ($result->errorMessages as $msg) {
                $this->warn('  - '.$msg);
            }
        }

        return self::SUCCESS;
    }
}
