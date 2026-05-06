<x-layouts.store title="Zonas de reparto" description="Consulta si hacemos reparto a domicilio en tu zona. Cubrimos toda la isla de Ibiza, códigos postales 07800 a 07849.">

    @push('head')
    {{-- BreadcrumbList --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ route('home') }}"},
            {"@type": "ListItem", "position": 2, "name": "Zonas de reparto", "item": "{{ route('delivery') }}"}
        ]
    }
    </script>

    {{-- FAQPage --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "¿Cómo funciona el reparto de Barco de Papel?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Haz tu pedido, confirmamos el horario de entrega, nuestro equipo te lo lleva a casa en 24–48 horas y puedes pagar en efectivo o tarjeta a la entrega."
                }
            },
            {
                "@type": "Question",
                "name": "¿Qué códigos postales cubre el reparto en Ibiza?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Cubrimos toda la isla de Ibiza, códigos postales del 07800 al 07849, incluyendo Eivissa, Santa Eulària, Sant Antoni y Sant Josep."
                }
            },
            {
                "@type": "Question",
                "name": "¿Cuánto cuesta el envío a domicilio?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "La tarifa de envío varía según la zona. Consulta nuestra tabla de zonas de reparto para conocer el coste exacto para tu código postal."
                }
            }
        ]
    }
    </script>
    @endpush

    <section class="pt-24 lg:pt-32 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav aria-label="Breadcrumb" class="mb-6">
                <ol class="flex items-center gap-2 text-sm text-gray-500">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-600 transition-colors">Inicio</a></li>
                    <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                    <li class="text-gray-600 font-medium">Zonas de reparto</li>
                </ol>
            </nav>

            {{-- Header --}}
            <div class="max-w-3xl mb-12">
                <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl text-gray-900 mb-4">Zonas de reparto</h1>
                <p class="text-lg text-gray-500">Hacemos reparto propio en toda la isla de Ibiza. Consulta las zonas que cubrimos y la tarifa de env&iacute;o para tu c&oacute;digo postal.</p>
            </div>

            {{-- Postal code checker --}}
            <div class="bg-white rounded-2xl border border-gray-100 p-6 lg:p-8 mb-12 max-w-xl"
                 x-data="{
                    cp: '',
                    result: null,
                    zone: null,
                    checking: false,
                    check() {
                        this.checking = true;
                        this.result = null;
                        this.zone = null;
                        setTimeout(() => {
                            const num = parseInt(this.cp);
                            if (num >= 7800 && num <= 7849) {
                                this.result = 'yes';
                            } else if (this.cp.length === 5) {
                                this.result = 'no';
                            } else {
                                this.result = 'invalid';
                            }
                            this.checking = false;
                        }, 400);
                    }
                 }">
                <h2 class="font-display text-xl text-gray-900 mb-2">&iquest;Llegamos a tu zona?</h2>
                <p class="text-sm text-gray-500 mb-4">Introduce tu c&oacute;digo postal para comprobarlo.</p>
                <div class="flex gap-3">
                    <div class="flex-1">
                        <label for="cp-delivery" class="sr-only">C&oacute;digo postal</label>
                        <input
                            id="cp-delivery"
                            type="text"
                            x-model="cp"
                            @keydown.enter="check()"
                            maxlength="5"
                            inputmode="numeric"
                            placeholder="Ej: 07800"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl text-base focus:border-brand-500 focus:ring-brand-500 placeholder:text-gray-300"
                        >
                    </div>
                    <button
                        @click="check()"
                        :disabled="cp.length < 5 || checking"
                        class="px-6 py-3 bg-brand-600 text-white font-medium rounded-xl hover:bg-brand-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        <span x-show="!checking">Comprobar</span>
                        <span x-show="checking" x-cloak>...</span>
                    </button>
                </div>
                <div class="mt-4 min-h-[48px]">
                    <div x-show="result === 'yes'" x-cloak x-transition class="flex items-center gap-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700">
                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span><strong>&iexcl;S&iacute;!</strong> Hacemos reparto en tu zona. Consulta la tarifa en la tabla.</span>
                    </div>
                    <div x-show="result === 'no'" x-cloak x-transition class="flex items-center gap-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
                        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                        <span>Lo sentimos, no cubrimos ese c&oacute;digo postal. Solo repartimos en Ibiza (07800–07849).</span>
                    </div>
                    <div x-show="result === 'invalid'" x-cloak x-transition class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-500">
                        Introduce un c&oacute;digo postal v&aacute;lido de 5 d&iacute;gitos.
                    </div>
                </div>
            </div>

            {{-- Zones by city --}}
            <div class="space-y-10">
                @foreach ($zones as $city => $cityZones)
                    <div>
                        <h2 class="font-display text-2xl text-gray-900 mb-4 flex items-center gap-3">
                            <svg class="w-6 h-6 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            {{ $city }}
                        </h2>
                        <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                            <table class="w-full">
                                <caption class="sr-only">Zonas de reparto y tarifas de env&iacute;o para {{ $city }}</caption>
                                <thead>
                                    <tr class="border-b border-gray-100">
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">C&oacute;digo postal</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Zona / Barrio</th>
                                        <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Tarifa de env&iacute;o</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50">
                                    @foreach ($cityZones as $zone)
                                        <tr class="hover:bg-brand-50/30 transition-colors">
                                            <td class="px-6 py-3 text-sm font-mono text-gray-900">{{ $zone->postal_code }}</td>
                                            <td class="px-6 py-3 text-sm text-gray-600">{{ $zone->neighborhood }}</td>
                                            <td class="px-6 py-3 text-sm text-right font-medium text-gray-900">{{ $zone->formattedFee() }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Info box --}}
            <div class="mt-12 bg-brand-50/50 rounded-2xl border border-brand-100/50 p-6 lg:p-8 max-w-3xl">
                <h3 class="font-display text-xl text-gray-900 mb-4">&iquest;C&oacute;mo funciona el reparto?</h3>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold shrink-0">1</span>
                        Haz tu pedido seleccionando los productos que necesitas.
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold shrink-0">2</span>
                        Confirmamos tu pedido y te informamos del horario de entrega.
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold shrink-0">3</span>
                        Nuestro equipo te lleva el pedido a casa en 24–48 horas.
                    </li>
                    <li class="flex items-start gap-3 text-sm text-gray-600">
                        <span class="w-6 h-6 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center text-xs font-bold shrink-0">4</span>
                        Pago en efectivo o tarjeta a la entrega.
                    </li>
                </ul>
            </div>
        </div>
    </section>

</x-layouts.store>
