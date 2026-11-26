<x-guest-layout>
    <div class="mb-6 space-y-1">
        <h2 class="font-display text-3xl font-bold">Crea tu cuenta</h2>
        <p class="text-slate-600">Es gratis y te toma un minuto.</p>
    </div>

    {{-- x-data (Alpine.js, viene con Breeze): muestra los campos de empresa solo si se elige "Busco talento" --}}
    <form method="POST" action="{{ route('register') }}" x-data="{ tipo: '{{ old('tipo_cuenta', 'postulante') }}' }">
        @csrf

        <!-- Tipo de cuenta -->
        <fieldset>
            <legend class="block font-medium text-sm text-gray-700">¿Cómo quieres usar Ludan?</legend>
            <div class="mt-2 grid grid-cols-2 gap-3">
                <label class="cursor-pointer rounded-lg border-2 p-3 transition"
                       :class="tipo === 'postulante' ? 'border-coral-600 bg-coral-50' : 'border-gray-200'">
                    <input type="radio" name="tipo_cuenta" value="postulante" x-model="tipo" class="sr-only">
                    <span class="block font-semibold text-gray-900">Busco empleo</span>
                    <span class="block text-xs text-gray-600">Cuenta de postulante</span>
                </label>
                <label class="cursor-pointer rounded-lg border-2 p-3 transition"
                       :class="tipo === 'reclutador' ? 'border-coral-600 bg-coral-50' : 'border-gray-200'">
                    <input type="radio" name="tipo_cuenta" value="reclutador" x-model="tipo" class="sr-only">
                    <span class="block font-semibold text-gray-900">Busco talento</span>
                    <span class="block text-xs text-gray-600">Cuenta de reclutador</span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('tipo_cuenta')" class="mt-2" />
        </fieldset>

        <!-- Nombre -->
        <div class="mt-4">
            <x-input-label for="nombre" :value="__('Nombre completo')" />
            <x-text-input id="nombre" class="block mt-1 w-full" type="text" name="nombre" :value="old('nombre')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('nombre')" class="mt-2" />
        </div>

        <!-- Datos de la empresa (solo reclutadores) -->
        <div x-show="tipo === 'reclutador'" x-cloak class="mt-4 space-y-4 rounded-lg bg-gray-50 p-4">
            <div>
                <x-input-label for="razon_social" value="Razón social de la empresa" />
                <x-text-input id="razon_social" class="block mt-1 w-full" type="text" name="razon_social" :value="old('razon_social')" x-bind:required="tipo === 'reclutador'" />
                <x-input-error :messages="$errors->get('razon_social')" class="mt-2" />
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <x-input-label for="ruc" value="RUC" />
                    <x-text-input id="ruc" class="block mt-1 w-full" type="text" name="ruc" :value="old('ruc')" inputmode="numeric" maxlength="11" placeholder="20XXXXXXXXX" x-bind:required="tipo === 'reclutador'" />
                    <x-input-error :messages="$errors->get('ruc')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="ubicacion_id" value="Ubicación" />
                    <select id="ubicacion_id" name="ubicacion_id" x-bind:required="tipo === 'reclutador'"
                            class="block mt-1 w-full border-gray-300 focus:border-coral-500 focus:ring-coral-500 rounded-md shadow-sm">
                        <option value="">Selecciona…</option>
                        @foreach ($ubicaciones as $ubicacion)
                            <option value="{{ $ubicacion->id }}" @selected(old('ubicacion_id') == $ubicacion->id)>
                                {{ $ubicacion->nombre_completo }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('ubicacion_id')" class="mt-2" />
                </div>
            </div>

            <p class="text-xs text-amber-800 bg-amber-50 rounded p-2">
                Tu empresa quedará "pendiente" hasta que un supervisor la verifique.
            </p>
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Correo electrónico')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Contraseña')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <x-primary-button class="mt-6 w-full py-3 text-base">Crear cuenta</x-primary-button>

        <p class="mt-4 text-center text-sm text-slate-600">
            ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="font-semibold text-coral-700 hover:text-coral-900">Iniciar sesión</a>
        </p>
    </form>
</x-guest-layout>
