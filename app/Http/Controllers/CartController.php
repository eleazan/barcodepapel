<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\DeliveryZone;
use App\Models\Product;
use App\Services\Cart\Cart;
use App\Services\Delivery\DeliveryCalendar;
use App\Services\Delivery\DeliveryZoneResolver;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * El carrito se recibe por método, nunca por constructor: Laravel guarda la
 * instancia del controlador dentro del objeto Route, así que una dependencia
 * `scoped` inyectada en el constructor sobrevive a la petición que la creó.
 */
class CartController extends Controller
{
    public function index(Cart $cart): View
    {
        $items = $cart->items();

        return view('store.cart.index', [
            'items'    => $items,
            'subtotal' => $cart->subtotal(),
            'avisos'   => array_values(array_unique($cart->adjustments())),
        ]);
    }

    public function add(Request $request, Cart $cart, Product $product): RedirectResponse|JsonResponse
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
                $cart,
                success: false,
                message: "«{$product->name}» está agotado ahora mismo.",
            );
        }

        $cantidadFinal = $cart->add($product, (int) ($validated['quantity'] ?? 1));

        $mensaje = $cantidadFinal < (int) ($validated['quantity'] ?? 1)
            ? "Hemos añadido {$cantidadFinal} unidad(es) de «{$product->name}», el máximo disponible."
            : "«{$product->name}» añadido al carrito.";

        return $this->respond($request, $cart, success: true, message: $mensaje);
    }

    public function update(Request $request, Cart $cart, Product $product): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:'.Cart::MAX_QUANTITY],
        ], [
            'quantity.required' => 'Indica una cantidad.',
            'quantity.integer'  => 'La cantidad debe ser un número entero.',
        ]);

        $quantity = (int) $validated['quantity'];

        $cart->update($product, $quantity);

        return $this->respond(
            $request,
            $cart,
            success: true,
            message: $quantity === 0
                ? "«{$product->name}» eliminado del carrito."
                : 'Carrito actualizado.',
        );
    }

    public function remove(Request $request, Cart $cart, Product $product): RedirectResponse|JsonResponse
    {
        $cart->remove($product->id);

        return $this->respond($request, $cart, success: true, message: "«{$product->name}» eliminado del carrito.");
    }

    public function clear(Request $request, Cart $cart): RedirectResponse|JsonResponse
    {
        $cart->clear();

        return $this->respond($request, $cart, success: true, message: 'Carrito vaciado.');
    }

    /**
     * Comprobación de cobertura para el widget de código postal.
     */
    public function checkPostalCode(Request $request, DeliveryZoneResolver $zones, DeliveryCalendar $calendar): JsonResponse
    {
        $request->validate([
            'codigo_postal' => ['required', 'digits:5'],
        ], [
            'codigo_postal.digits' => 'El código postal debe tener 5 dígitos.',
        ]);

        $zone = $zones->resolve($request->string('codigo_postal')->toString());

        $proxima = $zone?->nextDeliveryDate();

        return response()->json([
            'cubierto'                => $zone !== null,
            'zona'                    => $zone?->neighborhood,
            'ciudad'                  => $zone?->city,
            'gastos_envio'            => $zone !== null ? (float) $zone->delivery_fee : null,
            'gastos_envio_formateado' => $zone?->formattedFee(),
            'dias_reparto'            => $zone?->deliveryDaysLabel(),
            'reparto_diario'          => $zone?->deliversAnyOpenDay(),
            'proxima_entrega'         => $proxima?->toDateString(),
            'proxima_entrega_texto'   => $proxima?->translatedFormat('l, j \d\e F'),
            'motivo_retraso'          => $zone !== null ? $this->motivoDelRetraso($zone, $calendar) : null,
        ]);
    }

    /**
     * Explicación de por qué la entrega no cae en el primer día de reparto:
     * «El jueves 15 de agosto cerramos por Asunción».
     */
    private function motivoDelRetraso(DeliveryZone $zone, DeliveryCalendar $calendar): ?string
    {
        $saltados = $calendar->closuresDelaying($zone);

        if ($saltados === []) {
            return null;
        }

        $primero = $saltados[0];

        return sprintf(
            'El %s cerramos por %s',
            $primero['fecha']->translatedFormat('l j \d\e F'),
            $primero['cierre']->name,
        );
    }

    private function respond(Request $request, Cart $cart, bool $success, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'ok'       => $success,
                'mensaje'  => $message,
                'unidades' => $cart->count(),
                'subtotal' => $cart->formattedSubtotal(),
            ], $success ? 200 : 422);
        }

        return back()->with($success ? 'success' : 'error', $message);
    }
}
