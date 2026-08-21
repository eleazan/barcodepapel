<?php

declare(strict_types=1);

use App\Exceptions\CheckoutException;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Services\Cart\Cart;
use App\Services\Checkout\PlaceOrderService;
use App\Services\Delivery\DeliveryZoneResolver;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

beforeEach(function () {
    Mail::fake();
    Session::start();

    DeliveryZone::factory()->create([
        'postal_code'  => '07800',
        'delivery_fee' => 3.00,
        'is_active'    => true,
    ]);
});

function datosValidos(array $overrides = []): array
{
    return array_merge([
        'customer_name'    => 'Joan Torres',
        'customer_email'   => 'joan@example.com',
        'customer_phone'   => '971987654',
        'delivery_address' => 'Avinguda Espanya 4',
        'postal_code'      => '07800',
        'notes'            => null,
    ], $overrides);
}

it('deshace el pedido completo si el stock no alcanza al confirmar', function () {
    $product = Product::factory()->create(['stock' => 3, 'price' => 10.00]);

    $cart = app(Cart::class);
    $cart->add($product, 3);
    $cart->items(); // fija la fotografía del carrito

    // Otro cliente se lleva las unidades justo antes de confirmar.
    DB::table('products')->where('id', $product->id)->update(['stock' => 1]);

    expect(fn () => app(PlaceOrderService::class)->place(datosValidos()))
        ->toThrow(CheckoutException::class);

    // Ni pedido ni movimiento de stock: la transacción se ha revertido.
    expect(Order::count())->toBe(0);
    expect(DB::table('order_items')->count())->toBe(0);
    expect((int) DB::table('products')->where('id', $product->id)->value('stock'))->toBe(1);
});

it('deshace el pedido si un producto se desactiva al confirmar', function () {
    $product = Product::factory()->create(['stock' => 5]);

    $cart = app(Cart::class);
    $cart->add($product, 2);
    $cart->items();

    DB::table('products')->where('id', $product->id)->update(['is_active' => false]);

    expect(fn () => app(PlaceOrderService::class)->place(datosValidos()))
        ->toThrow(CheckoutException::class);

    expect(Order::count())->toBe(0);
    expect((int) DB::table('products')->where('id', $product->id)->value('stock'))->toBe(5);
});

it('rechaza el pedido con el carrito vacío', function () {
    expect(fn () => app(PlaceOrderService::class)->place(datosValidos()))
        ->toThrow(CheckoutException::class, 'Tu carrito está vacío.');
});

it('rechaza el pedido fuera de zona de reparto', function () {
    $product = Product::factory()->create(['stock' => 5]);

    $cart = app(Cart::class);
    $cart->add($product, 1);

    expect(fn () => app(PlaceOrderService::class)->place(datosValidos(['postal_code' => '28001'])))
        ->toThrow(CheckoutException::class);

    expect(Order::count())->toBe(0);
    expect($product->fresh()->stock)->toBe(5);
});

it('crea el pedido con varias líneas y suma correctamente', function () {
    $uno = Product::factory()->create(['stock' => 10, 'price' => 12.30]);
    $dos = Product::factory()->create(['stock' => 10, 'price' => 4.15]);

    $cart = app(Cart::class);
    $cart->add($uno, 2);
    $cart->add($dos, 3);

    $order = app(PlaceOrderService::class)->place(datosValidos());

    expect($order->items)->toHaveCount(2);
    expect((float) $order->subtotal)->toBe(37.05);   // 24.60 + 12.45
    expect((float) $order->delivery_fee)->toBe(3.00);
    expect((float) $order->total)->toBe(40.05);

    expect($uno->fresh()->stock)->toBe(8);
    expect($dos->fresh()->stock)->toBe(7);
});

it('copia el verial_id del producto en la línea del pedido', function () {
    $product = Product::factory()->create(['stock' => 5, 'verial_id' => 4521]);

    $cart = app(Cart::class);
    $cart->add($product, 1);

    $order = app(PlaceOrderService::class)->place(datosValidos());

    expect($order->items->first()->verial_id)->toBe(4521);
});

it('genera un número de pedido con el formato de la librería', function () {
    $product = Product::factory()->create(['stock' => 5]);

    $cart = app(Cart::class);
    $cart->add($product, 1);

    $order = app(PlaceOrderService::class)->place(datosValidos());

    expect($order->order_number)->toMatch('/^BP-\d{8}-[A-Z0-9]{5}$/');
});

describe('resolución de zonas de reparto', function () {
    it('resuelve la zona por código postal', function () {
        $resolver = app(DeliveryZoneResolver::class);

        expect($resolver->covers('07800'))->toBeTrue();
        expect($resolver->fee('07800'))->toBe(3.00);
    });

    it('no cubre códigos postales de fuera de la isla', function () {
        $resolver = app(DeliveryZoneResolver::class);

        expect($resolver->covers('28001'))->toBeFalse();
        expect($resolver->resolve('28001'))->toBeNull();
    });

    it('descarta códigos postales con formato inválido', function () {
        $resolver = app(DeliveryZoneResolver::class);

        expect($resolver->covers('078'))->toBeFalse();
        expect($resolver->covers('abcde'))->toBeFalse();
        expect($resolver->covers(null))->toBeFalse();
    });

    it('elige la tarifa más baja del código postal', function () {
        DeliveryZone::factory()->create([
            'postal_code'  => '07800',
            'neighborhood' => 'Puig des Molins',
            'delivery_fee' => 1.00,
            'is_active'    => true,
        ]);

        expect(app(DeliveryZoneResolver::class)->fee('07800'))->toBe(1.00);
    });

    it('ignora las zonas desactivadas', function () {
        DeliveryZone::factory()->create([
            'postal_code'  => '07819',
            'delivery_fee' => 5.00,
            'is_active'    => false,
        ]);

        expect(app(DeliveryZoneResolver::class)->covers('07819'))->toBeFalse();
    });

    it('lista los códigos postales cubiertos', function () {
        DeliveryZone::factory()->create(['postal_code' => '07820', 'is_active' => true]);

        expect(app(DeliveryZoneResolver::class)->coveredPostalCodes())
            ->toContain('07800')
            ->toContain('07820');
    });
});
