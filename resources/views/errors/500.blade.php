<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>500 — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-sky-50/40 flex items-center justify-center p-6">

    <div class="text-center max-w-md">
        <div class="w-20 h-20 mx-auto mb-6 rounded-2xl bg-red-50 flex items-center justify-center">
            <svg class="w-10 h-10 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <p class="text-red-300 text-7xl font-bold tracking-tight">500</p>

        <h1 class="mt-4 text-xl font-semibold text-gray-800">Error del servidor</h1>

        <p class="mt-2 text-sm text-gray-400 leading-relaxed">
            Algo salió mal. Estamos trabajando para solucionarlo.
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
