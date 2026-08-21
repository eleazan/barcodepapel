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
