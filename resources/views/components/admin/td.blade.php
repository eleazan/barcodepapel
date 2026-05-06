@props(['align' => 'left'])

<td {{ $attributes->merge(['class' => "px-6 py-4 text-{$align}"]) }}>
    {{ $slot }}
</td>
