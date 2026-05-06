<x-layouts.store>

    @push('head')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "WebSite",
        "@id": "{{ url('/') }}#website",
        "url": "{{ url('/') }}",
        "name": "Barco de Papel",
        "description": "Librería y papelería en Ibiza con reparto a domicilio",
        "potentialAction": {
            "@type": "SearchAction",
            "target": {
                "@type": "EntryPoint",
                "urlTemplate": "{{ route('catalog') }}?buscar={search_term_string}"
            },
            "query-input": "required name=search_term_string"
        }
    }
    </script>
    @endpush

    {{-- ==================== HERO ==================== --}}
    <section class="relative overflow-hidden pt-24 lg:pt-32 pb-20 lg:pb-28">
        {{-- Background pattern --}}
        <div class="absolute inset-0 opacity-[0.03]" style="background-image: url(&quot;data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23000000'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E&quot;);"></div>

        {{-- Warm gradient accent --}}
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-gradient-to-bl from-brand-100/40 via-brand-50/20 to-transparent rounded-full blur-3xl -translate-y-1/2 translate-x-1/4"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-gradient-to-tr from-amber-100/30 to-transparent rounded-full blur-3xl translate-y-1/3 -translate-x-1/4"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-3xl mx-auto text-center">
                {{-- Badge --}}
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-brand-50 text-brand-700 rounded-full text-sm font-medium mb-6 border border-brand-100"
                     style="animation: fadeInUp 0.6s ease-out both;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Ibiza, Islas Baleares
                </div>

                {{-- Heading --}}
                <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl xl:text-7xl text-gray-900 leading-[1.1] mb-6"
                    style="animation: fadeInUp 0.6s ease-out 0.1s both;">
                    Tu librer&iacute;a en Ibiza,
                    <span class="relative inline-block">
                        <span class="relative z-10 text-brand-700">a domicilio</span>
                        <span class="absolute bottom-1 left-0 right-0 h-3 bg-brand-200/50 -skew-x-3 z-0"></span>
                    </span>
                </h1>

                {{-- Subtitle --}}
                <p class="text-lg sm:text-xl text-gray-500 leading-relaxed max-w-2xl mx-auto mb-10"
                   style="animation: fadeInUp 0.6s ease-out 0.2s both;">
                    Libros, cuadernos, material escolar y mucho m&aacute;s. Seleccionamos con cari&ntilde;o lo mejor para ti y te lo llevamos a casa en toda la isla.
                </p>

                {{-- CTA buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4"
                     style="animation: fadeInUp 0.6s ease-out 0.3s both;">
                    <a href="{{ route('catalog') }}" class="group inline-flex items-center gap-2.5 px-7 py-3.5 bg-brand-600 text-white font-medium rounded-full hover:bg-brand-700 transition-all duration-200 hover:shadow-xl hover:shadow-brand-600/20 hover:-translate-y-0.5">
                        Explorar cat&aacute;logo
                        <svg class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    <a href="{{ route('delivery') }}" class="inline-flex items-center gap-2 px-7 py-3.5 text-gray-700 font-medium rounded-full border border-gray-200 hover:border-gray-300 hover:bg-white transition-all duration-200">
                        <svg class="w-5 h-5 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        Zonas de reparto
                    </a>
                </div>

                {{-- Trust signals --}}
                <div class="flex items-center justify-center gap-8 mt-12 text-sm text-gray-500"
                     style="animation: fadeInUp 0.6s ease-out 0.4s both;">
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Env&iacute;o en 24–48h
                    </span>
                    <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Reparto propio
                    </span>
                    <span class="hidden sm:flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Toda la isla
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== CATEGORIES ==================== --}}
    <section id="categorias" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Section header --}}
            <div class="text-center max-w-2xl mx-auto mb-14">
                <p class="text-sm font-semibold text-brand-700 uppercase tracking-wider mb-3">Nuestro cat&aacute;logo</p>
                <h2 class="font-display text-3xl sm:text-4xl lg:text-5xl text-gray-900 mb-4">Todo lo que necesitas</h2>
                <p class="text-gray-500 text-lg">Desde los &uacute;ltimos bestsellers hasta material escolar para la vuelta al cole. Encuentra todo en un solo lugar.</p>
            </div>

            {{-- Category grid --}}
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-6">
                @foreach ($categories as $category)
                    <a href="{{ route('catalog', ['categoria' => $category->slug]) }}"
                       class="group relative bg-white rounded-2xl border border-gray-100 p-6 lg:p-8 hover:border-brand-200 hover:shadow-lg hover:shadow-brand-50 transition-all duration-300 hover:-translate-y-1 overflow-hidden">
                        {{-- Decorative corner --}}
                        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-bl from-brand-50 to-transparent rounded-bl-[40px] opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

                        {{-- Icon --}}
                        <div class="w-12 h-12 lg:w-14 lg:h-14 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center mb-4 group-hover:bg-brand-100 transition-colors duration-300">
                            @switch(strtolower($category->name))
                                @case('libros')
                                    <svg class="w-6 h-6 lg:w-7 lg:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    @break
                                @case('cuadernos')
                                    <svg class="w-6 h-6 lg:w-7 lg:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    @break
                                @case('papelería escolar')
                                    <svg class="w-6 h-6 lg:w-7 lg:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    @break
                                @case('arte')
                                    <svg class="w-6 h-6 lg:w-7 lg:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                    @break
                                @case('oficina')
                                    <svg class="w-6 h-6 lg:w-7 lg:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    @break
                                @case('mochilas')
                                    <svg class="w-6 h-6 lg:w-7 lg:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    @break
                                @default
                                    <svg class="w-6 h-6 lg:w-7 lg:h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    @break
                            @endswitch
                        </div>

                        {{-- Text --}}
                        <h3 class="font-display text-lg lg:text-xl text-gray-900 mb-1 group-hover:text-brand-700 transition-colors">{{ $category->name }}</h3>
                        <p class="text-sm text-gray-500">{{ $category->products_count }} {{ $category->products_count === 1 ? 'producto' : 'productos' }}</p>

                        {{-- Arrow --}}
                        <div class="absolute bottom-6 right-6 w-8 h-8 rounded-full bg-brand-50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 translate-x-2 group-hover:translate-x-0">
                            <svg class="w-4 h-4 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ==================== WHY CHOOSE US ==================== --}}
    <section class="py-20 lg:py-28 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <p class="text-sm font-semibold text-brand-700 uppercase tracking-wider mb-3">&iquest;Por qu&eacute; elegirnos?</p>
                <h2 class="font-display text-3xl sm:text-4xl lg:text-5xl text-gray-900 mb-4">M&aacute;s que una librer&iacute;a</h2>
                <p class="text-gray-500 text-lg">Somos parte de la comunidad ibicenca. Nos apasionan los libros y queremos que lleguen a todos los rincones de la isla.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                {{-- Reparto propio --}}
                <div class="text-center group">
                    <div class="w-16 h-16 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-brand-50 to-brand-100 text-brand-700 flex items-center justify-center shadow-sm group-hover:shadow-md group-hover:shadow-brand-100 transition-all duration-300 group-hover:-translate-y-1">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    </div>
                    <h3 class="font-display text-xl text-gray-900 mb-2">Reparto propio</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Nuestro equipo te lleva el pedido a casa. Sin intermediarios, sin esperas.</p>
                </div>

                {{-- Selección cuidada --}}
                <div class="text-center group">
                    <div class="w-16 h-16 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 text-amber-600 flex items-center justify-center shadow-sm group-hover:shadow-md group-hover:shadow-amber-100 transition-all duration-300 group-hover:-translate-y-1">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                    </div>
                    <h3 class="font-display text-xl text-gray-900 mb-2">Selecci&oacute;n cuidada</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Elegimos cada producto con cari&ntilde;o. Calidad y variedad para todas las edades.</p>
                </div>

                {{-- Negocio local --}}
                <div class="text-center group">
                    <div class="w-16 h-16 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 text-emerald-600 flex items-center justify-center shadow-sm group-hover:shadow-md group-hover:shadow-emerald-100 transition-all duration-300 group-hover:-translate-y-1">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <h3 class="font-display text-xl text-gray-900 mb-2">Negocio local</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Comprando aqu&iacute; apoyas al comercio ibicenco. Somos vecinos, no un almac&eacute;n.</p>
                </div>

                {{-- Material escolar --}}
                <div class="text-center group">
                    <div class="w-16 h-16 mx-auto mb-5 rounded-2xl bg-gradient-to-br from-violet-50 to-violet-100 text-violet-600 flex items-center justify-center shadow-sm group-hover:shadow-md group-hover:shadow-violet-100 transition-all duration-300 group-hover:-translate-y-1">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"/></svg>
                    </div>
                    <h3 class="font-display text-xl text-gray-900 mb-2">Vuelta al cole</h3>
                    <p class="text-sm text-gray-500 leading-relaxed">Todo el material escolar que necesitan tus hijos. Listas de &uacute;tiles y pedidos especiales.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== DELIVERY ZONES ==================== --}}
    <section id="reparto" class="py-20 lg:py-28 relative overflow-hidden">
        {{-- Background decoration --}}
        <div class="absolute inset-0 bg-gradient-to-b from-[#faf8f5] via-brand-50/30 to-[#faf8f5]"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto">
                <div class="bg-white rounded-3xl shadow-xl shadow-brand-100/30 border border-brand-100/50 overflow-hidden">
                    <div class="grid lg:grid-cols-2">
                        {{-- Left: info --}}
                        <div class="p-8 lg:p-12">
                            <p class="text-sm font-semibold text-brand-700 uppercase tracking-wider mb-3">Reparto a domicilio</p>
                            <h2 class="font-display text-3xl lg:text-4xl text-gray-900 mb-4">&iquest;Llegamos a tu zona?</h2>
                            <p class="text-gray-500 leading-relaxed mb-8">
                                Hacemos reparto propio en toda la isla de Ibiza. Introduce tu c&oacute;digo postal y comprueba si cubrimos tu zona.
                            </p>

                            {{-- Postal code checker --}}
                            <div x-data="{
                                cp: '',
                                result: null,
                                checking: false,
                                check() {
                                    this.checking = true;
                                    this.result = null;
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
                                <div class="flex gap-3">
                                    <div class="flex-1 relative">
                                        <label for="cp-home" class="sr-only">C&oacute;digo postal</label>
                                        <input
                                            id="cp-home"
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
                                        class="px-6 py-3 bg-brand-600 text-white font-medium rounded-xl hover:bg-brand-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed whitespace-nowrap"
                                    >
                                        <span x-show="!checking">Comprobar</span>
                                        <span x-show="checking" x-cloak class="flex items-center gap-2">
                                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                                            ...
                                        </span>
                                    </button>
                                </div>

                                {{-- Result --}}
                                <div class="mt-4 min-h-[48px]">
                                    <div x-show="result === 'yes'" x-cloak
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-1"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         class="flex items-center gap-3 p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-700">
                                        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <span><strong>&iexcl;Genial!</strong> Hacemos reparto en tu zona. &iexcl;Explora nuestro cat&aacute;logo!</span>
                                    </div>
                                    <div x-show="result === 'no'" x-cloak
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-1"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         class="flex items-center gap-3 p-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700">
                                        <svg class="w-5 h-5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        <span>Lo sentimos, de momento no llegamos a ese c&oacute;digo postal. Solo repartimos en Ibiza (07800–07849).</span>
                                    </div>
                                    <div x-show="result === 'invalid'" x-cloak
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 translate-y-1"
                                         x-transition:enter-end="opacity-100 translate-y-0"
                                         class="flex items-center gap-3 p-3 bg-gray-50 border border-gray-200 rounded-xl text-sm text-gray-500">
                                        <svg class="w-5 h-5 text-gray-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/></svg>
                                        <span>Introduce un c&oacute;digo postal v&aacute;lido de 5 d&iacute;gitos.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right: visual --}}
                        <div class="hidden lg:flex items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100 p-12 relative">
                            {{-- Decorative map-like illustration --}}
                            <div class="text-center">
                                <div class="w-32 h-32 mx-auto mb-6 rounded-full bg-white/80 shadow-inner flex items-center justify-center">
                                    <svg class="w-16 h-16 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <p class="font-display text-2xl text-brand-800 mb-2">Ibiza</p>
                                <p class="text-sm text-brand-700/70">C&oacute;digos postales</p>
                                <p class="text-lg font-semibold text-brand-700 mt-1">07800 – 07849</p>

                                {{-- Delivery zones count --}}
                                <div class="mt-6 inline-flex items-center gap-2 px-4 py-2 bg-white/70 rounded-full text-sm text-brand-700 font-medium">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                    {{ $deliveryZonesCount }} zonas activas
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== STATS ==================== --}}
    <section class="py-16 bg-white border-y border-gray-100">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div>
                    <p class="font-display text-3xl lg:text-4xl text-brand-700">{{ $productsCount }}+</p>
                    <p class="text-sm text-gray-500 mt-1">Productos</p>
                </div>
                <div>
                    <p class="font-display text-3xl lg:text-4xl text-brand-700">{{ $categoriesCount }}</p>
                    <p class="text-sm text-gray-500 mt-1">Categor&iacute;as</p>
                </div>
                <div>
                    <p class="font-display text-3xl lg:text-4xl text-brand-700">{{ $deliveryZonesCount }}</p>
                    <p class="text-sm text-gray-500 mt-1">Zonas de reparto</p>
                </div>
                <div>
                    <p class="font-display text-3xl lg:text-4xl text-brand-700">24h</p>
                    <p class="text-sm text-gray-500 mt-1">Entrega media</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== CTA ==================== --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-gradient-to-br from-brand-600 via-brand-700 to-brand-800 rounded-3xl px-8 py-16 lg:px-16 lg:py-20 relative overflow-hidden">
                {{-- Decorative elements --}}
                <div class="absolute top-0 left-0 w-40 h-40 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
                <div class="absolute bottom-0 right-0 w-60 h-60 bg-white/5 rounded-full translate-x-1/3 translate-y-1/3"></div>
                <div class="absolute top-1/2 left-1/4 w-2 h-2 bg-white/20 rounded-full"></div>
                <div class="absolute top-1/3 right-1/3 w-3 h-3 bg-white/10 rounded-full"></div>

                <div class="relative">
                    <h2 class="font-display text-3xl sm:text-4xl lg:text-5xl text-white mb-4">&iquest;Listo para explorar?</h2>
                    <p class="text-brand-100 text-lg max-w-xl mx-auto mb-10">
                        Descubre nuestro cat&aacute;logo completo y haz tu pedido. Te lo llevamos a casa en Ibiza.
                    </p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="{{ route('catalog') }}" class="group inline-flex items-center gap-2 px-8 py-4 bg-white text-brand-700 font-semibold rounded-full hover:bg-brand-50 transition-all duration-200 hover:shadow-xl hover:shadow-black/10">
                            Ver cat&aacute;logo
                            <svg class="w-5 h-5 transition-transform duration-200 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 px-8 py-4 text-white font-medium rounded-full border border-white/30 hover:bg-white/10 transition-all duration-200">
                            Cont&aacute;ctanos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Animation keyframes --}}
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

</x-layouts.store>
