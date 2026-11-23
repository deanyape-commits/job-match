{{--
    Campanita de notificaciones con menú desplegable (Alpine.js).
    Uso: <x-campanita />  o  <x-campanita oscuro />  (para el menú lateral)
--}}
@props(['oscuro' => false])

@php
    $usuario = Auth::user();
    $sinLeer = $usuario->unreadNotifications()->count();
    $recientes = $usuario->notifications()->limit(5)->get();
@endphp

<div x-data="{ abierto: false }" @click.outside="abierto = false" @keydown.escape="abierto = false" class="relative">
    <button type="button" @click="abierto = !abierto" :aria-expanded="abierto"
            aria-label="Notificaciones{{ $sinLeer ? " ($sinLeer sin leer)" : '' }}"
            class="relative flex h-10 w-10 items-center justify-center rounded-lg border {{ $oscuro ? 'border-slate-700 text-slate-200 hover:bg-slate-800' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-50' }}">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9" /><path d="M10 21a2 2 0 0 0 4 0" /></svg>
        @if ($sinLeer)
            <span class="absolute -right-1.5 -top-1.5 rounded-full bg-orange-700 px-1.5 text-[11px] font-bold leading-5 text-white">{{ $sinLeer > 9 ? '9+' : $sinLeer }}</span>
        @endif
    </button>

    <div x-show="abierto" x-cloak x-transition.origin.top
         class="absolute z-50 mt-2 w-80 max-w-[calc(100vw-2rem)] overflow-hidden rounded-xl border border-slate-200 bg-white text-slate-900 shadow-lg {{ $oscuro ? 'left-0' : 'right-0' }}">
        <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
            <p class="font-semibold">Notificaciones</p>
            @if ($sinLeer)
                <form method="POST" action="{{ route('notificaciones.leer-todas') }}">
                    @csrf
                    <button type="submit" class="text-xs font-semibold text-blue-700 hover:text-blue-900">Marcar todas como leídas</button>
                </form>
            @endif
        </div>
        <ul class="max-h-96 divide-y divide-slate-100 overflow-y-auto">
            @forelse ($recientes as $notificacion)
                <li>
                    <a href="{{ route('notificaciones.abrir', $notificacion->id) }}" class="flex gap-3 px-4 py-3 hover:bg-slate-50">
                        <span aria-hidden="true" class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $notificacion->read_at ? 'bg-transparent' : 'bg-blue-700' }}"></span>
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-semibold">{{ $notificacion->data['titulo'] ?? 'Notificación' }}</span>
                            <span class="block text-sm text-slate-600">{{ $notificacion->data['mensaje'] ?? '' }}</span>
                            <span class="block text-xs text-slate-500">{{ $notificacion->created_at->diffForHumans() }}</span>
                        </span>
                        @unless ($notificacion->read_at)<span class="sr-only">(sin leer)</span>@endunless
                    </a>
                </li>
            @empty
                <li class="px-4 py-6 text-center text-sm text-slate-500">No tienes notificaciones.</li>
            @endforelse
        </ul>
        <a href="{{ route('notificaciones.index') }}" class="block border-t border-slate-100 px-4 py-2.5 text-center text-sm font-semibold text-blue-700 hover:bg-slate-50">Ver todas</a>
    </div>
</div>
