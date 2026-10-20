@props(['product'])

<article class="group flex flex-col min-w-0">
    <a href="{{ route('product', $product) }}" class="block aspect-[2/3] rounded-xl bg-gray-50 border border-gray-100 overflow-hidden relative group-hover:border-brand-200 group-hover:shadow-lg group-hover:shadow-brand-50/60 transition-all duration-300 group-hover:-translate-y-1">
        @if ($product->image)
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}" width="300" height="450" class="w-full h-full object-contain p-2" loading="lazy">
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-brand-50 to-brand-100 p-4 text-center">
                <span class="font-display text-brand-800 leading-tight line-clamp-5">{{ $product->name }}</span>
            </div>
        @endif
    </a>

    <p class="mt-3 text-xs text-brand-700 font-medium truncate">{{ $product->category->name }}</p>
    <h3 class="mt-0.5 text-sm font-medium text-gray-900 leading-snug line-clamp-2">
        <a href="{{ route('product', $product) }}" class="hover:text-brand-700 transition-colors">{{ $product->name }}</a>
    </h3>

    <div class="mt-auto pt-3 flex items-center justify-between gap-2">
        <span class="font-display text-lg text-gray-900 tabular-nums">{{ $product->formattedPrice() }}</span>
        <form method="POST" action="{{ route('cart.add', $product) }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center px-3.5 py-1.5 border border-brand-600 text-brand-700 text-sm font-medium rounded-full hover:bg-brand-600 hover:text-white transition-colors"
                    aria-label="Añadir {{ $product->name }} al carrito">
                Añadir
            </button>
        </form>
    </div>
</article>
