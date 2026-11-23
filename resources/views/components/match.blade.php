{{-- Porcentaje de compatibilidad con color según el valor. Uso: <x-match :porcentaje="$vacante->porcentaje_match" /> --}}
@props(['porcentaje', 'etiqueta' => 'compatibilidad'])

@php
    $valor = (int) round((float) $porcentaje);
    $color = match (true) {
        $valor >= 80 => 'text-teal-700',
        $valor >= 50 => 'text-blue-700',
        default => 'text-slate-600',
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex min-w-[76px] flex-col items-center']) }}>
    <span class="font-display text-2xl font-bold {{ $color }}">{{ $valor }}%</span>
    <span class="text-xs text-slate-600">{{ $etiqueta }}</span>
</div>
