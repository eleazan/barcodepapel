<?php

declare(strict_types=1);

use App\Models\DeliveryZone;
use App\Models\Product;

it('muestra el carrito vacío', function () {
    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Tu carrito est&aacute; vac&iacute;o', false);
});

it('añade un producto al carrito', function () {
    $product = Product::factory()->create(['stock' => 10, 'price' => 12.50]);

    $this->post(route('cart.add', $product), ['quantity' => 2])
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(session('carrito'))->toBe([$product->id => 2]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee($product->name)
        ->assertSee('25,00 €', false);
});

it('acumula unidades al añadir el mismo producto dos veces', function () {
    $product = Product::factory()->create(['stock' => 10]);

    $this->post(route('cart.add', $product), ['quantity' => 2]);
    $this->post(route('cart.add', $product), ['quantity' => 3]);

    expect(session('carrito'))->toBe([$product->id => 5]);
});

it('no permite añadir más unidades que el stock disponible', function () {
    $product = Product::factory()->create(['stock' => 3]);

    $this->post(route('cart.add', $product), ['quantity' => 10])
        ->assertRedirect();

    expect(session('carrito'))->toBe([$product->id => 3]);
});

it('rechaza añadir un producto agotado', function () {
    $product = Product::factory()->outOfStock()->create();

    $this->post(route('cart.add', $product), ['quantity' => 1])
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(session('carrito'))->toBeNull();
});

it('devuelve 404 al añadir un producto inactivo', function () {
    $product = Product::factory()->inactive()->create(['stock' => 5]);

    $this->post(route('cart.add', $product), ['quantity' => 1])
        ->assertNotFound();
});

it('actualiza la cantidad de una línea', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $this->post(route('cart.add', $product), ['quantity' => 1]);

    $this->patch(route('cart.update', $product), ['quantity' => 4])
        ->assertRedirect();

    expect(session('carrito'))->toBe([$product->id => 4]);
});

it('elimina la línea al actualizar la cantidad a cero', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $this->post(route('cart.add', $product), ['quantity' => 2]);

    $this->patch(route('cart.update', $product), ['quantity' => 0])
        ->assertRedirect();

    expect(session('carrito'))->toBe([]);
});

it('elimina un producto del carrito', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $this->post(route('cart.add', $product), ['quantity' => 2]);

    $this->delete(route('cart.remove', $product))
        ->assertRedirect()
        ->assertSessionHas('success');

    expect(session('carrito'))->toBe([]);
});

it('vacía el carrito completo', function () {
    $uno = Product::factory()->create(['stock' => 10]);
    $dos = Product::factory()->create(['stock' => 10]);

    $this->post(route('cart.add', $uno), ['quantity' => 1]);
    $this->post(route('cart.add', $dos), ['quantity' => 1]);

    $this->delete(route('cart.clear'))->assertRedirect();

    expect(session('carrito'))->toBeNull();
});

it('retira del carrito los productos que dejan de estar publicados', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $this->post(route('cart.add', $product), ['quantity' => 2]);

    $product->update(['is_active' => false]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('ya no está disponible', false)
        ->assertSee('Tu carrito est&aacute; vac&iacute;o', false);
});

it('retira del carrito los productos que se han agotado', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $this->post(route('cart.add', $product), ['quantity' => 2]);

    $product->update(['stock' => 0]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('se ha agotado', false);
});

it('recorta la cantidad cuando el stock baja por debajo de lo pedido', function () {
    $product = Product::factory()->create(['stock' => 10]);
    $this->post(route('cart.add', $product), ['quantity' => 8]);

    $product->update(['stock' => 3]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Hemos ajustado', false);

    expect(session('carrito'))->toBe([$product->id => 3]);
});

it('valida la cantidad al añadir', function () {
    $product = Product::factory()->create(['stock' => 10]);

    $this->post(route('cart.add', $product), ['quantity' => 0])
        ->assertSessionHasErrors('quantity');

    $this->post(route('cart.add', $product), ['quantity' => 500])
        ->assertSessionHasErrors('quantity');
});

it('devuelve JSON cuando la petición lo espera', function () {
    $product = Product::factory()->create(['stock' => 10, 'price' => 5.00]);

    $this->postJson(route('cart.add', $product), ['quantity' => 2])
        ->assertOk()
        ->assertJson([
            'ok'       => true,
            'unidades' => 2,
            'subtotal' => '10,00 €',
        ]);
});

describe('comprobación de código postal', function () {
    it('confirma cobertura y devuelve los gastos de reparto', function () {
        DeliveryZone::factory()->create([
            'postal_code'  => '07800',
            'neighborhood' => 'Vara de Rey',
            'delivery_fee' => 3.50,
            'is_active'    => true,
        ]);

        $this->getJson(route('delivery.check', ['codigo_postal' => '07800']))
            ->assertOk()
            ->assertJson([
                'cubierto'                => true,
                'zona'                    => 'Vara de Rey',
                'gastos_envio'            => 3.5,
                'gastos_envio_formateado' => '3,50 €',
            ]);
    });

    it('indica que no hay cobertura fuera de zona', function () {
        $this->getJson(route('delivery.check', ['codigo_postal' => '28001']))
            ->assertOk()
            ->assertJson(['cubierto' => false, 'gastos_envio' => null]);
    });

    it('no tiene en cuenta las zonas desactivadas', function () {
        DeliveryZone::factory()->create([
            'postal_code' => '07820',
            'is_active'   => false,
        ]);

        $this->getJson(route('delivery.check', ['codigo_postal' => '07820']))
            ->assertOk()
            ->assertJson(['cubierto' => false]);
    });

    it('exige un código postal de cinco dígitos', function () {
        $this->getJson(route('delivery.check', ['codigo_postal' => '078']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('codigo_postal');
    });
});
