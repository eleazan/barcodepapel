<x-layouts.store :title="$post->title" :description="$post->excerpt ?: Str::limit(strip_tags($post->body), 155)" :canonical="route('blog.show', $post)">

    @push('head')
    {{-- BreadcrumbList --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "BreadcrumbList",
        "itemListElement": [
            {"@type": "ListItem", "position": 1, "name": "Inicio", "item": "{{ route('home') }}"},
            {"@type": "ListItem", "position": 2, "name": "Blog", "item": "{{ route('blog.index') }}"},
            {"@type": "ListItem", "position": 3, "name": "{{ e($post->title) }}", "item": "{{ route('blog.show', $post) }}"}
        ]
    }
    </script>

    {{-- Article --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": "BlogPosting",
        "headline": "{{ e($post->title) }}",
        "description": "{{ e($post->excerpt ?: Str::limit(strip_tags($post->body), 200)) }}",
        @if ($post->image)
        "image": "{{ Storage::url($post->image) }}",
        @endif
        "datePublished": "{{ $post->published_at->toW3cString() }}",
        "dateModified": "{{ $post->updated_at->toW3cString() }}",
        "author": {
            "@type": "Person",
            "name": "{{ e($post->author->name) }}"
        },
        "publisher": {
            "@type": "BookStore",
            "name": "Barco de Papel",
            "url": "{{ url('/') }}"
        },
        "mainEntityOfPage": "{{ route('blog.show', $post) }}",
        "wordCount": {{ str_word_count(strip_tags($post->body)) }}
    }
    </script>

    {{-- OG Article --}}
    <meta property="og:type" content="article">
    <meta property="article:published_time" content="{{ $post->published_at->toW3cString() }}">
    <meta property="article:modified_time" content="{{ $post->updated_at->toW3cString() }}">
    <meta property="article:author" content="{{ $post->author->name }}">
    @if ($post->image)
    <meta property="og:image" content="{{ Storage::url($post->image) }}">
    @endif
    @endpush

    <article class="pt-24 lg:pt-32 pb-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav aria-label="Breadcrumb" class="mb-8">
                <ol class="flex items-center gap-2 text-sm text-gray-500">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-700 transition-colors">Inicio</a></li>
                    <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                    <li><a href="{{ route('blog.index') }}" class="hover:text-brand-700 transition-colors">Blog</a></li>
                    <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                    <li class="text-gray-600 font-medium truncate max-w-[200px]">{{ $post->title }}</li>
                </ol>
            </nav>

            {{-- Header --}}
            <header class="mb-10">
                <div class="flex items-center gap-3 text-sm text-gray-500 mb-4">
                    <time datetime="{{ $post->published_at->toDateString() }}">{{ $post->formattedDate() }}</time>
                    <span>&middot;</span>
                    <span>{{ $post->readingTime() }} min de lectura</span>
                    <span>&middot;</span>
                    <span>Por {{ $post->author->name }}</span>
                </div>
                <h1 class="font-display text-3xl sm:text-4xl lg:text-5xl text-gray-900 leading-tight">{{ $post->title }}</h1>
                @if ($post->excerpt)
                    <p class="text-xl text-gray-500 mt-4 leading-relaxed">{{ $post->excerpt }}</p>
                @endif
            </header>

            {{-- Featured image --}}
            @if ($post->image)
                <figure class="mb-10 -mx-4 sm:mx-0">
                    <img src="{{ Storage::url($post->image) }}" alt="{{ $post->title }}" class="w-full rounded-none sm:rounded-2xl aspect-[2/1] object-cover" fetchpriority="high">
                </figure>
            @endif

            {{-- Body --}}
            <div class="prose prose-lg prose-gray max-w-none
                        prose-headings:font-display prose-headings:text-gray-900
                        prose-a:text-brand-700 prose-a:no-underline hover:prose-a:underline
                        prose-img:rounded-xl">
                {!! $post->body !!}
            </div>

            {{-- Share --}}
            <div class="mt-12 pt-8 border-t border-gray-100">
                <p class="text-sm font-medium text-gray-900 mb-3">Compartir</p>
                <div class="flex items-center gap-3">
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('blog.show', $post)) }}&text={{ urlencode($post->title) }}"
                       target="_blank" rel="noopener noreferrer"
                       class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center hover:bg-brand-50 hover:text-brand-700 transition-colors"
                       aria-label="Compartir en Twitter">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('blog.show', $post)) }}"
                       target="_blank" rel="noopener noreferrer"
                       class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center hover:bg-brand-50 hover:text-brand-700 transition-colors"
                       aria-label="Compartir en Facebook">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    </a>
                    <a href="https://wa.me/?text={{ urlencode($post->title . ' ' . route('blog.show', $post)) }}"
                       target="_blank" rel="noopener noreferrer"
                       class="w-10 h-10 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center hover:bg-brand-50 hover:text-brand-700 transition-colors"
                       aria-label="Compartir en WhatsApp">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </article>

    {{-- Related posts --}}
    @if ($related->isNotEmpty())
        <section class="py-16 bg-white border-t border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="font-display text-2xl text-gray-900 mb-8">M&aacute;s art&iacute;culos</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($related as $item)
                        <a href="{{ route('blog.show', $item) }}" class="group bg-white rounded-2xl border border-gray-100 overflow-hidden hover:border-brand-200 hover:shadow-lg transition-all duration-300">
                            <div class="aspect-[16/9] bg-gray-50 overflow-hidden">
                                @if ($item->image)
                                    <img src="{{ Storage::url($item->image) }}" alt="{{ $item->title }}" width="400" height="225" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100">
                                        <svg class="w-10 h-10 text-brand-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                                    </div>
                                @endif
                            </div>
                            <div class="p-5">
                                <time class="text-xs text-gray-500" datetime="{{ $item->published_at->toDateString() }}">{{ $item->formattedDate() }}</time>
                                <h3 class="font-display text-base text-gray-900 mt-1 group-hover:text-brand-700 transition-colors line-clamp-2">{{ $item->title }}</h3>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

</x-layouts.store>
