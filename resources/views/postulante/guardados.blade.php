<x-app-layout>
    <x-slot name="titulo">Guardados</x-slot>

    <x-slot name="header">
        <h1 class="font-display text-3xl font-bold">Vacantes guardadas</h1>
        <p class="mt-1 text-slate-600">Las vacantes que marcaste para revisar después.</p>
    </x-slot>

    <div class="space-y-4">
        @forelse ($vacantes as $vacante)
            @include('postulante.vacantes._tarjeta', [
                'guardada' => true,
                'postulada' => in_array($vacante->id, $postuladas, true),
            ])
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600">
                Aún no guardaste vacantes. Usa el ícono de marcador en el buscador.
                <a href="{{ route('postulante.vacantes.index') }}" class="font-semibold text-blue-700 underline">Buscar empleos</a>
            </div>
        @endforelse

        <div>{{ $vacantes->links() }}</div>
    </div>
</x-app-layout>
