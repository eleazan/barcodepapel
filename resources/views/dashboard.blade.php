<x-layouts.app title="Dashboard">

    <!-- Page header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Bienvenido, {{ $user->name }}. Aquí está el resumen de tu cuenta.
                </p>
            </div>
            <span class="badge-success text-sm px-3 py-1">
                Activo
            </span>
        </div>
    </div>

    <!-- Session status alerts -->
    @if(session('status'))
        <div class="alert-success mb-6" role="alert">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if(request('verified'))
        <div class="alert-success mb-6" role="alert">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>¡Tu correo electrónico ha sido verificado correctamente!</span>
        </div>
    @endif

    <!-- Stats cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

        <div class="card p-6 flex items-center gap-4">
            <div class="w-12 h-12 bg-brand-100 dark:bg-brand-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-brand-700 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cuenta</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">Activa</p>
            </div>
        </div>

        <div class="card p-6 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-700 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $user->isVerified() ? 'Verificado' : 'Pendiente' }}
                </p>
            </div>
        </div>

        <div class="card p-6 flex items-center gap-4">
            <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-purple-700 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Miembro desde</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $user->created_at->format('M Y') }}
                </p>
            </div>
        </div>

        <div class="card p-6 flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-orange-700 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Último acceso</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ now()->format('d M') }}
                </p>
            </div>
        </div>

    </div>

    <!-- Profile & Welcome section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Welcome card -->
        <div class="lg:col-span-2 card">
            <div class="card-header">
                <h2 class="font-semibold text-gray-900 dark:text-white">Bienvenido a {{ config('app.name') }}</h2>
            </div>
            <div class="card-body">
                <div class="prose prose-sm dark:prose-invert max-w-none">
                    <p class="text-gray-600 dark:text-gray-300">
                        Tu cuenta está activa y lista para usar. Este es tu panel de control donde podrás gestionar todos los aspectos de tu cuenta.
                    </p>

                    <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 text-brand-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Autenticación segura</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Login, registro y recuperación de contraseña configurados</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 text-brand-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Email verificado</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Verificación de correo electrónico activa</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 text-brand-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Alpine.js + Tailwind</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Frontend reactivo sin React ni Vue</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <svg class="w-5 h-5 text-brand-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                            </svg>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">Listo para Coolify</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Dockerfile y config de producción incluidos</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile card -->
        <div class="card">
            <div class="card-header">
                <h2 class="font-semibold text-gray-900 dark:text-white">Mi perfil</h2>
            </div>
            <div class="card-body">
                <div class="flex flex-col items-center text-center mb-6">
                    <div class="w-20 h-20 rounded-full bg-brand-600 text-white flex items-center justify-center text-2xl font-bold mb-3">
                        {{ $user->initials() }}
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $user->email }}</p>

                    @if($user->isVerified())
                        <span class="badge-success mt-2">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            Email verificado
                        </span>
                    @else
                        <span class="badge badge-warning mt-2">Email sin verificar</span>
                    @endif
                </div>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">Nombre</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $user->name }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-700">
                        <span class="text-gray-500 dark:text-gray-400">Correo</span>
                        <span class="font-medium text-gray-900 dark:text-white truncate max-w-[140px]" title="{{ $user->email }}">{{ $user->email }}</span>
                    </div>
                    <div class="flex justify-between items-center py-2">
                        <span class="text-gray-500 dark:text-gray-400">Registro</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $user->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

</x-layouts.app>
