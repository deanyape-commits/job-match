<x-app-layout>
    <x-slot name="titulo">Mis vacantes</x-slot>

    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold">Mis vacantes</h1>
                <p class="mt-1 text-slate-600">Todas las vacantes de {{ Auth::user()->empresa->razon_social }}.</p>
            </div>
            @if ($puedePublicar)
                <a href="{{ route('reclutador.vacantes.create') }}" class="inline-flex items-center rounded-lg bg-blue-700 px-5 py-2.5 font-semibold text-white hover:bg-blue-800">+ Publicar vacante</a>
            @endif
        </div>
    </x-slot>

    @unless ($puedePublicar)
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
            Tu empresa aún no está verificada. Podrás publicar vacantes cuando un supervisor la apruebe.
        </div>
    @endunless

    <nav aria-label="Filtrar por estado" class="mb-4 flex flex-wrap gap-2">
        @foreach ([null => 'Todas', 'publicada' => 'Publicadas', 'borrador' => 'Borradores', 'cerrada' => 'Cerradas'] as $clave => $texto)
            <a href="{{ route('reclutador.vacantes.index', array_filter(['estado' => $clave])) }}"
               class="rounded-full px-4 py-2 text-sm font-medium border {{ $estado == $clave ? 'bg-slate-900 text-white border-slate-900' : 'bg-white text-slate-700 border-slate-300 hover:border-slate-400' }}">
                {{ $texto }}
            </a>
        @endforeach
    </nav>

    @if ($vacantes->isEmpty())
        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600">
            Todavía no hay vacantes aquí.
        </div>
    @else
        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
            <table class="w-full min-w-[820px] text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wide text-slate-500 border-b border-slate-200">
                        <th scope="col" class="px-5 py-3 font-semibold">Vacante</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Estado</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Postulantes</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Contratados</th>
                        <th scope="col" class="px-5 py-3 font-semibold">Cierra</th>
                        <th scope="col" class="px-5 py-3"><span class="sr-only">Acciones</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vacantes as $vacante)
                        <tr class="border-b border-slate-100 last:border-0">
                            <td class="px-5 py-4">
                                <p class="font-semibold">{{ $vacante->titulo }}</p>
                                <p class="text-xs text-slate-500">{{ $vacante->modalidad_texto }} · {{ $vacante->nivel_texto }} · {{ $vacante->salario_texto }}</p>
                            </td>
                            <td class="px-5 py-4"><x-estado :valor="$vacante->estado" /></td>
                            <td class="px-5 py-4">{{ $vacante->postulaciones_count }}</td>
                            <td class="px-5 py-4">{{ $vacante->contratados_count }} / {{ $vacante->puestos }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $vacante->cierra_en?->translatedFormat('d M Y') ?? '—' }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-3 whitespace-nowrap">
                                    @if (Route::has('reclutador.vacantes.candidatos') && $vacante->estado !== 'borrador')
                                        <a href="{{ route('reclutador.vacantes.candidatos', $vacante) }}" class="font-semibold text-blue-700 hover:text-blue-900">Candidatos</a>
                                    @endif
                                    <a href="{{ route('reclutador.vacantes.edit', $vacante) }}" class="font-semibold text-slate-700 hover:text-slate-900">Editar</a>
                                    @if ($vacante->estado === 'publicada')
                                        <form method="POST" action="{{ route('reclutador.vacantes.cerrar', $vacante) }}"
                                              onsubmit="return confirm('¿Cerrar esta vacante? Dejará de aparecer en el buscador.')">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="font-semibold text-amber-700 hover:text-amber-900">Cerrar</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('reclutador.vacantes.destroy', $vacante) }}"
                                          onsubmit="return confirm('¿Eliminar esta vacante?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="font-semibold text-red-700 hover:text-red-900">Eliminar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $vacantes->links() }}</div>
    @endif
</x-app-layout>
