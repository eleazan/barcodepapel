<?php

declare(strict_types=1);

namespace App\Services\Jobs;

/**
 * Un contador de la tarea, tal como se pinta en el panel.
 */
class TaskStat
{
    public function __construct(
        public readonly string $label,
        public readonly int|string $value,
        public readonly string $color = 'sky',
    ) {}
}
