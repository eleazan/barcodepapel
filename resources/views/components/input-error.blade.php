@props(['messages' => [], 'field' => null])

@php
    $messages = $field ? $errors->get($field) : (is_array($messages) ? $messages : [$messages]);
@endphp

@if(count($messages) > 0)
    <div {{ $attributes }}>
        @foreach ((array) $messages as $message)
            <p class="form-error">{{ $message }}</p>
        @endforeach
    </div>
@endif
