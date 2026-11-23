<x-app-layout>
    <x-slot name="titulo">Notificaciones</x-slot>

    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <h1 class="font-display text-3xl font-bold">Notificaciones</h1>
                <p class="mt-1 text-slate-600">Avisos sobre tus postulaciones, entrevistas y tu empresa.</p>
            </div>
            @if (Auth::user()->unreadNotifications()->exists())
                <form method="POST" action="{{ route('notificaciones.leer-todas') }}">
                    @csrf
                    <x-secondary-button type="submit">Marcar todas como leídas</x-secondary-button>
                </form>
            @endif
        </div>
    </x-slot>

    <div class="max-w-3xl overflow-hidden rounded-2xl border border-slate-200 bg-white">
        <ul class="divide-y divide-slate-100">
            @forelse ($notificaciones as $notificacion)
                <li>
                    <a href="{{ route('notificaciones.abrir', $notificacion->id) }}" class="flex gap-4 px-5 py-4 hover:bg-slate-50 {{ $notificacion->read_at ? '' : 'bg-blue-50/50' }}">
                        <span aria-hidden="true" class="mt-2 h-2.5 w-2.5 shrink-0 rounded-full {{ $notificacion->read_at ? 'bg-slate-200' : 'bg-blue-700' }}"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-semibold">{{ $notificacion->data['titulo'] ?? 'Notificación' }}</span>
                            <span class="block text-slate-700">{{ $notificacion->data['mensaje'] ?? '' }}</span>
                        </span>
                        <span class="shrink-0 text-sm text-slate-500">{{ $notificacion->created_at->diffForHumans() }}</span>
                    </a>
                </li>
            @empty
                <li class="px-5 py-10 text-center text-slate-600">Aún no tienes notificaciones.</li>
            @endforelse
        </ul>
    </div>

    <div class="mt-5 max-w-3xl">{{ $notificaciones->links() }}</div>
</x-app-layout>
