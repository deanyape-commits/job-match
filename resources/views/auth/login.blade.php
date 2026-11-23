<x-guest-layout>
    <div class="mb-6 space-y-1">
        <h2 class="font-display text-3xl font-bold">Bienvenido de nuevo</h2>
        <p class="text-slate-600">Ingresa con tu correo y contraseña.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="tu@correo.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between text-sm">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-blue-700 shadow-sm focus:ring-blue-600" name="remember">
                <span class="ms-2 text-slate-700">{{ __('Remember me') }}</span>
            </label>
            @if (Route::has('password.request'))
                <a class="text-blue-700 hover:text-blue-900" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
            @endif
        </div>

        <x-primary-button class="w-full py-3 text-base">{{ __('Log in') }}</x-primary-button>

        <p class="text-center text-sm text-slate-600">
            ¿No tienes cuenta? <a href="{{ route('register') }}" class="font-semibold text-blue-700 hover:text-blue-900">Crear cuenta</a>
        </p>
    </form>
</x-guest-layout>
