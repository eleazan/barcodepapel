<x-layouts.store :title="$product->name" :description="Str::limit(strip_tags($product->description ?: $product->name . ' — Disponible en Barco de Papel, librería en Ibiza.'), 155)" :canonical="route('product', $product)">

    @push('head')
    {{-- BreadcrumbList --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ route('home') }}"},
            {"@type": "ListItem", "position": 2, "name": "Catálogo", "item": "{{ route('catalog') }}"},
            {"@type": "ListItem", "position": 3, "name": "{{ e($product->category->name) }}", "item": "{{ route('catalog', ['categoria' => $product->category->slug]) }}"},
            {"@type": "ListItem", "position": 4, "name": "{{ e($product->name) }}", "item": "{{ route('product', $product) }}"}
        ]
    }
    </script>
    @endpush

    <section class="pt-24 lg:pt-32 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav aria-label="Breadcrumb" class="mb-8">
                <ol class="flex items-center gap-2 text-sm text-gray-500">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-700 transition-colors">Inicio</a></li>
                    <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                    <li><a href="{{ route('catalog') }}" class="hover:text-brand-700 transition-colors">Cat&aacute;logo</a></li>
                    <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                    <li><a href="{{ route('catalog', ['categoria' => $product->category->slug]) }}" class="hover:text-brand-700 transition-colors">{{ $product->category->name }}</a></li>
                    <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                    <li class="text-gray-600 font-medium truncate max-w-[200px]">{{ $product->name }}</li>
                </ol>
            </nav>

            {{-- Product detail --}}
            <div class="lg:grid lg:grid-cols-2 lg:gap-12">

                {{-- Gallery --}}
                <div x-data="{ activeImage: 0 }">
                    {{-- Main image --}}
                    <div class="aspect-square bg-white rounded-2xl border border-gray-100 overflow-hidden mb-4">
                        @if ($product->image || $product->images->isNotEmpty())
                            @php
                                $allImages = collect();
                                if ($product->image) $allImages->push($product->image_url);
                                foreach ($product->images as $img) { $allImages->push($img->url()); }
                            @endphp
                            @foreach ($allImages as $i => $imgUrl)
                                <img
                                    x-show="activeImage === {{ $i }}"
                                    x-transition:enter="transition ease-out duration-300"
                                    x-transition:enter-start="opacity-0"
                                    x-transition:enter-end="opacity-100"
                                    src="{{ $imgUrl }}"
                                    alt="{{ $product->name }}{{ $i > 0 ? ' - imagen ' . ($i + 1) : '' }}"
                                    class="w-full h-full object-contain"
                                    @if($i === 0) fetchpriority="high" @else loading="lazy" @endif
                                >
                            @endforeach
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-24 h-24 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                    </div>

                    {{-- Thumbnails --}}
                    @if (isset($allImages) && $allImages->count() > 1)
                        <div class="flex gap-3 overflow-x-auto pb-2">
                            @foreach ($allImages as $i => $imgUrl)
                                <button
                                    type="button"
                                    @click="activeImage = {{ $i }}"
                                    :class="activeImage === {{ $i }} ? 'border-brand-500 ring-2 ring-brand-200' : 'border-gray-200 hover:border-gray-300'"
                                    class="w-16 h-16 rounded-xl border overflow-hidden shrink-0 transition-all"
                                >
                                    <img src="{{ $imgUrl }}" alt="" class="w-full h-full object-cover" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Info --}}
                <div class="mt-8 lg:mt-0">
                    <div class="mb-4">
                        <a href="{{ route('catalog', ['categoria' => $product->category->slug]) }}" class="inline-flex items-center gap-1.5 text-sm text-brand-700 font-medium hover:text-brand-700 transition-colors">
                            {{ $product->category->name }}
                        </a>
                    </div>

                    <h1 class="font-display text-3xl lg:text-4xl text-gray-900 mb-4">{{ $product->name }}</h1>

                    @if ($product->sku)
                        <p class="text-xs text-gray-500 mb-4">SKU: {{ $product->sku }}</p>
                    @endif

                    <p class="font-display text-3xl text-brand-700 mb-6">{{ $product->formattedPrice() }}</p>

                    {{-- Stock status --}}
                    <div class="flex items-center gap-2 mb-8">
                        @if ($product->hasStock())
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="text-sm text-emerald-600 font-medium">En stock</span>
                        @else
                            <span class="w-2.5 h-2.5 rounded-full bg-red-400"></span>
                            <span class="text-sm text-red-600 font-medium">Agotado</span>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if ($product->description)
                        <div class="prose prose-sm prose-gray max-w-none mb-8">
                            {!! nl2br(e($product->description)) !!}
                        </div>
                    @endif

                    {{-- Delivery info --}}
                    <div class="bg-brand-50/50 rounded-2xl p-6 border border-brand-100/50">
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Informaci&oacute;n de entrega</h3>
                        <ul class="space-y-3">
                            <li class="flex items-start gap-3 text-sm text-gray-600">
                                <svg class="w-5 h-5 text-brand-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                Reparto propio en toda Ibiza (07800–07849)
                            </li>
                            <li class="flex items-start gap-3 text-sm text-gray-600">
                                <svg class="w-5 h-5 text-brand-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Entrega en 24–48 horas
                            </li>
                            <li class="flex items-start gap-3 text-sm text-gray-600">
                                <svg class="w-5 h-5 text-brand-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <a href="{{ route('delivery') }}" class="text-brand-700 hover:text-brand-700 underline">Consulta tu zona de reparto</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Related products --}}
            @if ($related->isNotEmpty())
                <div class="mt-20">
                    <h2 class="font-display text-2xl text-gray-900 mb-8">Tambi&eacute;n te puede interesar</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 lg:gap-6">
                        @foreach ($related as $item)
                            <a href="{{ route('product', $item) }}" class="group bg-white rounded-2xl border border-gray-100 overflow-hidden hover:border-brand-200 hover:shadow-lg hover:shadow-brand-50/50 transition-all duration-300">
                                <div class="aspect-[4/3] bg-gray-50 overflow-hidden">
                                    @if ($item->image)
                                        <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="p-4">
                                    <h3 class="font-medium text-gray-900 text-sm leading-snug mb-1 group-hover:text-brand-700 transition-colors line-clamp-2">{{ $item->name }}</h3>
                                    <p class="font-display text-base text-gray-900">{{ $item->formattedPrice() }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </section>

    {{-- JSON-LD Product --}}
    @php
        $jsonImages = collect();
        if ($product->image) $jsonImages->push($product->image_url);
        foreach ($product->images as $img) { $jsonImages->push($img->url()); }
    @endphp
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "Product",
        "name": "{{ e($product->name) }}",
        "description": "{{ e(Str::limit(strip_tags($product->description), 200)) }}",
        "image": {!! $jsonImages->isNotEmpty() ? $jsonImages->toJson() : '[]' !!},
        "sku": "{{ e($product->sku ?? '') }}",
        "category": "{{ e($product->category->name) }}",
        "brand": {
            "@type": "Brand",
            "name": "Barco de Papel"
        },
        "offers": {
            "@type": "Offer",
            "url": "{{ route('product', $product) }}",
            "priceCurrency": "EUR",
            "price": "{{ $product->price }}",
            "availability": "{{ $product->hasStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
            "seller": {
                "@type": "BookStore",
                "name": "Barco de Papel"
            }
        }
    }
    </script>

</x-layouts.store>
