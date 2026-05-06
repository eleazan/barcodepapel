@props(['href', 'active' => false])

<a href="{{ $href }}"
   @class([
       'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150',
       'bg-brand-50 text-brand-700 shadow-sm' => $active,
       'text-gray-500 hover:text-gray-700 hover:bg-brand-50/60' => ! $active,
   ])
>
    <span @class(['shrink-0', 'text-brand-500' => $active, 'text-gray-400' => ! $active])>
        {{ $icon }}
    </span>
    {{ $slot }}
</a>
