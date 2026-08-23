<?php

declare(strict_types=1);

use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;

/**
 * En los tests el transporte es `array` (ver phpunit.xml), así que se pueden
 * inspeccionar los mensajes tal y como salen, sin `Mail::fake()`.
 *
 * @return list<Email>
 */
function correosEnviados(): array
{
    return Mail::mailer()
        ->getSymfonyTransport()
        ->messages()
        ->map(fn ($enviado) => $enviado->getOriginalMessage())
        ->all();
}

beforeEach(function () {
    Mail::mailer()->getSymfonyTransport()->flush();
});

it('envía el correo de prueba al destinatario indicado', function () {
    $this->artisan('mail:test', ['destinatario' => 'librera@example.com'])
        ->assertSuccessful();

    $correos = correosEnviados();

    expect($correos)->toHaveCount(1);
    expect($correos[0]->getTo()[0]->getAddress())->toBe('librera@example.com');
    expect($correos[0]->getSubject())->toContain(config('tienda.nombre'));
});

it('rechaza una dirección que no es válida', function () {
    $this->artisan('mail:test', ['destinatario' => 'no-es-un-correo'])
        ->assertFailed();

    expect(correosEnviados())->toBeEmpty();
});

it('pone el buzón de la tienda como dirección de respuesta', function () {
    $this->actingAs(User::factory()->create(['email_verified_at' => now()]));

    DeliveryZone::factory()->create(['postal_code' => '07800', 'is_active' => true]);
    $product = Product::factory()->create(['stock' => 5]);

    $this->post(route('cart.add', $product), ['quantity' => 1]);
    $this->post(route('checkout.store'), [
        'customer_name'      => 'Marta Serra',
        'customer_email'     => 'marta@example.com',
        'customer_phone'     => '971123456',
        'delivery_address'   => 'Carrer de la Mar 12',
        'postal_code'        => '07800',
        'acepta_condiciones' => '1',
    ]);

    expect(Order::count())->toBe(1);

    // Dos correos: el acuse al cliente y el aviso interno a la librería.
    $correos = correosEnviados();

    expect($correos)->toHaveCount(2);

    // Aunque salgan desde otra cuenta —una de Gmail, por ejemplo—, las
    // respuestas tienen que llegar al buzón público de la librería.
    foreach ($correos as $correo) {
        expect($correo->getReplyTo()[0]->getAddress())->toBe(config('tienda.email'));
    }
});
