<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DeliveryZone>
 */
class DeliveryZoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'postal_code' => fake()->numerify('#####'),
            'neighborhood' => fake()->streetName(),
            'city' => fake()->city(),
            'delivery_fee' => fake()->randomElement([0, 2, 3, 5, 6, 7]),
            'is_active' => true,
        ];
    }
}
