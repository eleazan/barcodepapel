<?php

declare(strict_types=1);

namespace App\Rules;

use App\Services\Delivery\DeliveryZoneResolver;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Solo aceptamos pedidos a códigos postales donde la librería reparte.
 * No hay paquetería externa, así que fuera de zona no hay venta posible.
 */
class CodigoPostalConReparto implements ValidationRule
{
    public function __construct(
        private readonly DeliveryZoneResolver $zones,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value) || ! $this->zones->covers($value)) {
            $fail('No hacemos reparto en ese código postal. Consulta las zonas que cubrimos.');
        }
    }
}
