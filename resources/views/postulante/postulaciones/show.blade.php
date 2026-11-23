@php
    $textos = [
        'postulado' => 'Postulaste a la vacante',
        'en_revision' => 'La empresa está revisando tu perfil',
        'entrevista' => 'Fuiste seleccionado para una entrevista',
        'rechazado' => 'La empresa continuó con otros candidatos',
        'contratado' => '¡Fuiste contratado!',
    ];
@endphp

<x-app-layout>
    <x-slot name="titulo">Mi postulación</x-slot>

    <a href="{{ route('postulante.postulaciones.index') }}" class="text-sm font-medium text-coral-700 hover:text-coral-900">← Mis postulaciones</a>

    <div class="mt-4 flex flex-wrap items-start gap-7">
        <main class="min-w-0 flex-[999_1_560px] space-y-5">
            <section class="rounded-2xl border border-slate-200 bg-white p-7">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h1 class="font-display text-2xl font-bold">{{ $postulacion->vacante->titulo }}</h1>
                        <p class="text-slate-600">{{ $postulacion->vacante->empresa->razon_social }}</p>
                    </div>
                    <x-match :porcentaje="$postulacion->porcentaje_match" etiqueta="match al postular" />
                </div>
                <p class="mt-4 flex items-center gap-2 text-sm text-slate-700">Estado actual: <x-estado :valor="$postulacion->estado" /></p>
                @unless ($postulacion->vacante->trashed())
                    <a href="{{ route('postulante.vacantes.show', $postulacion->vacante) }}" class="mt-3 inline-block text-sm font-semibold text-coral-700 hover:text-coral-900">Ver la vacante</a>
                @endunless
            </section>

            {{-- Línea de tiempo: datos de historial_postulaciones (llenada por el trigger) --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-7">
                <h2 class="text-lg font-semibold">Historial</h2>
                <ol class="mt-5 space-y-0">
                    @foreach ($postulacion->historial as $evento)
                        <li class="relative flex gap-4 pb-6 last:pb-0">
                            @unless ($loop->last)
                                <span aria-hidden="true" class="absolute left-[7px] top-5 h-full w-0.5 bg-slate-200"></span>
                            @endunless
                            <span aria-hidden="true" class="relative mt-1 h-4 w-4 shrink-0 rounded-full border-4 border-white {{ $loop->last ? 'bg-coral-700 ring-2 ring-coral-200' : 'bg-slate-400' }}"></span>
                            <div>
                                <p class="font-semibold">{{ $textos[$evento->estado_nuevo] ?? $evento->estado_nuevo }}</p>
                                <p class="text-sm text-slate-600">{{ $evento->cambiado_en->translatedFormat('d M Y, g:i a') }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </section>
        </main>

        <aside class="flex-[1_1_320px] space-y-5 lg:max-w-[400px]">
            <section class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-semibold">Entrevistas</h2>
                @forelse ($postulacion->entrevistas as $entrevista)
                    <div class="mt-4 space-y-2 border-t border-slate-100 pt-4 first:border-0 first:pt-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold">{{ ucfirst($entrevista->fecha_hora->translatedFormat('l j \d\e F')) }}</p>
                            <x-estado :valor="$entrevista->estado" />
                        </div>
                        <p class="text-sm text-slate-600">{{ $entrevista->fecha_hora->format('g:i a') }} · {{ $entrevista->duracion_minutos }} minutos</p>
                        @if ($entrevista->estado === 'programada')
                            <a href="{{ $entrevista->enlace_reunion }}" target="_blank" rel="noopener" class="flex h-11 items-center justify-center rounded-lg bg-coral-700 font-semibold text-white hover:bg-coral-800">Unirse a la videollamada</a>
                        @endif
                    </div>
                @empty
                    <p class="mt-3 text-sm text-slate-600">Aún no tienes entrevistas para esta postulación.</p>
                @endforelse
            </section>

            @if ($postulacion->carta_presentacion)
                <section class="rounded-2xl border border-slate-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Tu carta de presentación</h2>
                    <p class="mt-3 text-sm leading-relaxed text-slate-700">{!! nl2br(e($postulacion->carta_presentacion)) !!}</p>
                </section>
            @endif
        </aside>
    </div>
</x-app-layout>
