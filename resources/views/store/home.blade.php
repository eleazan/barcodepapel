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

    @php
        $campana = config('tienda.campana');
        $direccion = config('tienda.direccion');
        $horario = collect(config('tienda.horario'))->filter(fn ($tramo) => ! empty($tramo['abre']));
        $mapa = 'https://www.google.com/maps/search/?api=1&query='.config('tienda.geo.latitud').','.config('tienda.geo.longitud');
    @endphp

    {{-- ==================== HERO: buscador + reparto ==================== --}}
    <section class="relative overflow-hidden pt-24 lg:pt-32 pb-12 lg:pb-16">
        <div class="absolute top-0 right-0 w-[600px] h-[600px] bg-gradient-to-bl from-brand-100/40 via-brand-50/20 to-transparent rounded-full blur-3xl -translate-y-1/2 translate-x-1/4"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-[1.15fr_1fr] gap-8 lg:gap-12 items-stretch">

                <div class="flex flex-col justify-center" style="animation: fadeInUp 0.6s ease-out both;">
                    <p class="text-sm font-semibold text-brand-700 uppercase tracking-wider mb-3">Librería en Ibiza · Reparto propio</p>
                    <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl text-gray-900 leading-[1.1] mb-5">
                        Tu librería en Ibiza,
                        <span class="relative inline-block">
                            <span class="relative z-10 text-brand-700">a domicilio</span>
                            <span class="absolute bottom-1 left-0 right-0 h-3 bg-brand-200/50 -skew-x-3 z-0"></span>
                        </span>
                    </h1>
                    <p class="text-lg text-gray-500 leading-relaxed max-w-xl mb-7">
                        Más de {{ number_format($productsCount, 0, ',', '.') }} productos entre libros y papelería. Pagas al recibir el pedido, sin pasarelas.
                    </p>

                    <form action="{{ route('catalog') }}" method="GET" role="search" class="flex gap-2 bg-white border border-gray-200 rounded-full p-1.5 max-w-xl shadow-sm focus-within:border-brand-500">
                        <label for="buscar-home" class="sr-only">Buscar en el catálogo</label>
                        <input id="buscar-home" type="search" name="buscar" placeholder="Título, autor o ISBN"
                               class="flex-1 min-w-0 border-0 bg-transparent px-4 text-base focus:ring-0 placeholder:text-gray-400">
                        <button type="submit" class="px-6 py-2.5 bg-brand-600 text-white font-medium rounded-full hover:bg-brand-700 transition-colors">Buscar</button>
                    </form>

                    @if ($categories->isNotEmpty())
                        <div class="flex flex-wrap gap-2 mt-4">
                            @foreach ($categories->take(5) as $category)
                                <a href="{{ route('catalog', ['categoria' => $category->slug]) }}"
                                   class="px-3 py-1.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-full hover:border-brand-300 hover:text-brand-700 transition-colors">{{ $category->name }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="grid gap-4" style="animation: fadeInUp 0.6s ease-out 0.15s both;">
                    <div class="relative rounded-3xl overflow-hidden min-h-[200px] lg:min-h-0">
                        <img src="{{ asset('assets/stock-estanteria.jpg') }}" alt="Estantería llena de libros" width="1200" height="704" class="absolute inset-0 w-full h-full object-cover" fetchpriority="high">
                    </div>

                    {{-- Comprobador de reparto (consulta las zonas reales) --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-lg shadow-brand-100/30 p-5"
                         x-data="postalChecker('{{ route('delivery.check') }}')">
                        <h2 class="font-display text-xl text-gray-900 mb-3">¿Llegamos a tu zona?</h2>
                        <div class="flex gap-2">
                            <label for="cp-home" class="sr-only">Código postal</label>
                            <input id="cp-home" type="text" x-model="cp" @keydown.enter="comprobar()" maxlength="5" inputmode="numeric"
                                   placeholder="Tu código postal, ej. {{ $direccion['codigo_postal'] }}"
                                   class="flex-1 min-w-0 px-4 py-2.5 border border-gray-200 rounded-xl focus:border-brand-500 focus:ring-brand-500 placeholder:text-gray-400">
                            <button type="button" @click="comprobar()" :disabled="cp.length < 5 || cargando"
                                    class="px-5 py-2.5 bg-brand-600 text-white font-medium rounded-xl hover:bg-brand-700 transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                                <span x-show="! cargando">Comprobar</span>
                                <span x-show="cargando" x-cloak>…</span>
                            </button>
                        </div>
                        <div class="mt-3 text-sm" aria-live="polite">
                            <p x-show="estado === 'ok'" x-cloak class="text-emerald-800">
                                <strong>¡Sí!</strong> Repartimos en tu zona por <span x-text="gastos"></span>.
                                <span x-show="proximaEntrega" x-cloak>Si pides hoy, te llega el <strong x-text="proximaEntrega"></strong>.</span>
                                <span x-show="motivoRetraso" x-cloak class="block text-emerald-700 mt-1"><span x-text="motivoRetraso"></span>, así que pasa al siguiente día de reparto.</span>
                            </p>
                            <p x-show="estado === 'fuera'" x-cloak class="text-amber-700">De momento no llegamos a ese código postal. <a href="{{ route('delivery') }}" class="underline">Ver zonas de reparto</a>.</p>
                            <p x-show="estado === 'invalido'" x-cloak class="text-gray-500">Introduce un código postal de 5 dígitos.</p>
                            <p x-show="estado === 'error'" x-cloak class="text-gray-500">No hemos podido comprobarlo ahora mismo. Inténtalo de nuevo.</p>
                            <p x-show="! estado" class="text-gray-500">Te decimos la tarifa y cuándo te llegaría.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ==================== CONFIANZA ==================== --}}
    <section class="bg-white border-y border-gray-100 py-5" aria-label="Cómo compramos">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <ul class="grid grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-4 text-sm">
                @foreach ([
                    ['Reparto propio', 'Sin paquetería externa'],
                    ['Pagas al recibir', 'IVA incluido'],
                    ['Entrega con fecha', 'La ves antes de confirmar'],
                    ['Librería de barrio', $direccion['calle'].', '.$direccion['ciudad']],
                ] as [$titulo, $detalle])
                    <li class="flex items-center gap-3 min-w-0">
                        <span class="shrink-0 w-9 h-9 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block font-semibold text-gray-900">{{ $titulo }}</span>
                            <span class="block text-gray-500 text-xs truncate">{{ $detalle }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ==================== ACCESOS POR CATEGORÍA ==================== --}}
    @if ($categories->isNotEmpty())
        <section id="categorias" class="py-14 lg:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4 mb-6">
                    <h2 class="font-display text-3xl sm:text-4xl text-gray-900">Empieza por aquí</h2>
                    <a href="{{ route('catalog') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800 whitespace-nowrap">Todo el catálogo →</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 lg:gap-4">
                    @foreach ($categories->take(6) as $category)
                        <a href="{{ route('catalog', ['categoria' => $category->slug]) }}"
                           class="group bg-white rounded-2xl border border-gray-100 p-5 text-center hover:border-brand-200 hover:shadow-lg hover:shadow-brand-50 transition-all duration-300 hover:-translate-y-1">
                            <span class="mx-auto mb-3 w-12 h-12 rounded-xl bg-brand-50 text-brand-700 flex items-center justify-center font-display text-2xl group-hover:bg-brand-100 transition-colors" aria-hidden="true">{{ mb_strtoupper(mb_substr($category->name, 0, 1)) }}</span>
                            <span class="block font-semibold text-sm text-gray-900 group-hover:text-brand-700 transition-colors">{{ $category->name }}</span>
                            <span class="block text-xs text-gray-500 mt-0.5">{{ $category->products_count }} {{ $category->products_count === 1 ? 'producto' : 'productos' }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ==================== NOVEDADES ==================== --}}
    @if ($novedades->isNotEmpty())
        <section class="py-14 lg:py-16 bg-white border-y border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4 mb-6">
                    <div>
                        <p class="text-sm font-semibold text-brand-700 uppercase tracking-wider mb-2">Recién llegados</p>
                        <h2 class="font-display text-3xl sm:text-4xl text-gray-900">Novedades</h2>
                    </div>
                    <a href="{{ route('catalog') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800 whitespace-nowrap">Ver todas →</a>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 lg:gap-5">
                    @foreach ($novedades as $product)
                        <x-store.product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ==================== MÁS VENDIDOS ==================== --}}
    @if ($masVendidos->isNotEmpty())
        <section class="py-14 lg:py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="mb-6">
                    <p class="text-sm font-semibold text-brand-700 uppercase tracking-wider mb-2">Lo que más sale</p>
                    <h2 class="font-display text-3xl sm:text-4xl text-gray-900">Más vendidos</h2>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 lg:gap-5">
                    @foreach ($masVendidos as $product)
                        <x-store.product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ==================== CAMPAÑA (config/tienda.php) ==================== --}}
    @if (! empty($campana['activa']))
        <section class="py-10 lg:py-14">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-brand-700 to-brand-900 text-white">
                    @if (! empty($campana['imagen']))
                        <img src="{{ asset($campana['imagen']) }}" alt="" class="absolute inset-y-0 right-0 w-1/2 h-full object-cover hidden md:block" loading="lazy">
                        <div class="absolute inset-y-0 right-0 w-1/2 bg-gradient-to-r from-brand-800 to-transparent hidden md:block"></div>
                    @endif
                    <div class="relative px-8 py-12 lg:px-14 lg:py-16 max-w-2xl">
                        <p class="text-sm font-semibold text-brand-200 uppercase tracking-wider mb-3">{{ $campana['etiqueta'] }}</p>
                        <h2 class="font-display text-3xl sm:text-4xl lg:text-5xl mb-4">{{ $campana['titulo'] }}</h2>
                        <p class="text-brand-100 text-lg mb-8">{{ $campana['texto'] }}</p>
                        <a href="{{ url($campana['url']) }}" class="inline-flex items-center gap-2 px-7 py-3.5 bg-white text-brand-800 font-semibold rounded-full hover:bg-brand-50 transition-colors">{{ $campana['boton'] }}</a>
                    </div>
                </div>
            </div>
        </section>
    @endif

    {{-- ==================== BLOG ==================== --}}
    @if ($posts->isNotEmpty())
        <section class="py-14 lg:py-16 bg-white border-y border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between gap-4 mb-6">
                    <div>
                        <p class="text-sm font-semibold text-brand-700 uppercase tracking-wider mb-2">Del blog</p>
                        <h2 class="font-display text-3xl sm:text-4xl text-gray-900">Lecturas y noticias de la librería</h2>
                    </div>
                    <a href="{{ route('blog.index') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-800 whitespace-nowrap">Todas las entradas →</a>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 lg:gap-6">
                    @foreach ($posts as $post)
                        <article class="group bg-white rounded-2xl border border-gray-100 overflow-hidden hover:border-brand-200 hover:shadow-lg hover:shadow-brand-50/50 transition-all duration-300 hover:-translate-y-0.5">
                            <a href="{{ route('blog.show', $post) }}" class="block">
                                <div class="aspect-[16/9] bg-gray-50 overflow-hidden">
                                    @if ($post->image)
                                        <img src="{{ Storage::url($post->image) }}" alt="" width="600" height="338" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                    @else
                                        <div class="w-full h-full bg-gradient-to-br from-brand-50 to-brand-100"></div>
                                    @endif
                                </div>
                                <div class="p-5">
                                    <time datetime="{{ $post->published_at->toDateString() }}" class="text-xs text-gray-500">{{ $post->formattedDate() }}</time>
                                    <h3 class="font-display text-xl text-gray-900 mt-1 mb-2 group-hover:text-brand-700 transition-colors line-clamp-2">{{ $post->title }}</h3>
                                    @if ($post->excerpt)
                                        <p class="text-sm text-gray-500 line-clamp-3">{{ $post->excerpt }}</p>
                                    @endif
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ==================== LA LIBRERÍA ==================== --}}
    <section class="py-14 lg:py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-2 gap-8 lg:gap-14 items-center">
                <div>
                    <p class="text-sm font-semibold text-brand-700 uppercase tracking-wider mb-3">La librería</p>
                    <h2 class="font-display text-3xl sm:text-4xl lg:text-5xl text-gray-900 mb-4">Un local de verdad, en Eivissa</h2>
                    <p class="text-gray-500 text-lg mb-8 max-w-xl">Puedes pasar a mirar, preguntar o recoger un pedido. Si prefieres no moverte, te lo llevamos nosotros.</p>

                    <dl class="grid sm:grid-cols-2 gap-6 mb-8">
                        <div>
                            <dt class="text-xs font-semibold text-brand-700 uppercase tracking-wider mb-1">Dirección</dt>
                            <dd class="text-gray-700">{{ $direccion['calle'] }}<br>{{ $direccion['codigo_postal'] }} {{ $direccion['ciudad'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-semibold text-brand-700 uppercase tracking-wider mb-1">Horario</dt>
                            <dd class="text-gray-700">
                                @foreach ($horario as $tramo)
                                    <span class="block">{{ $tramo['etiqueta'] }}: {{ $tramo['abre'] }}–{{ $tramo['cierra'] }}</span>
                                @endforeach
                            </dd>
                        </div>
                    </dl>

                    <div class="flex flex-wrap gap-3">
                        <a href="{{ $mapa }}" target="_blank" rel="noopener" class="inline-flex items-center px-6 py-3 bg-brand-600 text-white font-medium rounded-full hover:bg-brand-700 transition-colors">Cómo llegar</a>
                        <a href="{{ route('contact') }}" class="inline-flex items-center px-6 py-3 text-gray-700 font-medium rounded-full border border-gray-200 hover:border-gray-300 hover:bg-white transition-colors">Contacto</a>
                    </div>
                </div>

                <div class="rounded-3xl overflow-hidden aspect-[4/3] lg:aspect-[5/4]">
                    <img src="{{ asset('assets/stock-libreria.jpg') }}" alt="Pasillo de una librería con estanterías y bombillas colgadas" width="1200" height="1109" class="w-full h-full object-cover" loading="lazy">
                </div>
            </div>
        </div>
    </section>

    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @media (prefers-reduced-motion: reduce) {
            [style*="fadeInUp"] { animation: none !important; }
        }
    </style>

</x-layouts.store>
