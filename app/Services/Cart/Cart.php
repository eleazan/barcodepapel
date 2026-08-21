<?php

declare(strict_types=1);

namespace App\Services\Cart;

use App\Models\Product;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

/**
 * Carrito de la compra persistido en sesión.
 *
 * La sesión guarda únicamente [product_id => cantidad]. Los precios se leen
 * siempre de la base de datos al construir las líneas, para que un cambio de
 * tarifa se refleje antes de confirmar el pedido.
 */
class Cart
{
    private const SESSION_KEY = 'carrito';

    /** Cantidad máxima por línea, como salvaguarda frente a pedidos absurdos. */
    public const MAX_QUANTITY = 99;

    /** @var Collection<int, CartItem>|null */
    private ?Collection $items = null;

    /** @var list<string> Avisos generados al reconciliar el carrito con el catálogo. */
    private array $adjustments = [];

    public function __construct(
        private readonly Session $session,
    ) {}

    /**
     * Añade unidades de un producto. Devuelve la cantidad final de la línea.
     */
    public function add(Product $product, int $quantity = 1): int
    {
        $raw     = $this->raw();
        $current = $raw[$product->id] ?? 0;

        $raw[$product->id] = $this->clamp($current + $quantity, $product);

        $this->persist($raw);

        return $raw[$product->id];
    }

    /**
     * Fija la cantidad de una línea. Con cantidad <= 0 se elimina.
     */
    public function update(Product $product, int $quantity): void
    {
        $raw = $this->raw();

        if ($quantity <= 0) {
            unset($raw[$product->id]);
        } else {
            $raw[$product->id] = $this->clamp($quantity, $product);
        }

        $this->persist($raw);
    }

    public function remove(int $productId): void
    {
        $raw = $this->raw();
        unset($raw[$productId]);

        $this->persist($raw);
    }

    public function clear(): void
    {
        $this->session->forget(self::SESSION_KEY);
        $this->items       = null;
        $this->adjustments = [];
    }

    /**
     * Líneas del carrito, ya reconciliadas contra el catálogo.
     *
     * @return Collection<int, CartItem>
     */
    public function items(): Collection
    {
        if ($this->items !== null) {
            return $this->items;
        }

        $raw = $this->raw();

        if ($raw === []) {
            return $this->items = collect();
        }

        $products = Product::query()
            ->with('category')
            ->whereIn('id', array_keys($raw))
            ->get()
            ->keyBy('id');

        $reconciled = [];
        $items      = collect();

        foreach ($raw as $productId => $quantity) {
            $product = $products->get($productId);

            // El producto ya no existe o dejó de estar publicado.
            if ($product === null || ! $product->is_active) {
                $this->adjustments[] = $product === null
                    ? 'Un producto que tenías en el carrito ya no está disponible y se ha retirado.'
                    : "«{$product->name}» ya no está disponible y se ha retirado del carrito.";

                continue;
            }

            // Se agotó mientras el carrito estaba en sesión.
            if (! $product->hasStock()) {
                $this->adjustments[] = "«{$product->name}» se ha agotado y se ha retirado del carrito.";

                continue;
            }

            $adjusted = $this->clamp($quantity, $product);

            if ($adjusted !== $quantity) {
                $this->adjustments[] = "Hemos ajustado «{$product->name}» a {$adjusted} unidad(es), el máximo disponible.";
            }

            $reconciled[$productId] = $adjusted;
            $items->push(new CartItem($product, $adjusted));
        }

        // Solo escribimos en sesión si la reconciliación cambió algo.
        if ($reconciled !== $raw) {
            $this->session->put(self::SESSION_KEY, $reconciled);
        }

        return $this->items = $items;
    }

    /**
     * Avisos acumulados al reconciliar. Requiere haber llamado a items().
     *
     * @return list<string>
     */
    public function adjustments(): array
    {
        return $this->adjustments;
    }

    public function isEmpty(): bool
    {
        return $this->items()->isEmpty();
    }

    /**
     * Número de líneas distintas.
     */
    public function lineCount(): int
    {
        return $this->items()->count();
    }

    /**
     * Número total de unidades, para el contador de la cabecera.
     */
    public function count(): int
    {
        return (int) $this->items()->sum(fn (CartItem $item) => $item->quantity);
    }

    public function subtotal(): float
    {
        return round((float) $this->items()->sum(fn (CartItem $item) => $item->total()), 2);
    }

    public function formattedSubtotal(): string
    {
        return number_format($this->subtotal(), 2, ',', '.').' €';
    }

    public function has(int $productId): bool
    {
        return isset($this->raw()[$productId]);
    }

    public function quantityOf(int $productId): int
    {
        return $this->raw()[$productId] ?? 0;
    }

    /**
     * Cantidad válida para un producto: entre 1 y el mínimo de stock y tope duro.
     */
    private function clamp(int $quantity, Product $product): int
    {
        $max = min((int) $product->stock, self::MAX_QUANTITY);

        return max(1, min($quantity, max(1, $max)));
    }

    /**
     * @return array<int, int>
     */
    private function raw(): array
    {
        /** @var array<int, int> $raw */
        $raw = $this->session->get(self::SESSION_KEY, []);

        return is_array($raw) ? $raw : [];
    }

    /**
     * @param  array<int, int>  $raw
     */
    private function persist(array $raw): void
    {
        $this->session->put(self::SESSION_KEY, $raw);
        $this->items       = null;
        $this->adjustments = [];
    }
}
