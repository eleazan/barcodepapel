<x-layouts.admin title="Clientes">

    <x-admin.table>
        <x-slot:header>
            <form method="GET" action="{{ route('admin.customers.index') }}" class="flex items-center gap-3 w-full">
                <div class="relative flex-1 max-w-xs">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre o correo..." class="form-input pl-9 py-2">
                </div>
            </form>
        </x-slot:header>

        <x-slot:head>
            <x-admin.th>Cliente</x-admin.th>
            <x-admin.th>Correo</x-admin.th>
            <x-admin.th align="center">Pedidos</x-admin.th>
            <x-admin.th align="center">Estado</x-admin.th>
            <x-admin.th align="right">Acciones</x-admin.th>
        </x-slot:head>

        @forelse ($customers as $customer)
            <tr class="hover:bg-sky-50/30 transition-colors">
                <x-admin.td>
                    <a href="{{ route('admin.customers.show', $customer) }}" class="font-medium text-gray-700 hover:text-sky-600">
                        {{ $customer->name }}
                    </a>
                    @if ($customer->is_admin)
                        <span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-brand-50 text-brand-700">Admin</span>
                    @endif
                </x-admin.td>
                <x-admin.td>
                    <span class="text-gray-500 text-sm font-mono">{{ $customer->email }}</span>
                </x-admin.td>
                <x-admin.td align="center">
                    <span class="text-gray-600">{{ $customer->orders_count }}</span>
                </x-admin.td>
                <x-admin.td align="center">
                    @if ($customer->email_verified_at)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-600">Verificado</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-amber-50 text-amber-600">Sin verificar</span>
                    @endif
                </x-admin.td>
                <x-admin.td align="right">
                    <a href="{{ route('admin.customers.show', $customer) }}" class="p-2 text-gray-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-colors inline-block" title="Ver ficha">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </a>
                </x-admin.td>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    <x-admin.empty-state message="Todavía no hay clientes registrados." />
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $customers->links() }}
        </x-slot:pagination>
    </x-admin.table>
</x-layouts.admin>
