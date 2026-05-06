@props(['disabled' => false, 'id' => null, 'name' => null, 'type' => 'text'])

<input
    {{ $disabled ? 'disabled' : '' }}
    {!! $attributes->merge([
        'class'        => 'form-input' . ($errors->has($name ?? $id ?? '') ? ' border-red-300 focus:border-red-500 focus:ring-red-500' : ''),
        'id'           => $id ?? $name,
        'name'         => $name ?? $id,
        'type'         => $type,
        'autocomplete' => $type === 'password' ? 'current-password' : 'off',
    ]) !!}
/>
