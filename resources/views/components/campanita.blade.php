{{--
    Campanita de notificaciones (Alpine.js).
    Se actualiza sola cada 30 segundos consultando /notificaciones/resumen:
    si llega algo nuevo, la campana se sacude y aparece un aviso flotante.
    Uso: <x-campanita />  o  <x-campanita oscuro />  (para el menú lateral)
--}}
@props(['oscuro' => false])

@php
    $datos = \App\Http\Controllers\NotificacionController::datosCampanita(Auth::user());
@endphp

<div x-data="campanita(@js(route('notificaciones.resumen')), @js($datos))"
     @click.outside="abierto = false" @keydown.escape="abierto = false" class="relative">
    <button type="button" @click="abierto = !abierto" :aria-expanded="abierto"
            aria-label="Notificaciones{{ $datos['sin_leer'] ? " ({$datos['sin_leer']} sin leer)" : '' }}"
            :aria-label="'Notificaciones' + (sinLeer ? ' (' + sinLeer + ' sin leer)' : '')"
            class="relative flex h-10 w-10 items-center justify-center rounded-lg border transition active:scale-95 {{ $oscuro ? 'border-slate-700 text-slate-200 hover:bg-carbon' : 'border-slate-200 bg-white text-slate-700 hover:bg-crema' }}">
        <svg :class="sacudir && 'animate-campana'" class="origin-top" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9" /><path d="M10 21a2 2 0 0 0 4 0" /></svg>
        <span x-show="sinLeer" x-text="sinLeer > 9 ? '9+' : sinLeer" @if (! $datos['sin_leer']) x-cloak @endif
              class="absolute -right-1.5 -top-1.5 rounded-full bg-coral-700 px-1.5 text-[11px] font-bold leading-5 text-white">{{ $datos['sin_leer'] > 9 ? '9+' : $datos['sin_leer'] }}</span>
    </button>

    <div x-show="abierto" x-cloak
         x-transition:enter="transition duration-150 ease-out" x-transition:enter-start="-translate-y-1 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
         class="absolute z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-slate-200 bg-white text-slate-900 shadow-lg {{ $oscuro ? 'left-0' : 'right-0' }}">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <p class="font-semibold">Notificaciones</p>
            <form x-show="sinLeer" method="POST" action="{{ route('notificaciones.leer-todas') }}">
                @csrf
                <button type="submit" class="text-xs font-semibold text-coral-700 hover:text-coral-900">Marcar todas como leídas</button>
            </form>
        </div>
        <ul class="max-h-96 divide-y divide-slate-100 overflow-y-auto">
            <template x-for="item in items" :key="item.id">
                <li>
                    <a :href="item.url" class="flex gap-3 px-4 py-3 hover:bg-crema">
                        <span aria-hidden="true" class="mt-1.5 h-2 w-2 shrink-0 rounded-full" :class="item.leida ? 'bg-transparent' : 'bg-coral-700'"></span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold" x-text="item.titulo"></span>
                            <span class="block text-sm text-slate-600" x-text="item.mensaje"></span>
                            <span class="block text-xs text-slate-500" x-text="item.hace"></span>
                        </span>
                        <span x-show="!item.leida" class="sr-only">(sin leer)</span>
                    </a>
                </li>
            </template>
            <li x-show="!items.length" class="px-4 py-6 text-center text-sm text-slate-500">No tienes notificaciones.</li>
        </ul>
        <a href="{{ route('notificaciones.index') }}" class="block border-t border-slate-100 px-4 py-2.5 text-center text-sm font-semibold text-coral-700 hover:bg-crema">Ver todas</a>
    </div>
</div>
