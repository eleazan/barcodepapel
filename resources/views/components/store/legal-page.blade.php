@props([
    'titulo',
    'descripcion' => null,
])

@php
    $actualizado = config('tienda.legal.actualizado');
@endphp

<x-layouts.store :title="$titulo" :description="$descripcion">

    <section class="pt-24 lg:pt-32 pb-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Breadcrumb --}}
            <nav aria-label="Breadcrumb" class="mb-6">
                <ol class="flex items-center gap-2 text-sm text-gray-500">
                    <li><a href="{{ route('home') }}" class="hover:text-brand-700 transition-colors">Inicio</a></li>
                    <li><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></li>
                    <li class="text-gray-600 font-medium">{{ $titulo }}</li>
                </ol>
            </nav>

            <h1 class="font-display text-3xl lg:text-4xl text-gray-900">{{ $titulo }}</h1>

            @if ($actualizado)
                <p class="mt-3 text-sm text-gray-500">
                    &Uacute;ltima actualizaci&oacute;n: {{ \Illuminate\Support\Carbon::parse($actualizado)->translatedFormat('j \d\e F \d\e Y') }}
                </p>
            @endif

            <div class="mt-10 prose prose-sm sm:prose-base max-w-none prose-headings:font-display prose-headings:text-gray-900 prose-a:text-brand-700 prose-strong:text-gray-900 text-gray-600">
                {{ $slot }}
            </div>
        </div>
    </section>

</x-layouts.store>
