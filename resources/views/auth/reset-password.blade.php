<x-layouts.guest title="Nueva contraseña">
    <div class="auth-card">
        <div class="card-body">

            <div class="mb-6 text-center">
                <h1 class="text-2xl font-bold text-gray-900">Nueva contraseña</h1>
                <p class="mt-1 text-sm text-gray-500">Establece tu nueva contraseña segura</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <!-- Token -->
                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <!-- Email -->
                <div class="mb-4">
                    <x-input-label for="email" required>Correo electrónico</x-input-label>
                    <x-text-input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="username"
                        :value="old('email', $request->email)"
                        required
                        autofocus
                        placeholder="tu@correo.com"
                    />
                    <x-input-error field="email" class="mt-1" />
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <x-input-label for="password" required>Nueva contraseña</x-input-label>
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                        placeholder="Mínimo 8 caracteres"
                    />
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
                        placeholder="Repite tu nueva contraseña"
                    />
                    <x-input-error field="password_confirmation" class="mt-1" />
                </div>

                <x-primary-button x-bind:loading="loading">
                    <span x-show="!loading">Restablecer contraseña</span>
                    <span x-show="loading" style="display:none">Guardando...</span>
                </x-primary-button>
            </form>

        </div>
    </div>
</x-layouts.guest>
