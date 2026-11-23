@php
    $claseSelect = 'block w-full border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-lg shadow-sm';
    $marcado = fn (string $campo, string $valor) => in_array($valor, $filtros[$campo] ?? [], true);
@endphp

<x-app-layout>
    <x-slot name="titulo">Buscar empleos</x-slot>

    {{-- Barra de búsqueda --}}
    <form method="GET" action="{{ route('postulante.vacantes.index') }}" id="form-busqueda"
          class="mb-6 flex flex-wrap gap-3 rounded-2xl border border-slate-200 bg-white p-4">
        <label class="flex-[2_1_280px]">
            <span class="mb-1 block text-xs font-medium text-slate-700">Puesto, empresa o habilidad</span>
            <x-text-input name="q" :value="$filtros['q'] ?? ''" class="w-full" placeholder="Ej.: Laravel, Analista de datos…" />
        </label>
        <label class="flex-[1_1_180px]">
            <span class="mb-1 block text-xs font-medium text-slate-700">Departamento</span>
            <select name="departamento" class="{{ $claseSelect }}">
                <option value="">Todo el Perú</option>
                @foreach ($departamentos as $departamento)
                    <option value="{{ $departamento }}" @selected(($filtros['departamento'] ?? '') === $departamento)>{{ $departamento }}</option>
                @endforeach
            </select>
        </label>
        <x-primary-button class="self-end h-[42px]">Buscar</x-primary-button>
    </form>

    @if ($completitud['porcentaje'] < 100)
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
            Tu perfil está completo al <strong>{{ $completitud['porcentaje'] }}%</strong>.
            Agrega {{ implode(', ', $completitud['faltantes']) }} para que tu compatibilidad sea más precisa.
            <a href="{{ route('postulante.perfil.edit') }}" class="font-semibold underline">Completar perfil</a>
        </div>
    @endif

    <div class="flex flex-wrap items-start gap-7">
        {{-- Filtros (usan el mismo formulario mediante form="form-busqueda") --}}
        <aside aria-label="Filtros" class="flex-[1_1_240px] lg:max-w-[280px] space-y-5 rounded-2xl border border-slate-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <h2 class="font-semibold">Filtros</h2>
                <a href="{{ route('postulante.vacantes.index') }}" class="text-sm text-blue-700 hover:text-blue-900">Limpiar</a>
            </div>

            @foreach ([
                ['modalidad', 'Modalidad', \App\Models\Vacante::MODALIDADES],
                ['nivel', 'Nivel de experiencia', \App\Models\Vacante::NIVELES],
                ['tipo_contrato', 'Tipo de contrato', \App\Models\Vacante::TIPOS_CONTRATO],
            ] as [$campo, $titulo, $opciones])
                <fieldset class="space-y-2">
                    <legend class="mb-2 text-sm font-semibold">{{ $titulo }}</legend>
                    @foreach ($opciones as $valor => $texto)
                        <label class="flex items-center gap-2 text-sm text-slate-700">
                            <input type="checkbox" form="form-busqueda" name="{{ $campo }}[]" value="{{ $valor }}" @checked($marcado($campo, $valor))
                                   class="rounded border-slate-300 text-blue-700 focus:ring-blue-600">
                            {{ $texto }}
                        </label>
                    @endforeach
                </fieldset>
            @endforeach

            <label class="block text-sm font-semibold">Categoría
                <select name="categoria_id" form="form-busqueda" class="{{ $claseSelect }} mt-2 font-normal">
                    <option value="">Todas</option>
                    @foreach ($categorias as $categoria)
                        <option value="{{ $categoria->id }}" @selected(($filtros['categoria_id'] ?? null) == $categoria->id)>{{ $categoria->nombre }}</option>
                    @endforeach
                </select>
            </label>

            <label class="block text-sm font-semibold">Salario desde (S/)
                <x-text-input type="number" name="salario" form="form-busqueda" min="0" step="500" :value="$filtros['salario'] ?? ''" class="mt-2 w-full font-normal" placeholder="Ej.: 3000" />
            </label>

            <x-primary-button form="form-busqueda" class="w-full">Aplicar filtros</x-primary-button>
        </aside>

        <main class="min-w-0 flex-[999_1_560px] space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-slate-700">
                    <strong class="text-slate-900">{{ $vacantes->total() }} {{ $vacantes->total() === 1 ? 'vacante' : 'vacantes' }}</strong>
                    @if (! empty($filtros['q'])) para "{{ $filtros['q'] }}" @endif
                </p>
                <label class="flex items-center gap-2 text-sm text-slate-700">Ordenar por
                    <select name="orden" form="form-busqueda" onchange="this.form.submit()" class="rounded-lg border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600">
                        <option value="match" @selected($orden === 'match')>Mayor compatibilidad</option>
                        <option value="recientes" @selected($orden === 'recientes')>Más recientes</option>
                        <option value="salario" @selected($orden === 'salario')>Mayor salario</option>
                    </select>
                </label>
            </div>

            @forelse ($vacantes as $vacante)
                @include('postulante.vacantes._tarjeta', [
                    'guardada' => in_array($vacante->id, $guardadas, true),
                    'postulada' => in_array($vacante->id, $postuladas, true),
                ])
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-slate-600">
                    No encontramos vacantes con esos filtros. Prueba con una búsqueda más amplia.
                </div>
            @endforelse

            <div>{{ $vacantes->links() }}</div>
        </main>
    </div>
</x-app-layout>
