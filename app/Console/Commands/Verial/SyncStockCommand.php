<?php

declare(strict_types=1);

namespace App\Console\Commands\Verial;

use App\Services\Verial\SyncStockService;
use App\Services\Verial\VerialClient;
use Illuminate\Console\Command;

class SyncStockCommand extends Command
{
    protected $signature = 'verial:sync-stock';

    protected $description = 'Actualiza el stock de productos desde Verial';

    public function handle(VerialClient $client, SyncStockService $service): int
    {
        if (! $client->isConfigured()) {
            $this->warn('Verial no está configurado. Define VERIAL_HOST y VERIAL_SESSION en .env.');

            return self::SUCCESS;
        }

        $this->info('Sincronizando stock desde Verial...');

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
