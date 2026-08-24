<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Día en que la librería no reparte: un festivo o un cierre por vacaciones.
 *
 * Un día suelto se guarda con `starts_on` y `ends_on` iguales. Los festivos de
 * fecha fija (Navidad, Año Nuevo, Sant Ciriac…) se marcan como recurrentes y
 * valen para todos los años sin volver a darlos de alta.
 */
class NonWorkingDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'starts_on',
        'ends_on',
        'recurs_annually',
    ];

    protected function casts(): array
    {
        return [
            'starts_on'       => 'date',
            'ends_on'         => 'date',
            'recurs_annually' => 'boolean',
        ];
    }

    /**
     * Cierres que aún pueden afectar a un reparto: los recurrentes siempre, y
     * los de fecha concreta mientras no hayan terminado.
     */
    public function scopeVigentes(Builder $query, ?CarbonInterface $desde = null): Builder
    {
        $desde = $desde ?? now();

        return $query->where(function (Builder $q) use ($desde) {
            $q->where('recurs_annually', true)
                ->orWhere('ends_on', '>=', $desde->format('Y-m-d'));
        });
    }

    /**
     * ¿Cae esta fecha dentro del cierre?
     */
    public function covers(CarbonInterface $date): bool
    {
        if (! $this->recurs_annually) {
            return $date->betweenIncluded($this->starts_on, $this->ends_on);
        }

        // En los recurrentes solo cuenta el día del año, no el año en sí.
        return $this->dayOfYearKey($date) >= $this->dayOfYearKey($this->starts_on)
            && $this->dayOfYearKey($date) <= $this->dayOfYearKey($this->ends_on);
    }

    public function isSingleDay(): bool
    {
        return $this->starts_on->isSameDay($this->ends_on);
    }

    /**
     * Fechas del cierre en un año concreto: «25 de diciembre» o
     * «del 1 al 15 de agosto».
     */
    public function formattedRange(): string
    {
        if ($this->isSingleDay()) {
            return $this->starts_on->translatedFormat($this->recurs_annually ? 'j \d\e F' : 'j \d\e F \d\e Y');
        }

        $formato = $this->recurs_annually ? 'j \d\e F' : 'j \d\e F \d\e Y';

        return 'del '.$this->starts_on->translatedFormat('j \d\e F').' al '.$this->ends_on->translatedFormat($formato);
    }

    /**
     * Mes y día como número comparable (MMDD), para los cierres recurrentes.
     */
    private function dayOfYearKey(CarbonInterface $date): int
    {
        return (int) $date->format('md');
    }
}
