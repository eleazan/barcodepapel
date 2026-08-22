<?php

declare(strict_types=1);

use App\Models\DeliveryZone;
use App\Models\NonWorkingDay;
use App\Models\Order;
use App\Models\Product;
use App\Services\Delivery\DeliveryCalendar;
use Database\Seeders\NonWorkingDaySeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Mail::fake();
    // Lunes 24 de agosto de 2026.
    Carbon::setTestNow(Carbon::parse('2026-08-24 10:00:00'));
});

afterEach(function () {
    Carbon::setTestNow();
});

describe('cierres puntuales', function () {
    it('salta un festivo suelto', function () {
        NonWorkingDay::factory()->on('2026-08-27')->create(['name' => 'Festivo local']);

        // La zona reparte los jueves: el 27 es festivo, así que toca el 3.
        $zona = DeliveryZone::factory()->onlyOn(4)->create();

        expect($zona->nextDeliveryDate()->toDateString())->toBe('2026-09-03');
    });

    it('salta un cierre por vacaciones completo', function () {
        NonWorkingDay::factory()->between('2026-08-25', '2026-09-06')->create([
            'name' => 'Vacaciones de verano',
        ]);

        $zona = DeliveryZone::factory()->create(['delivery_days' => null]);

        expect($zona->nextDeliveryDate()->toDateString())->toBe('2026-09-07');
    });

    it('no tiene en cuenta un cierre que ya ha pasado', function () {
        NonWorkingDay::factory()->on('2026-08-10')->create(['name' => 'Cierre pasado']);

        $zona = DeliveryZone::factory()->create(['delivery_days' => null]);

        expect($zona->nextDeliveryDate()->toDateString())->toBe('2026-08-25');
    });
});

describe('festivos que se repiten cada año', function () {
    it('aplica el festivo recurrente aunque esté dado de alta en otro año', function () {
        // Dado de alta en 2020, pero se repite cada 25 de diciembre.
        NonWorkingDay::factory()->on('2020-12-25')->recurring()->create(['name' => 'Navidad']);

        Carbon::setTestNow(Carbon::parse('2026-12-24 10:00:00'));

        $zona = DeliveryZone::factory()->create(['delivery_days' => null]);

        // El 25 es festivo y el 27 es domingo: pasa al sábado 26.
        expect($zona->nextDeliveryDate()->toDateString())->toBe('2026-12-26');
    });

    it('cubre un rango recurrente', function () {
        NonWorkingDay::factory()->between('2020-08-24', '2020-08-31')->recurring()->create([
            'name' => 'Cierre de verano',
        ]);

        $zona = DeliveryZone::factory()->create(['delivery_days' => null]);

        expect($zona->nextDeliveryDate()->toDateString())->toBe('2026-09-01');
    });

    it('sabe si una fecha concreta está cerrada', function () {
        NonWorkingDay::factory()->on('2020-01-01')->recurring()->create(['name' => 'Año Nuevo']);

        $calendar = app(DeliveryCalendar::class);

        expect($calendar->isClosed(Carbon::parse('2027-01-01')))->toBeTrue();
        expect($calendar->isClosed(Carbon::parse('2027-01-02')))->toBeFalse();
        expect($calendar->closureOn(Carbon::parse('2030-01-01'))->name)->toBe('Año Nuevo');
    });
});

describe('explicar el retraso', function () {
    it('dice qué cierre ha movido la entrega', function () {
        NonWorkingDay::factory()->on('2026-08-27')->create(['name' => 'Festivo local']);

        $zona = DeliveryZone::factory()->onlyOn(4)->create([
            'postal_code' => '07840',
            'is_active'   => true,
        ]);

        $saltados = app(DeliveryCalendar::class)->closuresDelaying($zona);

        expect($saltados)->toHaveCount(1);
        expect($saltados[0]['cierre']->name)->toBe('Festivo local');
        expect($saltados[0]['fecha']->toDateString())->toBe('2026-08-27');
    });

    it('lo expone en el comprobador de código postal', function () {
        NonWorkingDay::factory()->on('2026-08-27')->create(['name' => 'Festivo local']);

        DeliveryZone::factory()->onlyOn(4)->create([
            'postal_code' => '07840',
            'is_active'   => true,
        ]);

        $this->getJson(route('delivery.check', ['codigo_postal' => '07840']))
            ->assertOk()
            ->assertJson([
                'proxima_entrega' => '2026-09-03',
                'motivo_retraso'  => 'El jueves 27 de agosto cerramos por Festivo local',
            ]);
    });

    it('no informa de retraso cuando no lo hay', function () {
        DeliveryZone::factory()->onlyOn(4)->create([
            'postal_code' => '07840',
            'is_active'   => true,
        ]);

        $this->getJson(route('delivery.check', ['codigo_postal' => '07840']))
            ->assertOk()
            ->assertJson(['motivo_retraso' => null]);
    });
});

it('guarda en el pedido la fecha ya corregida por el festivo', function () {
    NonWorkingDay::factory()->on('2026-08-27')->create(['name' => 'Festivo local']);

    DeliveryZone::factory()->onlyOn(4)->create([
        'postal_code'  => '07840',
        'delivery_fee' => 4.00,
        'is_active'    => true,
    ]);

    $product = Product::factory()->create(['stock' => 5]);
    $this->post(route('cart.add', $product), ['quantity' => 1]);
    $this->post(route('checkout.store'), [
        'customer_name'      => 'Marta Serra',
        'customer_phone'     => '971123456',
        'delivery_address'   => 'Carrer del Sol 3',
        'postal_code'        => '07840',
        'acepta_condiciones' => '1',
    ]);

    expect(Order::latest('id')->first()->estimated_delivery_date->toDateString())
        ->toBe('2026-09-03');
});

it('carga los festivos de fecha fija con el seeder', function () {
    $this->seed(NonWorkingDaySeeder::class);

    $navidad = NonWorkingDay::firstWhere('name', 'Navidad');

    expect($navidad->recurs_annually)->toBeTrue();
    expect($navidad->starts_on->format('m-d'))->toBe('12-25');
    expect(NonWorkingDay::firstWhere('name', 'Sant Ciriac, patrón de Eivissa'))->not->toBeNull();

    // Volver a ejecutarlo no duplica nada.
    $total = NonWorkingDay::count();
    $this->seed(NonWorkingDaySeeder::class);

    expect(NonWorkingDay::count())->toBe($total);
});

it('publica los próximos días sin reparto en la página de reparto', function () {
    NonWorkingDay::factory()->on('2026-08-27')->create(['name' => 'Festivo local']);
    NonWorkingDay::factory()->on('2020-12-25')->recurring()->create(['name' => 'Navidad']);
    NonWorkingDay::factory()->on('2026-01-05')->create(['name' => 'Cierre ya pasado']);

    DeliveryZone::factory()->create(['is_active' => true]);

    $this->get(route('delivery'))
        ->assertOk()
        ->assertSee('as sin reparto', false)
        ->assertSee('Festivo local')
        ->assertSee('Navidad')
        ->assertDontSee('Cierre ya pasado');
});
