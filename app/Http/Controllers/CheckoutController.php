<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Http\Requests\Store\CheckoutRequest;
use App\Models\Order;
use App\Services\Cart\Cart;
use App\Services\Cart\CartSnapshot;
use App\Services\Checkout\PlaceOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * El carrito se recibe por método, nunca por constructor: Laravel guarda la
 * instancia del controlador dentro del objeto Route, así que una dependencia
 * `scoped` inyectada en el constructor sobrevive a la petición que la creó.
 */
class CheckoutController extends Controller
{
    /** Clave de sesión con los pedidos que este visitante acaba de realizar. */
    private const SESSION_ORDERS = 'pedidos_realizados';

    /** Clave de sesión con el resumen que el cliente vio en el formulario. */
    private const SESSION_SNAPSHOT = 'checkout_resumen';

    public function show(Request $request, Cart $cart): View|RedirectResponse
    {
        $items = $cart->items();

        // Avisos de la reconciliación contra el catálogo: líneas retiradas,
        // cantidades recortadas. Se pintan sobre el propio formulario.
        $avisos = array_values(array_unique($cart->adjustments()));

        if ($items->isEmpty()) {
            $request->session()->forget(self::SESSION_SNAPSHOT);

            return redirect()
                ->route('cart.index')
                ->with('error', $avisos === []
                    ? 'Tu carrito está vacío. Añade algún producto antes de finalizar el pedido.'
                    : implode(' ', $avisos).' Tu carrito se ha quedado vacío.');
        }

        // Fotografía de lo que el cliente está viendo, para contrastarla al confirmar.
        $request->session()->put(
            self::SESSION_SNAPSHOT,
            CartSnapshot::fromItems($items)->toArray(),
        );

        return view('store.checkout.show', [
            'items'    => $items,
            'subtotal' => $cart->subtotal(),
            'usuario'  => Auth::user(),
            'avisos'   => $avisos,
        ]);
    }

    public function store(CheckoutRequest $request, Cart $cart, PlaceOrderService $placeOrder): RedirectResponse
    {
        // El carrito es la misma instancia durante toda la petición, así que lo
        // que se compara aquí es exactamente lo que se va a facturar abajo.
        $cambios = $this->cambiosDesdeElResumen($request, $cart);

        if ($cambios !== []) {
            return back()
                ->withInput()
                ->with('cart_changes', $cambios);
        }

        try {
            $order = $placeOrder->place($request->orderData(), Auth::id());
        } catch (CheckoutException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }

        $request->session()->forget(self::SESSION_SNAPSHOT);

        // Referencia por sesión, además de por `user_id`: así el cliente ve la
        // confirmación aunque cierre sesión justo después de comprar.
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
     * Diferencias entre el resumen que el cliente aceptó y el estado actual del
     * carrito. Sin resumen en sesión —POST directo, sesión caducada— no hay
     * nada que contrastar y el pedido sigue su curso.
     *
     * @return list<string>
     */
    private function cambiosDesdeElResumen(Request $request, Cart $cart): array
    {
        $aceptado = CartSnapshot::fromArray($request->session()->get(self::SESSION_SNAPSHOT));

        if ($aceptado === null) {
            return [];
        }

        return $aceptado->differences(CartSnapshot::fromItems($cart->items()));
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
