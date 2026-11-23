@php
    $perfil = $postulacion->perfilPostulante;
    $vacante = $postulacion->vacante;
    $idsPerfil = $perfil->habilidades->pluck('id');
    $grados = ['tecnico' => 'Técnico', 'bachiller' => 'Bachiller', 'titulado' => 'Titulado', 'maestria' => 'Maestría', 'curso' => 'Curso'];
@endphp

<x-app-layout>
    <x-slot name="titulo">{{ $perfil->usuario->nombre }}</x-slot>

    <a href="{{ route('reclutador.vacantes.candidatos', $vacante) }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">← Candidatos de {{ $vacante->titulo }}</a>

    <div class="mt-4 flex flex-wrap items-start gap-7">
        <main class="min-w-0 flex-[999_1_560px] space-y-5">
            {{-- Encabezado del candidato --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-7">
                <div class="flex flex-wrap items-center gap-5">
                    <x-avatar :usuario="$perfil->usuario" class="h-16 w-16 text-xl" />
                    <div class="min-w-0 flex-1">
                        <h1 class="font-display text-2xl font-bold">{{ $perfil->usuario->nombre }}</h1>
                        <p class="text-slate-700">{{ $perfil->titular ?? 'Sin titular' }}</p>
                        <p class="text-sm text-slate-600">
                            {{ $perfil->ubicacion?->nombre_completo ?? 'Ubicación no indicada' }} ·
                            {{ $perfil->anios_experiencia }} {{ $perfil->anios_experiencia === 1 ? 'año' : 'años' }} de experiencia
                            @if ($perfil->salario_esperado) · Espera S/ {{ number_format($perfil->salario_esperado, 0, '.', ',') }} @endif
                        </p>
                    </div>
                    <x-match :porcentaje="$postulacion->porcentaje_match" etiqueta="match" />
                </div>

                <div class="mt-5 flex flex-wrap gap-2 text-sm">
                    <a href="mailto:{{ $perfil->usuario->email }}" class="rounded-lg border border-slate-300 px-3 py-2 font-medium hover:bg-slate-50">{{ $perfil->usuario->email }}</a>
                    @if ($perfil->usuario->telefono)
                        <span class="rounded-lg border border-slate-300 px-3 py-2">{{ $perfil->usuario->telefono }}</span>
                    @endif
                    @if ($perfil->cv_ruta)
                        <a href="{{ route('cv.show', $perfil) }}" target="_blank" class="rounded-lg bg-blue-700 px-3 py-2 font-semibold text-white hover:bg-blue-800">Ver CV (PDF)</a>
                    @endif
                    @if ($perfil->linkedin_url)
                        <a href="{{ $perfil->linkedin_url }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-300 px-3 py-2 font-medium hover:bg-slate-50">LinkedIn</a>
                    @endif
                    @if ($perfil->portafolio_url)
                        <a href="{{ $perfil->portafolio_url }}" target="_blank" rel="noopener" class="rounded-lg border border-slate-300 px-3 py-2 font-medium hover:bg-slate-50">Portafolio</a>
                    @endif
                </div>

                @if ($perfil->resumen)
                    <p class="mt-5 leading-relaxed text-slate-700">{!! nl2br(e($perfil->resumen)) !!}</p>
                @endif
            </section>

            {{-- Habilidades frente a la vacante --}}
            <section class="rounded-2xl border border-slate-200 bg-white p-7">
                <h2 class="text-lg font-semibold">Habilidades que pide la vacante</h2>
                <ul class="mt-4 grid gap-2 sm:grid-cols-2">
                    @foreach ($vacante->habilidades as $habilidad)
                        @php $tiene = $idsPerfil->contains($habilidad->id); $nivel = $perfil->habilidades->firstWhere('id', $habilidad->id)?->pivot->nivel; @endphp
                        <li class="flex items-center justify-between gap-2 rounded-lg bg-slate-50 px-3 py-2 text-sm">
                            <span class="flex items-center gap-2">
                                <span aria-hidden="true" class="h-2.5 w-2.5 rounded-full {{ $tiene ? 'bg-teal-700' : 'bg-slate-300' }}"></span>
                                {{ $habilidad->nombre }}
                                <span class="text-xs text-slate-500">({{ $habilidad->pivot->obligatoria ? 'obligatoria' : 'deseable' }})</span>
                            </span>
                            <span class="text-xs font-semibold {{ $tiene ? 'text-teal-800' : 'text-slate-500' }}">{{ $tiene ? "Nivel $nivel/5" : 'No la tiene' }}</span>
                        </li>
                    @endforeach
                </ul>
                @php $otras = $perfil->habilidades->whereNotIn('id', $vacante->habilidades->pluck('id')); @endphp
                @if ($otras->isNotEmpty())
                    <p class="mt-4 text-sm text-slate-600"><span class="font-semibold">Otras habilidades:</span> {{ $otras->pluck('nombre')->implode(', ') }}</p>
                @endif
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-7">
                <h2 class="text-lg font-semibold">Experiencia</h2>
                @forelse ($perfil->experiencias as $experiencia)
                    <div class="mt-4">
                        <p class="font-semibold">{{ $experiencia->cargo }} · <span class="font-normal">{{ $experiencia->empresa_nombre }}</span></p>
                        <p class="text-sm text-slate-500">{{ $experiencia->fecha_inicio->translatedFormat('M Y') }} – {{ $experiencia->fecha_fin?->translatedFormat('M Y') ?? 'Actualidad' }}</p>
                        @if ($experiencia->descripcion) <p class="mt-1 text-sm text-slate-700">{{ $experiencia->descripcion }}</p> @endif
                    </div>
                @empty
                    <p class="mt-3 text-sm text-slate-500">No registró experiencia laboral.</p>
                @endforelse

                <h2 class="mt-7 text-lg font-semibold">Estudios</h2>
                @forelse ($perfil->estudios as $estudio)
                    <div class="mt-4">
                        <p class="font-semibold">{{ $estudio->carrera }}</p>
                        <p class="text-sm text-slate-600">{{ $estudio->institucion }} · {{ $grados[$estudio->grado] ?? $estudio->grado }} · {{ $estudio->fecha_inicio->format('Y') }} – {{ $estudio->fecha_fin?->format('Y') ?? 'En curso' }}</p>
                    </div>
                @empty
                    <p class="mt-3 text-sm text-slate-500">No registró estudios.</p>
                @endforelse
            </section>

            @if ($postulacion->carta_presentacion)
                <section class="rounded-2xl border border-slate-200 bg-white p-7">
                    <h2 class="text-lg font-semibold">Carta de presentación</h2>
                    <p class="mt-3 leading-relaxed text-slate-700">{!! nl2br(e($postulacion->carta_presentacion)) !!}</p>
                </section>
            @endif
        </main>

        <aside class="flex-[1_1_320px] space-y-5 lg:max-w-[400px]">
            <section class="space-y-3 rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-semibold">Estado de la postulación</h2>
                @include('reclutador.candidatos._estado', ['confirmarContratado' => true])
                <p class="text-xs text-slate-500">Postuló {{ $postulacion->created_at->diffForHumans() }}.</p>
            </section>

            {{-- Agendar entrevista --}}
            <section id="agendar" class="space-y-4 rounded-2xl border-2 border-blue-700 bg-white p-6">
                <h2 class="text-lg font-semibold">Agendar entrevista</h2>
                <form method="POST" action="{{ route('reclutador.entrevistas.store', $postulacion) }}" class="space-y-4">
                    @csrf
                    <div>
                        <x-input-label for="fecha_hora" value="Fecha y hora" />
                        <x-text-input id="fecha_hora" name="fecha_hora" type="datetime-local" class="mt-1 block w-full" required
                                      :value="old('fecha_hora', now()->addDay()->setTime(10, 0)->format('Y-m-d\TH:i'))" />
                        <x-input-error :messages="$errors->entrevista->get('fecha_hora')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="duracion_minutos" value="Duración" />
                        <select id="duracion_minutos" name="duracion_minutos" class="mt-1 block w-full rounded-lg border-slate-300 shadow-sm focus:border-blue-600 focus:ring-blue-600">
                            @foreach ([15, 30, 45, 60, 90] as $minutos)
                                <option value="{{ $minutos }}" @selected(old('duracion_minutos', 30) == $minutos)>{{ $minutos }} minutos</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="notas" value="Notas para el equipo (opcional)" />
                        <textarea id="notas" name="notas" rows="2" maxlength="1000" class="mt-1 block w-full rounded-lg border-slate-300 text-sm shadow-sm focus:border-blue-600 focus:ring-blue-600">{{ old('notas') }}</textarea>
                    </div>
                    <p class="rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">Se generará un enlace de videollamada en Jitsi Meet. No hace falta instalar nada ni crear una cuenta.</p>
                    <x-primary-button class="w-full">Confirmar entrevista</x-primary-button>
                </form>
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-semibold">Entrevistas</h2>
                @forelse ($postulacion->entrevistas as $entrevista)
                    <div class="mt-4 space-y-2 border-t border-slate-100 pt-4 first-of-type:border-0">
                        <div class="flex items-center justify-between gap-2">
                            <p class="font-semibold">{{ ucfirst($entrevista->fecha_hora->translatedFormat('D j M, g:i a')) }}</p>
                            <x-estado :valor="$entrevista->estado" />
                        </div>
                        <p class="text-sm text-slate-600">{{ $entrevista->duracion_minutos }} minutos</p>
                        @if ($entrevista->notas) <p class="text-sm text-slate-700">{{ $entrevista->notas }}</p> @endif
                        @if ($entrevista->estado === 'programada')
                            <a href="{{ $entrevista->enlace_reunion }}" target="_blank" rel="noopener" class="block break-all text-sm font-semibold text-blue-700 underline">{{ $entrevista->enlace_reunion }}</a>
                            <div class="flex gap-2">
                                @foreach (['realizada' => 'Marcar realizada', 'cancelada' => 'Cancelar'] as $nuevo => $texto)
                                    <form method="POST" action="{{ route('reclutador.entrevistas.update', $entrevista) }}" class="flex-1">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="estado" value="{{ $nuevo }}">
                                        <button type="submit" class="w-full rounded-lg border px-3 py-2 text-sm font-semibold {{ $nuevo === 'realizada' ? 'border-teal-600 text-teal-800 hover:bg-teal-50' : 'border-red-300 text-red-800 hover:bg-red-50' }}">{{ $texto }}</button>
                                    </form>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="mt-3 text-sm text-slate-600">Aún no hay entrevistas agendadas.</p>
                @endforelse
            </section>

            <section class="rounded-2xl border border-slate-200 bg-white p-6">
                <h2 class="text-lg font-semibold">Historial</h2>
                <p class="text-xs text-slate-500">Registrado por el trigger <code>trg_historial_postulacion</code>.</p>
                <ol class="mt-3 space-y-2 text-sm">
                    @foreach ($postulacion->historial as $evento)
                        <li class="flex items-center justify-between gap-2">
                            <x-estado :valor="$evento->estado_nuevo" />
                            <span class="text-slate-600">{{ $evento->cambiado_en->translatedFormat('d M Y, g:i a') }}</span>
                        </li>
                    @endforeach
                </ol>
            </section>
        </aside>
    </div>
</x-app-layout>
