<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Product;
use App\Services\Books\BookEnricher;
use App\Services\Books\CoverOutcome;
use App\Services\Books\GoogleBooksQuota;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimited;
use Illuminate\Queue\SerializesModels;

class FetchBookDataFromIsbn implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Margen amplio: un job puede volver a la cola varias veces esperando a
     * que se renueve la cuota diaria de Google Books.
     */
    public int $tries = 8;

    /** Segundos entre reintentos: 1m, 5m, 15m */
    public array $backoff = [60, 300, 900];

    /**
     * @param  bool  $refresh  Rehace la ficha aunque el libro ya tenga portada.
     */
    public function __construct(
        private readonly Product $product,
        private readonly bool $refresh = false,
    ) {}

    /** Tope absoluto: pasados tres días el job se descarta. */
    public function retryUntil(): \DateTimeInterface
    {
        return now()->addDays(3);
    }

    /**
     * Red de seguridad frente a los 100 req/100 s de Google Books. El ritmo
     * principal lo pone el retardo escalonado con que la tarea encola el lote.
     *
     * @return array<int,object>
     */
    public function middleware(): array
    {
        return [new RateLimited('google-books')];
    }

    public function handle(BookEnricher $enricher, GoogleBooksQuota $quota): void
    {
        // El admin puede cancelar el lote desde el panel a mitad de proceso.
        if ($this->batch()?->cancelled()) {
            return;
        }

        $outcome = $enricher->enrich($this->product, $this->refresh);

        if ($outcome === CoverOutcome::LimiteAlcanzado) {
            // Sin cuota no se insiste: se vuelve cuando la API se renueve.
            $this->release($quota->secondsUntilReset());
        }
    }
}
