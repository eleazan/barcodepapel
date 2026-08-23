<?php

declare(strict_types=1);

namespace App\Services\Jobs;

/**
 * Una tarea en segundo plano que el admin puede lanzar por lotes desde
 * /admin/jobs.
 *
 * Para añadir otra: implementar esta interfaz y registrarla en
 * AppServiceProvider, igual que los canales de notificación. El panel se
 * pinta solo a partir de lo que declare cada tarea.
 */
interface BatchTask
{
    /** Identificador en la URL. Solo minúsculas y guiones. */
    public function key(): string;

    public function label(): string;

    public function description(): string;

    /**
     * Contadores que el panel pinta como tarjetas.
     *
     * @return array<int,TaskStat>
     */
    public function stats(): array;

    /** Elementos que quedan por procesar. */
    public function pendingCount(): int;

    /**
     * Pendientes que se pueden encolar ahora mismo, ya descontados los límites
     * externos (cuotas de API). Nunca mayor que pendingCount().
     */
    public function availableNow(): int;

    /** Explicación del límite que aplica hoy, para el aviso del panel. */
    public function limitNote(): ?string;

    /**
     * Encola el lote y devuelve su identificador, o null si no había nada que
     * hacer.
     */
    public function dispatchBatch(int $limit): ?string;
}
