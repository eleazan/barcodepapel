@props(['id' => null, 'name' => null, 'checked' => false])

<input
    type="checkbox"
    @if($checked) checked @endif
    {{ $attributes->merge([
        'id'    => $id ?? $name,
        'name'  => $name ?? $id,
        'class' => 'rounded border-gray-300 text-brand-600 shadow-sm focus:ring-brand-500 h-4 w-4',
    ]) }}
/>
