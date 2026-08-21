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

class ProcessStockCsvJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;
    public int $timeout = 600;

    // Formato: 7 líneas de cabecera, separador ;, encoding CP1252
    // Col 0: Ficha | Col 2: Nombre | Col 4: Barras | Col 7: Stock
    private const SKIP_LINES = 7;
    private const COL_FICHA  = 0;
    private const COL_NOMBRE = 2;
    private const COL_BARRAS = 4;
    private const COL_STOCK  = 7;
    private const MIN_COLS   = 8;
    private const ENCODING   = 'CP1252';

    public function __construct(
        private readonly string $storagePath,
        private readonly string $originalName,
    ) {}

    public function handle(): void
    {
        $absolutePath = Storage::path($this->storagePath);

        if (! file_exists($absolutePath)) {
            Log::error("ProcessStockCsvJob: archivo no encontrado: {$absolutePath}");

            return;
        }

        $handle = fopen($absolutePath, 'r');
        if (! $handle) {
            Log::error("ProcessStockCsvJob: no se pudo abrir: {$absolutePath}");

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
            $stock   = max(0, (int) (float) str_replace(',', '.', trim($cols[self::COL_STOCK] ?? '0')));

            if (empty($barcode) && empty($ficha)) {
                $omitidos++;
                continue;
            }

            try {
                $product = $this->findProduct($ficha, $barcode);

                if ($product) {
                    $product->update(['stock' => $stock]);
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
                        'stock'         => $stock,
                        'price'         => 0.00,
                        'is_active'     => false,
                        'tipo_articulo' => $esLibro ? 2 : 1,
                    ]);

                    $creados++;
                }
            } catch (\Throwable $e) {
                $errores++;
                Log::warning("ProcessStockCsvJob: error en ficha {$ficha}: {$e->getMessage()}");
            }
        }

        fclose($handle);
        Storage::delete($this->storagePath);

        VerialSyncLog::record(
            entityType: 'producto',
            entityId: null,
            operation: 'import_stock_csv',
            verialMethod: 'CSV:'.$this->originalName,
            response: [
                'actualizados' => $actualizados,
                'creados'      => $creados,
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
