<x-layouts.guest title="Recuperar contraseña">
    <div class="auth-card">
        <div class="card-body">

            <div class="mb-6 text-center">
                <div class="mx-auto mb-4 w-14 h-14 bg-brand-100 rounded-full flex items-center justify-center">
                    <svg class="w-7 h-7 text-brand-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Recuperar contraseña</h1>
                <p class="mt-2 text-sm text-gray-500">
                    Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                </p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('password.email') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <!-- Email -->
                <div class="mb-6">
                    <x-input-label for="email" required>Correo electrónico</x-input-label>
                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="username"
                        :value="old('email')"
                        required
                        autofocus
                        placeholder="tu@correo.com"
                    />
                    <x-input-error field="email" class="mt-1" />
                </div>

                <x-primary-button x-bind:loading="loading">
                    <span x-show="!loading">Enviar enlace de recuperación</span>
                    <span x-show="loading" style="display:none">Enviando...</span>
                </x-primary-button>
            </form>

        </div>

        <div class="px-6 py-4 bg-gray-50 rounded-b-xl border-t border-gray-100 text-center">
            <a href="{{ route('login') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver al inicio de sesión
            </a>
        </div>
    </div>
</x-layouts.guest>
