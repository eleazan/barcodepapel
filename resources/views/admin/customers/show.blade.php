<x-layouts.admin :title="$customer->name">
    <x-slot:actions>
        <a href="{{ route('admin.customers.index') }}" class="btn-secondary btn-sm">Volver</a>
    </x-slot:actions>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Datos del cliente --}}
        <div class="lg:col-span-1 space-y-6">
            <x-admin.card title="Datos">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-400 text-xs">Nombre</dt>
                        <dd class="text-gray-700">{{ $customer->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Correo</dt>
                        <dd class="text-gray-700 font-mono text-xs">{{ $customer->email }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Verificado</dt>
                        <dd class="text-gray-700">
                            {{ $customer->email_verified_at?->format('d/m/Y H:i') ?? 'Sin verificar' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Alta</dt>
                        <dd class="text-gray-700">{{ $customer->created_at->format('d/m/Y') }}</dd>
                    </div>
                    @if ($customer->verial_cliente_id)
                        <div>
                            <dt class="text-gray-400 text-xs">Cliente en Verial</dt>
                            <dd class="text-gray-700 font-mono text-xs">#{{ $customer->verial_cliente_id }}</dd>
                        </div>
                    @endif
                </dl>
            </x-admin.card>

            <x-admin.card title="Resumen">
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-500">Pedidos</dt>
                        <dd class="font-medium text-gray-700">{{ $orders->count() }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-500">Total gastado</dt>
                        <dd class="font-medium text-gray-700">{{ number_format($totalGastado, 2, ',', '.') }} €</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-500">Avisos enviados</dt>
                        <dd class="font-medium text-gray-700">{{ $notifications->count() }}</dd>
                    </div>
                </dl>
            </x-admin.card>
        </div>

        {{-- Pedidos y avisos --}}
        <div class="lg:col-span-2 space-y-6">
            <x-admin.card title="Pedidos">
                @if ($orders->isEmpty())
                    <p class="text-sm text-gray-400 py-4">Este cliente todavía no ha hecho ningún pedido.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-sky-50 bg-sky-50/30">
                                    <x-admin.th>Pedido</x-admin.th>
                                    <x-admin.th>Fecha</x-admin.th>
                                    <x-admin.th align="center">Estado</x-admin.th>
                                    <x-admin.th align="right">Total</x-admin.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-sky-50">
                                @foreach ($orders as $order)
                                    <tr class="hover:bg-sky-50/30 transition-colors">
                                        <x-admin.td>
                                            <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs text-sky-600 hover:underline">
                                                {{ $order->order_number }}
                                            </a>
                                            @if ($order->user_id === null)
                                                <span class="ml-1 text-[10px] text-gray-400">como invitado</span>
                                            @endif
                                        </x-admin.td>
                                        <x-admin.td>
                                            <span class="text-gray-500 text-xs">{{ $order->created_at->format('d/m/Y') }}</span>
                                        </x-admin.td>
                                        <x-admin.td align="center">
                                            <x-admin.status-badge :status="$order->status" />
                                        </x-admin.td>
                                        <x-admin.td align="right">
                                            <span class="font-medium text-gray-700">{{ $order->formattedTotal() }}</span>
                                        </x-admin.td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-admin.card>

            <x-admin.card title="Avisos enviados">
                <p class="text-xs text-gray-400 mb-3">
                    Todo lo que se le ha enviado: avisos de sus pedidos y de su cuenta.
                </p>
                <x-admin.notification-list :logs="$notifications" :mostrar-pedido="true" />
            </x-admin.card>
        </div>
    </div>
</x-layouts.admin>
