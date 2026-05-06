<?php

declare(strict_types=1);

use App\Models\Product;
use App\Services\Verial\SyncStockService;
use App\Services\Verial\VerialClient;
use Illuminate\Support\Facades\Http;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

function makeSyncStockService(array $stocks): SyncStockService
{
    Http::fake([
        'http://127.0.0.1:8000/GetStockArticulosWS*' => Http::response(['Stocks' => $stocks], 200),
    ]);

    $client = new VerialClient(host: '127.0.0.1', port: 8000, session: 'test', timeout: 5);

    return new SyncStockService($client);
}

describe('SyncStockService', function () {

    test('sync() actualiza stock de producto por verial_id', function () {
        Product::factory()->create(['verial_id' => 200, 'stock' => 5]);

        $service = makeSyncStockService([
            ['CodigoArticulo' => 200, 'Stock' => 42],
        ]);

        $service->sync();

        $this->assertDatabaseHas('products', [
            'verial_id' => 200,
            'stock'     => 42,
        ]);
    });

    test('sync() ignora artículos no encontrados en BD local', function () {
        // verial_id 999 no existe en la BD
        $service = makeSyncStockService([
            ['CodigoArticulo' => 999, 'Stock' => 10],
        ]);

        $result = $service->sync();

        expect($result->errors)->toBe(0)
            ->and($result->updated)->toBe(0);
    });

    test('sync() devuelve conteos correctos de SyncResult', function () {
        Product::factory()->create(['verial_id' => 101, 'stock' => 0]);
        Product::factory()->create(['verial_id' => 102, 'stock' => 0]);

        $service = makeSyncStockService([
            ['CodigoArticulo' => 101, 'Stock' => 5],
            ['CodigoArticulo' => 102, 'Stock' => 10],
            ['CodigoArticulo' => 999, 'Stock' => 3], // no existe
        ]);

        $result = $service->sync();

        expect($result->processed)->toBe(3)
            ->and($result->updated)->toBe(2)
            ->and($result->errors)->toBe(0);
    });

});
