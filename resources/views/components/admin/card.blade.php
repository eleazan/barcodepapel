@props(['title' => null, 'padding' => true])

<div {{ $attributes->merge(['class' => 'bg-white rounded-2xl border border-sky-100 shadow-sm']) }}>
    @if ($title)
        <div class="px-6 py-4 border-b border-sky-50">
            <h3 class="text-sm font-semibold text-gray-700">{{ $title }}</h3>
        </div>
    @endif
    <div @class(['px-6 py-5' => $padding, '' => ! $padding])>
        {{ $slot }}
    </div>
</div>
