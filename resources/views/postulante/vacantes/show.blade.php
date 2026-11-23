@php
    $pct = (int) round($match['porcentaje']);
    [$colorAnillo, $colorTexto, $titulo] = match (true) {
        $pct >= 80 => ['#0F766E', 'text-teal-700', 'Excelente match'],
        $pct >= 50 => ['#B45309', 'text-amber-700', 'Buen match'],
        default => ['#64748B', 'text-slate-600', 'Match bajo'],
    };
    $faltantes = $match['habilidades']->where('tiene', false);
@endphp

<x-app-layout>
    <x-slot name="titulo">{{ $vacante->titulo }}</x-slot>

    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('postulante.vacantes.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">← Volver</a>

    <div class="mt-4 flex flex-wrap items-start gap-7">
        <main class="min-w-0 flex-[999_1_560px] space-y-5">
            <section class="space-y-5 rounded-2xl border border-slate-200 bg-white p-7">
                <div class="flex flex-wrap items-center gap-4">
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-blue-100 text-xl font-bold text-blue-900">{{ $vacante->empresa->iniciales }}</span>
                    <div>
                        <h1 class="font-display text-3xl font-bold leading-tight">{{ $vacante->titulo }}</h1>
                        <p class="mt-1 flex flex-wrap items-center gap-2 text-slate-600">
                            {{ $vacante->empresa->razon_social }}
                            <span class="inline-flex items-center gap-1 text-sm font-semibold text-teal-700">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="m5 12 5 5L20 7" /></svg>
                                Empresa verificada
                            </span>
                        </p>
                    </div>
                </div>

                @if ($vacante->estado !== 'publicada')
                    <p class="rounded-lg bg-red-50 px-4 py-2 text-sm text-red-900">Esta vacante ya está cerrada y no recibe nuevas postulaciones.</p>
                @endif

                <dl class="grid gap-3 [grid-template-columns:repeat(auto-fit,minmax(150px,1fr))]">
                    @foreach ([
                        'Ubicación' => $vacante->ubicacion->nombre_completo,
                        'Modalidad' => $vacante->modalidad_texto,
                        'Contrato' => $vacante->tipo_contrato_texto,
                        'Salario' => $vacante->salario_texto,
                        'Puestos' => $vacante->puestos.' '.($vacante->puestos === 1 ? 'puesto' : 'puestos'),
                    ] as $etiqueta => $valor)
                        <div class="rounded-lg bg-slate-50 px-4 py-3">
                            <dt class="text-xs uppercase tracking-wide text-slate-600">{{ $etiqueta }}</dt>
                            <dd class="font-semibold">{{ $valor }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-7 leading-relaxed">
                <h2 class="text-xl font-semibold">Descripción del puesto</h2>
                <div class="text-slate-700">{!! nl2br(e($vacante->descripcion)) !!}</div>
                <h3 class="pt-2 text-lg font-semibold">Requisitos</h3>
                <div class="text-slate-700">{!! nl2br(e($vacante->requisitos)) !!}</div>
                <p class="pt-2 text-sm text-slate-600">
                    {{ $vacante->categoria->nombre }} · {{ $vacante->nivel_texto }} · Publicada {{ $vacante->publicada_en?->diffForHumans() }}
                    @if ($vacante->cierra_en) · Cierra el {{ $vacante->cierra_en->translatedFormat('d \d\e F') }} @endif
                </p>
            </section>
        </main>

        <aside class="flex-[1_1_320px] space-y-5 lg:max-w-[400px]">
            {{-- Compatibilidad --}}
            <section aria-label="Compatibilidad" class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6">
                <div class="flex items-center gap-5">
                    <div role="img" aria-label="{{ $pct }} por ciento de compatibilidad"
                         class="flex h-24 w-24 shrink-0 items-center justify-center rounded-full"
                         style="background: conic-gradient({{ $colorAnillo }} 0 {{ $pct }}%, #E2E8F0 {{ $pct }}% 100%)">
                        <span class="flex h-[76px] w-[76px] items-center justify-center rounded-full bg-white font-display text-2xl font-bold {{ $colorTexto }}">{{ $pct }}%</span>
                    </div>
                    <div>
                        <p class="text-lg font-semibold">{{ $titulo }}</p>
                        <p class="text-sm leading-snug text-slate-600">
                            Tienes {{ $match['obligatorias_tiene'] }} de {{ $match['obligatorias_total'] }} habilidades obligatorias
                            y {{ $match['deseables_tiene'] }} de {{ $match['deseables_total'] }} deseables.
                        </p>
                    </div>
                </div>

                <ul class="space-y-2.5">
                    @foreach ($match['habilidades'] as $habilidad)
                        <li class="flex items-center justify-between gap-2 text-sm">
                            <span class="flex items-center gap-2">
                                <span aria-hidden="true" class="inline-block h-2.5 w-2.5 rounded-full {{ $habilidad['tiene'] ? 'bg-teal-700' : 'bg-slate-300' }}"></span>
                                {{ $habilidad['nombre'] }}
                                <span class="sr-only">{{ $habilidad['tiene'] ? '(la tienes)' : '(te falta)' }}</span>
                            </span>
                            <span class="rounded-full px-2 py-0.5 text-xs {{ $habilidad['tiene'] ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-600' }}">
                                {{ $habilidad['obligatoria'] ? 'Obligatoria' : 'Deseable' }}{{ $habilidad['tiene'] ? ' ✓' : '' }}
                            </span>
                        </li>
                    @endforeach
                    <li class="flex items-center justify-between gap-2 text-sm">
                        <span class="flex items-center gap-2">
                            <span aria-hidden="true" class="inline-block h-2.5 w-2.5 rounded-full {{ $match['ubicacion_ok'] ? 'bg-teal-700' : 'bg-slate-300' }}"></span>
                            Ubicación o modalidad remota
                        </span>
                        <span class="rounded-full px-2 py-0.5 text-xs {{ $match['ubicacion_ok'] ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-600' }}">
                            {{ $match['ubicacion_ok'] ? 'Coincide ✓' : 'No coincide' }}
                        </span>
                    </li>
                </ul>

                @if ($faltantes->isNotEmpty())
                    <p class="rounded-lg bg-slate-50 px-3 py-2.5 text-sm text-slate-600">
                        Agrega {{ $faltantes->pluck('nombre')->take(3)->implode(', ') }} a
                        <a href="{{ route('postulante.perfil.edit') }}" class="font-semibold text-blue-700 underline">tu perfil</a>
                        si las dominas, para subir tu compatibilidad.
                    </p>
                @endif
            </section>

            {{-- Postular --}}
            <section class="space-y-3 rounded-2xl border border-slate-200 bg-white p-6">
                @if ($postulacion)
                    <p class="font-semibold">Ya postulaste a esta vacante</p>
                    <p class="flex items-center gap-2 text-sm text-slate-600">Estado: <x-estado :valor="$postulacion->estado" /></p>
                    <a href="{{ route('postulante.postulaciones.show', $postulacion) }}" class="flex h-12 items-center justify-center rounded-lg border border-slate-300 font-semibold hover:bg-slate-50">Ver mi postulación</a>
                @elseif ($vacante->estado === 'publicada')
                    <form method="POST" action="{{ route('postulante.vacantes.postular', $vacante) }}" class="space-y-3">
                        @csrf
                        <label for="carta_presentacion" class="block text-sm font-semibold">Carta de presentación (opcional)</label>
                        <textarea id="carta_presentacion" name="carta_presentacion" rows="4" maxlength="2000"
                                  placeholder="Cuéntale a la empresa por qué te interesa el puesto…"
                                  class="block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">{{ old('carta_presentacion') }}</textarea>
                        <x-input-error :messages="$errors->get('carta_presentacion')" />
                        <x-primary-button class="w-full py-3 text-base">Postular ahora</x-primary-button>
                    </form>
                    <p class="text-center text-sm text-slate-600">
                        @if ($perfil->cv_ruta)
                            Se enviará tu perfil y tu CV.
                        @else
                            Aún no subiste tu CV. <a href="{{ route('postulante.perfil.edit') }}" class="font-semibold text-blue-700 underline">Súbelo aquí</a>.
                        @endif
                    </p>
                @endif

                <form method="POST" action="{{ route('postulante.vacantes.guardar', $vacante) }}">
                    @csrf
                    <x-secondary-button type="submit" class="w-full py-3">{{ $guardada ? 'Quitar de guardados' : 'Guardar vacante' }}</x-secondary-button>
                </form>
            </section>
        </aside>
    </div>
</x-app-layout>
