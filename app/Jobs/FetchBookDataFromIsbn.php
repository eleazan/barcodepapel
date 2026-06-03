<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Product;
use App\Models\ProductBookDetail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchBookDataFromIsbn implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    /** Segundos entre reintentos: 1m, 5m, 15m */
    public array $backoff = [60, 300, 900];

    public function __construct(private readonly Product $product) {}

    public function handle(): void
    {
        $isbn = $this->product->barcode;
        if (empty($isbn)) {
            return;
        }

        $params = ['q' => "isbn:{$isbn}", 'maxResults' => 1];
        if ($key = config('services.google_books.key')) {
            $params['key'] = $key;
        }

        $response = Http::timeout(15)
            ->get('https://www.googleapis.com/books/v1/volumes', $params);

        if ($response->status() === 429) {
            // Cuota agotada — volver a encolar en 10 minutos
            $this->release(600);
            return;
        }

        if (! $response->ok()) {
            Log::warning("Google Books error para ISBN {$isbn}: HTTP {$response->status()}");
            return;
        }

        $data = $response->json();

        $detail = $this->product->bookDetail
            ?? new ProductBookDetail(['product_id' => $this->product->id]);

        $detail->isbn                   = $isbn;
        $detail->google_books_synced_at = now();

        if (! empty($data['items'])) {
            $info = $data['items'][0]['volumeInfo'] ?? [];
            $lang = $info['language'] ?? '';

            // Título: solo reemplazamos si Google Books lo tiene en español o catalán
            if (! empty($info['title']) && in_array($lang, ['es', 'ca'], true)) {
                $this->product->name = $info['title'];
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
                $detail->anio_publicacion = (int) substr($info['publishedDate'], 0, 4);
            }

            if (empty($this->product->description) && ! empty($info['description'])) {
                $this->product->description = $info['description'];
            }

            if (empty($this->product->image)) {
                $thumb = $info['imageLinks']['thumbnail']
                    ?? $info['imageLinks']['smallThumbnail']
                    ?? null;

                if ($thumb) {
                    // Google Books devuelve HTTP; forzamos HTTPS
                    $this->product->image = str_replace('http://', 'https://', $thumb);
                    // Activar el producto en cuanto tiene portada
                    $this->product->is_active = true;
                }
            }

            $this->product->save();
        }

        $detail->save();
    }
}
