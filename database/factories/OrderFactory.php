<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        $subtotal    = fake()->randomFloat(2, 5, 150);
        $deliveryFee = fake()->randomElement([0, 2, 3, 5, 7]);

        return [
            'order_number'     => 'BP-'.now()->format('Ymd').'-'.strtoupper(fake()->unique()->bothify('?????')),
            'customer_name'    => fake()->name(),
            'customer_email'   => fake()->optional(0.7)->safeEmail(),
            'customer_phone'   => fake()->numerify('55########'),
            'delivery_address' => fake()->streetAddress().', Col. '.fake()->streetName(),
            'postal_code'      => fake()->numerify('#####'),
            'status'           => fake()->randomElement(array_keys(Order::STATUSES)),
            'subtotal'         => $subtotal,
            'delivery_fee'     => $deliveryFee,
            'total'            => $subtotal + $deliveryFee,
            'notes'            => fake()->optional(0.3)->sentence(),
        ];
    }

    public function pendiente(): static
    {
        return $this->state(fn () => ['status' => Order::STATUS_PENDIENTE]);
    }

    public function entregado(): static
    {
        return $this->state(fn () => ['status' => Order::STATUS_ENTREGADO]);
    }
}
