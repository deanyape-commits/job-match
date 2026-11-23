<x-app-layout>
    <x-slot name="titulo">Mi cuenta</x-slot>

    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-4">
            <x-avatar :usuario="$user" class="h-14 w-14 text-xl" />
            <div>
                <h1 class="font-display text-3xl font-bold">Mi cuenta</h1>
                <p class="text-slate-600">{{ $user->nombre }} · {{ $user->rol?->nombre }}</p>
            </div>
        </div>
        @if ($user->tieneRol('postulante'))
            <p class="mt-3 text-sm text-slate-600">
                Tu experiencia, estudios, CV y habilidades se editan en
                <a href="{{ route('postulante.perfil.edit') }}" class="font-semibold text-coral-700 underline">Mi perfil profesional</a>.
            </p>
        @endif
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            @include('profile.partials.update-photo-form')
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        {{-- Solo los postulantes pueden eliminar su propia cuenta --}}
        @if ($user->tieneRol('postulante'))
            <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
