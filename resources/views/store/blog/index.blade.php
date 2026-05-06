<x-layouts.store title="Blog" description="Novedades, recomendaciones de lectura y consejos desde Barco de Papel, tu librería en Ibiza.">

    @push('head')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ route('home') }}"},
            {"@type": "ListItem", "position": 2, "name": "Blog", "item": "{{ route('blog.index') }}"}
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
                    <li class="text-gray-600 font-medium">Blog</li>
                </ol>
            </nav>

            {{-- Header --}}
            <div class="max-w-3xl mb-12">
                <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl text-gray-900 mb-4">Blog</h1>
                <p class="text-lg text-gray-500">Novedades, recomendaciones de lectura y noticias desde nuestra librer&iacute;a en Ibiza.</p>
            </div>

            @if ($posts->isEmpty())
                <div class="text-center py-20">
                    <svg class="w-16 h-16 mx-auto text-gray-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    <h2 class="font-display text-xl text-gray-500 mb-2">Pr&oacute;ximamente</h2>
                    <p class="text-sm text-gray-500">Estamos preparando contenido interesante. &iexcl;Vuelve pronto!</p>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    @foreach ($posts as $post)
                        <article class="group bg-white rounded-2xl border border-gray-100 overflow-hidden hover:border-brand-200 hover:shadow-lg hover:shadow-brand-50/50 transition-all duration-300 hover:-translate-y-0.5">
                            <a href="{{ route('blog.show', $post) }}" class="block">
                                {{-- Image --}}
                                <div class="aspect-[16/9] bg-gray-50 overflow-hidden">
                                    @if ($post->image)
                                        <img src="{{ Storage::url($post->image) }}" alt="{{ $post->title }}" width="600" height="338" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100">
                                            <svg class="w-12 h-12 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                        </div>
                                    @endif
                                </div>

                                {{-- Content --}}
                                <div class="p-5">
                                    <div class="flex items-center gap-3 text-xs text-gray-500 mb-3">
                                        <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->formattedDate() }}</time>
                                        <span>&middot;</span>
                                        <span>{{ $post->readingTime() }} min de lectura</span>
                                    </div>
                                    <h2 class="font-display text-lg text-gray-900 mb-2 group-hover:text-brand-700 transition-colors line-clamp-2">{{ $post->title }}</h2>
                                    @if ($post->excerpt)
                                        <p class="text-sm text-gray-500 line-clamp-3">{{ $post->excerpt }}</p>
                                    @endif
                                </div>
                            </a>
                        </article>
                    @endforeach
                </div>

                @if ($posts->hasPages())
                    <nav class="mt-10 flex items-center justify-center gap-1" aria-label="Paginaci&oacute;n del blog">
                        @if ($posts->onFirstPage())
                            <span class="px-3 py-2 text-sm text-gray-500 cursor-not-allowed">&laquo; Anterior</span>
                        @else
                            <a href="{{ $posts->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-600 hover:text-brand-700 transition-colors">&laquo; Anterior</a>
                        @endif

                        @foreach ($posts->getUrlRange(max(1, $posts->currentPage() - 2), min($posts->lastPage(), $posts->currentPage() + 2)) as $page => $url)
                            <a href="{{ $url }}"
                               class="w-10 h-10 flex items-center justify-center rounded-full text-sm font-medium transition-colors {{ $page === $posts->currentPage() ? 'bg-brand-600 text-white' : 'text-gray-600 hover:bg-brand-50 hover:text-brand-700' }}">
                                {{ $page }}
                            </a>
                        @endforeach

                        @if ($posts->hasMorePages())
                            <a href="{{ $posts->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-600 hover:text-brand-700 transition-colors">Siguiente &raquo;</a>
                        @else
                            <span class="px-3 py-2 text-sm text-gray-500 cursor-not-allowed">Siguiente &raquo;</span>
                        @endif
                    </nav>
                @endif
            @endif
        </div>
    </section>

</x-layouts.store>
