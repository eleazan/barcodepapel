<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Jobs\BatchHistory;
use App\Services\Jobs\BatchTaskRegistry;
use Illuminate\Console\Command;

/**
 * Mismo lanzamiento que el panel /admin/jobs, desde la consola o el scheduler.
 */
class RunBatchTaskCommand extends Command
{
    protected $signature = 'jobs:run
                            {tarea? : Clave de la tarea. Sin ella, lista las disponibles}
                            {--cantidad=200 : Máximo de elementos a encolar}';

    protected $description = 'Lanza un lote de una tarea en segundo plano';

    public function handle(BatchTaskRegistry $registry, BatchHistory $history): int
    {
        $key = $this->argument('tarea');

        if ($key === null) {
            $this->listarTareas($registry);

            return self::SUCCESS;
        }

        $tarea = $registry->find((string) $key);

        if ($tarea === null) {
            $this->error("No existe la tarea «{$key}».");
            $this->listarTareas($registry);

            return self::FAILURE;
        }

        if ($history->running($tarea->key()) !== null) {
            $this->warn('Ya hay un lote en curso para esta tarea. No se lanza otro.');

            return self::SUCCESS;
        }

        $disponibles = $tarea->availableNow();

        if ($disponibles === 0) {
            $this->info($tarea->pendingCount() === 0
                ? 'Nada pendiente.'
                : 'El límite de la API no deja lanzar más ahora mismo.');

            return self::SUCCESS;
        }

        $cantidad = min((int) $this->option('cantidad'), $disponibles);
        $batchId  = $tarea->dispatchBatch($cantidad);

        if ($batchId === null) {
            $this->info('Nada pendiente.');

            return self::SUCCESS;
        }

        $this->info("Lote {$batchId} lanzado con {$cantidad} elementos.");
        $this->line('Recuerda tener un procesador de cola en marcha: php artisan queue:work');

        return self::SUCCESS;
    }

    private function listarTareas(BatchTaskRegistry $registry): void
    {
        $filas = collect($registry->all())->map(fn ($tarea) => [
            $tarea->key(),
            $tarea->label(),
            $tarea->pendingCount(),
            $tarea->availableNow(),
        ])->all();

        $this->table(['Clave', 'Tarea', 'Pendientes', 'Lanzables ahora'], $filas);
    }
}
