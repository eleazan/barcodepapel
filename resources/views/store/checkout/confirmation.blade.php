<x-layouts.store :title="'Pedido ' . $order->order_number" description="Confirmación de tu pedido en Barco de Papel." noindex>

    <section class="pt-24 lg:pt-32 pb-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Confirmación --}}
            <div class="text-center mb-10">
                <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-emerald-50 flex items-center justify-center">
                    <svg class="w-10 h-10 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h1 class="font-display text-3xl lg:text-4xl text-gray-900 mb-3">&iexcl;Pedido recibido!</h1>
                <p class="text-gray-500 max-w-lg mx-auto">
                    Gracias, {{ $order->customer_name }}. Estamos revisando tu pedido y te avisamos
                    @if ($order->customer_email)
                        por email
                    @else
                        por tel&eacute;fono
                    @endif
                    cuando est&eacute; preparado para el reparto.
                </p>
            </div>

            {{-- Número de pedido --}}
            <div class="bg-brand-50/60 rounded-2xl border border-brand-100 p-6 mb-6 text-center">
                <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">N&uacute;mero de pedido</p>
                <p class="font-display text-2xl text-brand-800">{{ $order->order_number }}</p>
                <p class="mt-2 text-xs text-gray-500">Gu&aacute;rdalo: te sirve para cualquier consulta sobre la entrega.</p>
            </div>

            {{-- Detalle --}}
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h2 class="font-display text-lg text-gray-900">Detalle del pedido</h2>
                </div>

                <ul class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <li class="flex items-start justify-between gap-4 px-6 py-4">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $item->quantity }}&times;
                                    @if ($item->product)
                                        <a href="{{ route('product', $item->product) }}" class="hover:text-brand-700 transition-colors">{{ $item->product->name }}</a>
                                    @else
                                        Producto
                                    @endif
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ number_format((float) $item->unit_price, 2, ',', '.') }} &euro; / unidad</p>
                            </div>
                            <p class="text-sm font-medium text-gray-900 shrink-0">{{ number_format((float) $item->total, 2, ',', '.') }} &euro;</p>
                        </li>
                    @endforeach
                </ul>

                <dl class="px-6 py-4 border-t border-gray-100 space-y-2 text-sm bg-gray-50/50">
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-500">Subtotal</dt>
                        <dd class="text-gray-900">{{ $order->formattedSubtotal() }}</dd>
                    </div>
                    <div class="flex items-center justify-between">
                        <dt class="text-gray-500">Gastos de reparto</dt>
                        <dd class="text-gray-900">{{ $order->formattedDeliveryFee() }}</dd>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                        <dt class="font-medium text-gray-900">Total</dt>
                        <dd class="font-display text-xl text-brand-700">{{ $order->formattedTotal() }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Entrega --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6">
                <h2 class="font-display text-lg text-gray-900 mb-4">Entrega</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex gap-3">
                        <dt class="text-gray-500 w-28 shrink-0">Direcci&oacute;n</dt>
                        <dd class="text-gray-900">{{ $order->delivery_address }}<br>CP {{ $order->postal_code }}</dd>
                    </div>
                    <div class="flex gap-3">
                        <dt class="text-gray-500 w-28 shrink-0">Tel&eacute;fono</dt>
                        <dd class="text-gray-900">{{ $order->customer_phone }}</dd>
                    </div>
                    @if ($order->customer_email)
                        <div class="flex gap-3">
                            <dt class="text-gray-500 w-28 shrink-0">Email</dt>
                            <dd class="text-gray-900">{{ $order->customer_email }}</dd>
                        </div>
                    @endif
                    @if ($order->notes)
                        <div class="flex gap-3">
                            <dt class="text-gray-500 w-28 shrink-0">Indicaciones</dt>
                            <dd class="text-gray-900">{{ $order->notes }}</dd>
                        </div>
                    @endif
                    <div class="flex gap-3">
                        <dt class="text-gray-500 w-28 shrink-0">Estado</dt>
                        <dd>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">{{ $order->statusLabel() }}</span>
                        </dd>
                    </div>
                </dl>
            </div>

            {{-- Aviso de pago --}}
            <div class="flex items-start gap-3 p-4 rounded-2xl bg-amber-50 border border-amber-200 mb-8">
                <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm text-amber-800">
                    El pago se realiza en el momento de la entrega, en efectivo o con tarjeta. No hemos hecho
                    ning&uacute;n cargo online. Los importes incluyen el IVA.
                </p>
            </div>

            {{-- Acciones --}}
            <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('catalog') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-brand-600 text-white font-medium rounded-full hover:bg-brand-700 transition-all hover:shadow-lg hover:shadow-brand-600/25">
                    Seguir comprando
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="{{ route('contact') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 bg-white text-gray-700 font-medium rounded-full border border-gray-200 hover:bg-gray-50 transition-colors">
                    Contactar con la librer&iacute;a
                </a>
            </div>
        </div>
    </section>

</x-layouts.store>
