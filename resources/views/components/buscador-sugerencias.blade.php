{{--
    Campo de búsqueda con sugerencias en vivo (vacantes, habilidades y empresas).
    Navegable con el teclado: ↑ ↓ para moverse, Enter para abrir, Esc para cerrar.
    Uso: <x-buscador-sugerencias :valor="$q" class="..." input-class="..." placeholder="..." />
--}}
@props(['valor' => '', 'placeholder' => 'Puesto, empresa o habilidad', 'inputClass' => '', 'id' => 'busqueda'])

<div x-data="buscador(@js(route('sugerencias')), @js((string) $valor))"
     @click.outside="abierto = false" {{ $attributes->merge(['class' => 'relative']) }}>
    <input type="search" name="q" id="{{ $id }}" value="{{ $valor }}" autocomplete="off"
           x-model="q" @input="escribir()" @focus="if (grupos.length) abierto = true"
           @keydown.arrow-down.prevent="mover(1)" @keydown.arrow-up.prevent="mover(-1)"
           @keydown.enter="elegir($event)" @keydown.escape="abierto = false"
           role="combobox" aria-autocomplete="list" :aria-expanded="abierto.toString()" aria-controls="{{ $id }}-lista"
           :aria-activedescendant="activo >= 0 ? '{{ $id }}-op-' + activo : null"
           placeholder="{{ $placeholder }}" class="{{ $inputClass }}">

    <div x-show="abierto" x-cloak
         x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
         id="{{ $id }}-lista" role="listbox"
         class="absolute left-0 right-0 z-40 mt-2 max-h-96 overflow-y-auto rounded-xl border border-slate-200 bg-white py-2 text-left text-slate-900 shadow-xl">
        <template x-for="grupo in grupos" :key="grupo.titulo">
            <div role="group" :aria-label="grupo.titulo">
                <p class="px-4 pb-1 pt-2 text-xs font-semibold uppercase tracking-wide text-slate-500" x-text="grupo.titulo"></p>
                <template x-for="item in grupo.items" :key="item.url">
                    <a :href="item.url" role="option" :id="'{{ $id }}-op-' + indice(item)" :aria-selected="(indice(item) === activo).toString()"
                       @mouseenter="activo = indice(item)"
                       :class="indice(item) === activo ? 'bg-coral-50' : ''"
                       class="flex items-center justify-between gap-3 px-4 py-2.5">
                        <span class="truncate font-medium" x-text="item.texto"></span>
                        <span class="shrink-0 text-xs text-slate-500" x-text="item.detalle"></span>
                    </a>
                </template>
            </div>
        </template>
    </div>
</div>
