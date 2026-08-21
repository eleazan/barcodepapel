<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\Product;

/**
 * Línea del carrito: producto + cantidad, con los importes ya calculados.
 */
readonly class CartItem
{
    public function __construct(
        public Product $product,
        public int $quantity,
    ) {}

    public function unitPrice(): float
    {
        return (float) $this->product->price;
    }

    public function total(): float
    {
        return round($this->unitPrice() * $this->quantity, 2);
    }

    public function formattedUnitPrice(): string
    {
        return number_format($this->unitPrice(), 2, ',', '.').' €';
    }

    public function formattedTotal(): string
    {
        return number_format($this->total(), 2, ',', '.').' €';
    }

    /**
     * Máximo que el cliente puede pedir de este producto: lo que hay en
     * stock, sin pasar del tope por línea que aplica el carrito.
     */
    public function maxQuantity(): int
    {
        return max(1, min((int) $this->product->stock, Cart::MAX_QUANTITY));
    }
}
