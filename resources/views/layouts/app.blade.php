<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($titulo) ? $titulo.' · ' : '' }}{{ config('app.name', 'JobMatch') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
        {{-- Marca que el JavaScript está activo antes de dibujar (evita parpadeos en las animaciones) --}}
        <script>document.documentElement.classList.add('js')</script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|space-grotesk:500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-slate-900 bg-crema">
        @if (Auth::user()->tieneRol('postulante'))
            {{-- Postulante: barra de navegación superior --}}
            <div class="min-h-screen">
                @include('layouts.navigation')

                <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    @include('layouts.partials.alertas')
                    @isset($header)
                        <header class="mb-6">{{ $header }}</header>
                    @endisset
                    {{ $slot }}
                </main>
            </div>
        @else
            {{-- Reclutador, supervisor y administrador: menú lateral --}}
            <div class="min-h-screen flex flex-wrap">
                @include('layouts.sidebar')

                <main class="flex-[999_1_560px] min-w-0 px-4 sm:px-8 py-8">
                    @include('layouts.partials.alertas')
                    @isset($header)
                        <header class="mb-6">{{ $header }}</header>
                    @endisset
                    {{ $slot }}
                </main>
            </div>
        @endif
    </body>
</html>
