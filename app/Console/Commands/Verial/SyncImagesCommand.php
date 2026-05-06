<?php

declare(strict_types=1);

namespace App\Console\Commands\Verial;

use App\Services\Verial\SyncImagesService;
use App\Services\Verial\VerialClient;
use Illuminate\Console\Command;

class SyncImagesCommand extends Command
{
    protected $signature = 'verial:sync-images';

    protected $description = 'Descarga y sincroniza imágenes de artículos desde Verial';

    public function handle(VerialClient $client, SyncImagesService $service): int
    {
        if (! $client->isConfigured()) {
            $this->warn('Verial no está configurado. Define VERIAL_HOST y VERIAL_SESSION en .env.');

            return self::SUCCESS;
        }

        $this->info('Sincronizando imágenes desde Verial...');

        $result = $service->sync();

        $this->info($result->summary());

        if (! $result->isOk()) {
            foreach ($result->errorMessages as $msg) {
                $this->warn('  - ' . $msg);
            }
        }

        return self::SUCCESS;
    }
}
