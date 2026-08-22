<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\Verial\SendOrderService;
use App\Services\Verial\VerialClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function makeConfiguredSendOrderService(array $response = []): SendOrderService
{
    Http::fake([
        'http://127.0.0.1:8000/NuevoDocClienteWS*' => Http::response(
            array_merge(['CodigoPedido' => 99, 'Referencia' => 'REF-001'], $response),
            200
        ),
    ]);

    $client = new VerialClient(host: '127.0.0.1', port: 8000, session: 'test', timeout: 5);

    return new SendOrderService($client);
}

function makeOrderWithItems(): Order
{
    $product = Product::factory()->create(['verial_id' => 55, 'price' => 10.00]);
    $order   = Order::factory()->create([
        'customer_name'    => 'Juan García',
        'customer_email'   => 'juan@example.com',
        'customer_phone'   => '600000000',
        'delivery_address' => 'Calle Mayor 1',
        'postal_code'      => '07800',
    ]);

    OrderItem::factory()->create([
        'order_id'   => $order->id,
        'product_id' => $product->id,
        'quantity'   => 2,
        'unit_price' => 10.00,
        'total'      => 20.00,
    ]);

    return $order->fresh();
}

describe('SendOrderService', function () {

    test('send() envía payload correcto a NuevoDocClienteWS', function () {
        $order   = makeOrderWithItems();
        $service = makeConfiguredSendOrderService();

        $service->send($order);

        Http::assertSent(function ($request) use ($order) {
            $body = $request->data();

            return str_contains($request->url(), 'NuevoDocClienteWS')
                && $body['NombreCliente'] === $order->customer_name
                && $body['EmailCliente']  === $order->customer_email
                && isset($body['Lineas']);
        });
    });

    test('send() actualiza pedido con verial_pedido_id y verial_referencia', function () {
        $order   = makeOrderWithItems();
        $service = makeConfiguredSendOrderService(['CodigoPedido' => 99, 'Referencia' => 'REF-001']);

        $service->send($order);

        $this->assertDatabaseHas('orders', [
            'id'                => $order->id,
            'verial_pedido_id'  => 99,
            'verial_referencia' => 'REF-001',
        ]);
    });

    test('send() establece verial_enviado_at con timestamp', function () {
        $order   = makeOrderWithItems();
        $service = makeConfiguredSendOrderService();

        $service->send($order);

        $order->refresh();
        expect($order->verial_enviado_at)->not->toBeNull();
    });

    test('send() registra log de sincronización', function () {
        $order   = makeOrderWithItems();
        $service = makeConfiguredSendOrderService();

        $service->send($order);

        $this->assertDatabaseHas('verial_sync_log', [
            'entity_type'   => 'pedido',
            'entity_id'     => $order->id,
            'operation'     => 'send_order',
            'verial_method' => 'NuevoDocClienteWS',
            'status'        => 'ok',
        ]);
    });

    test('send() lanza RuntimeException cuando Verial no está configurado', function () {
        $order   = makeOrderWithItems();
        $client  = new VerialClient(host: null, port: 8000, session: null, timeout: 5);
        $service = new SendOrderService($client);

        expect(fn () => $service->send($order))
            ->toThrow(RuntimeException::class);
    });

});
