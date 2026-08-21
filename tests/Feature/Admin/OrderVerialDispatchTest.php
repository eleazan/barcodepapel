<?php

declare(strict_types=1);

use App\Jobs\Verial\SendOrderToVerialJob;
use App\Models\Order;
use App\Models\User;
use App\Services\Verial\VerialClient;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Mail::fake();
    Queue::fake();

    $this->admin = User::factory()->create(['is_admin' => true]);
});

/**
 * Sustituye el cliente Verial por uno con o sin configuración.
 */
function fakeVerial(bool $configurado): void
{
    app()->singleton(VerialClient::class, fn () => new VerialClient(
        host: $configurado ? '127.0.0.1' : null,
        port: 8000,
        session: $configurado ? 'sesion-test' : null,
        timeout: 5,
    ));
}

it('envía el pedido al ERP al marcarlo como preparado', function () {
    fakeVerial(configurado: true);

    $order = Order::factory()->pendiente()->create(['verial_enviado_at' => null]);

    $this->actingAs($this->admin)
        ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_PREPARADO])
        ->assertRedirect();

    Queue::assertPushed(SendOrderToVerialJob::class);
});

it('no envía nada al ERP si Verial no está configurado', function () {
    fakeVerial(configurado: false);

    $order = Order::factory()->pendiente()->create(['verial_enviado_at' => null]);

    $this->actingAs($this->admin)
        ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_PREPARADO])
        ->assertRedirect();

    Queue::assertNotPushed(SendOrderToVerialJob::class);

    // El cambio de estado se completa igualmente.
    expect($order->fresh()->status)->toBe(Order::STATUS_PREPARADO);
});

it('no reenvía un pedido que ya está en el ERP', function () {
    fakeVerial(configurado: true);

    $order = Order::factory()->create([
        'status'            => Order::STATUS_PREPARADO,
        'verial_enviado_at' => now(),
    ]);

    $this->actingAs($this->admin)
        ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_EN_REPARTO])
        ->assertRedirect();

    Queue::assertNotPushed(SendOrderToVerialJob::class);
});

it('no envía al ERP mientras el pedido sigue pendiente', function () {
    fakeVerial(configurado: true);

    $order = Order::factory()->create([
        'status'            => Order::STATUS_PREPARADO,
        'verial_enviado_at' => null,
    ]);

    $this->actingAs($this->admin)
        ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_PENDIENTE])
        ->assertRedirect();

    Queue::assertNotPushed(SendOrderToVerialJob::class);
});

it('no encola nada si el estado no cambia', function () {
    fakeVerial(configurado: true);

    $order = Order::factory()->create([
        'status'            => Order::STATUS_PREPARADO,
        'verial_enviado_at' => null,
    ]);

    $this->actingAs($this->admin)
        ->patch(route('admin.orders.status', $order), ['status' => Order::STATUS_PREPARADO])
        ->assertRedirect();

    Queue::assertNotPushed(SendOrderToVerialJob::class);
});
