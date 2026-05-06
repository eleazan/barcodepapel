<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Services\Notifications\OrderNotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $orders = Order::withCount('items')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, fn ($q, $s) => $q->where(function ($q) use ($s) {
                $q->where('order_number', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function create(): View
    {
        $products = Product::active()->where('stock', '>', 0)->orderBy('name')->get();

        return view('admin.orders.create', compact('products'));
    }

    public function store(OrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];
        unset($data['items']);

        $subtotal = 0;
        $orderItems = [];

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $itemTotal = $product->price * $item['quantity'];
            $subtotal += $itemTotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total' => $itemTotal,
            ];

            $product->decrement('stock', $item['quantity']);
        }

        $data['subtotal'] = $subtotal;
        $data['total'] = $subtotal + ($data['delivery_fee'] ?? 0);

        $order = Order::create($data);
        $order->items()->createMany($orderItems);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Pedido creado correctamente.');
    }

    public function show(Order $order): View
    {
        $order->load(['items.product', 'notificationLogs', 'auditLogs.user']);

        return view('admin.orders.show', compact('order'));
    }

    public function edit(Order $order): View
    {
        $order->load('items.product');
        $products = Product::active()->orderBy('name')->get();

        return view('admin.orders.edit', compact('order', 'products'));
    }

    public function update(OrderRequest $request, Order $order): RedirectResponse
    {
        $data = $request->validated();
        $items = $data['items'];
        unset($data['items']);

        // Restore stock from old items
        foreach ($order->items as $oldItem) {
            $oldItem->product->increment('stock', $oldItem->quantity);
        }

        $subtotal = 0;
        $orderItems = [];

        foreach ($items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $itemTotal = $product->price * $item['quantity'];
            $subtotal += $itemTotal;

            $orderItems[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'total' => $itemTotal,
            ];

            $product->decrement('stock', $item['quantity']);
        }

        $data['subtotal'] = $subtotal;
        $data['total'] = $subtotal + ($data['delivery_fee'] ?? 0);

        $order->update($data);
        $order->items()->delete();
        $order->items()->createMany($orderItems);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('success', 'Pedido actualizado correctamente.');
    }

    public function updateStatus(Request $request, Order $order, OrderNotificationService $notificationService): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(Order::STATUSES))],
        ]);

        $previousStatus = $order->status;
        $order->update(['status' => $request->status]);

        if ($previousStatus !== $request->status) {
            $notificationService->sendAll($order, context: [
                'previous_status' => $previousStatus,
            ]);
        }

        return back()->with('success', 'Estado del pedido actualizado.');
    }

    public function pdf(Order $order): Response
    {
        $order->load('items.product');

        $pdf = Pdf::loadView('admin.orders.pdf.albaran', compact('order'));

        return $pdf->download("albaran-{$order->order_number}.pdf");
    }

    public function destroy(Order $order): RedirectResponse
    {
        // Restore stock
        foreach ($order->items as $item) {
            $item->product->increment('stock', $item->quantity);
        }

        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('success', 'Pedido eliminado correctamente.');
    }
}
