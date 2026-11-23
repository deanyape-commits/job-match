<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Panel del reclutador · {{ $empresa?->razon_social }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if ($empresa?->estado === 'pendiente')
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-amber-900">
                    <strong>Tu empresa está pendiente de verificación.</strong>
                    Podrás publicar vacantes cuando un supervisor la apruebe.
                </div>
            @elseif ($empresa?->estado === 'rechazada')
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-900">
                    <strong>Tu empresa fue rechazada.</strong> Comunícate con soporte para revisar los datos.
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 text-gray-900">
                <p class="text-lg font-semibold">Hola, {{ Auth::user()->nombre }}</p>
                <dl class="mt-3 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div><dt class="text-gray-500">Empresa</dt><dd class="font-medium">{{ $empresa?->razon_social }}</dd></div>
                    <div><dt class="text-gray-500">RUC</dt><dd class="font-medium">{{ $empresa?->ruc }}</dd></div>
                    <div><dt class="text-gray-500">Estado</dt><dd class="font-medium capitalize">{{ $empresa?->estado }}</dd></div>
                </dl>
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-600">
                Próximamente (fase 5): dashboard con reporte, publicar vacantes, candidatos y entrevistas.
            </div>
        </div>
    </div>
</x-app-layout>
