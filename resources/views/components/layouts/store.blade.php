@props(['title' => null, 'description' => null, 'canonical' => null, 'noindex' => false])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#00b5b5">

    {{-- SEO Meta --}}
    <title>{{ isset($title) ? $title . ' — Barco de Papel' : 'Barco de Papel — Librería en Ibiza con reparto a domicilio' }}</title>
    <meta name="description" content="{{ $description ?? 'Barco de Papel, tu librería en Ibiza. Libros, cuadernos, material escolar, arte, oficina y mochilas. Reparto a domicilio en toda la isla (07800–07849).' }}">
    <meta name="keywords" content="librería Ibiza, papelería Eivissa, libros Ibiza, material escolar, cuadernos, mochilas, reparto domicilio Ibiza">
    <meta name="author" content="Barco de Papel">
    <meta name="robots" content="{{ $noindex ? 'noindex, follow' : 'index, follow' }}">
    <link rel="canonical" href="{{ $canonical ?? url()->current() }}">
    <link rel="alternate" hreflang="es-ES" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">

    {{-- Geo --}}
    <meta name="geo.region" content="ES-IB">
    <meta name="geo.placename" content="Ibiza, Islas Baleares">
    <meta name="geo.position" content="38.9067;1.4206">

    {{-- Open Graph --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="es_ES">
    <meta property="og:site_name" content="Barco de Papel">
    <meta property="og:title" content="{{ $title ?? 'Barco de Papel — Librería en Ibiza' }}">
    <meta property="og:description" content="{{ $description ?? 'Libros, papelería y material escolar con reparto a domicilio en Ibiza.' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('og-image.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Barco de Papel — Librería y papelería en Ibiza">

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'Barco de Papel — Librería en Ibiza' }}">
    <meta name="twitter:description" content="{{ $description ?? 'Libros, papelería y material escolar con reparto a domicilio en Ibiza.' }}">
    <meta name="twitter:image" content="{{ asset('og-image.jpg') }}">

    {{-- Favicon --}}
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">

    {{-- Fonts (non-blocking) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;500;600;700;1,9..40,400&family=DM+Serif+Display&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;500;600;700;1,9..40,400&family=DM+Serif+Display&display=swap" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;500;600;700;1,9..40,400&family=DM+Serif+Display&display=swap"></noscript>

    {{-- Styles & Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- JSON-LD Structured Data --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@type": ["BookStore", "LocalBusiness"],
        "@id": "{{ url('/') }}#business",
        "name": "Barco de Papel",
        "description": "Librería y papelería en Ibiza con catálogo de libros, cuadernos, material escolar, arte, oficina y mochilas. Reparto a domicilio en toda la isla.",
        "url": "{{ url('/') }}",
        "image": "{{ asset('og-image.jpg') }}",
        "telephone": "+34 971 000 000",
        "email": "info@barcodepapel.es",
        "address": {
            "@type": "PostalAddress",
            "streetAddress": "Carrer de l'Exemple, 1",
            "addressLocality": "Eivissa",
            "addressRegion": "Illes Balears",
            "postalCode": "07800",
            "addressCountry": "ES"
        },
        "geo": {
            "@type": "GeoCoordinates",
            "latitude": 38.9067,
            "longitude": 1.4206
        },
        "areaServed": {
            "@type": "Place",
            "name": "Ibiza (Eivissa)",
            "geo": {
                "@type": "GeoShape",
                "postalCode": ["07800","07801","07802","07810","07811","07812","07813","07814","07815","07816","07817","07818","07819","07820","07829","07830","07839","07840","07849"]
            }
        },
        "priceRange": "€€",
        "currenciesAccepted": "EUR",
        "paymentAccepted": "Efectivo, Tarjeta",
        "openingHoursSpecification": [
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": ["Monday","Tuesday","Wednesday","Thursday","Friday"],
                "opens": "09:30",
                "closes": "20:00"
            },
            {
                "@type": "OpeningHoursSpecification",
                "dayOfWeek": "Saturday",
                "opens": "10:00",
                "closes": "14:00"
            }
        ],
        "hasOfferCatalog": {
            "@type": "OfferCatalog",
            "name": "Catálogo de libros y papelería",
            "url": "{{ route('catalog') }}"
        }
    }
    </script>

    @stack('head')
</head>
<body class="min-h-screen flex flex-col bg-[#faf8f5] text-gray-800 font-sans antialiased">

    {{-- ==================== HEADER ==================== --}}
    <header
        x-data="{ mobileOpen: false, scrolled: false }"
        x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 20)"
        :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-sm' : 'bg-transparent'"
        class="fixed top-0 inset-x-0 z-50 transition-all duration-300"
    >
        {{-- Topbar accent --}}
        <div class="h-0.5 bg-gradient-to-r from-brand-400 via-brand-600 to-brand-400"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <nav class="flex items-center justify-between h-16 lg:h-20" aria-label="Navegaci&oacute;n principal">

                {{-- Logo --}}
                <a href="{{ url('/') }}" class="flex items-center group" aria-label="Barco de Papel — Inicio">
                    <img src="{{ asset('assets/logo-barco-papel.png') }}" alt="Barco de Papel" class="h-12 lg:h-14 w-auto transition-transform duration-300 group-hover:scale-105">
                </a>

                {{-- Desktop nav --}}
                <div class="hidden lg:flex items-center gap-1">
                    <a href="{{ route('home') }}" class="store-nav-link {{ request()->routeIs('home') ? 'store-nav-active' : '' }}">Inicio</a>
                    <a href="{{ route('catalog') }}" class="store-nav-link {{ request()->routeIs('catalog', 'product') ? 'store-nav-active' : '' }}">Cat&aacute;logo</a>
                    <a href="{{ route('delivery') }}" class="store-nav-link {{ request()->routeIs('delivery') ? 'store-nav-active' : '' }}">Reparto</a>
                    <a href="{{ route('blog.index') }}" class="store-nav-link {{ request()->routeIs('blog.*') ? 'store-nav-active' : '' }}">Blog</a>
                    <a href="{{ route('contact') }}" class="store-nav-link {{ request()->routeIs('contact') ? 'store-nav-active' : '' }}">Contacto</a>
                </div>

                {{-- Desktop CTA --}}
                <div class="hidden lg:flex items-center gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="store-nav-link">Mi cuenta</a>
                    @endauth
                    @guest
                        <a href="{{ route('login') }}" class="store-nav-link">Iniciar sesi&oacute;n</a>
                    @endguest
                    <x-store.cart-badge />
                    <a href="{{ route('catalog') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-brand-600 text-white text-sm font-medium rounded-full hover:bg-brand-700 transition-all duration-200 hover:shadow-lg hover:shadow-brand-600/25">
                        Ver productos
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>

                {{-- Mobile: carrito + hamburguesa --}}
                <div class="flex items-center gap-1 lg:hidden">
                    <x-store.cart-badge />
                </div>

                {{-- Mobile hamburger --}}
                <button
                    type="button"
                    @click="mobileOpen = !mobileOpen"
                    class="lg:hidden p-2 -mr-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100/80 transition-colors"
                    :aria-expanded="mobileOpen"
                    aria-label="Men&uacute; de navegaci&oacute;n"
                >
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </nav>
        </div>

        {{-- Mobile menu --}}
        <div
            x-show="mobileOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-2"
            @click.outside="mobileOpen = false"
            class="lg:hidden bg-white border-t border-gray-100 shadow-lg"
        >
            <div class="max-w-7xl mx-auto px-4 py-4 space-y-1">
                <a href="{{ route('home') }}" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-50 hover:text-brand-700 transition-colors">Inicio</a>
                <a href="{{ route('catalog') }}" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-50 hover:text-brand-700 transition-colors">Cat&aacute;logo</a>
                <a href="{{ route('delivery') }}" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-50 hover:text-brand-700 transition-colors">Reparto</a>
                <a href="{{ route('blog.index') }}" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-50 hover:text-brand-700 transition-colors">Blog</a>
                <a href="{{ route('contact') }}" @click="mobileOpen = false" class="block px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-50 hover:text-brand-700 transition-colors">Contacto</a>
                <x-store.cart-badge mobile />
                <div class="pt-3 border-t border-gray-100">
                    @auth
                        <a href="{{ route('dashboard') }}" class="block px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-50 hover:text-brand-700 transition-colors">Mi cuenta</a>
                    @endauth
                    @guest
                        <a href="{{ route('login') }}" class="block px-4 py-3 rounded-xl text-gray-700 font-medium hover:bg-brand-50 hover:text-brand-700 transition-colors">Iniciar sesi&oacute;n</a>
                    @endguest
                    <a href="{{ route('catalog') }}" @click="mobileOpen = false" class="block mt-2 px-4 py-3 bg-brand-600 text-white text-center font-medium rounded-xl hover:bg-brand-700 transition-colors">Ver productos</a>
                </div>
            </div>
        </div>
    </header>

    {{-- ==================== MAIN CONTENT ==================== --}}
    <main class="flex-1">
        <x-store.flash />
        {{ $slot }}
    </main>

    {{-- ==================== FOOTER ==================== --}}
    <footer id="contacto" class="bg-gray-900 text-gray-300 mt-auto">
        {{-- Wave separator --}}
        <div class="bg-[#faf8f5]">
            <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-auto" preserveAspectRatio="none">
                <path d="M0 60L48 54C96 48 192 36 288 30C384 24 480 24 576 28C672 32 768 40 864 42C960 44 1056 40 1152 36C1248 32 1344 28 1392 26L1440 24V60H0Z" fill="#111827"/>
            </svg>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 lg:gap-8">

                {{-- Brand --}}
                <div class="lg:col-span-1">
                    <a href="{{ url('/') }}" class="flex items-center mb-4">
                        <img src="{{ asset('assets/logo-barco-papel.png') }}" alt="Barco de Papel" class="h-14 w-auto brightness-0 invert">
                    </a>
                    <p class="text-sm text-gray-300 leading-relaxed">
                        Tu librer&iacute;a de confianza en Ibiza. Libros, papeler&iacute;a y material escolar con reparto a domicilio en toda la isla.
                    </p>
                </div>

                {{-- Quick links --}}
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Navegaci&oacute;n</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('home') }}" class="text-sm text-gray-300 hover:text-brand-400 transition-colors">Inicio</a></li>
                        <li><a href="{{ route('catalog') }}" class="text-sm text-gray-300 hover:text-brand-400 transition-colors">Cat&aacute;logo</a></li>
                        <li><a href="{{ route('delivery') }}" class="text-sm text-gray-300 hover:text-brand-400 transition-colors">Zonas de reparto</a></li>
                        <li><a href="{{ route('blog.index') }}" class="text-sm text-gray-300 hover:text-brand-400 transition-colors">Blog</a></li>
                        <li><a href="{{ route('contact') }}" class="text-sm text-gray-300 hover:text-brand-400 transition-colors">Contacto</a></li>
                    </ul>
                </div>

                {{-- Delivery info --}}
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Reparto</h3>
                    <ul class="space-y-3 text-sm text-gray-300">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Toda la isla de Ibiza
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            C&oacute;digos postales 07800–07849
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Entrega en 24–48h
                        </li>
                    </ul>
                </div>

                {{-- Contact --}}
                <div>
                    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Contacto</h3>
                    <ul class="space-y-3 text-sm text-gray-300">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-brand-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            Carrer de l'Exemple, 1<br>07800 Eivissa
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            +34 971 000 000
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-brand-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            info@barcodepapel.es
                        </li>
                    </ul>

                    {{-- Horario --}}
                    <div class="mt-5 pt-4 border-t border-gray-800">
                        <p class="text-xs text-gray-300 uppercase tracking-wider mb-2 font-medium">Horario</p>
                        <p class="text-sm text-gray-300">Lun–Vie: 09:30–20:00</p>
                        <p class="text-sm text-gray-300">S&aacute;bado: 10:00–14:00</p>
                    </div>
                </div>
            </div>

            {{-- Bottom bar --}}
            <div class="mt-12 pt-8 border-t border-gray-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-gray-300">&copy; {{ date('Y') }} Barco de Papel. Todos los derechos reservados.</p>
                <p class="text-xs text-gray-300">Hecho con amor en Ibiza</p>
            </div>
        </div>
    </footer>

    <x-cookie-consent />

    <style>
        [x-cloak] { display: none !important; }
    </style>
</body>
</html>
