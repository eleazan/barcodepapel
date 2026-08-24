@props([
    'logs',
    'mostrarPedido' => false,
])

@php
    $colores = [
        'green'  => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'red'    => 'bg-red-50 text-red-700 ring-red-200',
        'yellow' => 'bg-amber-50 text-amber-700 ring-amber-200',
    ];
@endphp

@if ($logs->isEmpty())
    <p class="text-sm text-gray-400 py-4">Todavía no se le ha enviado ningún aviso.</p>
@else
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-sky-50 bg-sky-50/30">
                    <x-admin.th>Fecha</x-admin.th>
                    <x-admin.th>Tipo</x-admin.th>
                    @if ($mostrarPedido)
                        <x-admin.th>Pedido</x-admin.th>
                    @endif
                    <x-admin.th>Destinatario</x-admin.th>
                    <x-admin.th>Asunto</x-admin.th>
                    <x-admin.th align="center">Estado</x-admin.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-sky-50">
                @foreach ($logs as $log)
                    <tr class="hover:bg-sky-50/30 transition-colors align-top" x-data="{ abierto: false }">
                        <x-admin.td>
                            <span class="text-gray-500 text-xs whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                        </x-admin.td>
                        <x-admin.td>
                            <span class="text-xs font-medium text-gray-600">{{ $log->eventLabel() }}</span>
                        </x-admin.td>
                        @if ($mostrarPedido)
                            <x-admin.td>
                                @if ($log->order)
                                    <a href="{{ route('admin.orders.show', $log->order) }}" class="text-xs font-mono text-sky-600 hover:underline">
                                        {{ $log->order->order_number }}
                                    </a>
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </x-admin.td>
                        @endif
                        <x-admin.td>
                            <span class="text-gray-600 text-xs font-mono">{{ $log->recipient }}</span>
                        </x-admin.td>
                        <x-admin.td>
                            <button @click="abierto = ! abierto" class="text-gray-600 hover:text-sky-600 text-xs text-left">
                                {{ $log->subject ?: Str::limit($log->body, 40) }}
                            </button>
                        </x-admin.td>
                        <x-admin.td align="center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium ring-1 ring-inset {{ $colores[$log->statusColor()] ?? '' }}">
                                {{ $log->statusLabel() }}
                            </span>
                            @if ($log->error_message)
                                <p class="text-xs text-red-400 mt-0.5 max-w-[180px]" title="{{ $log->error_message }}">{{ Str::limit($log->error_message, 40) }}</p>
                            @endif
                        </x-admin.td>
                    </tr>
                    <tr x-show="abierto" x-cloak>
                        <td colspan="{{ $mostrarPedido ? 6 : 5 }}" class="px-4 pb-4">
                            <pre class="bg-gray-50 border border-gray-100 rounded-xl p-4 text-xs text-gray-600 whitespace-pre-wrap font-sans">{{ $log->body }}</pre>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
