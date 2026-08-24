<x-layouts.admin title="Nuevo día sin reparto">
    <div class="max-w-2xl">
        <x-admin.card>
            <form method="POST" action="{{ route('admin.non-working-days.store') }}" class="space-y-5">
                @csrf

                <x-admin.non-working-day-fields />

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Añadir día</button>
                    <a href="{{ route('admin.non-working-days.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </x-admin.card>
    </div>
</x-layouts.admin>
