<?php

declare(strict_types=1);

namespace App\Services\Jobs\Tasks;

use App\Jobs\FetchBookDataFromIsbn;
use App\Models\Product;
use App\Models\ProductBookDetail;
use App\Services\Books\GoogleBooksQuota;
use App\Services\Jobs\BatchTask;
use App\Services\Jobs\ResettableTask;
use App\Services\Jobs\TaskStat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Bus;

/**
 * Portadas y fichas de los libros del catálogo.
 *
 * Solo entran los libros que siguen sin portada propia y por los que no se ha
 * preguntado ya: cada intento queda marcado en `product_book_details`, así que
 * lanzar el lote dos veces no repite trabajo.
 */
class BookCoverTask implements BatchTask, ResettableTask
{
    public function __construct(
        private readonly GoogleBooksQuota $quota,
    ) {}

    public function key(): string
    {
        return 'portadas-libros';
    }

    public function label(): string
    {
        return 'Portadas y fichas de libros';
    }

    public function description(): string
    {
        return 'Busca por ISBN en Google Books y, como respaldo, en OpenLibrary. '
            .'Descarga la portada al disco de la tienda y completa autores, editorial, '
            .'páginas y año. Solo pasa por los libros pendientes: los ya resueltos y los '
            .'descartados no vuelven a entrar.';
    }

    /** @return array<int,TaskStat> */
    public function stats(): array
    {
        return [
            new TaskStat('Libros con ISBN', $this->books()->count(), 'sky'),
            new TaskStat('Con portada', $this->withCoverCount(), 'green'),
            new TaskStat('Pendientes', $this->pendingCount(), 'amber'),
            new TaskStat('Descartados', $this->discardedCount(), 'violet'),
        ];
    }

    public function pendingCount(): int
    {
        return $this->pending()->count();
    }

    public function availableNow(): int
    {
        // Cada libro pendiente cuesta una petición a Google Books.
        return min($this->pendingCount(), $this->quota->remaining());
    }

    public function limitNote(): ?string
    {
        $used      = $this->quota->used();
        $limit     = $this->quota->limit();
        $remaining = $this->quota->remaining();

        $note = sprintf(
            'Google Books: %s de %s peticiones consumidas hoy, quedan %s. La cuota se renueva a medianoche.',
            number_format($used, 0, ',', '.'),
            number_format($limit, 0, ',', '.'),
            number_format($remaining, 0, ',', '.'),
        );

        if ($remaining === 0) {
            $note .= ' Hoy no se puede lanzar el lote.';
        }

        return $note.' OpenLibrary no tiene cuota y solo se consulta cuando Google no trae portada.';
    }

    public function dispatchBatch(int $limit): ?string
    {
        $products = $this->pending()->limit($limit)->get();

        if ($products->isEmpty()) {
            return null;
        }

        $perMinute = max(1, (int) config('services.google_books.per_minute', 60));

        // El ritmo se pone al encolar: cada bloque de `perMinute` libros sale un
        // minuto más tarde. Es determinista y no gasta reintentos del job, al
        // contrario que dejar que la API responda 429.
        $jobs = $products->values()->map(
            fn (Product $product, int $index) => (new FetchBookDataFromIsbn($product))
                ->delay(now()->addMinutes(intdiv($index, $perMinute)))
        );

        return Bus::batch($jobs->all())
            ->name($this->key())
            ->allowFailures()
            ->dispatch()
            ->id;
    }

    public function resetDiscarded(): int
    {
        return ProductBookDetail::whereNull('cover_fetched_at')
            ->where('cover_attempts', '>=', ProductBookDetail::MAX_COVER_ATTEMPTS)
            ->update(['cover_attempts' => 0]);
    }

    public function resetLabel(): string
    {
        return 'Reintentar los descartados';
    }

    public function discardedCount(): int
    {
        return $this->books()
            ->whereHas('bookDetail', fn (Builder $q) => $q
                ->whereNull('cover_fetched_at')
                ->where('cover_attempts', '>=', ProductBookDetail::MAX_COVER_ATTEMPTS)
            )
            ->count();
    }

    /** Libros del catálogo con ISBN-13 utilizable. */
    private function books(): Builder
    {
        return Product::query()
            ->where('tipo_articulo', 2)
            ->where(fn (Builder $q) => $q
                ->where('barcode', 'like', '978%')
                ->orWhere('barcode', 'like', '979%')
            );
    }

    /**
     * Pendientes: sin portada en nuestro disco y sin intento archivado.
     *
     * Una `image` que empieza por http es una portada antigua servida desde
     * Google: cuenta como pendiente, porque la queremos en local.
     */
    private function pending(): Builder
    {
        return $this->books()
            ->where(fn (Builder $q) => $q
                ->whereNull('image')
                ->orWhere('image', '')
                ->orWhere('image', 'like', 'http%')
            )
            ->where(fn (Builder $q) => $q
                ->whereDoesntHave('bookDetail')
                ->orWhereHas('bookDetail', fn (Builder $d) => $d
                    ->whereNull('cover_fetched_at')
                    ->where('cover_attempts', '<', ProductBookDetail::MAX_COVER_ATTEMPTS)
                )
            );
    }

    private function withCoverCount(): int
    {
        return $this->books()
            ->whereNotNull('image')
            ->where('image', '!=', '')
            ->where('image', 'not like', 'http%')
            ->count();
    }
}
