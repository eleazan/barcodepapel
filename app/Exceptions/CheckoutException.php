<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;

/**
 * Error de negocio al confirmar un pedido: carrito vacío, sin stock o
 * código postal fuera de zona de reparto.
 */
class CheckoutException extends RuntimeException
{
    public static function carritoVacio(): self
    {
        return new self('Tu carrito está vacío.');
    }

    public static function fueraDeZona(string $postalCode): self
    {
        return new self("No hacemos reparto en el código postal {$postalCode}.");
    }

    public static function sinStock(string $productName, int $disponible): self
    {
        return new self($disponible > 0
            ? "Solo quedan {$disponible} unidad(es) de «{$productName}». Ajusta la cantidad."
            : "«{$productName}» se ha agotado mientras finalizabas el pedido.");
    }
}
