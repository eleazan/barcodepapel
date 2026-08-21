<?php

declare(strict_types=1);

namespace App\Jobs\Csv;

use App\Models\Category;
use App\Models\Product;
use App\Models\VerialSyncLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProcessPricesCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 600;

    // Formato: 4 líneas de cabecera, separador ;, encoding Latin-1/CP1252
    // Col 0: Ficha | Col 2: Nombre | Col 3: Barras | Col 4: Precio base
    private const SKIP_LINES = 4;
    private const COL_FICHA  = 0;
    private const COL_NOMBRE = 2;
    private const COL_BARRAS = 3;
    private const COL_PRECIO = 4;
    private const MIN_COLS   = 5;
    private const ENCODING   = 'CP1252';

    public function __construct(
        private readonly string $storagePath,
        private readonly string $originalName,
    ) {}

    public function handle(): void
    {
        $absolutePath = Storage::path($this->storagePath);

        if (! file_exists($absolutePath)) {
            Log::error("ProcessPricesCsvJob: archivo no encontrado: {$absolutePath}");

            return;
        }

        $handle = fopen($absolutePath, 'r');
        if (! $handle) {
            Log::error("ProcessPricesCsvJob: no se pudo abrir: {$absolutePath}");

            return;
        }

        for ($i = 0; $i < self::SKIP_LINES; $i++) {
            fgetcsv($handle, 0, ';');
        }

        $categoriaLibros = Category::firstOrCreate(
            ['slug' => 'libros'],
            ['name' => 'Libros', 'is_active' => true, 'sort_order' => 0],
        );

        $actualizados = 0;
        $creados      = 0;
        $activados    = 0;
        $omitidos     = 0;
        $errores      = 0;

        while (($cols = fgetcsv($handle, 0, ';')) !== false) {
            if (count($cols) < self::MIN_COLS || empty(trim($cols[self::COL_FICHA]))) {
                $omitidos++;
                continue;
            }

            $ficha   = str_replace(',', '', trim($cols[self::COL_FICHA]));
            $nombre  = mb_convert_encoding(trim($cols[self::COL_NOMBRE] ?? ''), 'UTF-8', self::ENCODING);
            $barcode = trim($cols[self::COL_BARRAS] ?? '');
            $precio  = (float) str_replace(',', '.', trim($cols[self::COL_PRECIO] ?? '0'));

            if ($precio <= 0) {
                $omitidos++;
                continue;
            }

            if (empty($barcode) && empty($ficha)) {
                $omitidos++;
                continue;
            }

            try {
                $product = $this->findProduct($ficha, $barcode);

                if ($product) {
                    $wasInactive    = ! $product->is_active;
                    $shouldActivate = $wasInactive && $product->stock > 0;

                    $product->update([
                        'price'     => $precio,
                        'is_active' => $shouldActivate ? true : $product->is_active,
                    ]);

                    if ($shouldActivate) {
                        $activados++;
                    }

                    $actualizados++;
                } else {
                    $esLibro = str_starts_with($barcode, '978') || str_starts_with($barcode, '979');

                    $slug = Str::slug($nombre);
                    if (Product::where('slug', $slug)->exists()) {
                        $slug .= '-'.($barcode ?: $ficha);
                    }

                    Product::create([
                        'category_id'   => $categoriaLibros->id,
                        'name'          => $nombre ?: ($barcode ?: $ficha),
                        'slug'          => $slug,
                        'sku'           => $ficha ?: null,
                        'barcode'       => $barcode ?: null,
                        'stock'         => 0,
                        'price'         => $precio,
                        'is_active'     => false,
                        'tipo_articulo' => $esLibro ? 2 : 1,
                    ]);

                    $creados++;
                }
            } catch (\Throwable $e) {
                $errores++;
                Log::warning("ProcessPricesCsvJob: error en ficha {$ficha}: {$e->getMessage()}");
            }
        }

        fclose($handle);
        Storage::delete($this->storagePath);

        VerialSyncLog::record(
            entityType: 'producto',
            entityId: null,
            operation: 'import_prices_csv',
            verialMethod: 'CSV:'.$this->originalName,
            response: [
                'actualizados' => $actualizados,
                'creados'      => $creados,
                'activados'    => $activados,
                'omitidos'     => $omitidos,
                'processed'    => $actualizados + $creados,
            ],
            error: $errores > 0 ? "{$errores} filas con error" : null,
        );
    }

    private function findProduct(string $ficha, string $barcode): ?Product
    {
        if ($ficha) {
            $product = Product::where('sku', $ficha)->first();
            if ($product) {
                return $product;
            }
        }

        if ($barcode) {
            return Product::where('barcode', $barcode)->first();
        }

        return null;
    }
}
