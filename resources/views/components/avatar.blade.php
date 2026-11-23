{{--
    Foto de perfil del usuario, o sus iniciales si no subió una.
    Uso: <x-avatar :usuario="$user" class="h-10 w-10" />
         <x-avatar :usuario="$user" class="h-16 w-16 text-xl" color="bg-teal-100 text-teal-800" />
--}}
@props(['usuario', 'color' => 'bg-teal-100 text-teal-800'])

@if ($usuario->avatar_url)
    <img src="{{ $usuario->avatar_url }}" alt="" {{ $attributes->merge(['class' => 'shrink-0 rounded-full object-cover']) }}>
@else
    <span aria-hidden="true" {{ $attributes->merge(['class' => "flex shrink-0 items-center justify-center rounded-full font-bold $color"]) }}>{{ $usuario->iniciales }}</span>
@endif
