<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\DeliveryZone;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryZone>
 */
class DeliveryZoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'postal_code'   => fake()->numerify('#####'),
            'neighborhood'  => fake()->streetName(),
            'city'          => fake()->city(),
            'delivery_fee'  => fake()->randomElement([0, 2, 3, 5, 6, 7]),
            'delivery_days' => null,
            'is_active'     => true,
        ];
    }

    /**
     * Zona con día fijo de reparto: onlyOn(4) para los jueves.
     *
     * @param  int|list<int>  $days
     */
    public function onlyOn(int|array $days): static
    {
        return $this->state(['delivery_days' => (array) $days]);
    }
}
