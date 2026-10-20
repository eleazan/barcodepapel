<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Post;
use App\Models\Product;
use App\Models\User;

it('muestra la portada sin productos ni entradas', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Tu librería en Ibiza')
        ->assertSee(config('tienda.direccion.calle'))
        ->assertDontSee('Novedades')
        ->assertDontSee('Más vendidos')
        ->assertDontSee('Del blog');
});

it('enseña en novedades solo productos activos y con stock', function () {
    Product::factory()->create(['name' => 'Visible novedad', 'stock' => 5]);
    Product::factory()->create(['name' => 'Sin existencias', 'stock' => 0]);
    Product::factory()->inactive()->create(['name' => 'Despublicado', 'stock' => 5]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Novedades')
        ->assertSee('Visible novedad')
        ->assertDontSee('Sin existencias')
        ->assertDontSee('Despublicado');
});

it('lista en más vendidos solo lo que se ha pedido', function () {
    $vendido = Product::factory()->create(['name' => 'Libro vendido', 'stock' => 5]);
    Product::factory()->create(['name' => 'Libro sin ventas', 'stock' => 5]);
    OrderItem::factory()->create(['order_id' => Order::factory(), 'product_id' => $vendido->id, 'quantity' => 2]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Más vendidos')
        ->assertSeeInOrder(['Más vendidos', 'Libro vendido']);
});

it('publica las últimas entradas del blog', function () {
    $autor = User::factory()->create();

    Post::create(['user_id' => $autor->id, 'title' => 'Entrada publicada', 'body' => 'Texto.', 'is_published' => true, 'published_at' => now()->subDay()]);
    Post::create(['user_id' => $autor->id, 'title' => 'Entrada borrador', 'body' => 'Texto.', 'is_published' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Entrada publicada')
        ->assertDontSee('Entrada borrador');
});

it('oculta el banner cuando la campaña está desactivada', function () {
    config(['tienda.campana.activa' => false]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee(config('tienda.campana.titulo'));
});
