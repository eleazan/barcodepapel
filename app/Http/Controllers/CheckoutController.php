<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Http\Requests\Store\CheckoutRequest;
use App\Models\Order;
use App\Services\Cart\Cart;
use App\Services\Checkout\PlaceOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /** Clave de sesión con los pedidos que este visitante acaba de realizar. */
    private const SESSION_ORDERS = 'pedidos_realizados';

    public function __construct(
        private readonly Cart $cart,
    ) {}

    public function show(): View|RedirectResponse
    {
        $items = $this->cart->items();

        if ($items->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Tu carrito está vacío. Añade algún producto antes de finalizar el pedido.');
        }

        $user = Auth::user();

        return view('store.checkout.show', [
            'items'    => $items,
            'subtotal' => $this->cart->subtotal(),
            'usuario'  => $user,
        ]);
    }

    public function store(CheckoutRequest $request, PlaceOrderService $placeOrder): RedirectResponse
    {
        try {
            $order = $placeOrder->place($request->orderData(), Auth::id());
        } catch (CheckoutException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        // Permite ver la confirmación a quien acaba de comprar, sin necesidad de cuenta.
        $request->session()->push(self::SESSION_ORDERS, $order->id);

        return redirect()->route('checkout.confirmation', $order->order_number);
    }

    public function confirmation(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with('items.product')
            ->firstOrFail();

        abort_unless($this->puedeVer($order), 403);

        return view('store.checkout.confirmation', compact('order'));
    }

    /**
     * Solo el comprador (por sesión), el usuario dueño del pedido o un admin.
     */
    private function puedeVer(Order $order): bool
    {
        $propios = (array) session(self::SESSION_ORDERS, []);

        if (in_array($order->id, $propios, true)) {
            return true;
        }

        $user = Auth::user();

        if ($user === null) {
            return false;
        }

        return $user->is_admin || ($order->user_id !== null && $order->user_id === $user->id);
    }
}
