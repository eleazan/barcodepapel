<?php

declare(strict_types=1);

namespace App\Services\Cart;

use Illuminate\Support\Collection;

/**
 * Fotografía del carrito tal y como se le mostró al cliente en el checkout.
 *
 * Se guarda en sesión al pintar el formulario y se compara al confirmar. Si
 * algo cambió entre medias —una línea retirada del catálogo, una cantidad
 * recortada por falta de stock o un precio actualizado desde el ERP— el pedido
 * no se crea a ciegas: se devuelve al cliente al checkout con el detalle del
 * cambio para que vuelva a aceptarlo.
 */
final readonly class CartSnapshot
{
    /**
     * @param  array<int, array{nombre: string, cantidad: int, precio: float}>  $lines
     */
    public function __construct(
        public array $lines,
    ) {}

    /**
     * @param  Collection<int, CartItem>  $items
     */
    public static function fromItems(Collection $items): self
    {
        return new self($items
            ->mapWithKeys(fn (CartItem $item) => [
                $item->product->id => [
                    'nombre'   => $item->product->name,
                    'cantidad' => $item->quantity,
                    'precio'   => $item->unitPrice(),
                ],
            ])
            ->all());
    }

    /**
     * Reconstruye una fotografía guardada en sesión. Devuelve null si el dato
     * no tiene la forma esperada: sesión de una versión anterior o manipulada.
     */
    public static function fromArray(mixed $raw): ?self
    {
        if (! is_array($raw)) {
            return null;
        }

        $lines = [];

        foreach ($raw as $productId => $line) {
            if (! is_int($productId) && ! ctype_digit((string) $productId)) {
                return null;
            }

            if (! is_array($line)
                || ! isset($line['nombre'], $line['cantidad'], $line['precio'])
                || ! is_string($line['nombre'])
                || ! is_numeric($line['cantidad'])
                || ! is_numeric($line['precio'])) {
                return null;
            }

            $lines[(int) $productId] = [
                'nombre'   => $line['nombre'],
                'cantidad' => (int) $line['cantidad'],
                'precio'   => (float) $line['precio'],
            ];
        }

        return new self($lines);
    }

    /**
     * @return array<int, array{nombre: string, cantidad: int, precio: float}>
     */
    public function toArray(): array
    {
        return $this->lines;
    }

    /**
     * Cambios legibles entre lo que el cliente aceptó y lo que hay ahora.
     * Vacío si va a pagar exactamente lo que vio.
     *
     * @return list<string>
     */
    public function differences(self $current): array
    {
        $avisos = [];

        foreach ($this->lines as $productId => $vista) {
            $ahora = $current->lines[$productId] ?? null;

            if ($ahora === null) {
                $avisos[] = "«{$vista['nombre']}» ya no está disponible y se ha retirado del pedido.";

                continue;
            }

            if ($ahora['cantidad'] !== $vista['cantidad']) {
                $avisos[] = "Hemos ajustado «{$vista['nombre']}» de {$vista['cantidad']} a {$ahora['cantidad']} unidad(es): es todo lo que nos queda.";
            }

            if (self::precioCambiado($vista['precio'], $ahora['precio'])) {
                $avisos[] = sprintf(
                    'El precio de «%s» ha cambiado de %s a %s.',
                    $vista['nombre'],
                    self::euros($vista['precio']),
                    self::euros($ahora['precio']),
                );
            }
        }

        // El cliente añadió algo desde otra pestaña mientras rellenaba el formulario.
        foreach ($current->lines as $productId => $ahora) {
            if (! isset($this->lines[$productId])) {
                $avisos[] = "«{$ahora['nombre']}» se ha añadido a tu pedido.";
            }
        }

        return array_values($avisos);
    }

    /**
     * Comparación tolerante al redondeo: solo cuenta a partir del céntimo.
     */
    private static function precioCambiado(float $antes, float $ahora): bool
    {
        return abs($antes - $ahora) >= 0.005;
    }

    private static function euros(float $importe): string
    {
        return number_format($importe, 2, ',', '.').' €';
    }
}
