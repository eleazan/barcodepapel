<?php

declare(strict_types=1);

namespace App\Services\Jobs;

/**
 * Registro de las tareas que aparecen en /admin/jobs.
 *
 * Se rellena en AppServiceProvider. Mismo patrón que los canales de
 * notificación: añadir una tarea es registrarla aquí, sin tocar el panel.
 */
class BatchTaskRegistry
{
    /** @var array<string,BatchTask> */
    private array $tasks = [];

    public function register(BatchTask $task): self
    {
        $this->tasks[$task->key()] = $task;

        return $this;
    }

    /** @return array<string,BatchTask> */
    public function all(): array
    {
        return $this->tasks;
    }

    public function find(string $key): ?BatchTask
    {
        return $this->tasks[$key] ?? null;
    }
}
