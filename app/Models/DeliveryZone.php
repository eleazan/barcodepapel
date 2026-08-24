<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Delivery\DeliveryCalendar;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryZone extends Model
{
    use HasFactory;

    /** Días de la semana en ISO-8601, como los guarda `delivery_days`. */
    public const DAYS = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miércoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sábado',
        7 => 'domingo',
    ];

    /** Equivalencia entre los nombres de schema.org de config/tienda.php e ISO-8601. */
    private const SCHEMA_DAYS = [
        'Monday'    => 1,
        'Tuesday'   => 2,
        'Wednesday' => 3,
        'Thursday'  => 4,
        'Friday'    => 5,
        'Saturday'  => 6,
        'Sunday'    => 7,
    ];

    protected $fillable = [
        'postal_code',
        'neighborhood',
        'city',
        'delivery_fee',
        'delivery_days',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'delivery_fee'  => 'decimal:2',
            'delivery_days' => 'array',
            'is_active'     => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function formattedFee(): string
    {
        return number_format((float) $this->delivery_fee, 2, ',', '.').' €';
    }

    /**
     * Días marcados para esta zona, ya saneados. Vacío significa que no hay
     * restricción: se reparte cualquier día que la librería abra.
     *
     * @return list<int>
     */
    public function configuredDays(): array
    {
        $dias = array_filter(
            array_map('intval', (array) ($this->delivery_days ?? [])),
            fn (int $dia) => isset(self::DAYS[$dia]),
        );

        $dias = array_values(array_unique($dias));
        sort($dias);

        return $dias;
    }

    /**
     * Días en que efectivamente se reparte en esta zona.
     *
     * @return list<int>
     */
    public function deliveryDays(): array
    {
        $dias = $this->configuredDays();

        return $dias !== [] ? $dias : self::openDays();
    }

    /**
     * Zona sin día fijo: se reparte cualquier día de apertura.
     */
    public function deliversAnyOpenDay(): bool
    {
        return $this->configuredDays() === [];
    }

    /**
     * Etiqueta legible de los días de reparto: «jueves», «lunes y jueves»,
     * «de lunes a sábado», «todos los días».
     */
    public function deliveryDaysLabel(): string
    {
        $dias = $this->deliveryDays();

        if (count($dias) === count(self::DAYS)) {
            return 'todos los días';
        }

        // Un tramo corrido de tres días o más se lee mejor como rango.
        if (count($dias) >= 3 && $dias === range($dias[0], end($dias))) {
            return 'de '.self::DAYS[$dias[0]].' a '.self::DAYS[end($dias)];
        }

        $nombres = array_map(fn (int $dia) => self::DAYS[$dia], $dias);

        if (count($nombres) === 1) {
            return $nombres[0];
        }

        $ultimo = array_pop($nombres);

        return implode(', ', $nombres).' y '.$ultimo;
    }

    /**
     * Primer día de reparto disponible, saltando festivos y cierres.
     *
     * El cálculo vive en DeliveryCalendar, que es quien conoce los días en que
     * no se reparte.
     */
    public function nextDeliveryDate(?CarbonInterface $desde = null): ?CarbonImmutable
    {
        return app(DeliveryCalendar::class)->nextDeliveryDate($this, $desde);
    }

    /**
     * Días en que la librería abre, según config/tienda.php. Es el techo del
     * reparto: un tramo sin hora de apertura está cerrado y no se reparte.
     *
     * @return list<int>
     */
    public static function openDays(): array
    {
        $dias = [];

        foreach ((array) config('tienda.horario', []) as $tramo) {
            if (empty($tramo['abre'])) {
                continue;
            }

            foreach ((array) ($tramo['dias'] ?? []) as $nombre) {
                if (isset(self::SCHEMA_DAYS[$nombre])) {
                    $dias[] = self::SCHEMA_DAYS[$nombre];
                }
            }
        }

        $dias = array_values(array_unique($dias));
        sort($dias);

        // Sin horario configurado nos quedamos con los días laborables.
        return $dias !== [] ? $dias : [1, 2, 3, 4, 5];
    }
}
