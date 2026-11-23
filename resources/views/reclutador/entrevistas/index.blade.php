<x-app-layout>
    <x-slot name="titulo">Entrevistas</x-slot>

    <x-slot name="header">
        <h1 class="font-display text-3xl font-bold">Entrevistas</h1>
        <p class="mt-1 text-slate-600">Videollamadas agendadas con los candidatos de {{ Auth::user()->empresa->razon_social }}.</p>
    </x-slot>

    <section>
        <h2 class="mb-3 text-lg font-semibold">Próximas</h2>
        @forelse ($proximas as $entrevista)
            @php $postulacion = $entrevista->postulacion; @endphp
            <article class="mb-3 flex flex-wrap items-center gap-5 rounded-2xl border border-slate-200 bg-white p-5">
                <div class="flex w-16 shrink-0 flex-col items-center rounded-xl bg-coral-50 py-2">
                    <span class="text-xs font-semibold uppercase text-coral-800">{{ $entrevista->fecha_hora->translatedFormat('D') }}</span>
                    <span class="text-2xl font-bold text-coral-900">{{ $entrevista->fecha_hora->format('d') }}</span>
                    <span class="text-xs text-coral-800">{{ $entrevista->fecha_hora->translatedFormat('M') }}</span>
                </div>
                <div class="min-w-0 flex-[1_1_260px]">
                    <a href="{{ route('reclutador.postulaciones.show', $postulacion) }}" class="font-semibold text-slate-900 hover:text-coral-700">{{ $postulacion->perfilPostulante->usuario->nombre }}</a>
                    <p class="text-sm text-slate-600">{{ $postulacion->vacante->titulo }}</p>
                    <p class="text-sm text-slate-600">{{ $entrevista->fecha_hora->format('g:i a') }} · {{ $entrevista->duracion_minutos }} minutos</p>
                </div>
                <a href="{{ $entrevista->enlace_reunion }}" target="_blank" rel="noopener"
                   class="flex items-center gap-2 rounded-lg bg-coral-700 px-4 py-2.5 font-semibold text-white hover:bg-coral-800">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="3" y="6" width="13" height="12" rx="2" /><path d="m16 10 5-3v10l-5-3" /></svg>
                    Entrar a la videollamada
                </a>
            </article>
        @empty
            <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center text-slate-600">
                No tienes entrevistas programadas. Agéndalas desde el perfil de cada candidato.
            </div>
        @endforelse
    </section>

    @if ($anteriores->isNotEmpty())
        <section class="mt-8">
            <h2 class="mb-3 text-lg font-semibold">Anteriores</h2>
            <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
                <table class="w-full min-w-[680px] text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-left text-xs uppercase tracking-wide text-slate-500">
                            <th scope="col" class="px-5 py-3 font-semibold">Candidato</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Vacante</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Fecha</th>
                            <th scope="col" class="px-5 py-3 font-semibold">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($anteriores as $entrevista)
                            <tr class="border-b border-slate-100 last:border-0">
                                <td class="px-5 py-3"><a href="{{ route('reclutador.postulaciones.show', $entrevista->postulacion) }}" class="font-semibold hover:text-coral-700">{{ $entrevista->postulacion->perfilPostulante->usuario->nombre }}</a></td>
                                <td class="px-5 py-3 text-slate-700">{{ $entrevista->postulacion->vacante->titulo }}</td>
                                <td class="px-5 py-3 text-slate-700">{{ $entrevista->fecha_hora->translatedFormat('d M Y, g:i a') }}</td>
                                <td class="px-5 py-3"><x-estado :valor="$entrevista->estado" /></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif
</x-app-layout>
