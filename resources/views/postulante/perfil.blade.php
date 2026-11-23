@php
    $claseCampo = 'block mt-1 w-full border-slate-300 focus:border-blue-600 focus:ring-blue-600 rounded-lg shadow-sm';
    $habilidadesActuales = old('habilidades')
        ? collect(old('habilidades'))->map(fn ($nivel, $id) => ['id' => (int) $id, 'nivel' => (int) $nivel])->values()
        : $perfil->habilidades->map(fn ($h) => ['id' => $h->id, 'nivel' => (int) $h->pivot->nivel])->values();
    $grados = ['tecnico' => 'Técnico', 'bachiller' => 'Bachiller', 'titulado' => 'Titulado', 'maestria' => 'Maestría', 'curso' => 'Curso / certificación'];
@endphp

<x-app-layout>
    <x-slot name="titulo">Mi perfil</x-slot>

    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold">Mi perfil profesional</h1>
                <p class="mt-1 text-slate-600">Es lo que ven las empresas cuando postulas, y lo que usamos para calcular tu compatibilidad.</p>
            </div>
            <div class="w-full max-w-xs">
                <p class="flex justify-between text-sm"><span class="text-slate-600">Perfil completo</span><strong>{{ $completitud['porcentaje'] }}%</strong></p>
                <div class="mt-1.5 h-2.5 rounded-full bg-slate-200" role="progressbar" aria-valuenow="{{ $completitud['porcentaje'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="Perfil completo">
                    <div class="h-full rounded-full {{ $completitud['porcentaje'] === 100 ? 'bg-teal-600' : 'bg-blue-700' }}" style="width: {{ $completitud['porcentaje'] }}%"></div>
                </div>
            </div>
        </div>
        @if ($completitud['faltantes'])
            <p class="mt-3 text-sm text-slate-600">Te falta: {{ implode(', ', $completitud['faltantes']) }}.</p>
        @endif
    </x-slot>

    <div class="max-w-4xl space-y-6">
        {{-- 1. Datos generales y CV --}}
        <form method="POST" action="{{ route('postulante.perfil.update') }}" enctype="multipart/form-data"
              class="space-y-5 rounded-2xl border border-slate-200 bg-white p-6">
            @csrf @method('PATCH')
            <h2 class="text-lg font-semibold">Datos profesionales</h2>

            <div>
                <x-input-label for="titular" value="Titular profesional" />
                <x-text-input id="titular" name="titular" class="block mt-1 w-full" :value="old('titular', $perfil->titular)" required maxlength="150" placeholder="Ej.: Desarrollador Backend Jr." />
                <x-input-error :messages="$errors->get('titular')" class="mt-2" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    <x-input-label for="ubicacion_id" value="Ubicación" />
                    <select id="ubicacion_id" name="ubicacion_id" class="{{ $claseCampo }}" required>
                        <option value="">Selecciona…</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}" @selected(old('ubicacion_id', $perfil->ubicacion_id) == $ubicacion->id)>{{ $ubicacion->nombre_completo }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('ubicacion_id')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="anios_experiencia" value="Años de experiencia" />
                    <x-text-input id="anios_experiencia" name="anios_experiencia" type="number" min="0" max="50" class="block mt-1 w-full" :value="old('anios_experiencia', $perfil->anios_experiencia)" required />
                    <x-input-error :messages="$errors->get('anios_experiencia')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="salario_esperado" value="Salario esperado (S/)" />
                    <x-text-input id="salario_esperado" name="salario_esperado" type="number" min="0" step="100" class="block mt-1 w-full" :value="old('salario_esperado', $perfil->salario_esperado ? (int) $perfil->salario_esperado : null)" />
                    <x-input-error :messages="$errors->get('salario_esperado')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="resumen" value="Resumen" />
                <textarea id="resumen" name="resumen" rows="4" maxlength="2000" class="{{ $claseCampo }}" placeholder="Cuéntales a las empresas quién eres y qué te interesa.">{{ old('resumen', $perfil->resumen) }}</textarea>
                <x-input-error :messages="$errors->get('resumen')" class="mt-2" />
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <x-input-label for="fecha_nacimiento" value="Fecha de nacimiento" />
                    <x-text-input id="fecha_nacimiento" name="fecha_nacimiento" type="date" class="block mt-1 w-full" :value="old('fecha_nacimiento', $perfil->fecha_nacimiento?->format('Y-m-d'))" />
                    <x-input-error :messages="$errors->get('fecha_nacimiento')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="linkedin_url" value="LinkedIn (URL)" />
                    <x-text-input id="linkedin_url" name="linkedin_url" type="url" class="block mt-1 w-full" :value="old('linkedin_url', $perfil->linkedin_url)" placeholder="https://linkedin.com/in/…" />
                    <x-input-error :messages="$errors->get('linkedin_url')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="portafolio_url" value="Portafolio o GitHub (URL)" />
                    <x-text-input id="portafolio_url" name="portafolio_url" type="url" class="block mt-1 w-full" :value="old('portafolio_url', $perfil->portafolio_url)" placeholder="https://github.com/…" />
                    <x-input-error :messages="$errors->get('portafolio_url')" class="mt-2" />
                </div>
            </div>

            <div class="rounded-xl bg-slate-50 p-4">
                <x-input-label for="cv" value="CV en PDF (máx. 2 MB)" />
                @if ($perfil->cv_ruta)
                    <p class="mt-1 text-sm text-slate-700">
                        Ya subiste tu CV. <a href="{{ route('cv.show', $perfil) }}" target="_blank" class="font-semibold text-blue-700 underline">Ver mi CV</a>.
                        Si eliges otro archivo, reemplazará al actual.
                    </p>
                @endif
                <input id="cv" name="cv" type="file" accept="application/pdf"
                       class="mt-2 block w-full text-sm text-slate-700 file:mr-4 file:rounded-lg file:border-0 file:bg-blue-700 file:px-4 file:py-2 file:font-semibold file:text-white hover:file:bg-blue-800">
                <x-input-error :messages="$errors->get('cv')" class="mt-2" />
            </div>

            <div class="flex justify-end">
                <x-primary-button>Guardar datos</x-primary-button>
            </div>
        </form>

        {{-- 2. Habilidades con nivel (Alpine.js) --}}
        <form method="POST" action="{{ route('postulante.perfil.habilidades') }}"
              class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6"
              x-data="{
                  catalogo: @js($catalogoHabilidades),
                  sel: @js($habilidadesActuales),
                  busqueda: '',
                  get sugerencias() {
                      const q = this.busqueda.trim().toLowerCase();
                      if (!q) return [];
                      return this.catalogo.filter(h => !this.sel.some(s => s.id === h.id) && h.nombre.toLowerCase().includes(q)).slice(0, 8);
                  },
                  nombre(id) { return this.catalogo.find(h => h.id === id)?.nombre },
                  agregar(h) { this.sel.push({ id: h.id, nivel: 3 }); this.busqueda = ''; },
                  quitar(id) { this.sel = this.sel.filter(s => s.id !== id); },
              }">
            @csrf @method('PUT')
            <div>
                <h2 class="text-lg font-semibold">Habilidades</h2>
                <p class="text-sm text-slate-600">Son las que se comparan con las que piden las vacantes. Indica tu nivel del 1 (básico) al 5 (experto).</p>
            </div>

            <div class="relative">
                <label for="buscar-habilidad" class="sr-only">Agregar habilidad</label>
                <input id="buscar-habilidad" type="text" x-model="busqueda" autocomplete="off"
                       @keydown.enter.prevent="if (sugerencias.length) agregar(sugerencias[0])"
                       placeholder="Escribe para agregar (ej. Laravel, SQL, Inglés…)" class="{{ $claseCampo }} mt-0">
                <ul x-show="sugerencias.length" x-cloak class="absolute z-10 mt-1 w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                    <template x-for="h in sugerencias" :key="h.id">
                        <li><button type="button" @click="agregar(h)" class="block w-full px-4 py-2 text-left hover:bg-slate-50" x-text="h.nombre"></button></li>
                    </template>
                </ul>
            </div>

            <ul class="grid gap-2 sm:grid-cols-2">
                <template x-for="s in sel" :key="s.id">
                    <li class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 px-4 py-2">
                        <span class="font-semibold" x-text="nombre(s.id)"></span>
                        <span class="flex items-center gap-1">
                            <label class="sr-only" :for="'nivel-' + s.id" x-text="'Nivel de ' + nombre(s.id)"></label>
                            <select :id="'nivel-' + s.id" :name="`habilidades[${s.id}]`" x-model.number="s.nivel"
                                    class="rounded-lg border-slate-300 py-1 text-sm focus:border-blue-600 focus:ring-blue-600">
                                <option value="1">1 · Básico</option>
                                <option value="2">2</option>
                                <option value="3">3 · Intermedio</option>
                                <option value="4">4</option>
                                <option value="5">5 · Experto</option>
                            </select>
                            <button type="button" @click="quitar(s.id)" class="h-9 w-9 rounded-lg text-xl text-slate-500 hover:bg-slate-200" :aria-label="'Quitar ' + nombre(s.id)">×</button>
                        </span>
                    </li>
                </template>
            </ul>
            <p x-show="!sel.length" class="text-sm text-slate-500">Aún no agregaste habilidades.</p>
            <x-input-error :messages="$errors->get('habilidades')" />

            <div class="flex justify-end">
                <x-primary-button>Guardar habilidades</x-primary-button>
            </div>
        </form>

        {{-- 3. Experiencia laboral --}}
        <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6" x-data="{ abierto: {{ $errors->experiencia->any() ? 'true' : 'false' }} }">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold">Experiencia laboral</h2>
                <x-secondary-button @click="abierto = !abierto" x-text="abierto ? 'Cancelar' : '+ Agregar'">+ Agregar</x-secondary-button>
            </div>

            <form x-show="abierto" x-cloak method="POST" action="{{ route('postulante.perfil.experiencias.store') }}" class="grid gap-4 rounded-xl bg-slate-50 p-4 sm:grid-cols-2">
                @csrf
                <div>
                    <x-input-label for="exp_cargo" value="Cargo" />
                    <x-text-input id="exp_cargo" name="cargo" class="block mt-1 w-full" :value="old('cargo')" required />
                    <x-input-error :messages="$errors->experiencia->get('cargo')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="exp_empresa" value="Empresa" />
                    <x-text-input id="exp_empresa" name="empresa_nombre" class="block mt-1 w-full" :value="old('empresa_nombre')" required />
                    <x-input-error :messages="$errors->experiencia->get('empresa_nombre')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="exp_inicio" value="Desde" />
                    <x-text-input id="exp_inicio" name="fecha_inicio" type="date" class="block mt-1 w-full" :value="old('fecha_inicio')" required />
                    <x-input-error :messages="$errors->experiencia->get('fecha_inicio')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="exp_fin" value="Hasta (vacío si es tu trabajo actual)" />
                    <x-text-input id="exp_fin" name="fecha_fin" type="date" class="block mt-1 w-full" :value="old('fecha_fin')" />
                    <x-input-error :messages="$errors->experiencia->get('fecha_fin')" class="mt-2" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="exp_desc" value="Funciones y logros (opcional)" />
                    <textarea id="exp_desc" name="descripcion" rows="3" maxlength="1000" class="{{ $claseCampo }}">{{ old('descripcion') }}</textarea>
                </div>
                <div class="sm:col-span-2 flex justify-end"><x-primary-button>Agregar experiencia</x-primary-button></div>
            </form>

            @forelse ($perfil->experiencias as $experiencia)
                <div class="flex items-start justify-between gap-4 border-t border-slate-100 pt-4 first-of-type:border-0">
                    <div>
                        <p class="font-semibold">{{ $experiencia->cargo }}</p>
                        <p class="text-sm text-slate-700">{{ $experiencia->empresa_nombre }}</p>
                        <p class="text-sm text-slate-500">
                            {{ $experiencia->fecha_inicio->translatedFormat('M Y') }} – {{ $experiencia->fecha_fin?->translatedFormat('M Y') ?? 'Actualidad' }}
                        </p>
                        @if ($experiencia->descripcion)
                            <p class="mt-1 text-sm text-slate-600">{{ $experiencia->descripcion }}</p>
                        @endif
                    </div>
                    <form method="POST" action="{{ route('postulante.perfil.experiencias.destroy', $experiencia) }}" onsubmit="return confirm('¿Eliminar esta experiencia?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-red-700 hover:text-red-900">Eliminar</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-slate-500">Aún no agregaste experiencia laboral. Si estás empezando, agrega prácticas o proyectos.</p>
            @endforelse
        </section>

        {{-- 4. Estudios --}}
        <section class="space-y-4 rounded-2xl border border-slate-200 bg-white p-6" x-data="{ abierto: {{ $errors->estudio->any() ? 'true' : 'false' }} }">
            <div class="flex items-center justify-between gap-3">
                <h2 class="text-lg font-semibold">Estudios</h2>
                <x-secondary-button @click="abierto = !abierto" x-text="abierto ? 'Cancelar' : '+ Agregar'">+ Agregar</x-secondary-button>
            </div>

            <form x-show="abierto" x-cloak method="POST" action="{{ route('postulante.perfil.estudios.store') }}" class="grid gap-4 rounded-xl bg-slate-50 p-4 sm:grid-cols-2">
                @csrf
                <div>
                    <x-input-label for="est_inst" value="Institución" />
                    <x-text-input id="est_inst" name="institucion" class="block mt-1 w-full" :value="old('institucion')" required placeholder="Ej.: Tecsup" />
                    <x-input-error :messages="$errors->estudio->get('institucion')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="est_carrera" value="Carrera o curso" />
                    <x-text-input id="est_carrera" name="carrera" class="block mt-1 w-full" :value="old('carrera')" required />
                    <x-input-error :messages="$errors->estudio->get('carrera')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="est_grado" value="Grado" />
                    <select id="est_grado" name="grado" class="{{ $claseCampo }}" required>
                        @foreach ($grados as $valor => $texto)
                            <option value="{{ $valor }}" @selected(old('grado') === $valor)>{{ $texto }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="est_inicio" value="Desde" />
                        <x-text-input id="est_inicio" name="fecha_inicio" type="date" class="block mt-1 w-full" :value="old('fecha_inicio')" required />
                    </div>
                    <div>
                        <x-input-label for="est_fin" value="Hasta" />
                        <x-text-input id="est_fin" name="fecha_fin" type="date" class="block mt-1 w-full" :value="old('fecha_fin')" />
                    </div>
                    <x-input-error :messages="array_merge($errors->estudio->get('fecha_inicio'), $errors->estudio->get('fecha_fin'))" class="col-span-2" />
                </div>
                <div class="sm:col-span-2 flex justify-end"><x-primary-button>Agregar estudio</x-primary-button></div>
            </form>

            @forelse ($perfil->estudios as $estudio)
                <div class="flex items-start justify-between gap-4 border-t border-slate-100 pt-4 first-of-type:border-0">
                    <div>
                        <p class="font-semibold">{{ $estudio->carrera }}</p>
                        <p class="text-sm text-slate-700">{{ $estudio->institucion }} · {{ $grados[$estudio->grado] ?? $estudio->grado }}</p>
                        <p class="text-sm text-slate-500">{{ $estudio->fecha_inicio->format('Y') }} – {{ $estudio->fecha_fin?->format('Y') ?? 'En curso' }}</p>
                    </div>
                    <form method="POST" action="{{ route('postulante.perfil.estudios.destroy', $estudio) }}" onsubmit="return confirm('¿Eliminar este estudio?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-sm font-semibold text-red-700 hover:text-red-900">Eliminar</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-slate-500">Aún no agregaste estudios.</p>
            @endforelse
        </section>
    </div>
</x-app-layout>
