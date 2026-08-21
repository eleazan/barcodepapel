<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Cart\Cart;
use App\Services\Delivery\DeliveryZoneResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly Cart $cart,
    ) {}

    public function index(): View
    {
        $items = $this->cart->items();

        return view('store.cart.index', [
            'items'    => $items,
            'subtotal' => $this->cart->subtotal(),
            'avisos'   => array_values(array_unique($this->cart->adjustments())),
        ]);
    }

    public function add(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        abort_unless($product->is_active, 404);

        $validated = $request->validate([
            'quantity' => ['nullable', 'integer', 'min:1', 'max:'.Cart::MAX_QUANTITY],
        ], [
            'quantity.integer' => 'La cantidad debe ser un número entero.',
            'quantity.min'     => 'La cantidad mínima es 1.',
        ]);

        if (! $product->hasStock()) {
            return $this->respond(
                $request,
                success: false,
                message: "«{$product->name}» está agotado ahora mismo.",
            );
        }

        $cantidadFinal = $this->cart->add($product, (int) ($validated['quantity'] ?? 1));

        $mensaje = $cantidadFinal < (int) ($validated['quantity'] ?? 1)
            ? "Hemos añadido {$cantidadFinal} unidad(es) de «{$product->name}», el máximo disponible."
            : "«{$product->name}» añadido al carrito.";

        return $this->respond($request, success: true, message: $mensaje);
    }

    public function update(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:'.Cart::MAX_QUANTITY],
        ], [
            'quantity.required' => 'Indica una cantidad.',
            'quantity.integer'  => 'La cantidad debe ser un número entero.',
        ]);

        $quantity = (int) $validated['quantity'];

        $this->cart->update($product, $quantity);

        return $this->respond(
            $request,
            success: true,
            message: $quantity === 0
                ? "«{$product->name}» eliminado del carrito."
                : 'Carrito actualizado.',
        );
    }

    public function remove(Request $request, Product $product): RedirectResponse|JsonResponse
    {
        $this->cart->remove($product->id);

        return $this->respond($request, success: true, message: "«{$product->name}» eliminado del carrito.");
    }

    public function clear(Request $request): RedirectResponse|JsonResponse
    {
        $this->cart->clear();

        return $this->respond($request, success: true, message: 'Carrito vaciado.');
    }

    /**
     * Comprobación de cobertura para el widget de código postal.
     */
    public function checkPostalCode(Request $request, DeliveryZoneResolver $zones): JsonResponse
    {
        $request->validate([
            'codigo_postal' => ['required', 'digits:5'],
        ], [
            'codigo_postal.digits' => 'El código postal debe tener 5 dígitos.',
        ]);

        $zone = $zones->resolve($request->string('codigo_postal')->toString());

        return response()->json([
            'cubierto'                => $zone !== null,
            'zona'                    => $zone?->neighborhood,
            'ciudad'                  => $zone?->city,
            'gastos_envio'            => $zone !== null ? (float) $zone->delivery_fee : null,
            'gastos_envio_formateado' => $zone?->formattedFee(),
        ]);
    }

    private function respond(Request $request, bool $success, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok'       => $success,
                'mensaje'  => $message,
                'unidades' => $this->cart->count(),
                'subtotal' => $this->cart->formattedSubtotal(),
            ], $success ? 200 : 422);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
