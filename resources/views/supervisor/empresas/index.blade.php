<x-app-layout>
    <x-slot name="titulo">Validar empresas</x-slot>

    <x-slot name="header">
        <h1 class="font-display text-3xl font-bold">Validar empresas</h1>
        <p class="mt-1 text-slate-600">Revisa el RUC y los datos de cada empresa antes de permitirle publicar vacantes.</p>
    </x-slot>

    {{-- Totales por estado (también sirven de pestañas) --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        @foreach (['pendiente' => ['Pendientes', 'text-amber-600'], 'verificada' => ['Verificadas', 'text-teal-700'], 'rechazada' => ['Rechazadas', 'text-red-700']] as $clave => [$texto, $color])
            <a href="{{ route('supervisor.empresas.index', ['estado' => $clave]) }}"
               @if ($estado === $clave) aria-current="page" @endif
               class="rounded-xl border bg-white p-5 transition {{ $estado === $clave ? 'border-coral-700 ring-2 ring-coral-700' : 'border-slate-200 hover:border-slate-300' }}">
                <span class="text-sm text-slate-600">{{ $texto }}</span>
                <span class="block font-display text-3xl font-bold {{ $color }}">{{ $totales[$clave] ?? 0 }}</span>
            </a>
        @endforeach
    </div>

    <form method="GET" class="mb-6 flex flex-wrap gap-3">
        <input type="hidden" name="estado" value="{{ $estado }}">
        <label class="flex-1 min-w-[240px]">
            <span class="sr-only">Buscar empresa</span>
            <x-text-input name="q" :value="request('q')" placeholder="Buscar por razón social o RUC" class="w-full" />
        </label>
        <x-secondary-button type="submit">Buscar</x-secondary-button>
    </form>

    @if ($empresas->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600">
            No hay empresas en este estado.
        </div>
    @else
        <div class="grid gap-4 [grid-template-columns:repeat(auto-fill,minmax(min(100%,360px),1fr))]">
            @foreach ($empresas as $empresa)
                <article class="rounded-2xl border border-slate-200 bg-white p-6 flex flex-col gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-coral-100 font-bold text-coral-900">{{ $empresa->iniciales }}</span>
                        <div class="min-w-0">
                            <h2 class="font-semibold text-lg truncate">{{ $empresa->razon_social }}</h2>
                            <p class="text-sm text-slate-600">
                                Registrada {{ $empresa->created_at->diffForHumans() }}
                                @if ($empresa->reclutadores->isNotEmpty())
                                    por {{ $empresa->reclutadores->first()->nombre }}
                                @endif
                            </p>
                        </div>
                        <x-estado :valor="$empresa->estado" class="ms-auto" />
                    </div>

                    <dl class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div><dt class="text-xs text-slate-500">RUC</dt><dd class="font-semibold">{{ $empresa->ruc }}</dd></div>
                        <div><dt class="text-xs text-slate-500">Ubicación</dt><dd class="font-semibold">{{ $empresa->ubicacion->nombre_completo }}</dd></div>
                        <div><dt class="text-xs text-slate-500">Contacto</dt><dd class="font-semibold truncate">{{ $empresa->reclutadores->first()?->email ?? '—' }}</dd></div>
                        <div><dt class="text-xs text-slate-500">Sitio web</dt><dd class="font-semibold truncate">{{ $empresa->sitio_web ?: '—' }}</dd></div>
                    </dl>

                    @if ($empresa->estado === 'pendiente')
                        <div class="mt-auto flex gap-3">
                            <form method="POST" action="{{ route('supervisor.empresas.rechazar', $empresa) }}" class="flex-1"
                                  onsubmit="return confirm('¿Rechazar a {{ addslashes($empresa->razon_social) }}?')">
                                @csrf @method('PATCH')
                                <button type="submit" class="w-full rounded-lg border border-red-300 bg-white py-2.5 font-semibold text-red-800 hover:bg-red-50">Rechazar</button>
                            </form>
                            <form method="POST" action="{{ route('supervisor.empresas.verificar', $empresa) }}" class="flex-1">
                                @csrf @method('PATCH')
                                <button type="submit" class="w-full rounded-lg bg-teal-700 py-2.5 font-semibold text-white hover:bg-teal-800">Verificar empresa</button>
                            </form>
                        </div>
                    @else
                        <p class="mt-auto text-sm text-slate-600">
                            {{ $empresa->estado === 'verificada' ? 'Verificada' : 'Rechazada' }}
                            por {{ $empresa->verificador?->nombre ?? '—' }}
                            el {{ $empresa->verificado_en?->translatedFormat('d M Y') }}.
                        </p>
                    @endif
                </article>
            @endforeach
        </div>

        <div class="mt-6">{{ $empresas->links() }}</div>
    @endif
</x-app-layout>
