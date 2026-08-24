<?php

declare(strict_types=1);

use App\Models\DeliveryZone;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\Delivery\DeliveryZoneResolver;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/** Lunes 24 de agosto de 2026, para que las fechas del test no se muevan. */
function unLunes(): Carbon
{
    return Carbon::parse('2026-08-24 10:00:00');
}

beforeEach(function () {
    Mail::fake();
    // Comprar exige cuenta con el correo verificado.
    test()->actingAs(User::factory()->create(['email_verified_at' => now()]));
    Carbon::setTestNow(unLunes());
});

afterEach(function () {
    Carbon::setTestNow();
});

describe('días configurados en la zona', function () {
    it('reparte cualquier día de apertura si no se marca ninguno', function () {
        $zona = DeliveryZone::factory()->create(['delivery_days' => null]);

        expect($zona->deliversAnyOpenDay())->toBeTrue();
        // config/tienda.php abre de lunes a sábado; el domingo está cerrado.
        expect($zona->deliveryDays())->toBe([1, 2, 3, 4, 5, 6]);
        expect($zona->deliveryDaysLabel())->toBe('de lunes a sábado');
    });

    it('respeta el día fijo de la zona', function () {
        $zona = DeliveryZone::factory()->onlyOn(4)->create();

        expect($zona->deliversAnyOpenDay())->toBeFalse();
        expect($zona->deliveryDays())->toBe([4]);
        expect($zona->deliveryDaysLabel())->toBe('jueves');
    });

    it('enumera varios días sueltos', function () {
        expect(DeliveryZone::factory()->onlyOn([1, 4])->create()->deliveryDaysLabel())
            ->toBe('lunes y jueves');

        expect(DeliveryZone::factory()->onlyOn([1, 3, 5])->create()->deliveryDaysLabel())
            ->toBe('lunes, miércoles y viernes');
    });

    it('resume como rango los días consecutivos', function () {
        expect(DeliveryZone::factory()->onlyOn([1, 2, 3, 4, 5])->create()->deliveryDaysLabel())
            ->toBe('de lunes a viernes');

        expect(DeliveryZone::factory()->onlyOn([1, 2, 3, 4, 5, 6, 7])->create()->deliveryDaysLabel())
            ->toBe('todos los días');
    });

    it('descarta los días que no son válidos', function () {
        $zona = DeliveryZone::factory()->create(['delivery_days' => [0, 4, 9, 'jueves']]);

        expect($zona->configuredDays())->toBe([4]);
    });
});

describe('próximo día de reparto', function () {
    it('cuenta desde el día siguiente, nunca el mismo día', function () {
        // Hoy es lunes y la zona reparte los lunes: toca el lunes que viene.
        $zona = DeliveryZone::factory()->onlyOn(1)->create();

        expect($zona->nextDeliveryDate()->toDateString())->toBe('2026-08-31');
    });

    it('encuentra el próximo día fijo de la semana', function () {
        // Lunes 24 → el jueves de esta misma semana.
        $zona = DeliveryZone::factory()->onlyOn(4)->create();

        expect($zona->nextDeliveryDate()->toDateString())->toBe('2026-08-27');
        expect($zona->nextDeliveryDate()->dayOfWeekIso)->toBe(4);
    });

    it('salta al día siguiente en las zonas sin día fijo', function () {
        $zona = DeliveryZone::factory()->create(['delivery_days' => null]);

        expect($zona->nextDeliveryDate()->toDateString())->toBe('2026-08-25');
    });

    it('salta el domingo cuando la librería está cerrada', function () {
        // Sábado: el día siguiente es domingo, que no es día de apertura.
        Carbon::setTestNow(Carbon::parse('2026-08-29 10:00:00'));

        $zona = DeliveryZone::factory()->create(['delivery_days' => null]);

        expect($zona->nextDeliveryDate()->toDateString())->toBe('2026-08-31');
    });

    it('usa la zona que se le aplica al código postal', function () {
        DeliveryZone::factory()->onlyOn(4)->create([
            'postal_code'  => '07840',
            'delivery_fee' => 4.00,
            'is_active'    => true,
        ]);

        $resolver = app(DeliveryZoneResolver::class);

        expect($resolver->nextDeliveryDate('07840')->toDateString())->toBe('2026-08-27');
        expect($resolver->deliveryDaysLabel('07840'))->toBe('jueves');
        expect($resolver->nextDeliveryDate('28001'))->toBeNull();
    });
});

describe('la fecha prevista viaja con el pedido', function () {
    it('guarda en el pedido el día que le toca a la zona', function () {
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

        $order = Order::latest('id')->first();

        expect($order->estimated_delivery_date->toDateString())->toBe('2026-08-27');
        expect($order->formattedEstimatedDelivery())->toBe('jueves, 27 de agosto');
    });

    it('anuncia la misma fecha en el checkout y en la confirmación', function () {
        DeliveryZone::factory()->onlyOn(4)->create([
            'postal_code'  => '07840',
            'delivery_fee' => 4.00,
            'is_active'    => true,
        ]);

        $this->getJson(route('delivery.check', ['codigo_postal' => '07840']))
            ->assertOk()
            ->assertJson([
                'cubierto'              => true,
                'dias_reparto'          => 'jueves',
                'reparto_diario'        => false,
                'proxima_entrega'       => '2026-08-27',
                'proxima_entrega_texto' => 'jueves, 27 de agosto',
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

        $order = Order::latest('id')->first();

        $this->get(route('checkout.confirmation', $order->order_number))
            ->assertOk()
            ->assertSee('Fecha prevista', false)
            ->assertSee('jueves, 27 de agosto');
    });

    it('informa de la fecha en el correo de confirmación', function () {
        DeliveryZone::factory()->onlyOn(4)->create([
            'postal_code'  => '07840',
            'delivery_fee' => 4.00,
            'is_active'    => true,
        ]);

        $product = Product::factory()->create(['stock' => 5]);
        $this->post(route('cart.add', $product), ['quantity' => 1]);
        $this->post(route('checkout.store'), [
            'customer_name'      => 'Marta Serra',
            'customer_email'     => 'marta@example.com',
            'customer_phone'     => '971123456',
            'delivery_address'   => 'Carrer del Sol 3',
            'postal_code'        => '07840',
            'acepta_condiciones' => '1',
        ]);

        $log = NotificationLog::where('event', NotificationLog::EVENT_ORDER_CREATED)->first();

        expect($log->body)->toContain('te lo llevamos el jueves, 27 de agosto');
    });
});

describe('la tienda publica los días de reparto', function () {
    it('los muestra en la página de zonas de reparto', function () {
        DeliveryZone::factory()->onlyOn(4)->create([
            'postal_code'  => '07840',
            'neighborhood' => 'Santa Eulària',
            'city'         => 'Santa Eulària des Riu',
            'is_active'    => true,
        ]);

        $this->get(route('delivery'))
            ->assertOk()
            ->assertSee('Días de reparto', false)
            ->assertSee('jueves');
    });

    it('explica en las condiciones de venta cómo se calcula la fecha', function () {
        $this->get(route('terms'))
            ->assertOk()
            ->assertSee('fecha prevista de entrega', false);
    });
});
