<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DeliveryZone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionSeeder extends Seeder
{
    private const DATA_FILE  = 'data/production_seed.json';
    private const CHUNK_SIZE = 500;

    public function run(): void
    {
        $path = database_path(self::DATA_FILE);

        if (! file_exists($path)) {
            $this->command->error('No se encuentra '.self::DATA_FILE);

            return;
        }

        $data = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);

        $this->seedCategories($data['categories'] ?? []);
        $this->seedDeliveryZones($data['delivery_zones'] ?? []);
        $this->seedProducts($data['products'] ?? []);
    }

    private function seedCategories(array $categories): void
    {
        foreach ($categories as $c) {
            Category::updateOrCreate(
                ['slug' => $c['slug']],
                [
                    'name'        => $c['name'],
                    'description' => $c['description'],
                    'sort_order'  => (int) $c['sort_order'],
                    'is_active'   => (bool) $c['is_active'],
                ],
            );
        }

        $this->command->info(count($categories).' categorías.');
    }

    private function seedDeliveryZones(array $zones): void
    {
        foreach ($zones as $z) {
            DeliveryZone::updateOrCreate(
                ['postal_code' => $z['postal_code'], 'neighborhood' => $z['neighborhood']],
                [
                    'city'         => $z['city'],
                    'delivery_fee' => (float) $z['delivery_fee'],
                    'is_active'    => (bool) $z['is_active'],
                ],
            );
        }

        $this->command->info(count($zones).' zonas de reparto.');
    }

    private function seedProducts(array $products): void
    {
        $categoryIds = Category::pluck('id', 'slug')->all();
        $now         = now();
        $total       = 0;

        $bar = $this->command->getOutput()->createProgressBar(count($products));
        $bar->start();

        foreach (array_chunk($products, self::CHUNK_SIZE) as $chunk) {
            $productRows = [];

            foreach ($chunk as $p) {
                $productRows[] = [
                    'category_id' => $categoryIds[$p['category_slug']] ?? $categoryIds['libros'],
                    'name'        => $p['name'],
                    'slug'        => $p['slug'],
                    'sku'         => $p['sku'],
                    'barcode'     => $p['barcode'],
                    'price'       => (float) $p['price'],
                    'stock'       => (int) $p['stock'],
                    // El precio llega a 0 desde el CSV de stock: se publica al
                    // importar el CSV de tarifas, no aquí.
                    'is_active'     => false,
                    'tipo_articulo' => (int) $p['tipo_articulo'],
                    'iva_percent'   => (float) $p['iva_percent'],
                    'image'         => $p['image'],
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }

            DB::table('products')->upsert(
                $productRows,
                ['barcode'],
                ['name', 'slug', 'sku', 'price', 'stock', 'tipo_articulo', 'iva_percent', 'image', 'updated_at'],
            );

            $this->upsertBookDetails($chunk, $now);

            $total += count($chunk);
            $bar->advance(count($chunk));
        }

        $bar->finish();
        $this->command->newLine(2);
        $this->command->info("{$total} productos importados.");
    }

    private function upsertBookDetails(array $chunk, \DateTimeInterface $now): void
    {
        $isbns = array_values(array_filter(array_column($chunk, 'isbn')));

        if ($isbns === []) {
            return;
        }

        $productIds = DB::table('products')
            ->whereIn('barcode', $isbns)
            ->pluck('id', 'barcode')
            ->all();

        $detailRows = [];

        foreach ($chunk as $p) {
            $productId = $productIds[$p['isbn'] ?? ''] ?? null;

            if ($productId === null) {
                continue;
            }

            $detailRows[] = [
                'product_id'       => $productId,
                'isbn'             => $p['isbn'],
                'subtitulo'        => $p['subtitulo'],
                'autores'          => $p['autores'],
                'editorial'        => $p['editorial'],
                'coleccion'        => $p['coleccion'],
                'paginas'          => $p['paginas']          !== null ? (int) $p['paginas'] : null,
                'anio_publicacion' => $p['anio_publicacion'] !== null ? (int) $p['anio_publicacion'] : null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ];
        }

        if ($detailRows !== []) {
            DB::table('product_book_details')->upsert(
                $detailRows,
                ['product_id'],
                ['isbn', 'subtitulo', 'autores', 'editorial', 'coleccion', 'paginas', 'anio_publicacion', 'updated_at'],
            );
        }
    }
}
