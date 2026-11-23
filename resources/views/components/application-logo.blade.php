{{--
    Logo de JobMatch: silueta + nombre.
    <x-application-logo />         → silueta oscura, para fondos claros
    <x-application-logo oscuro />  → silueta blanca, para fondos oscuros
--}}
@props(['oscuro' => false])

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2']) }}>
    <img src="{{ asset($oscuro ? 'img/logo-blanco.png' : 'img/logo-negro.png') }}" alt="" class="h-10 w-10" width="40" height="40">
    <span class="font-display text-xl font-bold {{ $oscuro ? 'text-white' : 'text-slate-900' }}">JobMatch</span>
</span>
