<x-layouts.store title="Finalizar pedido" description="Completa tus datos de entrega para recibir tu pedido en casa. Reparto propio en toda Ibiza." noindex>

    <section class="pt-24 lg:pt-32 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Cabecera --}}
            <div class="mb-10">
                <nav aria-label="Breadcrumb" class="mb-4">
                    <ol class="flex items-center gap-2 text-sm text-gray-500">
                        <li><a href="{{ route('home') }}" class="hover:text-brand-700 transition-colors">Inicio</a></li>
                        <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                        <li><a href="{{ route('cart.index') }}" class="hover:text-brand-700 transition-colors">Mi carrito</a></li>
                        <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                        <li class="text-gray-600 font-medium">Finalizar pedido</li>
                    </ol>
                </nav>
                <h1 class="font-display text-3xl lg:text-4xl text-gray-900">Finalizar pedido</h1>
                <p class="mt-2 text-gray-500">Repartimos nosotros mismos en toda la isla. El pago se realiza en el momento de la entrega.</p>
            </div>

            <form
                method="POST"
                action="{{ route('checkout.store') }}"
                x-data="checkoutForm('{{ route('delivery.check') }}', '{{ old('postal_code') }}')"
                @submit="enviando = true"
            >
                @csrf

                <div class="lg:grid lg:grid-cols-3 lg:gap-8 items-start">

                    {{-- ==================== DATOS ==================== --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- Contacto --}}
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-display text-xl text-gray-900 mb-1">Tus datos</h2>
                            <p class="text-sm text-gray-500 mb-6">Los usamos para avisarte del reparto.</p>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                                <div class="sm:col-span-2">
                                    <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Nombre y apellidos <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="customer_name"
                                        name="customer_name"
                                        type="text"
                                        value="{{ old('customer_name', $usuario?->name) }}"
                                        required
                                        autocomplete="name"
                                        maxlength="255"
                                        class="w-full rounded-xl border-gray-200 text-sm focus:border-brand-400 focus:ring-brand-400 @error('customer_name') border-red-300 @enderror"
                                    >
                                    @error('customer_name')
                                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Tel&eacute;fono <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="customer_phone"
                                        name="customer_phone"
                                        type="tel"
                                        value="{{ old('customer_phone') }}"
                                        required
                                        autocomplete="tel"
                                        placeholder="971 000 000"
                                        maxlength="30"
                                        class="w-full rounded-xl border-gray-200 text-sm focus:border-brand-400 focus:ring-brand-400 @error('customer_phone') border-red-300 @enderror"
                                    >
                                    @error('customer_phone')
                                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                    @else
                                        <p class="mt-1.5 text-xs text-gray-500">Te llamamos si hay alguna duda con la entrega.</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1.5">Email</label>
                                    <input
                                        id="customer_email"
                                        name="customer_email"
                                        type="email"
                                        value="{{ old('customer_email', $usuario?->email) }}"
                                        autocomplete="email"
                                        maxlength="255"
                                        class="w-full rounded-xl border-gray-200 text-sm focus:border-brand-400 focus:ring-brand-400 @error('customer_email') border-red-300 @enderror"
                                    >
                                    @error('customer_email')
                                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                    @else
                                        <p class="mt-1.5 text-xs text-gray-500">Opcional. Si lo indicas, te enviamos la confirmaci&oacute;n.</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Entrega --}}
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-display text-xl text-gray-900 mb-1">D&oacute;nde lo llevamos</h2>
                            <p class="text-sm text-gray-500 mb-6">Solo repartimos en Ibiza: c&oacute;digos postales 07800–07849.</p>

                            <div class="space-y-5">
                                <div>
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        C&oacute;digo postal <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="postal_code"
                                        name="postal_code"
                                        type="text"
                                        inputmode="numeric"
                                        maxlength="5"
                                        value="{{ old('postal_code') }}"
                                        required
                                        autocomplete="postal-code"
                                        x-model="cp"
                                        @blur="comprobar()"
                                        class="w-full sm:w-40 rounded-xl border-gray-200 text-sm focus:border-brand-400 focus:ring-brand-400 @error('postal_code') border-red-300 @enderror"
                                    >

                                    @error('postal_code')
                                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                    @enderror

                                    {{-- Resultado de la comprobación en vivo --}}
                                    <template x-if="estado === 'ok'">
                                        <p class="mt-2 text-sm text-emerald-700 flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <span>
                                                Repartimos aqu&iacute;<span x-show="zona" x-cloak> (<span x-text="zona"></span>)</span>.
                                                Gastos: <strong x-text="gastos"></strong>
                                            </span>
                                        </p>
                                    </template>
                                    <template x-if="estado === 'fuera'">
                                        <p class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14L21 3m0 0v7m0-7h-7M5 5h4l2 5-2.5 1.5a11 11 0 005 5L15 14l5 2v4a1 1 0 01-1 1C10.6 21 3 13.4 3 5a1 1 0 011-1z"/></svg>
                                            No llegamos a ese c&oacute;digo postal. <a href="{{ route('contact') }}" class="underline">Habla con nosotros</a> y buscamos una soluci&oacute;n.
                                        </p>
                                    </template>
                                    <template x-if="estado === 'invalido'">
                                        <p class="mt-2 text-sm text-red-600">El c&oacute;digo postal debe tener 5 d&iacute;gitos.</p>
                                    </template>
                                </div>

                                <div>
                                    <label for="delivery_address" class="block text-sm font-medium text-gray-700 mb-1.5">
                                        Direcci&oacute;n de entrega <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        id="delivery_address"
                                        name="delivery_address"
                                        rows="3"
                                        required
                                        autocomplete="street-address"
                                        maxlength="500"
                                        placeholder="Calle, n&uacute;mero, piso y puerta. Localidad."
                                        class="w-full rounded-xl border-gray-200 text-sm focus:border-brand-400 focus:ring-brand-400 resize-y @error('delivery_address') border-red-300 @enderror"
                                    >{{ old('delivery_address') }}</textarea>
                                    @error('delivery_address')
                                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">Indicaciones para el reparto</label>
                                    <textarea
                                        id="notes"
                                        name="notes"
                                        rows="2"
                                        maxlength="1000"
                                        placeholder="Horario preferente, timbre que no funciona, dejar en porter&iacute;a&hellip;"
                                        class="w-full rounded-xl border-gray-200 text-sm focus:border-brand-400 focus:ring-brand-400 resize-y @error('notes') border-red-300 @enderror"
                                    >{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Condiciones --}}
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <label for="acepta_condiciones" class="flex items-start gap-3 cursor-pointer">
                                <input
                                    id="acepta_condiciones"
                                    name="acepta_condiciones"
                                    type="checkbox"
                                    value="1"
                                    @checked(old('acepta_condiciones'))
                                    required
                                    class="mt-0.5 rounded border-gray-300 text-brand-600 focus:ring-brand-400"
                                >
                                <span class="text-sm text-gray-600">
                                    Acepto las condiciones de venta y reparto: el pedido se entrega en la direcci&oacute;n indicada
                                    dentro de nuestras <a href="{{ route('delivery') }}" class="text-brand-700 underline">zonas de reparto</a>
                                    y el pago se realiza en el momento de la entrega.
                                    <span class="text-red-500">*</span>
                                </span>
                            </label>
                            @error('acepta_condiciones')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- ==================== RESUMEN ==================== --}}
                    <div class="mt-8 lg:mt-0 lg:sticky lg:top-28">
                        <div class="bg-white rounded-2xl border border-gray-100 p-6">
                            <h2 class="font-display text-xl text-gray-900 mb-5">Tu pedido</h2>

                            <ul class="space-y-3 mb-5 max-h-72 overflow-y-auto">
                                @foreach ($items as $item)
                                    <li class="flex items-start justify-between gap-3 text-sm">
                                        <span class="text-gray-600 min-w-0">
                                            <span class="font-medium text-gray-900">{{ $item->quantity }}&times;</span>
                                            {{ $item->product->name }}
                                        </span>
                                        <span class="text-gray-900 font-medium shrink-0">{{ $item->formattedTotal() }}</span>
                                    </li>
                                @endforeach
                            </ul>

                            <dl class="space-y-2.5 text-sm pt-4 border-t border-gray-100">
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-500">Subtotal</dt>
                                    <dd class="font-medium text-gray-900">{{ number_format($subtotal, 2, ',', '.') }} &euro;</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-gray-500">Reparto</dt>
                                    <dd class="font-medium text-gray-900">
                                        <span x-show="estado === 'ok'" x-text="gastos" x-cloak></span>
                                        <span x-show="estado !== 'ok'" class="text-gray-400 font-normal">Seg&uacute;n c&oacute;digo postal</span>
                                    </dd>
                                </div>
                                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                    <dt class="font-medium text-gray-900">Total</dt>
                                    <dd class="font-display text-2xl text-brand-700">
                                        <span x-show="estado === 'ok'" x-text="totalFormateado({{ $subtotal }})" x-cloak></span>
                                        <span x-show="estado !== 'ok'">{{ number_format($subtotal, 2, ',', '.') }} &euro;<span class="text-sm text-gray-400"> + reparto</span></span>
                                    </dd>
                                </div>
                            </dl>

                            <button
                                type="submit"
                                :disabled="enviando"
                                class="mt-6 w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 bg-brand-600 text-white font-medium rounded-full hover:bg-brand-700 transition-all hover:shadow-lg hover:shadow-brand-600/25 disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <span x-show="! enviando">Confirmar pedido</span>
                                <span x-show="enviando" x-cloak>Procesando&hellip;</span>
                                <svg x-show="! enviando" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>

                            <p class="mt-4 text-xs text-gray-500 text-center">
                                No se realiza ning&uacute;n cobro online. Pagas al recibir el pedido.
                            </p>

                            <a href="{{ route('cart.index') }}" class="mt-4 block text-center text-sm text-gray-500 hover:text-brand-700 transition-colors">
                                Volver al carrito
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

</x-layouts.store>
