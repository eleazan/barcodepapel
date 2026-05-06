@props(['label', 'value', 'color' => 'sky'])

@php
    $colorClasses = [
        'sky' => 'bg-sky-50 text-sky-600',
        'green' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'violet' => 'bg-violet-50 text-violet-600',
    ];
    $iconColor = $colorClasses[$color] ?? $colorClasses['sky'];
@endphp

<div class="bg-white rounded-2xl border border-sky-100 shadow-sm px-5 py-4 flex items-center gap-4">
    <div class="w-11 h-11 rounded-xl {{ $iconColor }} flex items-center justify-center shrink-0">
        {{ $icon ?? '' }}
    </div>
    <div>
        <p class="text-2xl font-bold text-gray-800 tracking-tight">{{ $value }}</p>
        <p class="text-xs text-gray-400 font-medium mt-0.5">{{ $label }}</p>
    </div>
</div>
