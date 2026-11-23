<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel del supervisor</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                <p class="text-sm text-gray-500">Empresas pendientes de verificación</p>
                <p class="mt-1 text-4xl font-bold text-amber-600">{{ $empresasPendientes }}</p>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-600">
                Próximamente (fase 5): validar o rechazar empresas.
            </div>
        </div>
    </div>
</x-app-layout>
