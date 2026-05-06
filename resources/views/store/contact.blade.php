<x-layouts.store title="Contacto" description="Contacta con Barco de Papel, tu librería en Ibiza. Visítanos en Carrer de l'Exemple 1, Eivissa. Llámanos al 971 000 000 o escríbenos.">

    @push('head')
    {{-- BreadcrumbList --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ route('home') }}"},
            {"@type": "ListItem", "position": 2, "name": "Contacto", "item": "{{ route('contact') }}"}
        ]
    }
    </script>
    @endpush

    <section class="pt-24 lg:pt-32 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav aria-label="Breadcrumb" class="mb-6">
                <ol class="flex items-center gap-2 text-sm text-gray-500">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-700 transition-colors">Inicio</a></li>
                    <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                    <li class="text-gray-600 font-medium">Contacto</li>
                </ol>
            </nav>

            {{-- Header --}}
            <div class="max-w-3xl mb-12">
                <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl text-gray-900 mb-4">Contacto</h1>
                <p class="text-lg text-gray-500">Estamos aqu&iacute; para ayudarte. Vis&iacute;tanos en nuestra tienda, ll&aacute;manos o escr&iacute;benos un email.</p>
            </div>

            <div class="grid lg:grid-cols-2 gap-8 lg:gap-12">

                {{-- Contact info --}}
                <address class="space-y-6 not-italic">

                    {{-- Address --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-display text-lg text-gray-900 mb-1">Direcci&oacute;n</h2>
                                <p class="text-gray-600 text-sm">Carrer de l'Exemple, 1</p>
                                <p class="text-gray-600 text-sm">07800 Eivissa, Ibiza</p>
                                <p class="text-gray-600 text-sm">Islas Baleares, Espa&ntilde;a</p>
                            </div>
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-display text-lg text-gray-900 mb-1">Tel&eacute;fono</h2>
                                <a href="tel:+34971000000" class="text-brand-700 hover:text-brand-700 text-sm font-medium transition-colors">+34 971 000 000</a>
                                <p class="text-gray-500 text-xs mt-1">Llamadas en horario comercial</p>
                            </div>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-display text-lg text-gray-900 mb-1">Email</h2>
                                <a href="mailto:info@barcodepapel.es" class="text-brand-700 hover:text-brand-700 text-sm font-medium transition-colors">info@barcodepapel.es</a>
                                <p class="text-gray-500 text-xs mt-1">Respondemos en menos de 24h</p>
                            </div>
                        </div>
                    </div>

                    {{-- Hours --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="font-display text-lg text-gray-900 mb-1">Horario</h2>
                                <div class="space-y-1 text-sm text-gray-600">
                                    <div class="flex justify-between gap-8">
                                        <span>Lunes – Viernes</span>
                                        <span class="font-medium text-gray-900">09:30 – 20:00</span>
                                    </div>
                                    <div class="flex justify-between gap-8">
                                        <span>S&aacute;bado</span>
                                        <span class="font-medium text-gray-900">10:00 – 14:00</span>
                                    </div>
                                    <div class="flex justify-between gap-8">
                                        <span>Domingo</span>
                                        <span class="text-red-600 font-medium">Cerrado</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </address>

                {{-- Map + CTA --}}
                <div class="space-y-6">
                    {{-- Map placeholder --}}
                    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden aspect-[4/3]">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12459.176797!2d1.4206!3d38.9067!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x129946c3e7c1818d%3A0xdd1cdcba6c45c72d!2sEivissa%2C%20Illes%20Balears!5e0!3m2!1ses!2ses!4v1"
                            width="100%"
                            height="100%"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="Ubicaci&oacute;n de Barco de Papel en Ibiza"
                        ></iframe>
                    </div>

                    {{-- Quick actions --}}
                    <div class="bg-gradient-to-br from-brand-600 to-brand-700 rounded-2xl p-6 lg:p-8 text-center">
                        <h3 class="font-display text-2xl text-white mb-2">&iquest;Buscas algo en concreto?</h3>
                        <p class="text-brand-100 text-sm mb-6">Si no encuentras lo que buscas en el cat&aacute;logo, ll&aacute;manos y lo pedimos para ti.</p>
                        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
                            <a href="tel:+34971000000" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-brand-700 font-semibold rounded-full hover:bg-brand-50 transition-colors w-full sm:w-auto justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                Llamar ahora
                            </a>
                            <a href="mailto:info@barcodepapel.es" class="inline-flex items-center gap-2 px-6 py-3 text-white font-medium rounded-full border border-white/30 hover:bg-white/10 transition-colors w-full sm:w-auto justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                Enviar email
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

</x-layouts.store>
