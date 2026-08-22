<?php

declare(strict_types=1);

use App\Models\DeliveryZone;
use App\Models\Product;

it('publica el aviso legal', function () {
    $this->get(route('legal'))
        ->assertOk()
        ->assertSee('Aviso legal')
        ->assertSee(config('tienda.direccion.calle'))
        ->assertSee(config('tienda.email'));
});

it('publica la política de privacidad', function () {
    $this->get(route('privacy'))
        ->assertOk()
        ->assertSee('Política de privacidad', false)
        ->assertSee('Responsable del tratamiento', false)
        ->assertSee('www.aepd.es');
});

it('publica las condiciones de venta', function () {
    $this->get(route('terms'))
        ->assertOk()
        ->assertSee('Condiciones de venta y reparto')
        ->assertSee('Derecho de desistimiento', false)
        ->assertSee('Real Decreto Legislativo 1/2007');
});

it('muestra los datos del titular cuando están configurados', function () {
    config([
        'tienda.legal.razon_social' => 'Llibreria Barco de Papel SL',
        'tienda.legal.nif'          => 'B00000000',
    ]);

    $this->get(route('legal'))
        ->assertOk()
        ->assertSee('Llibreria Barco de Papel SL')
        ->assertSee('B00000000');
});

it('cae en el nombre comercial si no hay razón social configurada', function () {
    config(['tienda.legal.razon_social' => null]);

    $this->get(route('terms'))
        ->assertOk()
        ->assertSee(config('tienda.nombre'));
});

it('enlaza las páginas legales desde el pie de la tienda', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('legal'))
        ->assertSee(route('privacy'))
        ->assertSee(route('terms'));
});

it('enlaza las condiciones y la privacidad desde el checkout', function () {
    DeliveryZone::factory()->create(['postal_code' => '07800', 'is_active' => true]);
    $product = Product::factory()->create(['stock' => 5]);

    $this->post(route('cart.add', $product), ['quantity' => 1]);

    $this->get(route('checkout.show'))
        ->assertOk()
        ->assertSee(route('terms'))
        ->assertSee(route('privacy'))
        ->assertSee('derechos de acceso');
});

it('incluye las páginas legales en el sitemap', function () {
    $this->get(route('sitemap'))
        ->assertOk()
        ->assertSee(route('legal'))
        ->assertSee(route('privacy'))
        ->assertSee(route('terms'));
});
