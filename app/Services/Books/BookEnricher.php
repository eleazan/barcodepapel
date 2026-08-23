<?php

declare(strict_types=1);

namespace App\Services\Books;

use App\Models\Product;
use App\Models\ProductBookDetail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Consigue la portada y la ficha de un libro a partir de su ISBN.
 *
 * Dos fuentes: Google Books (portada + metadatos, con cuota diaria) y
 * OpenLibrary como respaldo (solo portada, sin clave ni cuota). Cada intento
 * queda marcado en `product_book_details` para que el mismo libro no vuelva a
 * la cola: con portada se archiva, y sin ella se cuenta el intento hasta el
 * tope de ProductBookDetail::MAX_COVER_ATTEMPTS.
 */
class BookEnricher
{
    /** Portadas por orden de preferencia: de mayor a menor tamaño. */
    private const IMAGE_KEYS = ['extraLarge', 'large', 'medium', 'small', 'thumbnail', 'smallThumbnail'];

    public function __construct(
        private readonly GoogleBooksQuota $quota,
    ) {}

    /**
     * @param  bool  $refresh  Vuelve a pedir la ficha aunque el libro ya tenga
     *                         portada. Lo usa `books:reprocess` para corregir
     *                         títulos y metadatos ya importados.
     */
    public function enrich(Product $product, bool $refresh = false): CoverOutcome
    {
        $isbn = trim((string) $product->barcode);

        if ($isbn === '') {
            return CoverOutcome::SinIsbn;
        }

        $tienePortada = $this->hasLocalCover($product);

        if ($tienePortada && ! $refresh) {
            // Ya tiene fichero propio: solo dejamos constancia para que no
            // vuelva a entrar en los pendientes.
            $this->markFetched($product, $isbn, source: 'local');

            return CoverOutcome::YaTenia;
        }

        $googleUsed  = false;
        $coverUrl    = null;
        $rateLimited = false;

        if (! $this->quota->exhausted()) {
            $googleUsed = true;
            $response   = $this->fetchFromGoogleBooks($isbn);

            if ($response === 'rate_limited') {
                $rateLimited = true;
            } elseif (is_array($response)) {
                $coverUrl = $this->applyGoogleBooksData($product, $response);
            }
        } else {
            // Sin cuota no se pregunta a Google, pero OpenLibrary sigue
            // disponible: puede que la portada salga de ahí.
            $rateLimited = true;
        }

        if ($tienePortada) {
            // Refresco de metadatos: la portada que ya está no se toca.
            if ($rateLimited && ! $googleUsed) {
                return CoverOutcome::LimiteAlcanzado;
            }

            $this->markFetched($product, $isbn, $product->bookDetail?->cover_source ?? 'local', $googleUsed);

            return CoverOutcome::YaTenia;
        }

        $path   = null;
        $source = null;

        if ($coverUrl !== null) {
            $path   = $this->download($coverUrl, $isbn);
            $source = 'google_books';
        }

        // Respaldo: OpenLibrary solo si Google no ha dado portada.
        if ($path === null) {
            $path   = $this->download($this->openLibraryUrl($isbn), $isbn);
            $source = 'openlibrary';
        }

        if ($path !== null) {
            $product->image = $path;
            // Un libro con portada ya se puede publicar.
            $product->is_active = true;
            $product->save();

            $this->markFetched($product, $isbn, $source, $googleUsed);

            return CoverOutcome::Obtenida;
        }

        if ($rateLimited) {
            // No damos el libro por perdido: se reintenta cuando haya cuota.
            return CoverOutcome::LimiteAlcanzado;
        }

        $this->markAttempt($product, $isbn, $googleUsed);

        return CoverOutcome::SinPortada;
    }

    private function hasLocalCover(Product $product): bool
    {
        $image = trim((string) $product->image);

        // Las URL remotas (portadas antiguas servidas desde Google) no cuentan
        // como portada propia: se vuelven a descargar a nuestro disco.
        return $image !== '' && ! str_starts_with($image, 'http');
    }

    /**
     * @return array<string,mixed>|string|null Datos del volumen, 'rate_limited' o null
     */
    private function fetchFromGoogleBooks(string $isbn): array|string|null
    {
        $params = ['q' => "isbn:{$isbn}", 'maxResults' => 1];
        if ($key = config('services.google_books.key')) {
            $params['key'] = $key;
        }

        try {
            $response = Http::timeout(15)
                ->get('https://www.googleapis.com/books/v1/volumes', $params);
        } catch (\Throwable $e) {
            Log::warning("Google Books inaccesible para ISBN {$isbn}: {$e->getMessage()}");

            return null;
        }

        $this->quota->hit();

        if ($response->status() === 429) {
            return 'rate_limited';
        }

        if (! $response->ok()) {
            Log::warning("Google Books error para ISBN {$isbn}: HTTP {$response->status()}");

            return null;
        }

        return $response->json('items.0.volumeInfo') ?? [];
    }

    /**
     * Vuelca los metadatos del volumen en el producto y su ficha.
     *
     * @param  array<string,mixed>  $info
     * @return string|null URL de la portada, si Google la tiene
     */
    private function applyGoogleBooksData(Product $product, array $info): ?string
    {
        if ($info === []) {
            return null;
        }

        $detail = $product->bookDetail
            ?? new ProductBookDetail(['product_id' => $product->id]);

        $lang = $info['language'] ?? '';

        // Título: solo reemplazamos si Google Books lo tiene en español o catalán
        if (! empty($info['title']) && in_array($lang, ['es', 'ca'], true)) {
            $product->name = $info['title'];
        }

        if (! empty($info['subtitle'])) {
            $detail->subtitulo = $info['subtitle'];
        }
        if (! empty($info['authors'])) {
            $detail->autores = implode(', ', $info['authors']);
        }
        if (! empty($info['publisher'])) {
            $detail->editorial = $info['publisher'];
        }
        if (! empty($info['pageCount'])) {
            $detail->paginas = (int) $info['pageCount'];
        }
        if (! empty($info['publishedDate'])) {
            $detail->anio_publicacion = (int) substr((string) $info['publishedDate'], 0, 4);
        }

        if (empty($product->description) && ! empty($info['description'])) {
            $product->description = $info['description'];
        }

        $product->save();
        $detail->save();
        $product->setRelation('bookDetail', $detail);

        return $this->pickImageLink($info['imageLinks'] ?? []);
    }

    /** @param array<string,string> $links */
    private function pickImageLink(array $links): ?string
    {
        foreach (self::IMAGE_KEYS as $key) {
            if (! empty($links[$key])) {
                // Google Books devuelve HTTP; forzamos HTTPS
                return str_replace('http://', 'https://', $links[$key]);
            }
        }

        return null;
    }

    /**
     * OpenLibrary no necesita clave ni consume cuota. Con `default=false`
     * responde 404 cuando no tiene portada, en lugar de una imagen en blanco.
     */
    private function openLibraryUrl(string $isbn): string
    {
        return "https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg?default=false";
    }

    /** Descarga la portada al disco público y devuelve su ruta relativa. */
    private function download(?string $url, string $isbn): ?string
    {
        if ($url === null) {
            return null;
        }

        try {
            $response = Http::timeout(30)->get($url);
        } catch (\Throwable $e) {
            Log::warning("No se pudo descargar la portada de {$isbn}: {$e->getMessage()}");

            return null;
        }

        if (! $response->successful() || $response->body() === '') {
            return null;
        }

        $extension = str_contains((string) $response->header('Content-Type'), 'png') ? 'png' : 'jpg';
        // Nombre derivado del ISBN: reprocesar un libro sustituye su portada
        // en lugar de dejar ficheros huérfanos en el disco.
        $path = "covers/{$isbn}.{$extension}";

        Storage::disk('public')->put($path, $response->body());

        return $path;
    }

    private function markFetched(Product $product, string $isbn, string $source, bool $googleUsed = false): void
    {
        $this->updateDetail($product, $isbn, [
            'cover_source'       => $source,
            'cover_fetched_at'   => now(),
            'cover_attempted_at' => now(),
        ], $googleUsed);
    }

    private function markAttempt(Product $product, string $isbn, bool $googleUsed): void
    {
        $detail = $product->bookDetail;

        $this->updateDetail($product, $isbn, [
            'cover_attempts'     => ($detail?->cover_attempts ?? 0) + 1,
            'cover_attempted_at' => now(),
        ], $googleUsed);
    }

    /** @param array<string,mixed> $values */
    private function updateDetail(Product $product, string $isbn, array $values, bool $googleUsed): void
    {
        $detail = $product->bookDetail
            ?? new ProductBookDetail(['product_id' => $product->id]);

        $detail->isbn = $detail->isbn ?: $isbn;

        if ($googleUsed) {
            $detail->google_books_synced_at = now();
        }

        $detail->fill($values);
        $detail->product_id = $product->id;
        $detail->save();

        $product->setRelation('bookDetail', $detail);
    }
}
