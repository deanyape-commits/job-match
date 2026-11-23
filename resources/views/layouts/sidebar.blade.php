@php
    $usuario = Auth::user();

    // Enlaces del menú según el rol: [texto, nombre de ruta, patrón para marcar activo]
    $enlaces = match ($usuario->rol?->slug) {
        'reclutador' => [
            ['Dashboard', 'reclutador.inicio', 'reclutador.inicio'],
            ['Mis vacantes', 'reclutador.vacantes.index', 'reclutador.vacantes.*'],
            ['Entrevistas', 'reclutador.entrevistas.index', 'reclutador.entrevistas.*'],
        ],
        'supervisor' => [
            ['Validar empresas', 'supervisor.empresas.index', 'supervisor.empresas.*'],
        ],
        'administrador' => [
            ['Resumen', 'admin.inicio', 'admin.inicio'],
            ['Usuarios', 'admin.usuarios.index', 'admin.usuarios.*'],
            ['Validar empresas', 'supervisor.empresas.index', 'supervisor.empresas.*'],
        ],
        default => [],
    };

    $etiquetaRol = match ($usuario->rol?->slug) {
        'administrador' => ['ADMINISTRADOR', 'bg-red-100 text-red-800'],
        'supervisor' => ['SUPERVISOR', 'bg-amber-100 text-amber-800'],
        default => null,
    };
@endphp

<aside class="flex-[1_1_240px] lg:max-w-[260px] bg-slate-900 text-slate-200 px-4 py-6 flex flex-col gap-6">
    <div class="flex items-center justify-between gap-3 px-2">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 text-white">
            <span class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-blue-700 font-display text-lg font-bold">J</span>
            <span class="font-display text-xl font-bold">JobMatch</span>
        </a>
        <x-campanita oscuro />
    </div>

    @if ($usuario->tieneRol('reclutador') && $usuario->empresa)
        <div class="rounded-xl bg-slate-800 p-3 flex items-center gap-3">
            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-blue-100 font-bold text-blue-900">
                {{ $usuario->empresa->iniciales }}
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-semibold text-white">{{ $usuario->empresa->razon_social }}</p>
                <p class="text-xs {{ $usuario->empresa->estaVerificada() ? 'text-teal-300' : 'text-amber-300' }}">
                    Empresa {{ $usuario->empresa->estado }}
                </p>
            </div>
        </div>
    @endif

    @if ($etiquetaRol)
        <span class="self-start ms-2 rounded-full px-2.5 py-1 text-xs font-bold {{ $etiquetaRol[1] }}">{{ $etiquetaRol[0] }}</span>
    @endif

    <nav aria-label="Menú principal" class="flex flex-col gap-1 text-[15px]">
        @foreach ($enlaces as [$texto, $ruta, $patron])
            @if (Route::has($ruta))
                @php $activo = request()->routeIs($patron); @endphp
                <a href="{{ route($ruta) }}" @if ($activo) aria-current="page" @endif
                   class="rounded-lg px-3 py-2.5 {{ $activo ? 'bg-slate-800 font-semibold text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    {{ $texto }}
                </a>
            @endif
        @endforeach
    </nav>

    <div class="mt-auto border-t border-slate-800 pt-4 px-2 text-sm">
        <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 rounded-lg hover:opacity-90">
            <x-avatar :usuario="$usuario" class="h-10 w-10" color="bg-slate-700 text-white" />
            <span class="min-w-0">
                <span class="block font-semibold text-white truncate">{{ $usuario->nombre }}</span>
                <span class="block text-slate-400 truncate">{{ $usuario->email }}</span>
            </span>
        </a>
        <div class="mt-3 flex flex-wrap gap-x-4 gap-y-1">
            <a href="{{ route('profile.edit') }}" class="text-slate-300 hover:text-white">Mi cuenta</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-slate-300 hover:text-white">Cerrar sesión</button>
            </form>
        </div>
    </div>
</aside>
