<x-layouts.admin title="Pedidos">
    <x-slot:actions>
        <a href="{{ route('admin.orders.create') }}" class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo pedido
        </a>
    </x-slot:actions>

    <x-admin.table>
        <x-slot:header>
            <form method="GET" action="{{ route('admin.orders.index') }}" class="flex items-center gap-3 w-full">
                <div class="relative flex-1 max-w-xs">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar pedido o cliente..." class="form-input pl-9 py-2">
                </div>
                <select name="status" class="form-select py-2 w-auto" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    @foreach (\App\Models\Order::STATUSES as $key => $label)
                        <option value="{{ $key }}" {{ request('status') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </x-slot:header>

        <x-slot:head>
            <x-admin.th>Pedido</x-admin.th>
            <x-admin.th>Cliente</x-admin.th>
            <x-admin.th align="center">Items</x-admin.th>
            <x-admin.th align="right">Total</x-admin.th>
            <x-admin.th align="center">Estado</x-admin.th>
            <x-admin.th align="right">Fecha</x-admin.th>
            <x-admin.th align="right">Acciones</x-admin.th>
        </x-slot:head>

        @forelse ($orders as $order)
            <tr class="hover:bg-sky-50/30 transition-colors">
                <x-admin.td>
                    <a href="{{ route('admin.orders.show', $order) }}" class="font-medium text-sky-600 hover:text-sky-800">
                        {{ $order->order_number }}
                    </a>
                </x-admin.td>
                <x-admin.td>
                    <p class="text-gray-700">{{ $order->customer_name }}</p>
                    <p class="text-xs text-gray-400">{{ $order->customer_phone }}</p>
                </x-admin.td>
                <x-admin.td align="center">
                    <span class="text-gray-500">{{ $order->items_count }}</span>
                </x-admin.td>
                <x-admin.td align="right">
                    <span class="font-medium text-gray-700">{{ $order->formattedTotal() }}</span>
                </x-admin.td>
                <x-admin.td align="center">
                    <x-admin.status-badge :status="$order->status" />
                </x-admin.td>
                <x-admin.td align="right">
                    <span class="text-gray-400 text-xs">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                </x-admin.td>
                <x-admin.td align="right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('admin.orders.show', $order) }}" class="p-2 text-gray-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-colors" title="Ver">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </a>
                        <a href="{{ route('admin.orders.edit', $order) }}" class="p-2 text-gray-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-colors" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                    </div>
                </x-admin.td>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-admin.empty-state message="No hay pedidos." action="Crear pedido" :actionUrl="route('admin.orders.create')" />
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $orders->links() }}
        </x-slot:pagination>
    </x-admin.table>
</x-layouts.admin>
