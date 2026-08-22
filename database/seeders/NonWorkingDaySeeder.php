<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\NonWorkingDay;
use Illuminate\Database\Seeder;

/**
 * Festivos de fecha fija que aplican a la librería: los nacionales, el de las
 * Illes Balears y el patrón de Eivissa.
 *
 * Solo los que caen siempre el mismo día del año, así que se marcan como
 * recurrentes y no hay que volver a darlos de alta. Los festivos móviles
 * (Jueves y Viernes Santo, segunda fiesta de Pascua) y los cierres por
 * vacaciones se añaden a mano cada año desde el panel.
 *
 * Se ejecuta con: php artisan db:seed --class=NonWorkingDaySeeder
 */
class NonWorkingDaySeeder extends Seeder
{
    /**
     * @var list<array{name: string, date: string}>
     */
    private const FESTIVOS = [
        ['name' => 'Año Nuevo',                      'date' => '01-01'],
        ['name' => 'Reyes',                          'date' => '01-06'],
        ['name' => 'Dia de les Illes Balears',       'date' => '03-01'],
        ['name' => 'Fiesta del Trabajo',             'date' => '05-01'],
        ['name' => 'Sant Ciriac, patrón de Eivissa', 'date' => '08-08'],
        ['name' => 'Asunción de la Virgen',          'date' => '08-15'],
        ['name' => 'Fiesta Nacional de España',      'date' => '10-12'],
        ['name' => 'Todos los Santos',               'date' => '11-01'],
        ['name' => 'Día de la Constitución',         'date' => '12-06'],
        ['name' => 'Inmaculada Concepción',          'date' => '12-08'],
        ['name' => 'Navidad',                        'date' => '12-25'],
    ];

    public function run(): void
    {
        $anyo = now()->year;

        foreach (self::FESTIVOS as $festivo) {
            $fecha = "{$anyo}-{$festivo['date']}";

            NonWorkingDay::firstOrCreate(
                ['name' => $festivo['name']],
                [
                    'starts_on'       => $fecha,
                    'ends_on'         => $fecha,
                    'recurs_annually' => true,
                ],
            );
        }

        $this->command?->info(count(self::FESTIVOS).' festivos de fecha fija dados de alta.');
    }
}
