<x-layouts.admin title="Editar día sin reparto">
    <div class="max-w-2xl">
        <x-admin.card>
            <form method="POST" action="{{ route('admin.non-working-days.update', $day) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <x-admin.non-working-day-fields :day="$day" />

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" class="btn-primary">Guardar cambios</button>
                    <a href="{{ route('admin.non-working-days.index') }}" class="btn-secondary">Cancelar</a>
                </div>
            </form>
        </x-admin.card>
    </div>
</x-layouts.admin>
