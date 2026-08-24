<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NonWorkingDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NonWorkingDay>
 */
class NonWorkingDayFactory extends Factory
{
    public function definition(): array
    {
        $fecha = fake()->dateTimeBetween('now', '+3 months')->format('Y-m-d');

        return [
            'name'            => fake()->words(2, true),
            'starts_on'       => $fecha,
            'ends_on'         => $fecha,
            'recurs_annually' => false,
        ];
    }

    /**
     * Cierre de un solo día en una fecha concreta.
     */
    public function on(string $date): static
    {
        return $this->state(['starts_on' => $date, 'ends_on' => $date]);
    }

    /**
     * Cierre que ocupa varios días, como unas vacaciones.
     */
    public function between(string $from, string $to): static
    {
        return $this->state(['starts_on' => $from, 'ends_on' => $to]);
    }

    /**
     * Festivo de fecha fija, que vale para todos los años.
     */
    public function recurring(): static
    {
        return $this->state(['recurs_annually' => true]);
    }
}
