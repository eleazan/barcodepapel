<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="{{ $description ?? config('app.name') }}">

    <title>{{ isset($title) ? $title . ' — ' . config('app.name') : config('app.name') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-brand-50 via-white to-slate-50 flex flex-col">

    <div class="flex-1 flex flex-col items-center justify-center p-4 py-12">

        <!-- Logo -->
        <a href="{{ url('/') }}" class="flex items-center mb-8">
            <img src="{{ asset('assets/logo-barco-papel.png') }}" alt="{{ config('app.name') }}" class="h-24 w-auto">
        </a>

        <!-- Card slot -->
        {{ $slot }}

    </div>

    <!-- Footer -->
    <footer class="py-4 text-center">
        <p class="text-xs text-gray-400">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.
        </p>
    </footer>

</body>
</html>
