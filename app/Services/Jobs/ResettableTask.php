<?php

declare(strict_types=1);

namespace App\Services\Jobs;

/**
 * Tarea que recuerda por qué elementos ya ha pasado y puede olvidarlo.
 *
 * Sin esto, un lote solo procesa lo pendiente y nunca repite —que es lo
 * habitual—; con esto el admin puede devolver a la cola, de forma explícita,
 * lo que se descartó tras agotar los intentos.
 */
interface ResettableTask
{
    /** Devuelve a pendientes lo descartado. Responde cuántos elementos son. */
    public function resetDiscarded(): int;

    /** Texto del botón, p. ej. «Reintentar los 42 descartados». */
    public function resetLabel(): string;

    public function discardedCount(): int;
}
