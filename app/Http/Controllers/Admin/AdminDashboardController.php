<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\DeliveryZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        // Orders by status for doughnut chart
        $ordersByStatus = Order::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        // Sales last 7 days for bar chart
        $salesByDay = Order::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders_count'),
            )
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Fill missing days
        $salesChart = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $day = $salesByDay->firstWhere('date', $date);
            $salesChart->push([
                'label' => now()->subDays($i)->translatedFormat('D d'),
                'revenue' => $day ? (float) $day->revenue : 0,
                'count' => $day ? $day->orders_count : 0,
            ]);
        }

        // Top 5 selling products
        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->take(5)
            ->with('product:id,name')
            ->get();

        return view('admin.dashboard', [
            'totalProducts' => Product::count(),
            'activeProducts' => Product::active()->count(),
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('status', Order::STATUS_PENDIENTE)->count(),
            'inDeliveryOrders' => Order::where('status', Order::STATUS_EN_REPARTO)->count(),
            'totalCategories' => Category::count(),
            'totalZones' => DeliveryZone::active()->count(),
            'totalRevenue' => Order::sum('total'),
            'recentOrders' => Order::latest()->take(5)->get(),
            'lowStockProducts' => Product::where('stock', '<=', 5)->where('is_active', true)->take(5)->get(),
            'ordersByStatus' => $ordersByStatus,
            'salesChart' => $salesChart,
            'topProducts' => $topProducts,
        ]);
    }
}
