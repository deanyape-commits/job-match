{{-- Selector para cambiar el estado de una postulación. Se envía al elegir una opción. Variable: $postulacion --}}
<form method="POST" action="{{ route('reclutador.postulaciones.estado', $postulacion) }}"
      @if (! empty($confirmarContratado)) onsubmit="return this.estado.value !== 'contratado' || confirm('¿Marcar como contratado?')" @endif>
    @csrf @method('PATCH')
    <label class="flex flex-col gap-1 text-xs font-medium text-slate-600">
        Estado
        <select name="estado" onchange="this.form.requestSubmit()"
                class="h-10 rounded-lg border-slate-300 text-sm text-slate-900 focus:border-blue-600 focus:ring-blue-600">
            @foreach (\App\Models\Postulacion::ESTADOS_TEXTO as $valor => $texto)
                <option value="{{ $valor }}" @selected($postulacion->estado === $valor)>{{ $texto }}</option>
            @endforeach
        </select>
    </label>
    <noscript><button type="submit" class="mt-1 text-sm font-semibold text-blue-700">Guardar</button></noscript>
</form>
