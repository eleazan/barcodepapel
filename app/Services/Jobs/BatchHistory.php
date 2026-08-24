<?php

declare(strict_types=1);

namespace App\Services\Jobs;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Lectura del historial de lotes que Laravel guarda en `job_batches`.
 *
 * Cada tarea nombra sus lotes con su propia clave, así que no hace falta
 * ninguna tabla adicional para saber qué se lanzó, cuándo y cómo va.
 */
class BatchHistory
{
    /** @return Collection<int,object> */
    public function forTask(string $key, int $limit = 10): Collection
    {
        return DB::table('job_batches')
            ->where('name', $key)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /** Lote en curso de la tarea, si hay alguno. */
    public function running(string $key): ?object
    {
        return DB::table('job_batches')
            ->where('name', $key)
            ->whereNull('finished_at')
            ->whereNull('cancelled_at')
            ->orderByDesc('created_at')
            ->first();
    }

    /** Porcentaje procesado de un lote, redondeado. */
    public function progress(object $batch): int
    {
        if ((int) $batch->total_jobs === 0) {
            return 100;
        }

        $done = (int) $batch->total_jobs - (int) $batch->pending_jobs;

        return (int) round($done / (int) $batch->total_jobs * 100);
    }
}
