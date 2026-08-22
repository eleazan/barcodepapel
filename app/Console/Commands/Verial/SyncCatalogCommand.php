<?php

declare(strict_types=1);

namespace App\Console\Commands\Verial;

use App\Services\Verial\SyncCatalogService;
use App\Services\Verial\VerialClient;
use Illuminate\Console\Command;

class SyncCatalogCommand extends Command
{
    protected $signature = 'verial:sync-catalog
                            {--full : Sincronización completa sin filtro de fecha}
                            {--since= : Fecha desde la que sincronizar (YYYY-MM-DD)}';

    protected $description = 'Sincroniza el catálogo de artículos desde Verial';

    public function handle(VerialClient $client, SyncCatalogService $service): int
    {
        if (! $client->isConfigured()) {
            $this->warn('Verial no está configurado. Define VERIAL_HOST y VERIAL_SESSION en .env.');

            return self::SUCCESS;
        }

        $since = null;

        if (! $this->option('full')) {
            $since = $this->option('since') ?? now()->toDateString();
        }

        $label = $since !== null ? "desde {$since}" : 'completo';
        $this->info("Sincronizando catálogo Verial ({$label})...");

        $result = $service->sync($since);

        $this->info($result->summary());

        if (! $result->isOk()) {
            foreach ($result->errorMessages as $msg) {
                $this->warn('  - '.$msg);
            }
        }

        return self::SUCCESS;
    }
}
