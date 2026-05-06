<x-layouts.guest title="Verificar correo electrónico">
    <div class="auth-card">
        <div class="card-body text-center">

            <div class="mx-auto mb-4 w-16 h-16 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>

            <h1 class="text-2xl font-bold text-gray-900 mb-2">Verifica tu correo</h1>

            <p class="text-sm text-gray-600 mb-6">
                ¡Gracias por registrarte! Antes de continuar, verifica tu dirección de correo electrónico haciendo clic en el enlace que te acabamos de enviar.
            </p>

            @if(session('status') === 'verification-link-sent')
                <div class="alert-success mb-6">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Se ha enviado un nuevo enlace de verificación a tu correo electrónico.</span>
                </div>
            @endif

            <div class="flex flex-col gap-3">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn-primary btn-lg w-full">
                        Reenviar correo de verificación
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary btn-lg w-full">
                        Cerrar sesión
                    </button>
                </form>
            </div>

        </div>
    </div>
</x-layouts.guest>
