<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StoreController extends Controller
{
    public function catalog(Request $request): View
    {
        $categories = Category::active()->orderBy('sort_order')->get();

        $products = Product::active()
            ->with('category')
            ->when($request->categoria, fn ($q, $slug) => $q->whereHas('category', fn ($q) => $q->where('slug', $slug)))
            ->when($request->buscar, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('description', 'like', "%{$s}%");
            }))
            ->when($request->orden === 'precio_asc', fn ($q) => $q->orderBy('price'))
            ->when($request->orden === 'precio_desc', fn ($q) => $q->orderByDesc('price'))
            ->when($request->orden === 'nombre', fn ($q) => $q->orderBy('name'))
            ->when(! $request->orden, fn ($q) => $q->latest())
            ->paginate(12)
            ->withQueryString();

        $activeCategory = $request->categoria
            ? $categories->firstWhere('slug', $request->categoria)
            : null;

        return view('store.catalog', compact('categories', 'products', 'activeCategory'));
    }

    public function product(Product $product): View
    {
        abort_unless($product->is_active, 404);

        $product->load(['category', 'images']);

        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->inRandomOrder()
            ->limit(4)
            ->get();

        return view('store.product', compact('product', 'related'));
    }

    public function delivery(): View
    {
        $zones = DeliveryZone::active()
            ->orderBy('city')
            ->orderBy('neighborhood')
            ->get()
            ->groupBy('city');

        return view('store.delivery', compact('zones'));
    }

    public function contact(): View
    {
        return view('store.contact');
    }
}
