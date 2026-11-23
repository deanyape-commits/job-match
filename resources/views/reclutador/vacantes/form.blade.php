@php
    $editando = $vacante->exists;
    $claseSelect = 'block mt-1 w-full border-slate-300 focus:border-coral-600 focus:ring-coral-600 rounded-lg shadow-sm';

    // Habilidades ya elegidas: las del intento anterior (si hubo error) o las guardadas
    $seleccionadas = old('habilidades')
        ? collect(old('habilidades'))->map(fn ($obligatoria, $id) => ['id' => (int) $id, 'obligatoria' => (bool) $obligatoria])->values()
        : $vacante->habilidades->map(fn ($h) => ['id' => $h->id, 'obligatoria' => (bool) $h->pivot->obligatoria])->values();
@endphp

<x-app-layout>
    <x-slot name="titulo">{{ $editando ? 'Editar vacante' : 'Publicar vacante' }}</x-slot>

    <x-slot name="header">
        <a href="{{ route('reclutador.vacantes.index') }}" class="text-sm font-medium text-coral-700 hover:text-coral-900">← Mis vacantes</a>
        <h1 class="mt-2 font-display text-3xl font-bold">{{ $editando ? 'Editar vacante' : 'Publicar nueva vacante' }}</h1>
        <p class="mt-1 text-slate-600">Las habilidades que marques se usan para calcular la compatibilidad de cada postulante.</p>
    </x-slot>

    <form method="POST" action="{{ $editando ? route('reclutador.vacantes.update', $vacante) : route('reclutador.vacantes.store') }}" class="max-w-4xl space-y-6">
        @csrf
        @if ($editando) @method('PUT') @endif

        @if ($errors->any())
            <div role="alert" class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-900">
                Revisa los campos marcados en rojo.
            </div>
        @endif

        {{-- 1. Información general --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 space-y-5">
            <h2 class="text-lg font-semibold">1. Información general</h2>

            <div>
                <x-input-label for="titulo" value="Título del puesto" />
                <x-text-input id="titulo" name="titulo" class="block mt-1 w-full" :value="old('titulo', $vacante->titulo)" required maxlength="150" placeholder="Ej.: Desarrollador Backend PHP Jr." />
                <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <x-input-label for="categoria_id" value="Categoría" />
                    <select id="categoria_id" name="categoria_id" class="{{ $claseSelect }}" required>
                        <option value="">Selecciona…</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(old('categoria_id', $vacante->categoria_id) == $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('categoria_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="ubicacion_id" value="Ubicación" />
                    <select id="ubicacion_id" name="ubicacion_id" class="{{ $claseSelect }}" required>
                        <option value="">Selecciona…</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}" @selected(old('ubicacion_id', $vacante->ubicacion_id) == $ubicacion->id)>{{ $ubicacion->nombre_completo }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('ubicacion_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="nivel_experiencia" value="Nivel de experiencia" />
                    <select id="nivel_experiencia" name="nivel_experiencia" class="{{ $claseSelect }}" required>
                        @foreach (\App\Models\Vacante::NIVELES as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('nivel_experiencia', $vacante->nivel_experiencia) === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <fieldset>
                <legend class="block font-medium text-sm text-slate-700">Modalidad</legend>
                <div class="mt-2 flex flex-wrap gap-3">
                    @foreach (\App\Models\Vacante::MODALIDADES as $valor => $texto)
                        <label class="cursor-pointer">
                            <input type="radio" name="modalidad" value="{{ $valor }}" class="peer sr-only" @checked(old('modalidad', $vacante->modalidad) === $valor)>
                            <span class="inline-block rounded-lg border-2 border-slate-200 px-5 py-2.5 font-medium text-slate-700 peer-checked:border-coral-700 peer-checked:bg-coral-50 peer-checked:text-coral-900 peer-focus-visible:ring-2 peer-focus-visible:ring-coral-500">{{ $texto }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('modalidad')" class="mt-2" />
            </fieldset>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div class="lg:col-span-1">
                    <x-input-label for="tipo_contrato" value="Tipo de contrato" />
                    <select id="tipo_contrato" name="tipo_contrato" class="{{ $claseSelect }}" required>
                        @foreach (\App\Models\Vacante::TIPOS_CONTRATO as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('tipo_contrato', $vacante->tipo_contrato) === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="salario_min" value="Salario mínimo (S/)" />
                    <x-text-input id="salario_min" name="salario_min" type="number" min="0" step="50" class="block mt-1 w-full" :value="old('salario_min', $vacante->salario_min ? (int) $vacante->salario_min : null)" />
                    <x-input-error :messages="$errors->get('salario_min')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="salario_max" value="Salario máximo (S/)" />
                    <x-text-input id="salario_max" name="salario_max" type="number" min="0" step="50" class="block mt-1 w-full" :value="old('salario_max', $vacante->salario_max ? (int) $vacante->salario_max : null)" />
                    <x-input-error :messages="$errors->get('salario_max')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="puestos" value="N.º de puestos" />
                    <x-text-input id="puestos" name="puestos" type="number" min="1" max="100" class="block mt-1 w-full" :value="old('puestos', $vacante->puestos)" required />
                    <x-input-error :messages="$errors->get('puestos')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="cierra_en" value="Cierra el (opcional)" />
                    <x-text-input id="cierra_en" name="cierra_en" type="date" class="block mt-1 w-full" :value="old('cierra_en', $vacante->cierra_en?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('cierra_en')" class="mt-2" />
                </div>
            </div>
        </section>

        {{-- 2. Descripción --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 space-y-5">
            <h2 class="text-lg font-semibold">2. Descripción</h2>
            <div>
                <x-input-label for="descripcion" value="Descripción y funciones" />
                <textarea id="descripcion" name="descripcion" rows="6" required class="block mt-1 w-full border-slate-300 focus:border-coral-600 focus:ring-coral-600 rounded-lg shadow-sm">{{ old('descripcion', $vacante->descripcion) }}</textarea>
                <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="requisitos" value="Requisitos" />
                <textarea id="requisitos" name="requisitos" rows="4" required class="block mt-1 w-full border-slate-300 focus:border-coral-600 focus:ring-coral-600 rounded-lg shadow-sm">{{ old('requisitos', $vacante->requisitos) }}</textarea>
                <x-input-error :messages="$errors->get('requisitos')" class="mt-2" />
            </div>
        </section>

        {{-- 3. Habilidades (Alpine.js) --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 space-y-4"
                 x-data="{
                     catalogo: @js($habilidades),
                     sel: @js($seleccionadas),
                     busqueda: '',
                     get sugerencias() {
                         const q = this.busqueda.trim().toLowerCase();
                         if (!q) return [];
                         return this.catalogo.filter(h => !this.sel.some(s => s.id === h.id) && h.nombre.toLowerCase().includes(q)).slice(0, 8);
                     },
                     nombre(id) { return this.catalogo.find(h => h.id === id)?.nombre },
                     agregar(h) { this.sel.push({ id: h.id, obligatoria: true }); this.busqueda = ''; },
                     quitar(id) { this.sel = this.sel.filter(s => s.id !== id); },
                 }">
            <h2 class="text-lg font-semibold">3. Habilidades para el matching</h2>

            <div class="relative">
                <label for="buscar-habilidad" class="sr-only">Agregar habilidad</label>
                <input id="buscar-habilidad" type="text" x-model="busqueda" autocomplete="off"
                       @keydown.enter.prevent="if (sugerencias.length) agregar(sugerencias[0])"
                       placeholder="Escribe para agregar una habilidad (ej. Laravel)"
                       class="block w-full border-slate-300 focus:border-coral-600 focus:ring-coral-600 rounded-lg shadow-sm">
                <ul x-show="sugerencias.length" x-cloak class="absolute z-10 mt-1 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                    <template x-for="h in sugerencias" :key="h.id">
                        <li><button type="button" @click="agregar(h)" class="block w-full px-4 py-2 text-left hover:bg-crema" x-text="h.nombre"></button></li>
                    </template>
                </ul>
            </div>

            <ul class="space-y-2">
                <template x-for="s in sel" :key="s.id">
                    <li class="flex flex-wrap items-center justify-between gap-3 rounded-lg bg-crema px-4 py-2.5">
                        <span class="font-semibold" x-text="nombre(s.id)"></span>
                        <span class="flex items-center gap-2">
                            <button type="button" @click="s.obligatoria = !s.obligatoria"
                                    :class="s.obligatoria ? 'bg-coral-700 text-white border-coral-700' : 'bg-white text-slate-700 border-slate-300'"
                                    class="rounded-full border px-3 py-1.5 text-xs font-semibold"
                                    x-text="s.obligatoria ? 'Obligatoria' : 'Deseable'"></button>
                            <button type="button" @click="quitar(s.id)" class="h-9 w-9 rounded-lg text-xl text-slate-500 hover:bg-slate-200" :aria-label="'Quitar ' + nombre(s.id)">×</button>
                            <input type="hidden" :name="`habilidades[${s.id}]`" :value="s.obligatoria ? 1 : 0">
                        </span>
                    </li>
                </template>
            </ul>
            <p x-show="!sel.length" class="text-sm text-slate-500">Aún no agregaste habilidades.</p>
            <x-input-error :messages="$errors->get('habilidades')" />
        </section>

        <div class="flex flex-wrap justify-end gap-3">
            <x-secondary-button type="submit" name="accion" value="borrador">Guardar borrador</x-secondary-button>
            <x-primary-button name="accion" value="publicar">{{ $editando && $vacante->estado === 'publicada' ? 'Guardar cambios' : 'Publicar vacante' }}</x-primary-button>
        </div>
    </form>
</x-app-layout>
