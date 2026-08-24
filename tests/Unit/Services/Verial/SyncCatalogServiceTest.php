<?php

declare(strict_types=1);

use App\Models\Product;
use App\Services\Verial\SyncCatalogService;
use App\Services\Verial\VerialClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function makeArticulo(array $overrides = []): array
{
    return array_merge([
        'CodigoArticulo' => 1001,
        'Descripcion'    => 'Libro de prueba',
        'CodigoBarras'   => '9781234567890',
        'TipoArticulo'   => 1,
        'PrecioConIVA'   => 12.50,
        'PorcentajeIVA'  => 4.00,
    ], $overrides);
}

function makeSyncCatalogService(?array $articulos = null): SyncCatalogService
{
    $articulos ??= [];

    Http::fake([
        'http://127.0.0.1:8000/GetArticulosWS*' => Http::response(['Articulos' => $articulos], 200),
    ]);

    $client = new VerialClient(host: '127.0.0.1', port: 8000, session: 'test', timeout: 5);

    return new SyncCatalogService($client);
}

describe('SyncCatalogService', function () {

    test('sync() devuelve SyncResult con ceros cuando no hay artículos', function () {
        $service = makeSyncCatalogService([]);

        $result = $service->sync();

        expect($result->processed)->toBe(0)
            ->and($result->created)->toBe(0)
            ->and($result->updated)->toBe(0)
            ->and($result->errors)->toBe(0);
    });

    test('sync() crea producto desde artículo Verial', function () {
        $service = makeSyncCatalogService([makeArticulo()]);

        $result = $service->sync();

        expect($result->created)->toBe(1)
            ->and($result->updated)->toBe(0);

        $this->assertDatabaseHas('products', [
            'verial_id' => 1001,
            'name'      => 'Libro de prueba',
        ]);
    });

    test('sync() actualiza producto existente por verial_id', function () {
        Product::factory()->create(['verial_id' => 1001, 'name' => 'Nombre anterior']);

        $service = makeSyncCatalogService([makeArticulo(['Descripcion' => 'Nombre actualizado'])]);

        $result = $service->sync();

        expect($result->created)->toBe(0)
            ->and($result->updated)->toBe(1);

        $this->assertDatabaseHas('products', [
            'verial_id' => 1001,
            'name'      => 'Nombre actualizado',
        ]);
    });

    test('sync() crea detalle de libro cuando tipo_articulo es 2', function () {
        $articulo = makeArticulo([
            'TipoArticulo' => 2,
            'ISBN'         => '9781234567890',
            'Autores'      => 'Autor Ejemplo',
            'Editorial'    => 'Editorial Test',
        ]);

        $service = makeSyncCatalogService([$articulo]);
        $service->sync();

        $product = Product::where('verial_id', 1001)->firstOrFail();

        $this->assertDatabaseHas('product_book_details', [
            'product_id' => $product->id,
            'isbn'       => '9781234567890',
            'autores'    => 'Autor Ejemplo',
        ]);
    });

    test('sync() establece is_active false cuando fecha_inactivo está informada', function () {
        $articulo = makeArticulo(['FechaInactivo' => '2024-01-15 00:00:00']);

        $service = makeSyncCatalogService([$articulo]);
        $service->sync();

        $this->assertDatabaseHas('products', [
            'verial_id' => 1001,
            'is_active' => 0,
        ]);
    });

    test('sync() registra log de sincronización en éxito', function () {
        $service = makeSyncCatalogService([makeArticulo()]);
        $service->sync();

        $this->assertDatabaseHas('verial_sync_log', [
            'entity_type'   => 'producto',
            'operation'     => 'sync_catalog',
            'verial_method' => 'GetArticulosWS',
            'status'        => 'ok',
        ]);
    });

    test('sync() registra log con error cuando falla', function () {
        Http::fake([
            'http://127.0.0.1:8000/GetArticulosWS*' => Http::response('', 500),
        ]);

        $client  = new VerialClient(host: '127.0.0.1', port: 8000, session: 'test', timeout: 5);
        $service = new SyncCatalogService($client);

        $result = $service->sync();

        expect($result->errors)->toBeGreaterThan(0);

        $this->assertDatabaseHas('verial_sync_log', [
            'entity_type' => 'producto',
            'status'      => 'error',
        ]);
    });

});
