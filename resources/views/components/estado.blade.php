{{-- Etiqueta de color para cualquier estado. Uso: <x-estado :valor="$vacante->estado" /> --}}
@props(['valor'])

@php
    [$texto, $clases] = match ($valor) {
        // Empresas
        'pendiente' => ['Pendiente', 'bg-amber-100 text-amber-800'],
        'verificada' => ['Verificada', 'bg-teal-100 text-teal-800'],
        'rechazada' => ['Rechazada', 'bg-red-100 text-red-800'],
        // Vacantes
        'borrador' => ['Borrador', 'bg-slate-100 text-slate-700'],
        'publicada' => ['Publicada', 'bg-teal-100 text-teal-800'],
        'cerrada' => ['Cerrada', 'bg-red-100 text-red-800'],
        // Postulaciones
        'postulado' => ['Postulado', 'bg-slate-100 text-slate-700'],
        'en_revision' => ['En revisión', 'bg-blue-100 text-blue-800'],
        'entrevista' => ['Entrevista', 'bg-amber-100 text-amber-800'],
        'rechazado' => ['No seleccionado', 'bg-red-100 text-red-800'],
        'contratado' => ['Contratado', 'bg-teal-100 text-teal-800'],
        // Entrevistas
        'programada' => ['Programada', 'bg-blue-100 text-blue-800'],
        'realizada' => ['Realizada', 'bg-teal-100 text-teal-800'],
        'cancelada' => ['Cancelada', 'bg-red-100 text-red-800'],
        default => [ucfirst((string) $valor), 'bg-slate-100 text-slate-700'],
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-block rounded-full px-2.5 py-0.5 text-xs font-semibold $clases"]) }}>{{ $texto }}</span>
