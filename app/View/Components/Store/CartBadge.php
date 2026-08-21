<?php

declare(strict_types=1);

namespace App\View\Components\Store;

use App\Services\Cart\Cart;
use Illuminate\View\Component;
use Illuminate\View\View;

/**
 * Icono de carrito con el número de unidades, para la cabecera de la tienda.
 */
class CartBadge extends Component
{
    public function __construct(
        private readonly Cart $cart,
        public bool $mobile = false,
    ) {}

    public function render(): View
    {
        return view('components.store.cart-badge', [
            'unidades' => $this->cart->count(),
        ]);
    }
}
