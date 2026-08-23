<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ isset($title) ? $title . ' — Admin' : 'Admin' }} — {{ config('app.name') }}</title>

    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;500;600;700&family=DM+Serif+Display&display=swap">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-brand-50/30 font-sans antialiased" x-data="{ sidebarOpen: false }">

    {{-- Mobile overlay --}}
    <div
        x-show="sidebarOpen"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="sidebarOpen = false"
        class="fixed inset-0 z-40 bg-gray-900/40 backdrop-blur-sm lg:hidden"
        style="display: none;"
    ></div>

    {{-- Sidebar: siempre fixed — en desktop siempre visible vía lg:translate-x-0 --}}
    <aside
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-brand-100
               shadow-lg lg:shadow-none
               transform transition-transform duration-200 ease-in-out
               lg:translate-x-0 flex flex-col"
    >
        {{-- Acento teal superior --}}
        <div class="h-0.5 bg-gradient-to-r from-brand-300 via-brand-500 to-brand-300 shrink-0"></div>

        {{-- Logo --}}
        <div class="flex items-center px-5 h-16 border-b border-brand-50 shrink-0">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center">
                <img src="{{ asset('assets/logo-barco-papel.png') }}" alt="Barco de Papel" class="h-11 w-auto">
            </a>
        </div>

        {{-- Navegación --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
            <x-admin.nav-link href="{{ route('admin.dashboard') }}" :active="request()->routeIs('admin.dashboard')">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </x-slot:icon>
                Dashboard
            </x-admin.nav-link>

            <x-admin.nav-link href="{{ route('admin.categories.index') }}" :active="request()->routeIs('admin.categories.*')">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                </x-slot:icon>
                Categorías
            </x-admin.nav-link>

            <x-admin.nav-link href="{{ route('admin.products.index') }}" :active="request()->routeIs('admin.products.*')">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </x-slot:icon>
                Productos
            </x-admin.nav-link>

            <x-admin.nav-link href="{{ route('admin.orders.index') }}" :active="request()->routeIs('admin.orders.*')">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </x-slot:icon>
                Pedidos
            </x-admin.nav-link>

            <x-admin.nav-link href="{{ route('admin.delivery-zones.index') }}" :active="request()->routeIs('admin.delivery-zones.*')">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </x-slot:icon>
                Códigos Postales
            </x-admin.nav-link>

            <x-admin.nav-link href="{{ route('admin.customers.index') }}" :active="request()->routeIs('admin.customers.*')">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </x-slot:icon>
                Clientes
            </x-admin.nav-link>

            <x-admin.nav-link href="{{ route('admin.non-working-days.index') }}" :active="request()->routeIs('admin.non-working-days.*')">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </x-slot:icon>
                Días sin reparto
            </x-admin.nav-link>

            <x-admin.nav-link href="{{ route('admin.posts.index') }}" :active="request()->routeIs('admin.posts.*')">
                <x-slot:icon>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                </x-slot:icon>
                Blog
            </x-admin.nav-link>

            {{-- Herramientas --}}
            <div class="pt-3 mt-3 border-t border-brand-100">
                <p class="px-3 pb-1 text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Herramientas</p>

                <x-admin.nav-link href="{{ route('admin.jobs.index') }}" :active="request()->routeIs('admin.jobs.*')">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </x-slot:icon>
                    Tareas
                </x-admin.nav-link>

                <x-admin.nav-link href="{{ route('admin.verial.index') }}" :active="request()->routeIs('admin.verial.*')">
                    <x-slot:icon>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"/></svg>
                    </x-slot:icon>
                    Conector Verial
                </x-admin.nav-link>
            </div>
        </nav>

        {{-- Usuario --}}
        <div class="px-4 py-3 border-t border-brand-100 bg-brand-50/50 shrink-0">
            <div class="flex items-center gap-3">
                <span class="w-8 h-8 rounded-full bg-brand-500 text-white flex items-center justify-center text-sm font-semibold shrink-0">
                    {{ auth()->user()->initials() }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 transition-colors"
                            title="Cerrar sesión">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Contenido principal: margen izquierdo en desktop para ceder espacio al sidebar --}}
    <div class="lg:ml-64 min-h-screen flex flex-col">

        {{-- Línea de acento (misma altura que la del sidebar para alinear topbar con logo) --}}
        <div class="h-0.5 bg-gradient-to-r from-brand-100 via-brand-200 to-brand-100 shrink-0"></div>

        {{-- Topbar --}}
        <header class="sticky top-0 z-30 bg-white/90 backdrop-blur-md border-b border-brand-100 h-16 flex items-center gap-3 px-4 sm:px-6 lg:px-8 shrink-0">
            {{-- Botón hamburguesa (solo móvil) --}}
            <button
                @click="sidebarOpen = true"
                class="lg:hidden p-2 -ml-1 text-gray-500 hover:text-gray-700 hover:bg-brand-50 rounded-lg transition-colors"
                aria-label="Abrir menú"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            {{-- Título de página --}}
            <h1 class="text-base font-semibold text-gray-800 truncate">{{ $title ?? 'Admin' }}</h1>

            {{-- Acciones opcionales --}}
            <div class="ml-auto flex items-center gap-2">
                {{ $actions ?? '' }}
            </div>
        </header>

        {{-- Flash: éxito --}}
        @if (session('success'))
            <div x-data="{ show: true }" x-show="show"
                 x-init="setTimeout(() => show = false, 4000)"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                <div class="flex items-center gap-3 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        {{-- Flash: error --}}
        @if (session('error'))
            <div x-data="{ show: true }" x-show="show"
                 class="mx-4 sm:mx-6 lg:mx-8 mt-4">
                <div class="flex items-center gap-3 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="flex-1">{{ session('error') }}</span>
                    <button @click="show = false" class="text-red-400 hover:text-red-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        @endif

        {{-- Contenido --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            {{ $slot }}
        </main>
    </div>

</body>
</html>
