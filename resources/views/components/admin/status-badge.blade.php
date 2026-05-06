@props(['status'])

@php
    $colors = [
        'yellow' => 'bg-amber-50 text-amber-700 ring-amber-200',
        'blue' => 'bg-sky-50 text-sky-700 ring-sky-200',
        'purple' => 'bg-violet-50 text-violet-700 ring-violet-200',
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
        'gray' => 'bg-gray-50 text-gray-700 ring-gray-200',
    ];

    $statusLabels = \App\Models\Order::STATUSES;
    $statusColors = \App\Models\Order::STATUS_COLORS;

    $color = $statusColors[$status] ?? 'gray';
    $label = $statusLabels[$status] ?? $status;
    $classes = $colors[$color] ?? $colors['gray'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium ring-1 ring-inset {$classes}"]) }}>
    <span class="w-1.5 h-1.5 rounded-full bg-current opacity-60"></span>
    {{ $label }}
</span>
