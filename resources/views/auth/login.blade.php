<x-layouts.guest title="Iniciar sesión">
    <div class="auth-card">
        <div class="card-body">

            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-900">Bienvenido de nuevo</h1>
                <p class="mt-1 text-sm text-gray-500">Inicia sesión en tu cuenta</p>
            </div>

            <!-- Session Status -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form method="POST" action="{{ route('login') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

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
                        autofocus
                        placeholder="tu@correo.com"
                    />
                    <x-input-error field="email" class="mt-1" />
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-1">
                        <x-input-label for="password" required>Contraseña</x-input-label>
                        @if(Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-xs text-brand-600 hover:text-brand-700 font-medium">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>
                    <div class="relative" x-data="{ show: false }">
                        <x-text-input
                            id="password"
                            name="password"
                            :type="'password'"
                            autocomplete="current-password"
                            required
                            placeholder="••••••••"
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

                <!-- Remember me -->
                <div class="flex items-center mb-6">
                    <x-checkbox id="remember" name="remember" />
                    <label for="remember" class="ml-2 text-sm text-gray-600 cursor-pointer">
                        Mantenerme conectado
                    </label>
                </div>

                <!-- Submit -->
                <x-primary-button x-bind:loading="loading">
                    <span x-show="!loading">Iniciar sesión</span>
                    <span x-show="loading" style="display:none">
                        <svg class="animate-spin w-4 h-4 mr-2 inline" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Iniciando sesión...
                    </span>
                </x-primary-button>
            </form>

        </div>

        <!-- Register link -->
        @if(Route::has('register'))
        <div class="px-6 py-4 bg-gray-50 rounded-b-xl border-t border-gray-100 text-center">
            <p class="text-sm text-gray-600">
                ¿No tienes cuenta?
                <a href="{{ route('register') }}" class="font-medium text-brand-600 hover:text-brand-700">
                    Regístrate gratis
                </a>
            </p>
        </div>
        @endif
    </div>
</x-layouts.guest>
