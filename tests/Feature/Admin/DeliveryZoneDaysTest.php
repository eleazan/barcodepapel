<?php

declare(strict_types=1);

use App\Models\DeliveryZone;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->create(['is_admin' => true, 'email_verified_at' => now()]);
    $this->actingAs($this->admin);
});

it('guarda los días marcados al crear una zona', function () {
    $this->post(route('admin.delivery-zones.store'), [
        'postal_code'   => '07840',
        'neighborhood'  => 'Santa Eulària',
        'city'          => 'Santa Eulària des Riu',
        'delivery_fee'  => 4,
        'delivery_days' => ['4'],
        'is_active'     => '1',
    ])->assertRedirect(route('admin.delivery-zones.index'));

    $zona = DeliveryZone::firstWhere('postal_code', '07840');

    expect($zona->configuredDays())->toBe([4]);
    expect($zona->deliveryDaysLabel())->toBe('jueves');
});

it('deja la zona sin día fijo si no se marca ninguno', function () {
    $this->post(route('admin.delivery-zones.store'), [
        'postal_code'  => '07800',
        'delivery_fee' => 0,
        'is_active'    => '1',
    ])->assertRedirect(route('admin.delivery-zones.index'));

    $zona = DeliveryZone::firstWhere('postal_code', '07800');

    expect($zona->delivery_days)->toBeNull();
    expect($zona->deliversAnyOpenDay())->toBeTrue();
});

it('permite quitar el día fijo de una zona que ya lo tenía', function () {
    $zona = DeliveryZone::factory()->onlyOn(4)->create(['postal_code' => '07840']);

    $this->put(route('admin.delivery-zones.update', $zona), [
        'postal_code'  => '07840',
        'delivery_fee' => 4,
        'is_active'    => '1',
    ])->assertRedirect(route('admin.delivery-zones.index'));

    expect($zona->fresh()->delivery_days)->toBeNull();
});

it('rechaza días fuera de la semana', function () {
    $this->post(route('admin.delivery-zones.store'), [
        'postal_code'   => '07840',
        'delivery_fee'  => 4,
        'delivery_days' => ['9'],
        'is_active'     => '1',
    ])->assertSessionHasErrors('delivery_days.0');

    expect(DeliveryZone::where('postal_code', '07840')->exists())->toBeFalse();
});

it('marca los días de la zona en el formulario de edición', function () {
    $zona = DeliveryZone::factory()->onlyOn(4)->create();

    $this->get(route('admin.delivery-zones.edit', $zona))
        ->assertOk()
        ->assertSee('Días de reparto', false)
        ->assertSee('value="4"', false);
});
