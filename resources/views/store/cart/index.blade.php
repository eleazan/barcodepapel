<x-layouts.store title="Mi carrito" description="Revisa los productos de tu carrito y finaliza tu pedido con reparto a domicilio en Ibiza." noindex>

    <section class="pt-24 lg:pt-32 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Cabecera --}}
            <div class="mb-10">
                <nav aria-label="Breadcrumb" class="mb-4">
                    <ol class="flex items-center gap-2 text-sm text-gray-500">
                        <li><a href="{{ route('home') }}" class="hover:text-brand-700 transition-colors">Inicio</a></li>
                        <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                        <li class="text-gray-600 font-medium">Mi carrito</li>
                    </ol>
                </nav>
                <h1 class="font-display text-3xl lg:text-4xl text-gray-900">Mi carrito</h1>
            </div>

            {{-- Avisos de reconciliación: stock agotado, productos retirados del catálogo --}}
            @if (! empty($avisos))
                <div class="mb-8 p-4 rounded-2xl bg-amber-50 border border-amber-200" role="alert">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/></svg>
                        <div class="text-sm text-amber-800 space-y-1">
                            @foreach ($avisos as $aviso)
                                <p>{{ $aviso }}</p>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            @if ($items->isEmpty())
                {{-- Carrito vacío --}}
                <div class="bg-white rounded-3xl border border-gray-100 p-12 lg:p-20 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-brand-50 flex items-center justify-center">
                        <svg class="w-10 h-10 text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h2 class="font-display text-2xl text-gray-900 mb-3">Tu carrito est&aacute; vac&iacute;o</h2>
                    <p class="text-gray-500 mb-8 max-w-md mx-auto">A&uacute;n no has a&ntilde;adido nada. Echa un ojo al cat&aacute;logo: tenemos libros, papeler&iacute;a y material escolar con reparto en toda la isla.</p>
                    <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-brand-600 text-white font-medium rounded-full hover:bg-brand-700 transition-all hover:shadow-lg hover:shadow-brand-600/25">
                        Ver el cat&aacute;logo
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            @else
                <div class="lg:grid lg:grid-cols-3 lg:gap-8 items-start">

                    {{-- ==================== LÍNEAS ==================== --}}
                    <div class="lg:col-span-2 space-y-4">
                        @foreach ($items as $item)
                            <div class="bg-white rounded-2xl border border-gray-100 p-4 sm:p-5">
                                <div class="flex gap-4">

                                    {{-- Imagen --}}
                                    <a href="{{ route('product', $item->product) }}" class="w-20 h-20 sm:w-24 sm:h-24 shrink-0 rounded-xl bg-gray-50 overflow-hidden">
                                        @if ($item->product->image)
                                            <img src="{{ $item->product->image_url }}" alt="{{ $item->product->name }}" class="w-full h-full object-cover" loading="lazy">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-8 h-8 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                    </a>

                                    {{-- Datos --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-xs text-brand-700 font-medium mb-0.5">{{ $item->product->category?->name }}</p>
                                                <h2 class="font-medium text-gray-900 leading-snug">
                                                    <a href="{{ route('product', $item->product) }}" class="hover:text-brand-700 transition-colors">{{ $item->product->name }}</a>
                                                </h2>
                                                <p class="text-sm text-gray-500 mt-1">{{ $item->formattedUnitPrice() }} / unidad</p>
                                            </div>

                                            {{-- Eliminar línea --}}
                                            <form method="POST" action="{{ route('cart.remove', $item->product) }}" class="shrink-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-2 -mt-1 -mr-1 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors" aria-label="Quitar {{ $item->product->name }} del carrito">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>

                                        {{-- Cantidad y total de línea --}}
                                        <div class="flex items-end justify-between gap-4 mt-4">
                                            <form
                                                method="POST"
                                                action="{{ route('cart.update', $item->product) }}"
                                                x-data="{ cantidad: {{ $item->quantity }}, max: {{ $item->maxQuantity() }} }"
                                                class="flex items-center gap-2"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <label for="cantidad-{{ $item->product->id }}" class="sr-only">Cantidad de {{ $item->product->name }}</label>
                                                <div class="flex items-center rounded-full border border-gray-200 overflow-hidden">
                                                    <button
                                                        type="button"
                                                        @click="cantidad = Math.max(1, cantidad - 1); $nextTick(() => $root.requestSubmit())"
                                                        class="px-3 py-2 text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-colors disabled:opacity-40"
                                                        :disabled="cantidad <= 1"
                                                        aria-label="Quitar una unidad"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                                                    </button>
                                                    <input
                                                        id="cantidad-{{ $item->product->id }}"
                                                        type="number"
                                                        name="quantity"
                                                        x-model.number="cantidad"
                                                        min="1"
                                                        max="{{ $item->maxQuantity() }}"
                                                        class="w-14 text-center text-sm font-medium border-0 focus:ring-0 focus:outline-none"
                                                    >
                                                    <button
                                                        type="button"
                                                        @click="cantidad = Math.min(max, cantidad + 1); $nextTick(() => $root.requestSubmit())"
                                                        class="px-3 py-2 text-gray-500 hover:bg-gray-50 hover:text-gray-900 transition-colors disabled:opacity-40"
                                                        :disabled="cantidad >= max"
                                                        aria-label="A&ntilde;adir una unidad"
                                                    >
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                                    </button>
                                                </div>

                                                {{-- Sin JavaScript el usuario actualiza con este botón --}}
                                                <noscript><button type="submit" class="text-xs text-brand-700 underline">Actualizar</button></noscript>
                                            </form>

                                            <p class="font-display text-lg text-gray-900 shrink-0">{{ $item->formattedTotal() }}</p>
                                        </div>

                                        @if ($item->product->stock <= 5)
                                            <p class="text-xs text-amber-600 mt-2">Solo quedan {{ $item->product->stock }} unidad(es)</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Acciones del carrito --}}
                        <div class="flex flex-wrap items-center justify-between gap-3 pt-2">
                            <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 text-sm font-medium text-brand-700 hover:text-brand-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/></svg>
                                Seguir comprando
                            </a>

                            <form method="POST" action="{{ route('cart.clear') }}" x-data @submit="if (! confirm('¿Quieres vaciar todo el carrito?')) $event.preventDefault()">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm text-gray-500 hover:text-red-600 transition-colors">Vaciar carrito</button>
                            </form>
                        </div>
                    </div>

                    {{-- ==================== RESUMEN ==================== --}}
                    <div class="mt-8 lg:mt-0 lg:sticky lg:top-28">
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-display text-xl text-gray-900 mb-6">Resumen</h2>

                            <dl class="space-y-3 text-sm">
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-500">Subtotal ({{ $items->sum('quantity') }} art&iacute;culos)</dt>
                                    <dd class="font-medium text-gray-900">{{ number_format($subtotal, 2, ',', '.') }} &euro;</dd>
                                </div>

                                {{-- Comprobador de código postal --}}
                                <div
                                    class="pt-3 border-t border-gray-100"
                                    x-data="postalChecker('{{ route('delivery.check') }}')"
                                >
                                    <dt class="text-gray-500 mb-2">Gastos de reparto</dt>
                                    <div class="flex gap-2">
                                        <label for="cp-carrito" class="sr-only">C&oacute;digo postal</label>
                                        <input
                                            id="cp-carrito"
                                            type="text"
                                            inputmode="numeric"
                                            maxlength="5"
                                            placeholder="07800"
                                            x-model="cp"
                                            @keydown.enter.prevent="comprobar()"
                                            class="flex-1 min-w-0 rounded-full border-gray-200 text-sm focus:border-brand-400 focus:ring-brand-400"
                                        >
                                        <button
                                            type="button"
                                            @click="comprobar()"
                                            :disabled="cargando"
                                            class="px-4 py-2 bg-brand-600 text-white text-sm font-medium rounded-full hover:bg-brand-700 transition-colors disabled:opacity-60"
                                        >
                                            <span x-show="! cargando">Calcular</span>
                                            <span x-show="cargando" x-cloak>&hellip;</span>
                                        </button>
                                    </div>

                                    <template x-if="estado === 'ok'">
                                        <p class="mt-2 text-sm text-emerald-700">
                                            Repartimos en tu zona<span x-show="zona" x-cloak> (<span x-text="zona"></span>)</span>:
                                            <strong x-text="gastos"></strong>
                                        </p>
                                    </template>
                                    <template x-if="estado === 'fuera'">
                                        <p class="mt-2 text-sm text-red-600">No repartimos en ese c&oacute;digo postal. Solo Ibiza (07800–07849).</p>
                                    </template>
                                    <template x-if="estado === 'invalido'">
                                        <p class="mt-2 text-sm text-red-600">Introduce un c&oacute;digo postal de 5 d&iacute;gitos.</p>
                                    </template>
                                    <template x-if="estado === 'error'">
                                        <p class="mt-2 text-sm text-red-600">No hemos podido comprobarlo. Int&eacute;ntalo de nuevo.</p>
                                    </template>

                                    <p class="mt-2 text-xs text-gray-500">El importe definitivo se calcula al finalizar el pedido.</p>
                                </div>
                            </dl>

                            <a href="{{ route('checkout.show') }}" class="mt-6 w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-brand-600 text-white font-medium rounded-full hover:bg-brand-700 transition-all hover:shadow-lg hover:shadow-brand-600/25">
                                Finalizar pedido
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>

                            <ul class="mt-6 space-y-2.5 text-xs text-gray-500">
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-brand-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                    Reparto propio en toda Ibiza, sin paqueter&iacute;a externa
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-brand-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Entrega en 24–48 horas
                                </li>
                                <li class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-brand-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    Pago en el momento de la entrega
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>

</x-layouts.store>
