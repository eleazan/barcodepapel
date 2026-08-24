<?php

declare(strict_types=1);

use App\Models\DeliveryZone;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use App\Notifications\WelcomeNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Symfony\Component\Mime\Email;

/**
 * @return list<Email>
 */
function correos(): array
{
    return Mail::mailer()
        ->getSymfonyTransport()
        ->messages()
        ->map(fn ($enviado) => $enviado->getOriginalMessage())
        ->all();
}

function correoPara(string $direccion): ?Email
{
    foreach (correos() as $correo) {
        foreach ($correo->getTo() as $destinatario) {
            if ($destinatario->getAddress() === $direccion) {
                return $correo;
            }
        }
    }

    return null;
}

function hacerPedido(array $overrides = []): Order
{
    // Comprar exige cuenta con el correo verificado.
    if (! auth()->check()) {
        test()->actingAs(User::factory()->create([
            'email'             => 'marta@example.com',
            'email_verified_at' => now(),
        ]));
    }

    DeliveryZone::factory()->create(['postal_code' => '07800', 'delivery_fee' => 3.00, 'is_active' => true]);
    $product = Product::factory()->create(['stock' => 5, 'price' => 10.00]);

    test()->post(route('cart.add', $product), ['quantity' => 2]);
    test()->post(route('checkout.store'), array_merge([
        'customer_name'      => 'Marta Serra',
        'customer_email'     => 'marta@example.com',
        'customer_phone'     => '971123456',
        'delivery_address'   => 'Carrer de la Mar 12',
        'postal_code'        => '07800',
        'acepta_condiciones' => '1',
    ], $overrides));

    return Order::latest('id')->firstOrFail();
}

beforeEach(function () {
    Mail::mailer()->getSymfonyTransport()->flush();
});

describe('pedido nuevo', function () {
    it('avisa al cliente con el detalle maquetado', function () {
        $order = hacerPedido();

        $correo = correoPara('marta@example.com');

        expect($correo)->not->toBeNull();
        expect($correo->getSubject())->toContain($order->order_number);

        $html = $correo->getHtmlBody();
        expect($html)->toContain('Hemos recibido tu pedido');
        expect($html)->toContain($order->order_number);
        expect($html)->toContain('23,00 €');            // 2 × 10 + 3 de reparto
        expect($html)->toContain(config('tienda.nombre'));

        // Y su alternativa en texto plano, para los clientes que no pintan HTML.
        expect($correo->getTextBody())->toContain($order->order_number);
    });

    it('manda una copia a la librería', function () {
        $order = hacerPedido();

        $copia = correoPara(config('tienda.email'));

        expect($copia)->not->toBeNull();
        expect($copia->getSubject())->toContain('Nuevo pedido');
        expect($copia->getHtmlBody())->toContain('Marta Serra');
        expect($copia->getHtmlBody())->toContain('971123456');

        // Queda registrada en el historial del pedido como un envío más.
        $log = NotificationLog::where('order_id', $order->id)
            ->where('event', NotificationLog::EVENT_STORE_COPY)
            ->first();

        expect($log)->not->toBeNull();
        expect($log->recipient)->toBe(config('tienda.email'));
        expect($log->status)->toBe(NotificationLog::STATUS_SENT);
    });

    it('avisa a la librería aunque el cliente no deje correo', function () {
        hacerPedido(['customer_email' => null]);

        expect(correoPara(config('tienda.email')))->not->toBeNull();
    });

    it('no avisa a la librería si no hay buzón configurado', function () {
        config(['tienda.email' => null]);

        hacerPedido();

        expect(NotificationLog::where('event', NotificationLog::EVENT_STORE_COPY)->count())->toBe(0);
    });
});

describe('cambios de estado', function () {
    it('avisa de cada cambio con la maquetación de la tienda', function () {
        $order = hacerPedido();
        $admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);

        Mail::mailer()->getSymfonyTransport()->flush();

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_EN_REPARTO]);

        $correo = correoPara('marta@example.com');

        expect($correo)->not->toBeNull();
        expect($correo->getSubject())->toContain('En reparto');
        expect($correo->getHtmlBody())->toContain('Tu pedido va de camino');
    });

    it('despide el pedido entregado con su propio mensaje', function () {
        $order = hacerPedido();
        $admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);

        Mail::mailer()->getSymfonyTransport()->flush();

        $this->actingAs($admin)
            ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_ENTREGADO]);

        $html = correoPara('marta@example.com')?->getHtmlBody();

        expect($html)->toContain('Pedido entregado');
        expect($html)->toContain('14 días');
    });
});

describe('cuenta de cliente', function () {
    it('usa la plantilla de la tienda al verificar el correo', function () {
        Notification::fake();

        $this->post(route('register'), [
            'name'                  => 'Joan Torres',
            'email'                 => 'joan@example.com',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        Notification::assertSentTo(
            User::firstWhere('email', 'joan@example.com'),
            VerifyEmailNotification::class,
        );
    });

    it('maqueta el correo de verificación con el layout común', function () {
        $user = User::factory()->unverified()->create(['name' => 'Joan Torres']);

        $user->sendEmailVerificationNotification();

        $correo = correoPara($user->email);

        expect($correo)->not->toBeNull();
        expect($correo->getSubject())->toContain('Confirma tu correo');
        expect($correo->getHtmlBody())->toContain('Joan Torres');
        expect($correo->getHtmlBody())->toContain(config('tienda.direccion.calle'));
    });

    it('usa la plantilla de la tienda al recuperar la contraseña', function () {
        Notification::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPasswordNotification::class);
    });

    it('maqueta el correo de recuperación con el layout común', function () {
        $user = User::factory()->create(['name' => 'Joan Torres']);

        $user->sendPasswordResetNotification('un-token');

        $correo = correoPara($user->email);

        expect($correo)->not->toBeNull();
        expect($correo->getSubject())->toContain('Recupera tu contraseña');
        expect($correo->getHtmlBody())->toContain('un-token');
    });

    it('da la bienvenida cuando el cliente confirma su correo', function () {
        Notification::fake();

        $user = User::factory()->unverified()->create();

        event(new Verified($user));

        Notification::assertSentTo($user, WelcomeNotification::class);
    });

    it('maqueta la bienvenida con el layout común', function () {
        $user = User::factory()->create(['name' => 'Joan Torres']);

        $user->notify(new WelcomeNotification);

        $html = correoPara($user->email)?->getHtmlBody();

        expect($html)->toContain('Joan Torres');
        expect($html)->toContain('Ver el catálogo');
        expect($html)->toContain(config('tienda.telefono.display'));
    });
});

it('compone un documento HTML completo con el layout', function (string $vista, array $datos) {
    $html = view($vista, $datos)->render();

    expect(trim($html))->toStartWith('<!DOCTYPE html>');
    expect($html)->toContain('</html>');

    // La cabecera, el pie y los datos de la tienda salen del layout común.
    expect($html)->toContain(config('tienda.nombre'));
    expect($html)->toContain(config('tienda.direccion.calle'));
    expect($html)->toContain('Condiciones de venta');

    // Nada de Blade sin resolver ni componentes sin expandir.
    expect($html)->not->toContain('{{');
    expect($html)->not->toContain('<x-mail');
})->with([
    'bienvenida'   => ['emails.auth.welcome', ['nombre' => 'Marta']],
    'verificación' => ['emails.auth.verify', ['nombre' => 'Marta', 'url' => 'https://ejemplo.test/v']],
    'contraseña'   => ['emails.auth.reset-password', ['nombre' => 'Marta', 'url' => 'https://ejemplo.test/r', 'minutos' => 60]],
]);

it('compone también los correos de pedido sobre el layout', function () {
    $order = hacerPedido();

    foreach (['emails.orders.created', 'emails.orders.store-copy'] as $vista) {
        $html = view($vista, ['order' => $order])->render();

        expect(trim($html))->toStartWith('<!DOCTYPE html>');
        expect($html)->toContain('Condiciones de venta');
        expect($html)->not->toContain('<x-mail');
    }

    $status = view('emails.orders.status', [
        'order'   => $order,
        'titulo'  => 'Tu pedido va de camino',
        'mensaje' => 'Está en reparto.',
    ])->render();

    expect(trim($status))->toStartWith('<!DOCTYPE html>');
    expect($status)->not->toContain('<x-mail');
});

it('todos los correos responden al buzón de la tienda', function () {
    hacerPedido();

    foreach (correos() as $correo) {
        expect($correo->getReplyTo()[0]->getAddress())->toBe(config('tienda.email'));
    }
});
