<?php

declare(strict_types=1);

namespace App\Services\Delivery;

use App\Models\DeliveryZone;
use App\Models\NonWorkingDay;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Calendario de reparto: cruza los días de reparto de cada zona con los días
 * en que la librería no reparte (festivos y cierres por vacaciones).
 *
 * Los cierres se leen una sola vez por petición y se filtran en memoria: son
 * unas pocas decenas al año y los recurrentes no se pueden comparar en SQL de
 * forma portable entre MySQL y SQLite.
 */
class DeliveryCalendar
{
    /** Días que se miran hacia delante antes de rendirse. */
    private const SEARCH_WINDOW = 60;

    /** @var Collection<int, NonWorkingDay>|null */
    private ?Collection $closures = null;

    /**
     * Cierre que cae en esta fecha, si lo hay.
     */
    public function closureOn(CarbonInterface $date): ?NonWorkingDay
    {
        return $this->closures()->first(fn (NonWorkingDay $cierre) => $cierre->covers($date));
    }

    public function isClosed(CarbonInterface $date): bool
    {
        return $this->closureOn($date) !== null;
    }

    /**
     * Primer día de reparto de una zona.
     *
     * Se cuenta desde el día siguiente —el pedido necesita al menos una jornada
     * de preparación— y se saltan los días en que no se reparte.
     */
    public function nextDeliveryDate(DeliveryZone $zone, ?CarbonInterface $desde = null): ?CarbonImmutable
    {
        foreach ($this->candidates($zone, $desde) as $fecha) {
            if (! $this->isClosed($fecha)) {
                return $fecha;
            }
        }

        return null;
    }

    /**
     * Cierres que han retrasado la entrega: los que caían en un día de reparto
     * anterior a la fecha finalmente elegida. Sirve para explicarle al cliente
     * por qué su pedido no llega el día que esperaba.
     *
     * @return list<array{fecha: CarbonImmutable, cierre: NonWorkingDay}>
     */
    public function closuresDelaying(DeliveryZone $zone, ?CarbonInterface $desde = null): array
    {
        $saltados = [];

        foreach ($this->candidates($zone, $desde) as $fecha) {
            $cierre = $this->closureOn($fecha);

            if ($cierre === null) {
                return $saltados;
            }

            $saltados[] = ['fecha' => $fecha, 'cierre' => $cierre];
        }

        return $saltados;
    }

    /**
     * Próximos cierres, para publicarlos en la página de reparto.
     *
     * @return Collection<int, array{cierre: NonWorkingDay, fecha: CarbonImmutable}>
     */
    public function upcomingClosures(int $limit = 5, ?CarbonInterface $desde = null): Collection
    {
        $hoy        = CarbonImmutable::parse($desde ?? now())->startOfDay();
        $siguientes = [];

        foreach ($this->closures() as $cierre) {
            $fecha = $this->nextOccurrence($cierre, $hoy);

            if ($fecha !== null) {
                $siguientes[] = ['cierre' => $cierre, 'fecha' => $fecha];
            }
        }

        return collect($siguientes)
            ->sortBy(fn (array $item) => $item['fecha']->timestamp)
            ->take($limit)
            ->values();
    }

    /**
     * Días de reparto de la zona a partir de mañana, sin mirar los cierres.
     *
     * @return \Generator<int, CarbonImmutable>
     */
    private function candidates(DeliveryZone $zone, ?CarbonInterface $desde = null): \Generator
    {
        $dias = $zone->deliveryDays();

        if ($dias === []) {
            return;
        }

        $fecha = CarbonImmutable::parse($desde ?? now())->startOfDay()->addDay();

        for ($i = 0; $i < self::SEARCH_WINDOW; $i++) {
            if (in_array($fecha->dayOfWeekIso, $dias, true)) {
                yield $fecha;
            }

            $fecha = $fecha->addDay();
        }
    }

    /**
     * Primer día de este cierre que aún está por llegar.
     */
    private function nextOccurrence(NonWorkingDay $cierre, CarbonImmutable $desde): ?CarbonImmutable
    {
        if (! $cierre->recurs_annually) {
            $inicio = CarbonImmutable::parse($cierre->starts_on)->startOfDay();
            $fin    = CarbonImmutable::parse($cierre->ends_on)->startOfDay();

            if ($fin->lt($desde)) {
                return null;
            }

            return $inicio->gte($desde) ? $inicio : $desde;
        }

        // Recurrente: este año si aún no ha pasado, y si no el año que viene.
        foreach ([$desde->year, $desde->year + 1] as $anyo) {
            $inicio = CarbonImmutable::parse($cierre->starts_on)->setYear($anyo)->startOfDay();
            $fin    = CarbonImmutable::parse($cierre->ends_on)->setYear($anyo)->startOfDay();

            if ($fin->gte($desde)) {
                return $inicio->gte($desde) ? $inicio : $desde;
            }
        }

        return null;
    }

    /**
     * @return Collection<int, NonWorkingDay>
     */
    private function closures(): Collection
    {
        return $this->closures ??= NonWorkingDay::query()
            ->vigentes()
            ->orderBy('starts_on')
            ->get();
    }
}
