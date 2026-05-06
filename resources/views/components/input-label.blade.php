@props(['for' => null, 'required' => false])

<label
    {{ $attributes->merge(['class' => 'form-label']) }}
    @if($for) for="{{ $for }}" @endif
>
    {{ $slot }}
    @if($required)
        <span class="text-red-500 ml-0.5" aria-hidden="true">*</span>
    @endif
</label>
