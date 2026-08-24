<x-layouts.admin title="Pedido {{ $order->order_number }}">
    <x-slot:actions>
        <a href="{{ route('admin.orders.pdf', $order) }}" class="btn-secondary btn-sm" target="_blank">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Descargar PDF
        </a>
        <a href="{{ route('admin.orders.edit', $order) }}" class="btn-secondary btn-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Editar
        </a>
    </x-slot:actions>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Order details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Items --}}
            <x-admin.card title="Productos del pedido" :padding="false">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-sky-50 bg-sky-50/30">
                            <x-admin.th>Producto</x-admin.th>
                            <x-admin.th align="center">Cant.</x-admin.th>
                            <x-admin.th align="right">Precio unit.</x-admin.th>
                            <x-admin.th align="right">Total</x-admin.th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sky-50">
                        @foreach ($order->items as $item)
                            <tr>
                                <x-admin.td>
                                    <span class="font-medium text-gray-700">{{ $item->product->name }}</span>
                                </x-admin.td>
                                <x-admin.td align="center">{{ $item->quantity }}</x-admin.td>
                                <x-admin.td align="right">{{ number_format((float) $item->unit_price, 2, ',', '.') }} €</x-admin.td>
                                <x-admin.td align="right" class="font-medium">{{ number_format((float) $item->total, 2, ',', '.') }} €</x-admin.td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-sky-100">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right text-sm text-gray-500">Subtotal</td>
                            <td class="px-6 py-3 text-right text-sm font-medium text-gray-700">{{ $order->formattedSubtotal() }}</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="px-6 py-2 text-right text-sm text-gray-500">Envío</td>
                            <td class="px-6 py-2 text-right text-sm text-gray-500">{{ number_format((float) $order->delivery_fee, 2, ',', '.') }} €</td>
                        </tr>
                        <tr class="border-t border-sky-100">
                            <td colspan="3" class="px-6 py-3 text-right text-sm font-semibold text-gray-700">Total</td>
                            <td class="px-6 py-3 text-right text-base font-bold text-gray-800">{{ $order->formattedTotal() }}</td>
                        </tr>
                    </tfoot>
                </table>
            </x-admin.card>

            @if ($order->notes)
                <x-admin.card title="Notas">
                    <p class="text-sm text-gray-600">{{ $order->notes }}</p>
                </x-admin.card>
            @endif
        </div>

        {{-- Sidebar info --}}
        <div class="space-y-6">
            {{-- Status --}}
            <x-admin.card title="Estado del pedido">
                <div class="mb-4">
                    <x-admin.status-badge :status="$order->status" />
                </div>
                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex items-center gap-2">
                    @csrf @method('PATCH')
                    <select name="status" class="form-select py-2 flex-1 text-sm">
                        @foreach (\App\Models\Order::STATUSES as $key => $label)
                            <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn-primary btn-sm">Cambiar</button>
                </form>
            </x-admin.card>

            {{-- Customer (editable contact) --}}
            <x-admin.card title="Cliente" x-data="{ editingContact: false }">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-400 text-xs">Nombre</dt>
                        <dd class="text-gray-700 font-medium">{{ $order->customer_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Cuenta</dt>
                        <dd>
                            @if ($order->user)
                                <a href="{{ route('admin.customers.show', $order->user) }}" class="text-sky-600 hover:underline">
                                    Ver ficha del cliente
                                </a>
                            @else
                                <span class="text-gray-400">Compró como invitado</span>
                            @endif
                        </dd>
                    </div>
                </dl>

                {{-- View mode --}}
                <div x-show="!editingContact" class="mt-3 space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-400 text-xs">Teléfono</dt>
                        <dd class="text-gray-700">{{ $order->customer_phone }}</dd>
                    </div>
                    @if ($order->customer_email)
                        <div>
                            <dt class="text-gray-400 text-xs">Email</dt>
                            <dd class="text-gray-700">{{ $order->customer_email }}</dd>
                        </div>
                    @endif
                    <button type="button" @click="editingContact = true" class="text-xs text-sky-500 hover:text-sky-700 font-medium mt-2">
                        Corregir datos de contacto
                    </button>
                </div>

                {{-- Edit mode --}}
                <form x-show="editingContact" x-cloak method="POST" action="{{ route('admin.orders.contact.update', $order) }}" class="mt-3 space-y-3">
                    @csrf @method('PATCH')
                    <div>
                        <label class="text-gray-400 text-xs">Email</label>
                        <input type="email" name="customer_email" value="{{ $order->customer_email }}" class="form-input py-1.5 text-sm" placeholder="email@ejemplo.com">
                    </div>
                    <div>
                        <label class="text-gray-400 text-xs">Teléfono</label>
                        <input type="text" name="customer_phone" value="{{ $order->customer_phone }}" class="form-input py-1.5 text-sm">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="btn-primary btn-sm">Guardar</button>
                        <button type="button" @click="editingContact = false" class="btn-secondary btn-sm">Cancelar</button>
                    </div>
                </form>
            </x-admin.card>

            {{-- Delivery --}}
            <x-admin.card title="Entrega">
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-gray-400 text-xs">Dirección</dt>
                        <dd class="text-gray-700">{{ $order->delivery_address }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Código postal</dt>
                        <dd class="text-gray-700">{{ $order->postal_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-400 text-xs">Fecha prevista de entrega</dt>
                        <dd class="text-gray-700 first-letter:uppercase">
                            {{ $order->formattedEstimatedDelivery() ?? 'Sin fecha asignada' }}
                        </dd>
                    </div>
                </dl>
            </x-admin.card>

            {{-- Dates --}}
            <x-admin.card title="Fechas">
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Creado</dt>
                        <dd class="text-gray-600">{{ $order->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Actualizado</dt>
                        <dd class="text-gray-600">{{ $order->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </x-admin.card>
        </div>
    </div>

    {{-- Audit trail --}}
    <div class="mt-8">
        <x-admin.card title="Historial de cambios">
            @if ($order->auditLogs->count())
                <x-admin.audit-timeline :logs="$order->auditLogs" />
            @else
                <p class="text-sm text-gray-400">No hay registros de auditoría.</p>
            @endif
        </x-admin.card>
    </div>

    {{-- Notification history --}}
    <div class="mt-8" x-data="{ showSendForm: false }">
        <x-admin.card :padding="false">
            <div class="px-6 py-4 border-b border-sky-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-gray-700">Historial de notificaciones</h3>
                <button type="button" @click="showSendForm = !showSendForm" class="btn-primary btn-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    Enviar notificación
                </button>
            </div>

            {{-- Manual send form --}}
            <div x-show="showSendForm" x-cloak class="px-6 py-4 bg-sky-50/40 border-b border-sky-100">
                <form method="POST" action="{{ route('admin.orders.notifications.send', $order) }}" class="flex items-end gap-3 flex-wrap">
                    @csrf
                    <div>
                        <label class="form-label text-xs">Canal</label>
                        <select name="channel" class="form-select py-1.5 text-sm w-auto">
                            @foreach (\App\Models\NotificationLog::CHANNELS as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[200px]">
                        <label class="form-label text-xs">Destinatario</label>
                        <input type="text" name="recipient" value="{{ $order->customer_email ?? $order->customer_phone }}" class="form-input py-1.5 text-sm" placeholder="Email o teléfono" required>
                    </div>
                    <button type="submit" class="btn-primary btn-sm">Enviar</button>
                    <button type="button" @click="showSendForm = false" class="btn-secondary btn-sm">Cancelar</button>
                </form>
            </div>

            {{-- Log table --}}
            @if ($order->notificationLogs->count())
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-sky-50 bg-sky-50/30">
                                <x-admin.th>Fecha</x-admin.th>
                                <x-admin.th>Tipo</x-admin.th>
                                <x-admin.th>Canal</x-admin.th>
                                <x-admin.th>Destinatario</x-admin.th>
                                <x-admin.th>Asunto</x-admin.th>
                                <x-admin.th align="center">Estado</x-admin.th>
                                <x-admin.th align="right">Acciones</x-admin.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-sky-50">
                            @foreach ($order->notificationLogs->sortByDesc('created_at') as $log)
                                <tr class="hover:bg-sky-50/30 transition-colors" x-data="{ showResend: false, showBody: false }">
                                    <x-admin.td>
                                        <span class="text-gray-500 text-xs">{{ $log->created_at->format('d/m/Y H:i') }}</span>
                                    </x-admin.td>
                                    <x-admin.td>
                                        <span class="text-xs font-medium text-gray-600">{{ $log->eventLabel() }}</span>
                                    </x-admin.td>
                                    <x-admin.td>
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-600">
                                            @if ($log->channel === 'email')
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            @endif
                                            {{ $log->channelLabel() }}
                                        </span>
                                    </x-admin.td>
                                    <x-admin.td>
                                        <span class="text-gray-600 text-xs font-mono">{{ $log->recipient }}</span>
                                    </x-admin.td>
                                    <x-admin.td>
                                        <button @click="showBody = !showBody" class="text-gray-600 hover:text-sky-600 text-xs text-left truncate max-w-[200px] block">
                                            {{ $log->subject ?: Str::limit($log->body, 40) }}
                                        </button>
                                    </x-admin.td>
                                    <x-admin.td align="center">
                                        @php
                                            $colors = [
                                                'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
                                                'red' => 'bg-red-50 text-red-700 ring-red-200',
                                                'yellow' => 'bg-amber-50 text-amber-700 ring-amber-200',
                                            ];
                                        @endphp
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium ring-1 ring-inset {{ $colors[$log->statusColor()] ?? '' }}">
                                            {{ $log->statusLabel() }}
                                        </span>
                                        @if ($log->error_message)
                                            <p class="text-xs text-red-400 mt-0.5 truncate max-w-[150px]" title="{{ $log->error_message }}">{{ $log->error_message }}</p>
                                        @endif
                                    </x-admin.td>
                                    <x-admin.td align="right">
                                        <button @click="showResend = !showResend" class="p-1.5 text-gray-400 hover:text-sky-600 hover:bg-sky-50 rounded-lg transition-colors" title="Reenviar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                        </button>
                                    </x-admin.td>
                                </tr>
                                {{-- Expandable body --}}
                                <tr x-show="showBody" x-cloak>
                                    <td colspan="7" class="px-6 py-3 bg-gray-50">
                                        <pre class="text-xs text-gray-600 whitespace-pre-wrap font-sans">{{ $log->body }}</pre>
                                    </td>
                                </tr>
                                {{-- Resend form row --}}
                                <tr x-show="showResend" x-cloak>
                                    <td colspan="7" class="px-6 py-3 bg-sky-50/40">
                                        <form method="POST" action="{{ route('admin.orders.notifications.resend', [$order, $log]) }}" class="flex items-center gap-3">
                                            @csrf
                                            <span class="text-xs text-gray-500 shrink-0">Reenviar a:</span>
                                            <input type="text" name="recipient" value="{{ $log->recipient }}" class="form-input py-1.5 text-sm flex-1 max-w-xs" placeholder="Corregir destinatario">
                                            <button type="submit" class="btn-primary btn-sm">Reenviar</button>
                                            <button type="button" @click="showResend = false" class="btn-secondary btn-sm">Cancelar</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <x-admin.empty-state message="No se han enviado notificaciones para este pedido." />
            @endif
        </x-admin.card>
    </div>
</x-layouts.admin>
