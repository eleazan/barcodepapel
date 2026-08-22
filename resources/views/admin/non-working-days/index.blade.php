<x-layouts.admin title="Días sin reparto">
    <x-slot:actions>
        <a href="{{ route('admin.non-working-days.create') }}" class="btn-primary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo día
        </a>
    </x-slot:actions>

    <x-admin.table>
        <x-slot:header>
            <p class="text-sm text-gray-500">
                Festivos y cierres por vacaciones. Ningún pedido se programa para estos días: la fecha prevista de entrega salta al siguiente día de reparto de la zona.
            </p>
        </x-slot:header>

        <x-slot:head>
            <x-admin.th>Nombre</x-admin.th>
            <x-admin.th>Fechas</x-admin.th>
            <x-admin.th align="center">Se repite cada año</x-admin.th>
            <x-admin.th align="right">Acciones</x-admin.th>
        </x-slot:head>

        @forelse ($days as $day)
            <tr class="hover:bg-sky-50/30 transition-colors">
                <x-admin.td>
                    <span class="font-medium text-gray-700">{{ $day->name }}</span>
                </x-admin.td>
                <x-admin.td>
                    <span class="text-gray-600 first-letter:uppercase">{{ $day->formattedRange() }}</span>
                </x-admin.td>
                <x-admin.td align="center">
                    @if ($day->recurs_annually)
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-600">Cada año</span>
                    @else
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-500">Solo esta vez</span>
                    @endif
                </x-admin.td>
                <x-admin.td align="right">
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('admin.non-working-days.edit', $day) }}" class="p-2 text-gray-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-colors" title="Editar">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </a>
                        <form method="POST" action="{{ route('admin.non-working-days.destroy', $day) }}" onsubmit="return confirm('¿Eliminar «{{ $day->name }}»?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Eliminar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </x-admin.td>
            </tr>
        @empty
            <tr>
                <td colspan="4">
                    <x-admin.empty-state message="No hay días sin reparto dados de alta." action="Añadir día" :actionUrl="route('admin.non-working-days.create')" />
                </td>
            </tr>
        @endforelse

        <x-slot:pagination>
            {{ $days->links() }}
        </x-slot:pagination>
    </x-admin.table>
</x-layouts.admin>
