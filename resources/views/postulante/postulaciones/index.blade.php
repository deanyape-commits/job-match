@php
    // Etapas de la barra de progreso
    $etapa = ['postulado' => 1, 'en_revision' => 2, 'entrevista' => 3, 'contratado' => 4, 'rechazado' => 0];
@endphp

<x-app-layout>
    <x-slot name="titulo">Mis postulaciones</x-slot>

    <x-slot name="header">
        <h1 class="font-display text-3xl font-bold">Mis postulaciones</h1>
        <p class="mt-1 text-slate-600">Sigue el estado de cada proceso de selección.</p>
    </x-slot>

    <div class="grid gap-4 [grid-template-columns:repeat(auto-fit,minmax(180px,1fr))]">
        @foreach ($estadisticas as $etiqueta => $valor)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-sm text-slate-600">{{ $etiqueta }}</p>
                <p class="font-display text-3xl font-bold">{{ $valor }}</p>
            </div>
        @endforeach
    </div>

    @if ($proximaEntrevista)
        <section aria-label="Próxima entrevista" class="mt-6 flex flex-wrap items-center justify-between gap-5 rounded-2xl bg-marino p-6 text-white">
            <div class="space-y-1.5">
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-300">Próxima entrevista</p>
                <p class="text-xl font-semibold">{{ $proximaEntrevista->postulacion->vacante->titulo }} · {{ $proximaEntrevista->postulacion->vacante->empresa->razon_social }}</p>
                <p class="text-slate-300">
                    {{ ucfirst($proximaEntrevista->fecha_hora->translatedFormat('l j \d\e F, g:i a')) }} · {{ $proximaEntrevista->duracion_minutos }} minutos · Videollamada Jitsi
                </p>
            </div>
            <a href="{{ $proximaEntrevista->enlace_reunion }}" target="_blank" rel="noopener"
               class="flex items-center gap-2 rounded-lg bg-white px-5 py-3 font-semibold text-marino hover:bg-slate-100">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="6" width="13" height="12" rx="2" /><path d="m16 10 5-3v10l-5-3" /></svg>
                Unirse a la videollamada
            </a>
        </section>
    @endif

    @if ($postulaciones->isEmpty())
        <div class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600">
            Todavía no postulaste a ninguna vacante.
            <a href="{{ route('postulante.vacantes.index') }}" class="font-semibold text-blue-700 underline">Buscar empleos</a>
        </div>
    @else
        <div class="mt-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="w-full min-w-[820px] text-[15px]">
                <thead>
                    <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                        <th scope="col" class="px-5 py-3.5 font-semibold">Vacante</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Postulé</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Match</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Estado</th>
                        <th scope="col" class="px-5 py-3.5 font-semibold">Progreso</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($postulaciones as $postulacion)
                        @php $n = $etapa[$postulacion->estado]; $rechazado = $postulacion->estado === 'rechazado'; @endphp
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-5 py-4">
                                <a href="{{ route('postulante.postulaciones.show', $postulacion) }}" class="font-semibold text-slate-900 hover:text-blue-700">{{ $postulacion->vacante->titulo }}</a>
                                <p class="text-sm text-slate-600">{{ $postulacion->vacante->empresa->razon_social }}</p>
                            </td>
                            <td class="px-5 py-4 text-slate-700">{{ $postulacion->created_at->translatedFormat('d M Y') }}</td>
                            <td class="px-5 py-4 font-semibold">{{ round($postulacion->porcentaje_match) }}%</td>
                            <td class="px-5 py-4"><x-estado :valor="$postulacion->estado" /></td>
                            <td class="px-5 py-4">
                                <div class="flex gap-1" role="img" aria-label="{{ $rechazado ? 'Proceso finalizado' : "Etapa $n de 4" }}">
                                    @for ($i = 1; $i <= 4; $i++)
                                        <span class="h-2 w-8 rounded-full {{ $rechazado ? 'bg-red-300' : ($i <= $n ? 'bg-blue-700' : 'bg-slate-200') }}"></span>
                                    @endfor
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $postulaciones->links() }}</div>
        <p class="mt-3 text-sm text-slate-500">El historial de estados se registra automáticamente en la base de datos (trigger <code>trg_historial_postulacion</code>).</p>
    @endif
</x-app-layout>
