<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\DeliveryZone;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::active()
            ->withCount(['products' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        return view('store.home', [
            'categories'         => $categories,
            'productsCount'      => Product::active()->count(),
            'categoriesCount'    => $categories->count(),
            'deliveryZonesCount' => DeliveryZone::where('is_active', true)->count(),
        ]);
    }
}
