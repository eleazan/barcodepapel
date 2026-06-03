<x-layouts.store
    :title="$activeCategory ? $activeCategory->name . ' — Catálogo' : 'Catálogo'"
    :description="$activeCategory
        ? ($activeCategory->description ?: 'Compra ' . $activeCategory->name . ' en Barco de Papel, tu librería en Ibiza con reparto a domicilio.')
        : 'Explora libros, cuadernos, material escolar y más. Librería Barco de Papel en Ibiza con reparto propio.'"
    :canonical="$activeCategory ? route('catalog', ['categoria' => $activeCategory->slug]) : route('catalog')"
    :noindex="request()->hasAny(['buscar', 'orden'])"
>

    @push('head')
    {{-- Pagination --}}
    @if ($products->previousPageUrl())
        <link rel="prev" href="{{ $products->previousPageUrl() }}">
    @endif
    @if ($products->nextPageUrl())
        <link rel="next" href="{{ $products->nextPageUrl() }}">
    @endif

    {{-- BreadcrumbList --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ route('home') }}"},
            {"@type": "ListItem", "position": 2, "name": "Catálogo", "item": "{{ route('catalog') }}"}
            @if ($activeCategory)
            ,{"@type": "ListItem", "position": 3, "name": "{{ $activeCategory->name }}", "item": "{{ route('catalog', ['categoria' => $activeCategory->slug]) }}"}
            @endif
        ]
    }
    </script>

    {{-- ItemList --}}
    @if ($products->isNotEmpty())
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "ItemList",
        "name": "{{ $activeCategory ? $activeCategory->name : 'Catálogo' }}",
        "numberOfItems": {{ $products->total() }},
        "itemListElement": [
            @foreach ($products as $p)
            {"@type": "ListItem", "position": {{ ($products->currentPage() - 1) * $products->perPage() + $loop->index + 1 }}, "url": "{{ route('product', $p) }}", "name": "{{ addslashes($p->name) }}"}{{ $loop->last ? '' : ',' }}
            @endforeach
        ]
    }
    </script>
    @endif
    @endpush

    <section class="pt-24 lg:pt-32 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav aria-label="Breadcrumb" class="mb-6">
                <ol class="flex items-center gap-2 text-sm text-gray-500">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-700 transition-colors">Inicio</a></li>
                    <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                    @if ($activeCategory)
                        <li><a href="{{ route('catalog') }}" class="hover:text-brand-700 transition-colors">Cat&aacute;logo</a></li>
                        <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                        <li class="text-gray-600 font-medium">{{ $activeCategory->name }}</li>
                    @else
                        <li class="text-gray-600 font-medium">Cat&aacute;logo</li>
                    @endif
                </ol>
            </nav>

            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-8">
                <div>
                    <h1 class="font-display text-3xl sm:text-4xl text-gray-900">
                        {{ $activeCategory ? $activeCategory->name : 'Catálogo' }}
                    </h1>
                    @if ($activeCategory && $activeCategory->description)
                        <p class="text-gray-500 mt-2">{{ $activeCategory->description }}</p>
                    @else
                        <p class="text-gray-500 mt-2">{{ $products->total() }} {{ $products->total() === 1 ? 'producto' : 'productos' }} disponibles</p>
                    @endif
                </div>
            </div>

            <div class="lg:grid lg:grid-cols-[240px_1fr] lg:gap-8">

                {{-- Sidebar filters --}}
                <aside class="mb-8 lg:mb-0">
                    {{-- Search --}}
                    <form action="{{ route('catalog') }}" method="GET" class="mb-6">
                        @if ($activeCategory)
                            <input type="hidden" name="categoria" value="{{ $activeCategory->slug }}">
                        @endif
                        <div class="relative">
                            <input
                                type="text"
                                name="buscar"
                                value="{{ request('buscar') }}"
                                placeholder="Buscar productos..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:border-brand-500 focus:ring-brand-500 bg-white placeholder:text-gray-300"
                            >
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </form>

                    {{-- Categories --}}
                    <div class="bg-white rounded-2xl border border-gray-100 p-5">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Categor&iacute;as</h3>
                        <ul class="space-y-1">
                            <li>
                                <a href="{{ route('catalog') }}"
                                   class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors {{ ! $activeCategory ? 'bg-brand-50 text-brand-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                                    Todas
                                    <span class="text-xs text-gray-500">{{ $products->total() }}</span>
                                </a>
                            </li>
                            @foreach ($categories as $cat)
                                <li>
                                    <a href="{{ route('catalog', ['categoria' => $cat->slug]) }}"
                                       class="flex items-center justify-between px-3 py-2 rounded-lg text-sm transition-colors {{ $activeCategory && $activeCategory->id === $cat->id ? 'bg-brand-50 text-brand-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                                        {{ $cat->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    {{-- Sort (mobile-friendly) --}}
                    <div class="mt-4 bg-white rounded-2xl border border-gray-100 p-5">
                        <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-3">Ordenar por</h3>
                        <ul class="space-y-1">
                            @php
                                $sorts = [
                                    '' => 'M&aacute;s recientes',
                                    'precio_asc' => 'Precio: menor a mayor',
                                    'precio_desc' => 'Precio: mayor a menor',
                                    'nombre' => 'Nombre A–Z',
                                ];
                            @endphp
                            @foreach ($sorts as $value => $label)
                                <li>
                                    <a href="{{ route('catalog', array_filter(['categoria' => request('categoria'), 'buscar' => request('buscar'), 'orden' => $value ?: null])) }}"
                                       class="block px-3 py-2 rounded-lg text-sm transition-colors {{ request('orden', '') === $value ? 'bg-brand-50 text-brand-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                                        {!! $label !!}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </aside>

                {{-- Product grid --}}
                <div>
                    @if ($products->isEmpty())
                        <div class="text-center py-20">
                            <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <h3 class="font-display text-xl text-gray-500 mb-2">No se encontraron productos</h3>
                            <p class="text-sm text-gray-500">Prueba con otra b&uacute;squeda o categor&iacute;a.</p>
                            <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 bg-brand-600 text-white text-sm font-medium rounded-full hover:bg-brand-700 transition-colors">Ver todo el cat&aacute;logo</a>
                        </div>
                    @else
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-6">
                            @foreach ($products as $product)
                                <a href="{{ route('product', $product) }}" class="group bg-white rounded-2xl border border-gray-100 overflow-hidden hover:border-brand-200 hover:shadow-lg hover:shadow-brand-50/50 transition-all duration-300 hover:-translate-y-0.5">
                                    {{-- Image --}}
                                    <div class="aspect-[4/3] bg-gray-50 relative overflow-hidden">
                                        @if ($product->image)
                                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" width="400" height="300" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            </div>
                                        @endif
                                        @unless ($product->hasStock())
                                            <span class="absolute top-3 left-3 px-2.5 py-1 bg-gray-900/70 text-white text-xs font-medium rounded-full">Agotado</span>
                                        @endunless
                                    </div>

                                    {{-- Info --}}
                                    <div class="p-4">
                                        <p class="text-xs text-brand-700 font-medium mb-1">{{ $product->category->name }}</p>
                                        <h3 class="font-medium text-gray-900 text-sm leading-snug mb-2 group-hover:text-brand-700 transition-colors line-clamp-2">{{ $product->name }}</h3>
                                        <p class="font-display text-lg text-gray-900">{{ $product->formattedPrice() }}</p>
                                    </div>
                                </a>
                            @endforeach
                        </div>

                        {{-- Pagination --}}
                        @if ($products->hasPages())
                            <nav class="mt-10 flex items-center justify-center gap-1" aria-label="Paginaci&oacute;n">
                                @if ($products->onFirstPage())
                                    <span class="px-3 py-2 text-sm text-gray-500 cursor-not-allowed">&laquo; Anterior</span>
                                @else
                                    <a href="{{ $products->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-600 hover:text-brand-700 transition-colors">&laquo; Anterior</a>
                                @endif

                                @foreach ($products->getUrlRange(max(1, $products->currentPage() - 2), min($products->lastPage(), $products->currentPage() + 2)) as $page => $url)
                                    <a href="{{ $url }}"
                                       class="w-10 h-10 flex items-center justify-center rounded-full text-sm font-medium transition-colors {{ $page === $products->currentPage() ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-brand-50 hover:text-brand-700' }}">
                                        {{ $page }}
                                    </a>
                                @endforeach

                                @if ($products->hasMorePages())
                                    <a href="{{ $products->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-600 hover:text-brand-700 transition-colors">Siguiente &raquo;</a>
                                @else
                                    <span class="px-3 py-2 text-sm text-gray-500 cursor-not-allowed">Siguiente &raquo;</span>
                                @endif
                            </nav>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </section>

</x-layouts.store>
