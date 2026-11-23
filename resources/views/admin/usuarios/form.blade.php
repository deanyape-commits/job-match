@php
    $editando = $usuario->exists;
    $esYo = $editando && $usuario->is(Auth::user());
    $claseCampo = 'block mt-1 w-full rounded-lg border-slate-300 shadow-sm focus:border-coral-600 focus:ring-coral-600 disabled:bg-slate-100';
@endphp

<x-app-layout>
    <x-slot name="titulo">{{ $editando ? 'Editar usuario' : 'Nuevo usuario' }}</x-slot>

    <x-slot name="header">
        <a href="{{ route('admin.usuarios.index') }}" class="text-sm font-medium text-coral-700 hover:text-coral-900">← Usuarios</a>
        <h1 class="mt-2 font-display text-3xl font-bold">{{ $editando ? 'Editar usuario' : 'Nuevo usuario' }}</h1>
        <p class="mt-1 text-slate-600">Desde aquí se crean los supervisores y administradores: no pueden registrarse por su cuenta.</p>
    </x-slot>

    <form method="POST" action="{{ $editando ? route('admin.usuarios.update', $usuario) : route('admin.usuarios.store') }}"
          class="max-w-2xl space-y-5 rounded-2xl border border-slate-200 bg-white p-6"
          x-data="{ rol: '{{ old('rol_id', $usuario->rol_id) }}', reclutador: '{{ $rolReclutador }}' }">
        @csrf
        @if ($editando) @method('PUT') @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <x-input-label for="nombre" value="Nombre completo" />
                <x-text-input id="nombre" name="nombre" class="mt-1 block w-full" :value="old('nombre', $usuario->nombre)" required />
                <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="email" value="Correo electrónico" />
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $usuario->email)" required />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="telefono" value="Teléfono (opcional)" />
                <x-text-input id="telefono" name="telefono" class="mt-1 block w-full" :value="old('telefono', $usuario->telefono)" />
                <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="rol_id" value="Rol" />
                <select id="rol_id" name="rol_id" x-model="rol" class="{{ $claseCampo }}" @disabled($esYo) required>
                    <option value="">Selecciona…</option>
                    @foreach ($roles as $rol)
                        <option value="{{ $rol->id }}" @selected(old('rol_id', $usuario->rol_id) == $rol->id)>{{ $rol->nombre }}</option>
                    @endforeach
                </select>
                @if ($esYo)
                    {{-- Un select deshabilitado no se envía: se manda el valor actual --}}
                    <input type="hidden" name="rol_id" value="{{ $usuario->rol_id }}">
                    <p class="mt-1 text-xs text-slate-500">No puedes cambiar tu propio rol.</p>
                @endif
                <x-input-error :messages="$errors->get('rol_id')" class="mt-2" />
            </div>

            <div x-show="rol == reclutador" x-cloak>
                <x-input-label for="empresa_id" value="Empresa" />
                <select id="empresa_id" name="empresa_id" class="{{ $claseCampo }}">
                    <option value="">Selecciona…</option>
                    @foreach ($empresas as $empresa)
                        <option value="{{ $empresa->id }}" @selected(old('empresa_id', $usuario->empresa_id) == $empresa->id)>
                            {{ $empresa->razon_social }}{{ $empresa->estado !== 'verificada' ? ' ('.$empresa->estado.')' : '' }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('empresa_id')" class="mt-2" />
            </div>
        </div>

        <fieldset class="rounded-xl bg-crema p-4">
            <legend class="sr-only">Contraseña</legend>
            <p class="text-sm font-semibold">{{ $editando ? 'Cambiar contraseña (opcional)' : 'Contraseña' }}</p>
            @if ($editando)
                <p class="text-xs text-slate-500">Déjalo vacío para mantener la contraseña actual.</p>
            @endif
            <div class="mt-3 grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="password" value="Contraseña" />
                    <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="! $editando" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Confirmar contraseña" />
                    <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" :required="! $editando" />
                </div>
            </div>
        </fieldset>

        <label class="flex items-center gap-3">
            <input type="hidden" name="activo" value="0">
            <input type="checkbox" name="activo" value="1" @checked(old('activo', $usuario->activo ?? true)) @disabled($esYo)
                   class="rounded border-slate-300 text-coral-700 focus:ring-coral-600">
            <span class="text-sm">Cuenta activa (puede iniciar sesión)</span>
            @if ($esYo) <input type="hidden" name="activo" value="1"> @endif
        </label>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.usuarios.index') }}" class="inline-flex items-center rounded-lg border border-slate-300 px-5 py-2.5 font-semibold hover:bg-crema">Cancelar</a>
            <x-primary-button>{{ $editando ? 'Guardar cambios' : 'Crear usuario' }}</x-primary-button>
        </div>
    </form>
</x-app-layout>
