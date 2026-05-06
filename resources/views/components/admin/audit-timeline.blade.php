@props(['logs'])

@php
    $eventColors = [
        'green' => 'bg-emerald-400',
        'blue' => 'bg-sky-400',
        'red' => 'bg-red-400',
        'gray' => 'bg-gray-400',
    ];

    $fieldLabels = [
        'status' => 'Estado',
        'customer_name' => 'Nombre cliente',
        'customer_email' => 'Email',
        'customer_phone' => 'Teléfono',
        'delivery_address' => 'Dirección',
        'postal_code' => 'Código postal',
        'delivery_fee' => 'Envío',
        'subtotal' => 'Subtotal',
        'total' => 'Total',
        'notes' => 'Notas',
        'name' => 'Nombre',
        'description' => 'Descripción',
        'price' => 'Precio',
        'stock' => 'Stock',
        'is_active' => 'Activo',
        'slug' => 'Slug',
        'sku' => 'SKU',
        'category_id' => 'Categoría',
        'sort_order' => 'Orden',
        'neighborhood' => 'Colonia',
        'city' => 'Ciudad',
        'image' => 'Imagen',
    ];
@endphp

<div class="relative">
    {{-- Timeline line --}}
    <div class="absolute left-[11px] top-3 bottom-3 w-px bg-sky-100"></div>

    <div class="space-y-4">
        @foreach ($logs as $log)
            @php
                $dotColor = $eventColors[$log->eventColor()] ?? $eventColors['gray'];
                $changes = $log->changes();
            @endphp
            <div class="relative flex gap-4 pl-8">
                {{-- Dot --}}
                <span class="absolute left-0 top-1.5 w-[22px] h-[22px] rounded-full border-2 border-white {{ $dotColor }} flex items-center justify-center z-10">
                    @if ($log->event === 'created')
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"/></svg>
                    @elseif ($log->event === 'updated')
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    @elseif ($log->event === 'deleted')
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                    @endif
                </span>

                <div class="flex-1 min-w-0">
                    {{-- Header --}}
                    <div class="flex items-baseline gap-2 flex-wrap">
                        <span class="text-sm font-medium text-gray-700">{{ $log->eventLabel() }}</span>
                        <span class="text-xs text-gray-400">{{ $log->created_at->format('d/m/Y H:i:s') }}</span>
                        @if ($log->user)
                            <span class="text-xs text-sky-500">por {{ $log->user->name }}</span>
                        @else
                            <span class="text-xs text-gray-300">sistema</span>
                        @endif
                    </div>

                    {{-- Changes --}}
                    @if ($log->event === 'updated' && ! empty($changes))
                        <div class="mt-1.5 space-y-1">
                            @foreach ($changes as $field => $vals)
                                <div class="text-xs flex items-baseline gap-1.5 flex-wrap">
                                    <span class="font-medium text-gray-500">{{ $fieldLabels[$field] ?? $field }}:</span>
                                    <span class="text-red-400 line-through">{{ is_bool($vals['old']) ? ($vals['old'] ? 'Sí' : 'No') : ($vals['old'] ?? '—') }}</span>
                                    <svg class="w-3 h-3 text-gray-300 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    <span class="text-emerald-600 font-medium">{{ is_bool($vals['new']) ? ($vals['new'] ? 'Sí' : 'No') : ($vals['new'] ?? '—') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @elseif ($log->event === 'created')
                        <p class="text-xs text-gray-400 mt-0.5">Registro creado</p>
                    @elseif ($log->event === 'deleted')
                        <p class="text-xs text-gray-400 mt-0.5">Registro eliminado</p>
                    @endif

                    {{-- IP --}}
                    @if ($log->ip_address)
                        <p class="text-[10px] text-gray-300 mt-1">IP: {{ $log->ip_address }}</p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>
