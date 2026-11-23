{{--
    Avisos flotantes (toasts). Los mensajes flash de Laravel —redirect()->with('exito', '...')
    o ->with('error', '...')— y los que llegan por JavaScript (window.avisar) aparecen
    arriba a la derecha y se ocultan solos a los 5 segundos.
--}}
<div x-data
     x-init="
        @if (session('exito')) $store.avisos.mostrar(@js(session('exito')), 'exito'); @endif
        @if (session('error')) $store.avisos.mostrar(@js(session('error')), 'error'); @endif
     "
     aria-live="polite"
     class="pointer-events-none fixed right-4 top-4 z-[60] flex w-[min(calc(100%-2rem),24rem)] flex-col gap-2">
    <template x-for="aviso in $store.avisos.lista" :key="aviso.id">
        <div x-transition:enter="transition duration-300 ease-out"
             x-transition:enter-start="translate-x-8 opacity-0"
             x-transition:enter-end="translate-x-0 opacity-100"
             :role="aviso.tipo === 'error' ? 'alert' : 'status'"
             :class="{
                'border-teal-200 bg-teal-50 text-teal-900': aviso.tipo === 'exito',
                'border-red-200 bg-red-50 text-red-900': aviso.tipo === 'error',
                'border-coral-200 bg-white text-slate-900': aviso.tipo === 'info',
             }"
             class="pointer-events-auto flex items-start gap-3 rounded-xl border px-4 py-3 shadow-lg">
            <span class="flex-1 text-sm font-medium" x-text="aviso.texto"></span>
            <button type="button" @click="$store.avisos.cerrar(aviso.id)" class="-mr-1 -mt-0.5 rounded p-1 text-lg leading-none opacity-60 hover:opacity-100" aria-label="Cerrar aviso">×</button>
        </div>
    </template>
</div>

{{-- Sin JavaScript: los mensajes se muestran como antes --}}
<noscript>
    @if (session('exito'))
        <div role="status" class="mb-6 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-teal-900">{{ session('exito') }}</div>
    @endif
    @if (session('error'))
        <div role="alert" class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-900">{{ session('error') }}</div>
    @endif
</noscript>
