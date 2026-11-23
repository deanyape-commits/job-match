@php
    $habilidadesVacante = $vacante->habilidades;
@endphp

<x-app-layout>
    <x-slot name="titulo">Candidatos · {{ $vacante->titulo }}</x-slot>

    <x-slot name="header">
        <a href="{{ route('reclutador.vacantes.index') }}" class="text-sm font-medium text-blue-700 hover:text-blue-900">← Mis vacantes</a>
        <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold">{{ $vacante->titulo }}</h1>
                <p class="mt-1 flex flex-wrap items-center gap-2 text-slate-600">
                    {{ $totales->sum() }} postulantes · {{ $vacante->puestos }} {{ $vacante->puestos === 1 ? 'puesto' : 'puestos' }} · {{ $vacante->modalidad_texto }}
                    @if ($vacante->cierra_en) · Cierra el {{ $vacante->cierra_en->translatedFormat('d M Y') }} @endif
                    <x-estado :valor="$vacante->estado" />
                </p>
            </div>
            <a href="{{ route('reclutador.vacantes.edit', $vacante) }}" class="inline-flex items-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 font-medium hover:bg-slate-50">Editar vacante</a>
        </div>
    </x-slot>

    <nav aria-label="Filtrar por estado" class="mb-5 flex flex-wrap gap-2">
        @foreach (['' => 'Todos'] + \App\Models\Postulacion::ESTADOS_TEXTO as $clave => $texto)
            @php $activo = ($estado ?? '') === $clave; @endphp
            <a href="{{ route('reclutador.vacantes.candidatos', array_filter(['vacante' => $vacante, 'estado' => $clave])) }}"
               @if ($activo) aria-current="page" @endif
               class="rounded-full border px-4 py-2 text-sm font-medium {{ $activo ? 'border-slate-900 bg-slate-900 text-white' : 'border-slate-300 bg-white text-slate-700 hover:border-slate-400' }}">
                {{ $texto }} <span class="opacity-75">{{ $clave === '' ? $totales->sum() : ($totales[$clave] ?? 0) }}</span>
            </a>
        @endforeach
    </nav>

    <div class="space-y-3">
        @forelse ($postulaciones as $postulacion)
            @php
                $perfil = $postulacion->perfilPostulante;
                $idsPerfil = $perfil->habilidades->pluck('id');
            @endphp
            <article class="flex flex-wrap items-center gap-5 rounded-2xl border border-slate-200 bg-white p-5">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800">{{ $perfil->usuario->iniciales }}</span>

                <div class="min-w-0 flex-[1_1_260px] space-y-1.5">
                    <a href="{{ route('reclutador.postulaciones.show', $postulacion) }}" class="text-lg font-semibold text-slate-900 hover:text-blue-700">{{ $perfil->usuario->nombre }}</a>
                    <p class="text-sm text-slate-600">
                        {{ $perfil->titular ?? 'Sin titular' }}
                        @if ($perfil->ubicacion) · {{ $perfil->ubicacion->departamento }} @endif
                        · {{ $perfil->anios_experiencia }} {{ $perfil->anios_experiencia === 1 ? 'año' : 'años' }} de experiencia
                    </p>
                    <ul class="flex flex-wrap gap-1.5" aria-label="Habilidades que pide la vacante">
                        @foreach ($habilidadesVacante as $habilidad)
                            @php $tiene = $idsPerfil->contains($habilidad->id); @endphp
                            <li class="rounded-full px-2.5 py-0.5 text-xs {{ $tiene ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-500 line-through' }}">
                                {{ $habilidad->nombre }}<span class="sr-only">{{ $tiene ? ' (tiene)' : ' (no tiene)' }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <x-match :porcentaje="$postulacion->porcentaje_match" etiqueta="match" />

                <div class="flex min-w-[200px] shrink-0 flex-col gap-2">
                    @include('reclutador.candidatos._estado', ['confirmarContratado' => true])
                    <div class="flex gap-2">
                        @if ($perfil->cv_ruta)
                            <a href="{{ route('cv.show', $perfil) }}" target="_blank" class="flex h-9 flex-1 items-center justify-center rounded-lg border border-slate-300 text-sm font-medium hover:bg-slate-50">Ver CV</a>
                        @endif
                        <a href="{{ route('reclutador.postulaciones.show', $postulacion) }}#agendar" class="flex h-9 flex-1 items-center justify-center rounded-lg bg-blue-700 text-sm font-semibold text-white hover:bg-blue-800">Entrevistar</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600">
                {{ $estado ? 'No hay candidatos en este estado.' : 'Esta vacante aún no recibe postulaciones.' }}
            </div>
        @endforelse

        <div>{{ $postulaciones->links() }}</div>
    </div>
</x-app-layout>
