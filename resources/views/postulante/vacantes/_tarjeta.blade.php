{{-- Tarjeta de vacante del buscador y de guardados. Variables: $vacante, $guardada, $postulada --}}
<article class="flex flex-wrap items-center gap-5 rounded-2xl border border-slate-200 bg-white p-5">
    <span class="flex h-[52px] w-[52px] shrink-0 items-center justify-center rounded-xl bg-blue-100 text-lg font-bold text-blue-900">{{ $vacante->empresa->iniciales }}</span>

    <div class="min-w-0 flex-[1_1_320px] space-y-2">
        <a href="{{ route('postulante.vacantes.show', $vacante) }}" class="text-lg font-semibold text-slate-900 hover:text-blue-700">{{ $vacante->titulo }}</a>
        <p class="text-sm text-slate-600">
            {{ $vacante->empresa->razon_social }} · {{ $vacante->ubicacion->departamento }} · {{ $vacante->publicada_en?->diffForHumans() }}
        </p>
        <div class="flex flex-wrap gap-1.5 text-xs">
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">{{ $vacante->modalidad_texto }}</span>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">{{ $vacante->tipo_contrato_texto }}</span>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">{{ $vacante->nivel_texto }}</span>
            <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">{{ $vacante->salario_texto }}</span>
            @if ($postulada)
                <span class="rounded-full bg-blue-100 px-2.5 py-1 font-semibold text-blue-800">Ya postulaste</span>
            @endif
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-4">
        <x-match :porcentaje="$vacante->porcentaje_match" />
        <form method="POST" action="{{ route('postulante.vacantes.guardar', $vacante) }}">
            @csrf
            <button type="submit" aria-label="{{ $guardada ? 'Quitar de guardados' : 'Guardar vacante' }}" aria-pressed="{{ $guardada ? 'true' : 'false' }}"
                    class="flex h-11 w-11 items-center justify-center rounded-lg border {{ $guardada ? 'border-blue-700 bg-blue-50 text-blue-700' : 'border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="{{ $guardada ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 3h12v18l-6-4-6 4z" /></svg>
            </button>
        </form>
    </div>
</article>
