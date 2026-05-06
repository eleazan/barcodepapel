<x-layouts.admin title="Códigos Postales">
    <x-slot:actions>
        <a href="{{ route('admin.delivery-zones.create') }}" class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nueva zona
        </a>
    </x-slot:actions>

    <x-admin.table>
        <x-slot:header>
            <form method="GET" action="{{ route('admin.delivery-zones.index') }}" class="flex items-center gap-3 w-full">
                <div class="relative flex-1 max-w-xs">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar CP o colonia..." class="form-input pl-9 py-2">
                </div>
            </form>
        </x-slot:header>

        <x-slot:head>
            <x-admin.th>Código postal</x-admin.th>
            <x-admin.th>Colonia</x-admin.th>
            <x-admin.th>Ciudad</x-admin.th>
            <x-admin.th align="right">Tarifa envío</x-admin.th>
            <x-admin.th align="center">Estado</x-admin.th>
            <x-admin.th align="right">Acciones</x-admin.th>
        </x-slot:head>

        @forelse ($zones as $zone)
            <tr class="hover:bg-sky-50/30 transition-colors">
                <x-admin.td>
                    <span class="font-mono font-medium text-gray-700">{{ $zone->postal_code }}</span>
                </x-admin.td>
                <x-admin.td>
                    <span class="text-gray-600">{{ $zone->neighborhood ?: '—' }}</span>
                </x-admin.td>
                <x-admin.td>
                    <span class="text-gray-500">{{ $zone->city ?: '—' }}</span>
                </x-admin.td>
                <x-admin.td align="right">
                    <span class="font-medium text-gray-700">{{ $zone->formattedFee() }}</span>
                </x-admin.td>
                <x-admin.td align="center">
                    @if ($zone->is_active)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-600">Activa</span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-500">Inactiva</span>
                    @endif
                </x-admin.td>
                <x-admin.td align="right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('admin.delivery-zones.edit', $zone) }}" class="p-2 text-gray-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-colors" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.delivery-zones.destroy', $zone) }}" x-data
                              x-on:submit.prevent="if (confirm('¿Eliminar esta zona de reparto?')) $el.submit()">
                            @csrf @method('DELETE')
                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </x-admin.td>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-admin.empty-state message="No hay zonas de reparto." action="Crear zona" :actionUrl="route('admin.delivery-zones.create')" />
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $zones->links() }}
        </x-slot:pagination>
    </x-admin.table>
</x-layouts.admin>
