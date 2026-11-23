{{--
    Selector para cambiar el estado de una postulación. Variable: $postulacion
    Con JavaScript se guarda al instante sin recargar (Alpine.js) y muestra un aviso;
    sin JavaScript funciona como formulario normal.
--}}
<form method="POST" action="{{ route('reclutador.postulaciones.estado', $postulacion) }}"
      x-data="estadoPostulacion(@js(route('reclutador.postulaciones.estado', $postulacion)), @js($postulacion->estado))"
      @submit.prevent="cambiar()">
    @csrf @method('PATCH')
    <label class="flex flex-col gap-1 text-xs font-medium text-slate-600">
        Estado
        <select name="estado" x-model="estado" @change="cambiar()" :disabled="enviando"
                class="h-10 rounded-lg border-slate-300 text-sm text-slate-900 transition focus:border-coral-600 focus:ring-coral-600 disabled:opacity-60">
            @foreach (\App\Models\Postulacion::ESTADOS_TEXTO as $valor => $texto)
                <option value="{{ $valor }}" @selected($postulacion->estado === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
    </label>
    <noscript><button type="submit" class="mt-1 text-sm font-semibold text-coral-700">Guardar</button></noscript>
</form>
