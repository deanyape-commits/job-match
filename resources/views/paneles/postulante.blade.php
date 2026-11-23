<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel del postulante</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                <p class="text-lg font-semibold">Hola, {{ Auth::user()->nombre }}</p>
                <p class="mt-1 text-gray-600">Desde aquí buscarás vacantes, verás tu compatibilidad y seguirás tus postulaciones.</p>
            </div>

            @if (! $perfil?->titular)
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-900">
                    Tu perfil profesional está vacío. Complétalo con tu experiencia y habilidades para calcular tu porcentaje de match.
                </div>
            @endif

            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-600">
                Próximamente (fase 5): buscar vacantes, detalle con % de match, mis postulaciones y mi perfil.
            </div>
        </div>
    </div>
</x-app-layout>
