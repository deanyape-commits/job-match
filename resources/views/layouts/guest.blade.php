<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Ludan') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
        <script>document.documentElement.classList.add('js')</script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|space-grotesk:500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased bg-crema">
        <div class="min-h-screen flex flex-wrap">
            {{-- Foto de fondo oscurecida para que el texto se lea --}}
            <aside class="relative flex-[1_1_420px] min-h-[420px] overflow-hidden bg-carbon text-white">
                <img src="{{ asset('img/reunion-equipo.jpg') }}" alt="" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-carbon-900/65"></div>
                <div class="relative flex h-full flex-col justify-between gap-10 p-8 sm:p-14">
                    <a href="{{ route('inicio') }}" aria-label="Ludan, ir al inicio">
                        <x-application-logo oscuro />
                    </a>
                    <div class="max-w-md space-y-4" data-revelar>
                        <span class="block h-1 w-12 rounded bg-coral-500"></span>
                        <h1 class="font-display text-3xl font-bold leading-tight sm:text-4xl">Tu próximo empleo TI empieza con un buen match.</h1>
                        <p class="text-lg leading-relaxed text-slate-200">Vacantes de empresas verificadas, con tu porcentaje de compatibilidad antes de postular.</p>
                    </div>
                    <span class="text-sm text-slate-300">© {{ date('Y') }} Ludan</span>
                </div>
            </aside>

            <main class="flex flex-[1_1_520px] items-center justify-center px-6 py-12">
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
