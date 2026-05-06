<x-layouts.guest title="Crear cuenta">
    <div class="auth-card">
        <div class="card-body">

            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-900">Crear cuenta</h1>
                <p class="mt-1 text-sm text-gray-500">Comienza gratis, sin tarjeta de crédito</p>
            </div>

            <form method="POST" action="{{ route('register') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <!-- Name -->
                <div class="mb-4">
                    <x-input-label for="name" required>Nombre completo</x-input-label>
                    <x-text-input
                        id="name"
                        name="name"
                        type="text"
                        autocomplete="name"
                        :value="old('name')"
                        required
                        autofocus
                        placeholder="Juan Pérez"
                    />
                    <x-input-error field="name" class="mt-1" />
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <x-input-label for="email" required>Correo electrónico</x-input-label>
                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="username"
                        :value="old('email')"
                        required
                        placeholder="tu@correo.com"
                    />
                    <x-input-error field="email" class="mt-1" />
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <x-input-label for="password" required>Contraseña</x-input-label>
                    <div class="relative" x-data="{ show: false }">
                        <x-text-input
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            required
                            placeholder="Mínimo 8 caracteres"
                            x-bind:type="show ? 'text' : 'password'"
                        />
                        <button
                            type="button"
                            @click="show = !show"
                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                            tabindex="-1"
                        >
                            <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="display:none">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    <x-input-error field="password" class="mt-1" />
                </div>

                <!-- Confirm Password -->
                <div class="mb-6">
                    <x-input-label for="password_confirmation" required>Confirmar contraseña</x-input-label>
                    <x-text-input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        autocomplete="new-password"
                        required
                        placeholder="Repite tu contraseña"
                    />
                    <x-input-error field="password_confirmation" class="mt-1" />
                </div>

                <!-- Submit -->
                <x-primary-button x-bind:loading="loading">
                    <span x-show="!loading">Crear cuenta</span>
                    <span x-show="loading" style="display:none">
                        <svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Creando cuenta...
                    </span>
                </x-primary-button>

                <p class="mt-4 text-xs text-center text-gray-500">
                    Al registrarte, aceptas nuestros
                    <a href="#" class="text-brand-600 hover:underline">Términos de servicio</a>
                    y
                    <a href="#" class="text-brand-600 hover:underline">Política de privacidad</a>.
                </p>
            </form>

        </div>

        <!-- Login link -->
        <div class="px-6 py-4 bg-gray-50 rounded-b-xl border-t border-gray-100 text-center">
            <p class="text-sm text-gray-600">
                ¿Ya tienes cuenta?
                <a href="{{ route('login') }}" class="font-medium text-brand-600 hover:text-brand-700">
                    Inicia sesión
                </a>
            </p>
        </div>
    </div>
</x-layouts.guest>
