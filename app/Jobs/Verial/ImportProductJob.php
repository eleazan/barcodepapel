<?php

declare(strict_types=1);

namespace App\Jobs\Verial;

use App\Services\Verial\SyncCatalogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImportProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        private readonly array $articulo,
    ) {}

    public function handle(SyncCatalogService $service): void
    {
        // El servicio procesa un artículo individual reutilizando la lógica de mapeo
        $verialId = $this->articulo['CodigoArticulo'] ?? null;

        Log::info('ImportProductJob: procesando artículo', ['verial_id' => $verialId]);

        // Llamamos sync con datos ya en memoria (sin llamar al servidor de nuevo)
        // El artículo viene pre-cargado desde el job dispatch
        $service->syncArticulo($this->articulo);
    }
}
