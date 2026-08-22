<?php

declare(strict_types=1);

use App\Jobs\Verial\SendOrderToVerialJob;
use App\Models\DeliveryZone;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Mail::fake();

    $this->zona = DeliveryZone::factory()->create([
        'postal_code'  => '07800',
        'neighborhood' => 'Vara de Rey',
        'city'         => 'Eivissa',
        'delivery_fee' => 3.00,
        'is_active'    => true,
    ]);
});

/**
 * Datos válidos del formulario de checkout.
 */
function datosPedido(array $overrides = []): array
{
    return array_merge([
        'customer_name'      => 'Marta Serra',
        'customer_email'     => 'marta@example.com',
        'customer_phone'     => '971123456',
        'delivery_address'   => 'Carrer de la Mar 12, 3r B',
        'postal_code'        => '07800',
        'notes'              => 'Llamar al llegar',
        'acepta_condiciones' => '1',
    ], $overrides);
}

/**
 * Corte entre peticiones.
 *
 * El carrito está registrado como `scoped`: en producción cada petición
 * construye uno nuevo que relee el catálogo. En los tests la aplicación es la
 * misma entre llamadas HTTP, así que hay que soltar las instancias para que el
 * carrito no arrastre los precios y las cantidades que ya había leído.
 */
function nuevaPeticion(): void
{
    app()->forgetScopedInstances();
}

it('redirige al carrito si está vacío', function () {
    $this->get(route('checkout.show'))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('error');
});

it('muestra el formulario de checkout con productos en el carrito', function () {
    $product = Product::factory()->create(['stock' => 5, 'price' => 10.00]);
    $this->post(route('cart.add', $product), ['quantity' => 2]);

    $this->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('Finalizar pedido')
        ->assertSee($product->name)
        ->assertSee('20,00 €', false);
});

it('crea el pedido y redirige a la confirmación', function () {
    $product = Product::factory()->create(['stock' => 5, 'price' => 10.00]);
    $this->post(route('cart.add', $product), ['quantity' => 2]);

    $response = $this->post(route('checkout.store'), datosPedido());

    $order = Order::latest('id')->first();

    expect($order)->not->toBeNull();
    $response->assertRedirect(route('checkout.confirmation', $order->order_number));

    expect($order->customer_name)->toBe('Marta Serra');
    expect($order->postal_code)->toBe('07800');
    expect($order->status)->toBe(Order::STATUS_PENDIENTE);
    expect((float) $order->subtotal)->toBe(20.00);
    expect((float) $order->delivery_fee)->toBe(3.00);
    expect((float) $order->total)->toBe(23.00);
    expect($order->notes)->toBe('Llamar al llegar');
});

it('guarda las líneas del pedido con el precio del momento', function () {
    $product = Product::factory()->create(['stock' => 5, 'price' => 7.25]);
    $this->post(route('cart.add', $product), ['quantity' => 3]);

    $this->post(route('checkout.store'), datosPedido());

    $order = Order::latest('id')->first();

    expect($order->items)->toHaveCount(1);

    $item = $order->items->first();
    expect($item->product_id)->toBe($product->id);
    expect($item->quantity)->toBe(3);
    expect((float) $item->unit_price)->toBe(7.25);
    expect((float) $item->total)->toBe(21.75);
});

it('descuenta el stock de los productos comprados', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $this->post(route('cart.add', $product), ['quantity' => 2]);

    $this->post(route('checkout.store'), datosPedido());

    expect($product->fresh()->stock)->toBe(3);
});

it('vacía el carrito tras confirmar el pedido', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $this->post(route('cart.add', $product), ['quantity' => 1]);

    $this->post(route('checkout.store'), datosPedido());

    expect(session('carrito'))->toBeNull();
});

it('rechaza códigos postales sin reparto', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $this->post(route('cart.add', $product), ['quantity' => 1]);

    $this->post(route('checkout.store'), datosPedido(['postal_code' => '28001']))
        ->assertSessionHasErrors('postal_code');

    expect(Order::count())->toBe(0);
    expect($product->fresh()->stock)->toBe(5);
});

it('rechaza códigos postales de zonas desactivadas', function () {
    $this->zona->update(['is_active' => false]);

    $product = Product::factory()->create(['stock' => 5]);
    $this->post(route('cart.add', $product), ['quantity' => 1]);

    $this->post(route('checkout.store'), datosPedido())
        ->assertSessionHasErrors('postal_code');

    expect(Order::count())->toBe(0);
});

it('exige aceptar las condiciones de venta', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $this->post(route('cart.add', $product), ['quantity' => 1]);

    $this->post(route('checkout.store'), datosPedido(['acepta_condiciones' => null]))
        ->assertSessionHasErrors('acepta_condiciones');

    expect(Order::count())->toBe(0);
});

it('valida los datos de contacto y entrega', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $this->post(route('cart.add', $product), ['quantity' => 1]);

    $this->post(route('checkout.store'), datosPedido([
        'customer_name'    => '',
        'customer_phone'   => '',
        'delivery_address' => '',
        'customer_email'   => 'no-es-un-email',
    ]))->assertSessionHasErrors([
        'customer_name',
        'customer_phone',
        'delivery_address',
        'customer_email',
    ]);
});

it('acepta pedidos sin email', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $this->post(route('cart.add', $product), ['quantity' => 1]);

    $this->post(route('checkout.store'), datosPedido(['customer_email' => null]))
        ->assertSessionHasNoErrors();

    expect(Order::latest('id')->first()->customer_email)->toBeNull();
});

it('no crea el pedido si el carrito está vacío', function () {
    $this->post(route('checkout.store'), datosPedido())
        ->assertRedirect()
        ->assertSessionHas('error');

    expect(Order::count())->toBe(0);
});

it('aplica la tarifa más baja cuando el código postal tiene varias zonas', function () {
    DeliveryZone::factory()->create([
        'postal_code'  => '07800',
        'neighborhood' => 'Dalt Vila',
        'delivery_fee' => 1.50,
        'is_active'    => true,
    ]);

    $product = Product::factory()->create(['stock' => 5, 'price' => 10.00]);
    $this->post(route('cart.add', $product), ['quantity' => 1]);

    $this->post(route('checkout.store'), datosPedido());

    expect((float) Order::latest('id')->first()->delivery_fee)->toBe(1.50);
});

it('registra el acuse de recibo al cliente', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $this->post(route('cart.add', $product), ['quantity' => 1]);

    $this->post(route('checkout.store'), datosPedido());

    $log = NotificationLog::latest('id')->first();

    expect($log)->not->toBeNull();
    expect($log->event)->toBe(NotificationLog::EVENT_ORDER_CREATED);
    expect($log->channel)->toBe(NotificationLog::CHANNEL_EMAIL);
    expect($log->recipient)->toBe('marta@example.com');
    expect($log->status)->toBe(NotificationLog::STATUS_SENT);
});

it('asocia el pedido al usuario autenticado', function () {
    $user    = User::factory()->create();
    $product = Product::factory()->create(['stock' => 5]);

    $this->actingAs($user);
    $this->post(route('cart.add', $product), ['quantity' => 1]);
    $this->post(route('checkout.store'), datosPedido());

    expect(Order::latest('id')->first()->user_id)->toBe($user->id);
});

it('deja el pedido sin usuario cuando el cliente compra como invitado', function () {
    $product = Product::factory()->create(['stock' => 5]);
    $this->post(route('cart.add', $product), ['quantity' => 1]);

    $this->post(route('checkout.store'), datosPedido());

    expect(Order::latest('id')->first()->user_id)->toBeNull();
});

describe('confirmación del pedido', function () {
    it('deja ver la confirmación a quien acaba de comprar', function () {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('checkout.store'), datosPedido());

        $order = Order::latest('id')->first();

        $this->get(route('checkout.confirmation', $order->order_number))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('Pedido recibido', false);
    });

    it('oculta el pedido a terceros', function () {
        $order = Order::factory()->create();

        $this->get(route('checkout.confirmation', $order->order_number))
            ->assertForbidden();
    });

    it('permite al cliente registrado ver sus propios pedidos', function () {
        $user  = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('checkout.confirmation', $order->order_number))
            ->assertOk();
    });

    it('no permite ver los pedidos de otro cliente', function () {
        $otro  = User::factory()->create();
        $order = Order::factory()->create(['user_id' => User::factory()->create()->id]);

        $this->actingAs($otro)
            ->get(route('checkout.confirmation', $order->order_number))
            ->assertForbidden();
    });

    it('devuelve 404 si el número de pedido no existe', function () {
        $this->get(route('checkout.confirmation', 'BP-00000000-XXXXX'))
            ->assertNotFound();
    });
});

describe('avisos al entrar en el checkout', function () {
    it('avisa de las cantidades recortadas por falta de stock', function () {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.add', $product), ['quantity' => 3]);

        // Otro cliente se lleva unidades mientras este rellena el pedido.
        $product->update(['stock' => 1]);
        nuevaPeticion();

        $this->get(route('checkout.show'))
            ->assertOk()
            ->assertSee('Hemos ajustado', false)
            ->assertSee($product->name);
    });

    it('avisa de los productos retirados del catálogo', function () {
        $disponible = Product::factory()->create(['stock' => 5]);
        $retirado   = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.add', $disponible), ['quantity' => 1]);
        $this->post(route('cart.add', $retirado), ['quantity' => 1]);

        $retirado->update(['is_active' => false]);
        nuevaPeticion();

        $this->get(route('checkout.show'))
            ->assertOk()
            ->assertSee('ya no está disponible', false);
    });

    it('explica por qué el carrito se ha quedado vacío', function () {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.add', $product), ['quantity' => 1]);

        $product->update(['stock' => 0]);
        nuevaPeticion();

        $this->get(route('checkout.show'))
            ->assertRedirect(route('cart.index'))
            ->assertSessionHas('error', fn (string $error) => str_contains($error, 'se ha agotado'));
    });
});

describe('el pedido no cambia entre el resumen y la confirmación', function () {
    it('confirma el pedido cuando nada ha cambiado', function () {
        $product = Product::factory()->create(['stock' => 5, 'price' => 10.00]);
        $this->post(route('cart.add', $product), ['quantity' => 2]);
        $this->get(route('checkout.show'))->assertOk();

        $this->post(route('checkout.store'), datosPedido())
            ->assertSessionMissing('cart_changes');

        expect(Order::count())->toBe(1);
    });

    it('no crea el pedido si el precio ha subido desde que el cliente lo vio', function () {
        $product = Product::factory()->create(['stock' => 5, 'price' => 10.00]);
        $this->post(route('cart.add', $product), ['quantity' => 2]);
        $this->get(route('checkout.show'))->assertOk();

        $product->update(['price' => 12.50]);
        nuevaPeticion();

        $this->post(route('checkout.store'), datosPedido())
            ->assertRedirect(route('checkout.show'))
            ->assertSessionHas('cart_changes', fn (array $cambios) => count($cambios) === 1
                && str_contains($cambios[0], '10,00 €')
                && str_contains($cambios[0], '12,50 €'));

        expect(Order::count())->toBe(0);
        expect($product->fresh()->stock)->toBe(5);
    });

    it('no crea el pedido si el stock ya no da para las unidades pedidas', function () {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.add', $product), ['quantity' => 4]);
        $this->get(route('checkout.show'))->assertOk();

        $product->update(['stock' => 2]);
        nuevaPeticion();

        $this->post(route('checkout.store'), datosPedido())
            ->assertSessionHas('cart_changes', fn (array $cambios) => str_contains($cambios[0], 'de 4 a 2 unidad(es)'));

        expect(Order::count())->toBe(0);
    });

    it('no crea el pedido si un producto ha dejado de estar disponible', function () {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $this->get(route('checkout.show'))->assertOk();

        $product->update(['is_active' => false]);
        nuevaPeticion();

        $this->post(route('checkout.store'), datosPedido())
            ->assertSessionHas('cart_changes', fn (array $cambios) => str_contains($cambios[0], 'se ha retirado del pedido'));

        expect(Order::count())->toBe(0);
    });

    it('conserva los datos del formulario al avisar del cambio', function () {
        $product = Product::factory()->create(['stock' => 5, 'price' => 10.00]);
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $this->get(route('checkout.show'))->assertOk();

        $product->update(['price' => 11.00]);
        nuevaPeticion();

        $this->post(route('checkout.store'), datosPedido())
            ->assertSessionHasInput('customer_name', 'Marta Serra')
            ->assertSessionHasInput('delivery_address', 'Carrer de la Mar 12, 3r B');
    });

    it('acepta el pedido al reconfirmarlo con el precio nuevo', function () {
        $product = Product::factory()->create(['stock' => 5, 'price' => 10.00]);
        $this->post(route('cart.add', $product), ['quantity' => 2]);
        $this->get(route('checkout.show'))->assertOk();

        $product->update(['price' => 12.50]);
        nuevaPeticion();

        // Primer intento: solo avisa.
        $this->post(route('checkout.store'), datosPedido());
        expect(Order::count())->toBe(0);

        // El cliente vuelve al formulario, ve el precio nuevo y reconfirma.
        $this->get(route('checkout.show'))->assertOk();
        $this->post(route('checkout.store'), datosPedido());

        $order = Order::latest('id')->first();

        expect($order)->not->toBeNull();
        expect((float) $order->subtotal)->toBe(25.00);
        expect((float) $order->total)->toBe(28.00);
    });

    it('crea el pedido sin resumen previo en sesión', function () {
        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.add', $product), ['quantity' => 1]);

        // POST directo, sin pasar por el formulario: no hay nada que contrastar.
        $this->post(route('checkout.store'), datosPedido())
            ->assertSessionMissing('cart_changes');

        expect(Order::count())->toBe(1);
    });
});

describe('integración con Verial', function () {
    it('no encola ningún envío al ERP al confirmar el pedido', function () {
        Queue::fake();

        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('checkout.store'), datosPedido());

        // El pedido nace "pendiente": entra en el ERP al marcarlo "preparado".
        Queue::assertNotPushed(SendOrderToVerialJob::class);
    });

    it('funciona con Verial sin configurar', function () {
        config(['verial.host' => null, 'verial.session' => null]);

        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.add', $product), ['quantity' => 1]);

        $this->post(route('checkout.store'), datosPedido())
            ->assertSessionHasNoErrors();

        expect(Order::count())->toBe(1);
    });
});
