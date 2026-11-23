<x-app-layout>
    <x-slot name="titulo">Dashboard</x-slot>

    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold">Hola, {{ explode(' ', Auth::user()->nombre)[0] }}</h1>
                <p class="mt-1 text-slate-600">
                    Resumen de {{ $empresa->razon_social }} del {{ $desde->translatedFormat('j \d\e F') }} al {{ $hasta->translatedFormat('j \d\e F \d\e Y') }}
                </p>
            </div>
            @can('create', \App\Models\Vacante::class)
                <a href="{{ route('reclutador.vacantes.create') }}" class="inline-flex items-center rounded-lg bg-blue-700 px-5 py-2.5 font-semibold text-white hover:bg-blue-800">+ Publicar vacante</a>
            @endcan
        </div>
    </x-slot>

    @if ($empresa->estado === 'pendiente')
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
            <strong>Tu empresa está pendiente de verificación.</strong> Podrás publicar vacantes cuando un supervisor la apruebe.
        </div>
    @elseif ($empresa->estado === 'rechazada')
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-900">
            <strong>Tu empresa fue rechazada.</strong> Comunícate con soporte para revisar los datos.
        </div>
    @endif

    {{-- Indicadores (procedimiento almacenado) --}}
    <div class="grid gap-4 [grid-template-columns:repeat(auto-fit,minmax(170px,1fr))]">
        @foreach ([
            ['Vacantes publicadas', $reporte->vacantes_publicadas, $vacantesActivas.' activas ahora'],
            ['Postulaciones', $reporte->total_postulaciones, 'en el periodo'],
            ['Entrevistas realizadas', $reporte->entrevistas_realizadas, 'en el periodo'],
            ['Contratados', $reporte->total_contratados, 'en el periodo'],
            ['Match promedio', round($reporte->promedio_match).'%', 'de los postulantes'],
        ] as [$etiqueta, $valor, $detalle])
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-600">{{ $etiqueta }}</p>
                <p class="font-display text-3xl font-bold">{{ $valor }}</p>
                <p class="text-xs text-teal-700">{{ $detalle }}</p>
            </div>
        @endforeach
    </div>
    <p class="mt-2 text-xs text-slate-500">Datos del procedimiento almacenado <code>sp_reporte_empresa(empresa, desde, hasta)</code>.</p>

    <div class="mt-6 flex flex-wrap gap-5">
        {{-- Gráfico de barras hecho con CSS --}}
        <section class="flex-[2_1_460px] rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-semibold">Postulaciones por mes</h2>
            <div role="img" aria-label="Postulaciones por mes: {{ $grafico->map(fn ($g) => $g['etiqueta'].' '.$g['total'])->implode(', ') }}"
                 class="mt-4 flex h-52 items-end gap-4 border-b border-slate-200">
                @foreach ($grafico as $barra)
                    <div class="flex h-full flex-1 flex-col items-center justify-end gap-1.5">
                        <span class="text-xs font-semibold text-slate-700">{{ $barra['total'] }}</span>
                        <span class="w-full max-w-12 rounded-t-md bg-blue-700" style="height: {{ round($barra['total'] / $maximoGrafico * 160) }}px"></span>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex gap-4 text-xs text-slate-600">
                @foreach ($grafico as $barra)
                    <span class="flex-1 text-center">{{ $barra['etiqueta'] }}</span>
                @endforeach
            </div>
        </section>

        <section class="flex-[1_1_280px] rounded-2xl border border-slate-200 bg-white p-6">
            <h2 class="text-lg font-semibold">Entrevistas de esta semana</h2>
            @forelse ($entrevistas as $entrevista)
                <div class="flex items-center gap-3 border-b border-slate-100 py-3 last:border-0">
                    <div class="flex w-14 shrink-0 flex-col items-center rounded-lg bg-blue-50 py-1.5">
                        <span class="text-[11px] font-semibold uppercase text-blue-800">{{ $entrevista->fecha_hora->translatedFormat('D') }}</span>
                        <span class="text-lg font-bold text-blue-900">{{ $entrevista->fecha_hora->format('d') }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold">{{ $entrevista->postulacion->perfilPostulante->usuario->nombre }}</p>
                        <p class="truncate text-xs text-slate-600">{{ $entrevista->postulacion->vacante->titulo }} · {{ $entrevista->fecha_hora->format('g:i a') }}</p>
                    </div>
                </div>
            @empty
                <p class="mt-4 text-sm text-slate-600">No tienes entrevistas en los próximos 7 días.</p>
            @endforelse
        </section>
    </div>

    <section class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <div class="flex min-w-[720px] items-center justify-between px-6 pt-5 pb-2">
            <h2 class="text-lg font-semibold">Mis vacantes</h2>
            <a href="{{ route('reclutador.vacantes.index') }}" class="text-sm font-semibold text-blue-700 hover:text-blue-900">Ver todas</a>
        </div>
        @if ($vacantes->isEmpty())
            <p class="px-6 pb-6 text-sm text-slate-600">Aún no has creado vacantes.</p>
        @else
            <table class="w-full min-w-[720px] text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-200">
                        <th scope="col" class="px-6 py-3 font-semibold">Vacante</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Estado</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Postulantes</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Contratados</th>
                        <th scope="col" class="px-6 py-3 font-semibold">Cierra</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vacantes as $vacante)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-6 py-3.5 font-semibold">{{ $vacante->titulo }}</td>
                            <td class="px-6 py-3.5"><x-estado :valor="$vacante->estado" /></td>
                            <td class="px-6 py-3.5">{{ $vacante->postulaciones_count }}</td>
                            <td class="px-6 py-3.5">{{ $vacante->contratados_count }} / {{ $vacante->puestos }}</td>
                            <td class="px-6 py-3.5 text-slate-600">{{ $vacante->cierra_en?->translatedFormat('d M Y') ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </section>
</x-app-layout>
