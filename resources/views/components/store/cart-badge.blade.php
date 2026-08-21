
@if ($mobile)
    <a href="{{ route('cart.index') }}" class="flex items-center justify-between px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-50 hover:text-brand-700 transition-colors">
        <span class="flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            Mi carrito
        </span>
        @if ($unidades > 0)
            <span class="inline-flex items-center justify-center min-w-[1.5rem] h-6 px-1.5 bg-brand-600 text-white text-xs font-semibold rounded-full">{{ $unidades }}</span>
        @endif
    </a>
@else
    <a
        href="{{ route('cart.index') }}"
        class="relative p-2.5 rounded-full text-gray-600 hover:text-brand-700 hover:bg-brand-50 transition-colors"
        aria-label="Mi carrito{{ $unidades > 0 ? ' (' . $unidades . ' art&iacute;culos)' : ' (vac&iacute;o)' }}"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        @if ($unidades > 0)
            <span class="absolute -top-0.5 -right-0.5 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 bg-brand-600 text-white text-[11px] font-semibold rounded-full ring-2 ring-white">{{ $unidades > 99 ? '99+' : $unidades }}</span>
        @endif
    </a>
@endif
