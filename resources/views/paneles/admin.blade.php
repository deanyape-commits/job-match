<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Panel del administrador</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach (['Administrador' => 'Administradores', 'Supervisor' => 'Supervisores', 'Reclutador' => 'Reclutadores', 'Postulante' => 'Postulantes'] as $rol => $plural)
                    <div class="bg-white shadow-sm sm:rounded-lg p-5">
                        <p class="text-sm text-gray-500">{{ $plural }}</p>
                        <p class="mt-1 text-3xl font-bold text-gray-900">{{ $usuariosPorRol[$rol] ?? 0 }}</p>
                    </div>
                @endforeach
            </div>

            <div class="bg-white shadow-sm sm:rounded-lg p-6 text-gray-600">
                Próximamente (fase 5): gestión de usuarios, catálogos y reportes.
                Como administrador también puedes entrar al
                <a href="{{ route('supervisor.inicio') }}" class="text-indigo-600 underline">panel del supervisor</a>.
            </div>
        </div>
    </div>
</x-app-layout>
