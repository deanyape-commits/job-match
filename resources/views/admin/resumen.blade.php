@php
    $plurales = ['administrador' => 'Administradores', 'supervisor' => 'Supervisores', 'reclutador' => 'Reclutadores', 'postulante' => 'Postulantes'];
@endphp

<x-app-layout>
    <x-slot name="titulo">Resumen</x-slot>

    <x-slot name="header">
        <h1 class="font-display text-3xl font-bold">Resumen de la plataforma</h1>
        <p class="mt-1 text-slate-600">Cifras globales de JobMatch.</p>
    </x-slot>

    <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-slate-500">Usuarios</h2>
    <div class="grid gap-4 [grid-template-columns:repeat(auto-fit,minmax(170px,1fr))]">
        @foreach ($usuariosPorRol as $rol)
            <a href="{{ route('admin.usuarios.index', ['rol' => $rol->slug]) }}" class="rounded-xl border border-slate-200 bg-white p-5 hover:border-slate-300">
                <p class="text-sm text-slate-600">{{ $plurales[$rol->slug] ?? $rol->nombre }}</p>
                <p class="font-display text-3xl font-bold">{{ $rol->usuarios_count }}</p>
            </a>
        @endforeach
        <a href="{{ route('admin.usuarios.index', ['estado' => 'inactivos']) }}" class="rounded-xl border border-slate-200 bg-white p-5 hover:border-slate-300">
            <p class="text-sm text-slate-600">Desactivados</p>
            <p class="font-display text-3xl font-bold text-red-700">{{ $usuariosInactivos }}</p>
        </a>
    </div>

    <div class="mt-8 grid gap-5 lg:grid-cols-3">
        @foreach ([
            ['Empresas', $empresasPorEstado, ['pendiente', 'verificada', 'rechazada'], route('supervisor.empresas.index')],
            ['Vacantes', $vacantesPorEstado, ['publicada', 'borrador', 'cerrada'], null],
            ['Postulaciones', $postulacionesPorEstado, ['postulado', 'en_revision', 'entrevista', 'contratado', 'rechazado'], null],
        ] as [$titulo, $datos, $estados, $enlace])
            <section class="rounded-2xl border border-slate-200 bg-white p-6">
                <div class="flex items-baseline justify-between">
                    <h2 class="text-lg font-semibold">{{ $titulo }}</h2>
                    <span class="font-display text-2xl font-bold">{{ $datos->sum() }}</span>
                </div>
                <ul class="mt-4 space-y-2.5">
                    @foreach ($estados as $estado)
                        <li class="flex items-center justify-between text-sm">
                            <x-estado :valor="$estado" />
                            <span class="font-semibold">{{ $datos[$estado] ?? 0 }}</span>
                        </li>
                    @endforeach
                </ul>
                @if ($enlace)
                    <a href="{{ $enlace }}" class="mt-4 inline-block text-sm font-semibold text-blue-700 hover:text-blue-900">Validar empresas →</a>
                @endif
            </section>
        @endforeach
    </div>

    <section class="mt-8 rounded-2xl border border-slate-200 bg-white p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">Últimos registros</h2>
            <a href="{{ route('admin.usuarios.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">Ver todos</a>
        </div>
        <ul class="mt-3 divide-y divide-slate-100">
            @foreach ($ultimosUsuarios as $usuario)
                <li class="flex flex-wrap items-center justify-between gap-2 py-3">
                    <span>
                        <span class="font-semibold">{{ $usuario->nombre }}</span>
                        <span class="text-sm text-slate-500">· {{ $usuario->email }}</span>
                    </span>
                    <span class="flex items-center gap-3 text-sm text-slate-600">
                        {{ $usuario->rol->nombre }} · {{ $usuario->created_at->diffForHumans() }}
                    </span>
                </li>
            @endforeach
        </ul>
    </section>
</x-app-layout>
