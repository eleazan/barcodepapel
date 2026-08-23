<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Ficha de cliente: sus pedidos y todos los avisos que se le han enviado,
 * tanto los de pedido como los de cuenta.
 */
class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $customers = User::query()
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%");
            }))
            ->withCount('orders')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $customer): View
    {
        // Un cliente registrado puede haber pedido antes de tener cuenta: sus
        // pedidos de invitado se reconocen por el correo.
        $orders = Order::query()
            ->where('user_id', $customer->id)
            ->orWhere('customer_email', $customer->email)
            ->withCount('items')
            ->latest()
            ->get();

        $notifications = NotificationLog::query()
            ->where('user_id', $customer->id)
            ->orWhereIn('order_id', $orders->pluck('id'))
            ->with('order')
            ->latest()
            ->get();

        return view('admin.customers.show', [
            'customer'      => $customer,
            'orders'        => $orders,
            'notifications' => $notifications,
            'totalGastado'  => $orders->sum(fn (Order $order) => (float) $order->total),
        ]);
    }
}
