<?php

declare(strict_types=1);

namespace App\Services\Verial;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductBookDetail;
use App\Models\VerialSyncLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SyncCatalogService
{
    public function __construct(
        private readonly VerialClient $client,
    ) {}

    public function sync(?string $since = null): SyncResult
    {
        $processed     = 0;
        $created       = 0;
        $updated       = 0;
        $errors        = 0;
        $errorMessages = [];

        try {
            $params = [];
            if ($since !== null) {
                $params['FechaDesde'] = $since;
            }

            $response  = $this->client->get('GetArticulosWS', $params);
            $articulos = $response['Articulos'] ?? $response['articulos'] ?? $response;

            if (! is_array($articulos)) {
                $articulos = [];
            }

            foreach ($articulos as $articulo) {
                $processed++;

                try {
                    $this->processArticulo($articulo, $created, $updated);
                } catch (\Throwable $e) {
                    $errors++;
                    $errorMessages[] = $e->getMessage();
                    Log::warning('Error al procesar artículo Verial', [
                        'verial_id' => $articulo['CodigoArticulo'] ?? null,
                        'error'     => $e->getMessage(),
                    ]);
                }
            }

            VerialSyncLog::record(
                entityType: 'producto',
                entityId: null,
                operation: 'sync_catalog',
                verialMethod: 'GetArticulosWS',
                response: ['processed' => $processed, 'created' => $created, 'updated' => $updated, 'errors' => $errors],
            );
        } catch (\Throwable $e) {
            $errors++;
            $errorMessages[] = $e->getMessage();

            VerialSyncLog::record(
                entityType: 'producto',
                entityId: null,
                operation: 'sync_catalog',
                verialMethod: 'GetArticulosWS',
                response: [],
                error: $e->getMessage(),
            );
        }

        return new SyncResult($processed, $created, $updated, $errors, $errorMessages);
    }

    /**
     * Procesa un único artículo Verial (usado por ImportProductJob).
     */
    public function syncArticulo(array $articulo): void
    {
        $created = 0;
        $updated = 0;
        $this->processArticulo($articulo, $created, $updated);
    }

    private function processArticulo(array $articulo, int &$created, int &$updated): void
    {
        $verialId = (int) ($articulo['CodigoArticulo'] ?? 0);
        if ($verialId === 0) {
            return;
        }

        $exists = Product::where('verial_id', $verialId)->exists();
        $data   = $this->mapArticulo($articulo);

        /** @var Product $product */
        $product = Product::updateOrCreate(
            ['verial_id' => $verialId],
            $data
        );

        $exists ? $updated++ : $created++;

        // Si es libro, sincronizar detalles
        if ((int) ($articulo['TipoArticulo'] ?? 1) === 2) {
            $this->upsertBookDetail($product, $articulo);
        }
    }

    private function upsertBookDetail(Product $product, array $articulo): void
    {
        ProductBookDetail::updateOrCreate(
            ['product_id' => $product->id],
            [
                'isbn'             => $articulo['ISBN']      ?? null,
                'subtitulo'        => $articulo['Subtitulo'] ?? null,
                'autores'          => $articulo['Autores']   ?? null,
                'editorial'        => $articulo['Editorial'] ?? null,
                'coleccion'        => $articulo['Coleccion'] ?? null,
                'paginas'          => isset($articulo['Paginas']) ? (int) $articulo['Paginas'] : null,
                'edicion'          => $articulo['Edicion'] ?? null,
                'anio_publicacion' => isset($articulo['AnioPublicacion']) ? (int) $articulo['AnioPublicacion'] : null,
            ]
        );
    }

    private function mapArticulo(array $data): array
    {
        $tipoArticulo  = (int) ($data['TipoArticulo'] ?? 1);
        $fechaInactivo = $data['FechaInactivo'] ?? null;
        $isActive      = empty($fechaInactivo);

        // Resolver category_id a partir de CodigoFamilia; usar "Sin categoría" como fallback
        $categoryId = null;
        if (! empty($data['CodigoFamilia'])) {
            $category   = Category::where('verial_familia_id', (int) $data['CodigoFamilia'])->first();
            $categoryId = $category?->id;
        }
        if ($categoryId === null) {
            $categoryId = $this->getOrCreateDefaultCategory()->id;
        }

        // Resolver fabricante_id
        $fabricanteId = null;
        if (! empty($data['CodigoFabricante'])) {
            $fabricanteId = (int) $data['CodigoFabricante'];
        }

        $name   = trim((string) ($data['Descripcion'] ?? ''));
        $mapped = [
            'verial_id'            => (int) $data['CodigoArticulo'],
            'tipo_articulo'        => $tipoArticulo,
            'barcode'              => $data['CodigoBarras'] ?? null,
            'price'                => isset($data['PrecioConIVA']) ? (float) $data['PrecioConIVA'] : null,
            'iva_percent'          => isset($data['PorcentajeIVA']) ? (float) $data['PorcentajeIVA'] : 4.00,
            'fecha_disponibilidad' => ! empty($data['FechaDisponibilidad']) ? $data['FechaDisponibilidad'] : null,
            'fecha_inicio_venta'   => ! empty($data['FechaInicioVenta']) ? $data['FechaInicioVenta'] : null,
            'fecha_inactivo'       => ! empty($fechaInactivo) ? $fechaInactivo : null,
            'nexo'                 => $data['Nexo'] ?? null,
            'peso'                 => isset($data['Peso']) ? (float) $data['Peso'] : null,
            'verial_fabricante_id' => $fabricanteId,
            'is_active'            => $isActive,
            'verial_synced_at'     => now(),
        ];

        if ($name === '') {
            $name = 'Artículo '.$data['CodigoArticulo'];
        }
        $mapped['name'] = $name;
        $mapped['slug'] = $this->uniqueSlug(Str::slug($name), (int) $data['CodigoArticulo']);

        if (! empty($data['DescripcionLarga'])) {
            $mapped['description'] = $data['DescripcionLarga'];
        }

        $mapped['category_id'] = $categoryId;

        return $mapped;
    }

    private function uniqueSlug(string $base, int $verialId): string
    {
        // Si el slug ya pertenece a este producto Verial, reutilizarlo
        $existing = Product::where('slug', $base)->where('verial_id', $verialId)->exists();
        if ($existing) {
            return $base;
        }

        // Si el slug ya está en uso por otro producto, añadir sufijo con verial_id
        if (Product::where('slug', $base)->whereNot('verial_id', $verialId)->exists()) {
            return $base.'-'.$verialId;
        }

        return $base;
    }

    private function getOrCreateDefaultCategory(): Category
    {
        return Category::firstOrCreate(
            ['slug' => 'sin-categoria'],
            [
                'name'       => 'Sin categoría',
                'is_active'  => false,
                'sort_order' => 0,
            ]
        );
    }
}
