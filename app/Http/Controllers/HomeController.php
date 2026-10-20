<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Post;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class HomeController extends Controller
{
    private const PRODUCTOS_POR_FILA = 6;

    public function __invoke(): View
    {
        $categories = Category::active()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        return view('store.home', [
            'categories'         => $categories,
            'novedades'          => $this->novedades(),
            'masVendidos'        => $this->masVendidos(),
            'posts'              => Post::published()->latest('published_at')->limit(3)->get(),
            'productsCount'      => Product::active()->count(),
            'deliveryZonesCount' => DeliveryZone::where('is_active', true)->count(),
        ]);
    }

    /** Lo último que ha entrado, con stock y preferiblemente con portada. */
    private function novedades()
    {
        return $this->escaparate()
            ->orderByRaw('image IS NULL')
            ->latest()
            ->limit(self::PRODUCTOS_POR_FILA)
            ->get();
    }

    /** Unidades vendidas en pedidos reales; vacío mientras no haya ventas. */
    private function masVendidos()
    {
        return $this->escaparate()
            ->whereHas('orderItems')
            ->withSum('orderItems as unidades_vendidas', 'quantity')
            ->orderByDesc('unidades_vendidas')
            ->limit(self::PRODUCTOS_POR_FILA)
            ->get();
    }

    private function escaparate(): Builder
    {
        return Product::active()
            ->with('category')
            ->where('stock', '>', 0);
    }
}
