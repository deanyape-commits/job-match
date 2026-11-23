@php
    $colorRol = [
        'administrador' => 'bg-red-100 text-red-800',
        'supervisor' => 'bg-amber-100 text-amber-800',
        'reclutador' => 'bg-blue-100 text-blue-800',
        'postulante' => 'bg-teal-100 text-teal-800',
    ];
    $claseCampo = 'block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600';
@endphp

<x-app-layout>
    <x-slot name="titulo">Usuarios</x-slot>

    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold">Usuarios</h1>
                <p class="mt-1 text-slate-600">{{ number_format($total, 0, '.', ' ') }} usuarios registrados</p>
            </div>
            <a href="{{ route('admin.usuarios.create') }}" class="inline-flex items-center rounded-lg bg-blue-700 px-5 py-2.5 font-semibold text-white hover:bg-blue-800">+ Nuevo usuario</a>
        </div>
    </x-slot>

    <form method="GET" class="mb-5 flex flex-wrap items-end gap-3">
        <label class="flex-[2_1_260px] text-sm font-medium text-slate-700">Buscar
            <x-text-input type="search" name="q" :value="request('q')" placeholder="Nombre o correo" class="mt-1 w-full" />
        </label>
        <label class="flex-[1_1_160px] text-sm font-medium text-slate-700">Rol
            <select name="rol" class="{{ $claseCampo }} mt-1">
                <option value="">Todos</option>
                @foreach ($roles as $rol)
                    <option value="{{ $rol->slug }}" @selected(request('rol') === $rol->slug)>{{ $rol->nombre }}</option>
                @endforeach
            </select>
        </label>
        <label class="flex-[1_1_160px] text-sm font-medium text-slate-700">Estado
            <select name="estado" class="{{ $claseCampo }} mt-1">
                <option value="">Todos</option>
                <option value="activos" @selected(request('estado') === 'activos')>Activos</option>
                <option value="inactivos" @selected(request('estado') === 'inactivos')>Inactivos</option>
            </select>
        </label>
        <x-secondary-button type="submit" class="h-[42px]">Filtrar</x-secondary-button>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="w-full min-w-[860px] text-sm">
            <thead>
                <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                    <th scope="col" class="px-5 py-3 font-semibold">Usuario</th>
                    <th scope="col" class="px-5 py-3 font-semibold">Rol</th>
                    <th scope="col" class="px-5 py-3 font-semibold">Empresa</th>
                    <th scope="col" class="px-5 py-3 font-semibold">Registro</th>
                    <th scope="col" class="px-5 py-3 font-semibold">Activo</th>
                    <th scope="col" class="px-5 py-3"><span class="sr-only">Acciones</span></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($usuarios as $usuario)
                    @php $esYo = $usuario->is(Auth::user()); @endphp
                    <tr class="border-b border-slate-100 last:border-0">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-bold text-slate-700">{{ $usuario->iniciales }}</span>
                                <div class="min-w-0">
                                    <p class="font-semibold">{{ $usuario->nombre }} @if ($esYo)<span class="text-xs font-normal text-slate-500">(tú)</span>@endif</p>
                                    <p class="text-slate-600">{{ $usuario->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $colorRol[$usuario->rol->slug] ?? 'bg-slate-100 text-slate-700' }}">{{ $usuario->rol->nombre }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-700">{{ $usuario->empresa?->razon_social ?? '—' }}</td>
                        <td class="px-5 py-3 text-slate-600">{{ $usuario->created_at->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.usuarios.activo', $usuario) }}">
                                @csrf @method('PATCH')
                                <button type="submit" role="switch" aria-checked="{{ $usuario->activo ? 'true' : 'false' }}"
                                        aria-label="{{ $usuario->activo ? 'Desactivar' : 'Activar' }} a {{ $usuario->nombre }}"
                                        @disabled($esYo)
                                        class="flex h-[26px] w-[46px] rounded-full p-[3px] transition disabled:cursor-not-allowed disabled:opacity-50 {{ $usuario->activo ? 'justify-end bg-teal-700' : 'justify-start bg-slate-300' }}">
                                    <span class="block h-5 w-5 rounded-full bg-white shadow"></span>
                                </button>
                            </form>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.usuarios.edit', $usuario) }}" class="font-semibold text-blue-700 hover:text-blue-900">Editar</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-10 text-center text-slate-600">No hay usuarios con esos filtros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">{{ $usuarios->links() }}</div>
</x-app-layout>
