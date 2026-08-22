<?php

declare(strict_types=1);

use App\Models\NonWorkingDay;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create([
        'is_admin'          => true,
        'email_verified_at' => now(),
    ]));
});

it('da de alta un día suelto sin fecha de fin', function () {
    $this->post(route('admin.non-working-days.store'), [
        'name'      => 'Sant Ciriac',
        'starts_on' => '2026-08-08',
    ])->assertRedirect(route('admin.non-working-days.index'));

    $dia = NonWorkingDay::firstWhere('name', 'Sant Ciriac');

    expect($dia->starts_on->toDateString())->toBe('2026-08-08');
    expect($dia->ends_on->toDateString())->toBe('2026-08-08');
    expect($dia->isSingleDay())->toBeTrue();
    expect($dia->recurs_annually)->toBeFalse();
});

it('da de alta un cierre por vacaciones', function () {
    $this->post(route('admin.non-working-days.store'), [
        'name'      => 'Vacaciones de verano',
        'starts_on' => '2026-08-10',
        'ends_on'   => '2026-08-24',
    ])->assertRedirect(route('admin.non-working-days.index'));

    $dia = NonWorkingDay::firstWhere('name', 'Vacaciones de verano');

    expect($dia->isSingleDay())->toBeFalse();
    expect($dia->formattedRange())->toBe('del 10 de agosto al 24 de agosto de 2026');
});

it('marca un festivo como recurrente', function () {
    $this->post(route('admin.non-working-days.store'), [
        'name'            => 'Navidad',
        'starts_on'       => '2026-12-25',
        'recurs_annually' => '1',
    ])->assertRedirect(route('admin.non-working-days.index'));

    expect(NonWorkingDay::firstWhere('name', 'Navidad')->recurs_annually)->toBeTrue();
});

it('rechaza un cierre que termina antes de empezar', function () {
    $this->post(route('admin.non-working-days.store'), [
        'name'      => 'Al revés',
        'starts_on' => '2026-08-24',
        'ends_on'   => '2026-08-10',
    ])->assertSessionHasErrors('ends_on');

    expect(NonWorkingDay::count())->toBe(0);
});

it('rechaza un cierre recurrente que cruza el cambio de año', function () {
    $this->post(route('admin.non-working-days.store'), [
        'name'            => 'Fin de año',
        'starts_on'       => '2026-12-28',
        'ends_on'         => '2027-01-05',
        'recurs_annually' => '1',
    ])->assertSessionHasErrors('recurs_annually');

    expect(NonWorkingDay::count())->toBe(0);
});

it('permite ese mismo rango si no es recurrente', function () {
    $this->post(route('admin.non-working-days.store'), [
        'name'      => 'Fin de año',
        'starts_on' => '2026-12-28',
        'ends_on'   => '2027-01-05',
    ])->assertSessionHasNoErrors();

    expect(NonWorkingDay::count())->toBe(1);
});

it('edita y elimina un día', function () {
    $dia = NonWorkingDay::factory()->on('2026-08-27')->create(['name' => 'Provisional']);

    $this->put(route('admin.non-working-days.update', $dia), [
        'name'      => 'Festivo local',
        'starts_on' => '2026-08-28',
    ])->assertRedirect(route('admin.non-working-days.index'));

    expect($dia->fresh()->name)->toBe('Festivo local');
    expect($dia->fresh()->starts_on->toDateString())->toBe('2026-08-28');

    $this->delete(route('admin.non-working-days.destroy', $dia))
        ->assertRedirect(route('admin.non-working-days.index'));

    expect(NonWorkingDay::count())->toBe(0);
});

it('lista los días desde el panel', function () {
    NonWorkingDay::factory()->on('2026-12-25')->recurring()->create(['name' => 'Navidad']);

    $this->get(route('admin.non-working-days.index'))
        ->assertOk()
        ->assertSee('Navidad')
        ->assertSee('Cada año', false);
});

it('cierra el panel a quien no es admin', function () {
    $this->actingAs(User::factory()->create([
        'is_admin'          => false,
        'email_verified_at' => now(),
    ]));

    $this->get(route('admin.non-working-days.index'))->assertForbidden();
});
