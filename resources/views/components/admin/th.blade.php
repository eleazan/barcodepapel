@props(['align' => 'left'])

<th {{ $attributes->merge(['class' => "px-6 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider text-{$align}"]) }}>
    {{ $slot }}
</th>
