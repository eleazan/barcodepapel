<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\FetchBookDataFromIsbn;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBookDetail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportBooksCsvCommand extends Command
{
    protected $signature = 'import:books-csv
                            {--archivo= : Ruta al CSV (por defecto: example_data.csv en la raíz)}
                            {--sin-jobs : Importar sin despachar jobs de enriquecimiento}';

    protected $description = 'Importa libros (ISBN 978/979) desde el CSV de stock de Verial';

    public function handle(): int
    {
        $path = $this->option('archivo') ?? base_path('example_data.csv');

        if (! file_exists($path)) {
            $this->error("Archivo no encontrado: {$path}");

            return self::FAILURE;
        }

        // Primera pasada: contar líneas de datos para la barra de progreso
        $totalLineas = $this->contarLineasDatos($path);
        $this->line("Total de líneas a analizar: <info>{$totalLineas}</info>");

        $handle = fopen($path, 'r');
        if (! $handle) {
            $this->error('No se pudo abrir el archivo.');

            return self::FAILURE;
        }

        // Saltar 7 líneas de cabecera del informe
        for ($i = 0; $i < 7; $i++) {
            fgetcsv($handle, 0, ';');
        }

        $categoria = Category::firstOrCreate(
            ['slug' => 'libros'],
            ['name' => 'Libros', 'is_active' => true, 'sort_order' => 0]
        );

        $importados = 0;
        $omitidos   = 0;
        $duplicados = 0;
        $errores    = 0;

        $bar = $this->output->createProgressBar($totalLineas);
        $bar->start();

        while (($cols = fgetcsv($handle, 0, ';')) !== false) {
            $bar->advance();

            if (count($cols) < 5 || empty($cols[0])) {
                $omitidos++;
                continue;
            }

            // El CSV usa Windows-1252; convertir campos de texto a UTF-8
            $ficha   = str_replace(',', '', trim($cols[0]));
            $nombre  = mb_convert_encoding(trim($cols[2] ?? ''), 'UTF-8', 'CP1252');
            $barcode = trim($cols[4] ?? '');
            $stock   = max(0, (int) (float) trim($cols[7] ?? '0'));

            // Solo libros: ISBN-13 empieza por 978 o 979
            if (! str_starts_with($barcode, '978') && ! str_starts_with($barcode, '979')) {
                $omitidos++;
                continue;
            }

            if (empty($nombre) || empty($barcode)) {
                $omitidos++;
                continue;
            }

            if (Product::where('barcode', $barcode)->exists()) {
                $duplicados++;
                continue;
            }

            try {
                DB::transaction(function () use ($ficha, $nombre, $barcode, $stock, $categoria, &$importados) {
                    $slug = Str::slug($nombre);
                    if (Product::where('slug', $slug)->exists()) {
                        $slug .= '-'.$barcode;
                    }

                    $product = Product::create([
                        'category_id'   => $categoria->id,
                        'name'          => $nombre,
                        'slug'          => $slug,
                        'sku'           => $ficha ?: null,
                        'barcode'       => $barcode,
                        'stock'         => $stock,
                        'price'         => 0.00,
                        'is_active'     => false, // inactivo hasta asignar precio
                        'tipo_articulo' => 2,
                    ]);

                    ProductBookDetail::create([
                        'product_id' => $product->id,
                        'isbn'       => $barcode,
                    ]);

                    if (! $this->option('sin-jobs')) {
                        FetchBookDataFromIsbn::dispatch($product);
                    }

                    $importados++;
                });
            } catch (\Throwable $e) {
                $errores++;
                $this->newLine();
                $this->warn("  Error en ISBN {$barcode}: {$e->getMessage()}");
            }
        }

        fclose($handle);
        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['', 'Cantidad'],
            [
                ['<info>Libros importados</info>', $importados],
                ['No-libros omitidos',             $omitidos],
                ['Ya existían (duplicados)',        $duplicados],
                ['<error>Errores</error>',          $errores],
            ]
        );

        if ($importados > 0 && ! $this->option('sin-jobs')) {
            $this->newLine();
            $this->info("{$importados} jobs despachados en cola.");
            $this->line('Para procesarlos: <comment>php artisan queue:work --sleep=1</comment>');
            $this->line('<comment>Nota:</comment> Google Books API sin clave permite ~1.000 peticiones/día.');
            $this->line('Configura <comment>GOOGLE_BOOKS_API_KEY</comment> en .env para mayor cuota.');
        }

        return self::SUCCESS;
    }

    private function contarLineasDatos(string $path): int
    {
        $handle = fopen($path, 'r');
        $count  = 0;
        // Saltar cabeceras
        for ($i = 0; $i < 7; $i++) {
            fgets($handle);
        }
        while (fgets($handle) !== false) {
            $count++;
        }
        fclose($handle);

        return $count;
    }
}
