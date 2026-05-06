<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>403 — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-sky-50/40 flex items-center justify-center p-6">

    <div class="text-center max-w-md">
        {{-- Lock icon --}}
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-sky-100 flex items-center justify-center">
            <svg class="w-10 h-10 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
        </div>

        <p class="text-sky-300 text-7xl font-bold tracking-tight">403</p>

        <h1 class="mt-4 text-xl font-semibold text-gray-800">Acceso restringido</h1>

        <p class="mt-2 text-sm text-gray-400 leading-relaxed">
            {{ $exception->getMessage() ?: 'No tienes permisos para acceder a esta página.' }}
        </p>

        <div class="mt-8 flex items-center justify-center gap-3">
            <a href="{{ route('dashboard') }}" class="btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Ir al inicio
            </a>
            <a href="javascript:history.back()" class="btn-secondary">Volver</a>
        </div>
    </div>

</body>
</html>
