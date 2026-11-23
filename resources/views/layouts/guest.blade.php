<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'JobMatch') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|space-grotesk:500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-900 antialiased bg-slate-50">
        <div class="min-h-screen flex flex-wrap">
            <aside class="flex-[1_1_420px] bg-marino text-white p-8 sm:p-14 flex flex-col justify-between gap-10">
                <a href="/" class="flex items-center gap-2.5">
                    <span class="flex h-9 w-9 items-center justify-center rounded-[10px] bg-white font-display text-lg font-bold text-blue-700">J</span>
                    <span class="font-display text-xl font-bold">JobMatch</span>
                </a>
                <div class="max-w-md space-y-4">
                    <h1 class="font-display text-3xl sm:text-4xl font-bold leading-tight">Tu próximo empleo TI empieza con un buen match.</h1>
                    <p class="text-slate-300 text-lg leading-relaxed">Vacantes de empresas verificadas, con tu porcentaje de compatibilidad antes de postular.</p>
                </div>
                <span class="text-sm text-slate-400">© {{ date('Y') }} JobMatch</span>
            </aside>

            <main class="flex-[1_1_520px] flex items-center justify-center px-6 py-12">
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
