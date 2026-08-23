<?php

declare(strict_types=1);

use App\Models\DeliveryZone;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\WelcomeNotification;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
});

/**
 * Un pedido sin cuenta ya no se puede hacer por la web, pero siguen existiendo:
 * los de antes de exigir cuenta y los que da de alta la librería a mano.
 */
function pedidoDeInvitado(string $email): Order
{
    return Order::factory()->create([
        'user_id'        => null,
        'customer_email' => $email,
        'customer_name'  => 'Marta Serra',
    ]);
}

function pedidoDe(User $user, string $email): Order
{
    DeliveryZone::factory()->create(['postal_code' => '07800', 'delivery_fee' => 3.00, 'is_active' => true]);
    $product = Product::factory()->create(['stock' => 5, 'price' => 10.00]);

    test()->actingAs($user);

    test()->post(route('cart.add', $product), ['quantity' => 1]);
    test()->post(route('checkout.store'), [
        'customer_name'      => 'Marta Serra',
        'customer_email'     => $email,
        'customer_phone'     => '971123456',
        'delivery_address'   => 'Carrer de la Mar 12',
        'postal_code'        => '07800',
        'acepta_condiciones' => '1',
    ]);

    return Order::latest('id')->firstOrFail();
}

it('lista los clientes registrados', function () {
    User::factory()->create(['name' => 'Marta Serra', 'email' => 'marta@example.com']);

    $this->actingAs($this->admin)
        ->get(route('admin.customers.index'))
        ->assertOk()
        ->assertSee('Marta Serra')
        ->assertSee('marta@example.com');
});

it('busca clientes por nombre o correo', function () {
    User::factory()->create(['name' => 'Marta Serra', 'email' => 'marta@example.com']);
    User::factory()->create(['name' => 'Joan Torres', 'email' => 'joan@example.com']);

    $this->actingAs($this->admin)
        ->get(route('admin.customers.index', ['search' => 'joan']))
        ->assertOk()
        ->assertSee('Joan Torres')
        ->assertDontSee('Marta Serra');
});

it('cierra la ficha a quien no es admin', function () {
    $cliente = User::factory()->create();

    $this->actingAs(User::factory()->create(['is_admin' => false, 'email_verified_at' => now()]))
        ->get(route('admin.customers.show', $cliente))
        ->assertForbidden();
});

describe('la ficha reúne todo lo del cliente', function () {
    it('muestra sus pedidos', function () {
        $cliente = User::factory()->create(['email' => 'marta@example.com', 'email_verified_at' => now()]);
        $order   = pedidoDe($cliente, 'marta@example.com');

        $this->actingAs($this->admin)
            ->get(route('admin.customers.show', $cliente))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('13,00 €', false);      // 10 + 3 de reparto
    });

    it('recoge también los pedidos hechos como invitado con su mismo correo', function () {
        $cliente = User::factory()->create(['email' => 'marta@example.com']);
        $order   = pedidoDeInvitado('marta@example.com');

        expect($order->user_id)->toBeNull();

        $this->actingAs($this->admin)
            ->get(route('admin.customers.show', $cliente))
            ->assertOk()
            ->assertSee($order->order_number)
            ->assertSee('como invitado');
    });

    it('muestra los avisos de sus pedidos', function () {
        $cliente = User::factory()->create(['email' => 'marta@example.com', 'email_verified_at' => now()]);
        pedidoDe($cliente, 'marta@example.com');

        $this->actingAs($this->admin)
            ->get(route('admin.customers.show', $cliente))
            ->assertOk()
            ->assertSee('Pedido recibido');
    });

    it('muestra también los avisos de cuenta, que no tienen pedido', function () {
        $cliente = User::factory()->create(['name' => 'Marta Serra']);

        $cliente->notify(new WelcomeNotification);

        $log = NotificationLog::where('user_id', $cliente->id)->first();

        expect($log)->not->toBeNull();
        expect($log->order_id)->toBeNull();
        expect($log->event)->toBe(NotificationLog::EVENT_WELCOME);
        expect($log->isAccountNotice())->toBeTrue();

        $this->actingAs($this->admin)
            ->get(route('admin.customers.show', $cliente))
            ->assertOk()
            ->assertSee('Bienvenida');
    });

    it('registra la verificación de correo y la recuperación de contraseña', function () {
        $cliente = User::factory()->unverified()->create();

        $cliente->sendEmailVerificationNotification();
        $cliente->sendPasswordResetNotification('un-token');

        $eventos = NotificationLog::where('user_id', $cliente->id)->pluck('event')->all();

        expect($eventos)->toContain(NotificationLog::EVENT_EMAIL_VERIFICATION);
        expect($eventos)->toContain(NotificationLog::EVENT_PASSWORD_RESET);

        $this->actingAs($this->admin)
            ->get(route('admin.customers.show', $cliente))
            ->assertOk()
            ->assertSee('Verificación de correo')
            ->assertSee('Recuperar contraseña');
    });
});

describe('desde el pedido', function () {
    it('enlaza con la ficha del cliente registrado', function () {
        $cliente = User::factory()->create(['email' => 'marta@example.com', 'email_verified_at' => now()]);
        $order   = pedidoDe($cliente, 'marta@example.com');

        $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee(route('admin.customers.show', $cliente));
    });

    it('avisa de que el pedido es de un invitado', function () {
        $order = pedidoDeInvitado('invitada@example.com');

        $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('invitado');
    });

    it('muestra el tipo de cada aviso en el historial del pedido', function () {
        $cliente = User::factory()->create(['email' => 'marta@example.com', 'email_verified_at' => now()]);
        $order   = pedidoDe($cliente, 'marta@example.com');

        $this->actingAs($this->admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk()
            ->assertSee('Pedido recibido')
            ->assertSee('Aviso a la librería');
    });
});

it('vincula al cliente los avisos de sus pedidos', function () {
    $cliente = User::factory()->create(['email' => 'marta@example.com', 'email_verified_at' => now()]);
    $order   = pedidoDe($cliente, 'marta@example.com');

    $logs = NotificationLog::where('order_id', $order->id)->get();

    expect($logs)->not->toBeEmpty();
    foreach ($logs as $log) {
        expect($log->user_id)->toBe($cliente->id);
    }
});
