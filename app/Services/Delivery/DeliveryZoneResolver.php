<?php

declare(strict_types=1);

namespace App\Services\Delivery;

use App\Models\DeliveryZone;
use Illuminate\Support\Collection;

/**
 * Resuelve un código postal contra las zonas de reparto activas.
 *
 * No hay paquetería externa: si el código postal no está dado de alta como
 * zona activa, no se puede vender a esa dirección.
 */
class DeliveryZoneResolver
{
    /**
     * Zona aplicable a un código postal. Si hay varios barrios dados de alta
     * para el mismo CP, se aplica la tarifa más baja.
     */
    public function resolve(?string $postalCode): ?DeliveryZone
    {
        $postalCode = $this->normalize($postalCode);

        if ($postalCode === null) {
            return null;
        }

        return DeliveryZone::active()
            ->where('postal_code', $postalCode)
            ->orderBy('delivery_fee')
            ->first();
    }

    public function covers(?string $postalCode): bool
    {
        return $this->resolve($postalCode) !== null;
    }

    /**
     * Gastos de envío para un código postal. 0 si no hay zona (el checkout
     * bloquea antes de llegar a cobrar).
     */
    public function fee(?string $postalCode): float
    {
        return (float) ($this->resolve($postalCode)?->delivery_fee ?? 0.0);
    }

    /**
     * Barrios dados de alta para un código postal, para el desplegable del checkout.
     *
     * @return Collection<int, DeliveryZone>
     */
    public function zonesFor(?string $postalCode): Collection
    {
        $postalCode = $this->normalize($postalCode);

        if ($postalCode === null) {
            return collect();
        }

        return DeliveryZone::active()
            ->where('postal_code', $postalCode)
            ->orderBy('neighborhood')
            ->get();
    }

    /**
     * Todos los códigos postales cubiertos, para pintar ayudas en el formulario.
     *
     * @return list<string>
     */
    public function coveredPostalCodes(): array
    {
        return DeliveryZone::active()
            ->orderBy('postal_code')
            ->distinct()
            ->pluck('postal_code')
            ->all();
    }

    private function normalize(?string $postalCode): ?string
    {
        $postalCode = trim((string) $postalCode);

        return preg_match('/^\d{5}$/', $postalCode) === 1 ? $postalCode : null;
    }
}
