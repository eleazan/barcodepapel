<x-layouts.guest title="Confirmar contraseña">
    <div class="auth-card">
        <div class="card-body">

            <div class="mb-6 text-center">
                <div class="mx-auto mb-4 w-14 h-14 bg-yellow-100 rounded-full flex items-center justify-center">
                    <svg class="w-7 h-7 text-yellow-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-900">Confirmar contraseña</h1>
                <p class="mt-2 text-sm text-gray-500">
                    Esta es una zona segura. Por favor confirma tu contraseña antes de continuar.
                </p>
            </div>

            <form method="POST" action="{{ route('password.confirm') }}" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <div class="mb-6">
                    <x-input-label for="password" required>Contraseña</x-input-label>
                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        autofocus
                        placeholder="Tu contraseña actual"
                    />
                    <x-input-error field="password" class="mt-1" />
                </div>

                <x-primary-button x-bind:loading="loading">
                    Confirmar
                </x-primary-button>
            </form>

        </div>
    </div>
</x-layouts.guest>
