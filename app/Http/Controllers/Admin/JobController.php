<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RunBatchTaskRequest;
use App\Services\Jobs\BatchHistory;
use App\Services\Jobs\BatchTask;
use App\Services\Jobs\BatchTaskRegistry;
use App\Services\Jobs\ResettableTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Panel de tareas en segundo plano: qué hay pendiente, lanzar un lote, seguir
 * su progreso y cancelarlo.
 */
class JobController extends Controller
{
    public function __construct(
        private readonly BatchTaskRegistry $registry,
        private readonly BatchHistory $history,
    ) {}

    public function index(): View
    {
        $tareas = collect($this->registry->all())->map(fn (BatchTask $task) => [
            'task'        => $task,
            'stats'       => $task->stats(),
            'pendientes'  => $task->pendingCount(),
            'disponibles' => $task->availableNow(),
            'aviso'       => $task->limitNote(),
            'enCurso'     => $this->history->running($task->key()),
            'lotes'       => $this->history->forTask($task->key()),
        ])->values();

        $trabajosEnCola   = DB::table('jobs')->count();
        $trabajosFallidos = DB::table('failed_jobs')->count();

        return view('admin.jobs.index', [
            'tareas'           => $tareas,
            'history'          => $this->history,
            'trabajosEnCola'   => $trabajosEnCola,
            'trabajosFallidos' => $trabajosFallidos,
        ]);
    }

    public function run(RunBatchTaskRequest $request, string $task): RedirectResponse
    {
        $tarea = $this->tarea($task);

        if ($this->history->running($tarea->key()) !== null) {
            return $this->back('Ya hay un lote en curso para esta tarea. Espera a que acabe o cancélalo.', error: true);
        }

        $disponibles = $tarea->availableNow();

        if ($disponibles === 0) {
            return $this->back(
                $tarea->pendingCount() === 0
                    ? 'No hay nada pendiente en esta tarea.'
                    : 'El límite de la API no deja lanzar más hoy. Vuelve a intentarlo mañana.',
                error: true,
            );
        }

        // El tope real manda sobre lo que pida el formulario: nunca se encola
        // más de lo que la cuota del día permite atender.
        $cantidad = min((int) $request->integer('cantidad'), $disponibles);

        $batchId = $tarea->dispatchBatch($cantidad);

        if ($batchId === null) {
            return $this->back('No hay nada pendiente en esta tarea.', error: true);
        }

        return $this->back("Lote lanzado: {$cantidad} elementos en cola.");
    }

    public function cancel(string $task, string $batch): RedirectResponse
    {
        $this->tarea($task);

        Bus::findBatch($batch)?->cancel();

        return $this->back('Lote cancelado. Los trabajos que queden en cola se descartarán al llegar su turno.');
    }

    public function reset(string $task): RedirectResponse
    {
        $tarea = $this->tarea($task);

        if (! $tarea instanceof ResettableTask) {
            return $this->back('Esta tarea no admite reintentos.', error: true);
        }

        $total = $tarea->resetDiscarded();

        return $this->back($total === 0
            ? 'No había nada descartado que reintentar.'
            : "{$total} elementos vuelven a estar pendientes.");
    }

    private function tarea(string $key): BatchTask
    {
        return $this->registry->find($key) ?? abort(404);
    }

    private function back(string $mensaje, bool $error = false): RedirectResponse
    {
        return redirect()->route('admin.jobs.index')
            ->with($error ? 'error' : 'success', $mensaje);
    }
}
