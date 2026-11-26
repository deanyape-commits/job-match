@php
    $usuario = Auth::user();
    $esPostulante = ! $usuario || $usuario->tieneRol('postulante');
    // A dónde lleva cada tarjeta: los invitados pasan primero por el login
    $enlaceVacante = fn ($v) => $esPostulante ? route('postulante.vacantes.show', $v) : null;
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Ludan · Empleos del sector tecnológico en el Perú</title>
    <meta name="description" content="Encuentra empleos de tecnología que coinciden con tus habilidades. Ludan calcula tu porcentaje de compatibilidad con cada vacante.">
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <script>document.documentElement.classList.add('js')</script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|space-grotesk:500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-crema font-sans text-slate-900 antialiased">

<header class="border-b border-[#EDE6E1] bg-white">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-3.5">
        <a href="{{ route('inicio') }}" aria-label="Ludan, inicio"><x-application-logo /></a>
        <nav aria-label="Principal" class="flex flex-wrap items-center gap-6 text-[15px] font-medium">
            <a href="{{ route('postulante.vacantes.index') }}" class="text-slate-700 transition hover:text-coral-700">Buscar empleos</a>
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-lg bg-coral-700 px-4 py-2.5 font-semibold text-white transition hover:bg-coral-800 active:scale-[0.97]">Ir a mi panel</a>
            @else
                <a href="{{ route('register') }}" class="text-slate-700 transition hover:text-coral-700">Para empresas</a>
                <a href="{{ route('login') }}" class="font-semibold text-coral-700 hover:text-coral-900">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-coral-700 px-4 py-2.5 font-semibold text-white transition hover:bg-coral-800 active:scale-[0.97]">Crear cuenta</a>
            @endauth
        </nav>
    </div>
</header>

<main>
    {{-- ================= Portada ================= --}}
    <section class="overflow-hidden bg-carbon text-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-12 px-6 pb-24 pt-16">
            <div class="flex flex-[1_1_480px] flex-col gap-6">
                <span class="self-start rounded-full bg-coral-500/15 px-3.5 py-1.5 text-sm font-semibold text-coral-300" data-revelar>Empleos del sector tecnológico en todo el Perú</span>
                <h1 class="font-display text-4xl font-bold leading-[1.05] sm:text-[54px]" data-revelar data-revelar-retraso="80">
                    Encuentra el trabajo que coincide con <span class="text-coral-400">tus habilidades</span>
                </h1>
                <p class="max-w-xl text-lg leading-relaxed text-slate-300" data-revelar data-revelar-retraso="160">
                    Ludan compara tu perfil con cada vacante y te muestra tu porcentaje de compatibilidad antes de postular.
                </p>

                {{-- Texto rotativo con las áreas que más contratan --}}
                @if ($areas->isNotEmpty())
                    <p class="text-base text-slate-300" data-revelar data-revelar-retraso="220"
                       x-data="rotador(@js($areas->values()))">
                        Hoy las empresas buscan perfiles de
                        <span class="inline-block font-semibold text-white transition-all duration-300"
                              :class="visible ? 'translate-y-0 opacity-100' : '-translate-y-2 opacity-0'"
                              x-text="actual">{{ $areas->first() }}</span>
                    </p>
                @endif

                <form method="GET" action="{{ route('postulante.vacantes.index') }}" class="flex max-w-2xl flex-wrap gap-2 rounded-2xl bg-white p-2" data-revelar data-revelar-retraso="280">
                    <label for="busqueda-inicio" class="sr-only">Puesto, empresa o habilidad</label>
                    <div class="flex flex-[1_1_260px] items-center gap-2.5 rounded-xl bg-[#F5F0EC] pl-3.5 text-slate-600">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" class="shrink-0"><circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" /></svg>
                        <x-buscador-sugerencias id="busqueda-inicio" placeholder="Puesto, empresa o habilidad (ej. Laravel)" class="flex-1"
                            input-class="h-[50px] w-full border-0 bg-transparent text-base text-slate-900 focus:ring-0" />
                    </div>
                    <button type="submit" class="min-h-[50px] flex-none rounded-xl bg-coral-700 px-7 text-base font-semibold text-white transition hover:bg-coral-800 active:scale-[0.97]">Buscar empleos</button>
                </form>

                <div class="flex flex-wrap items-center gap-2.5 text-sm text-slate-300" data-revelar data-revelar-retraso="340">
                    <span>Populares:</span>
                    @foreach ($populares as $tag)
                        <a href="{{ route('postulante.vacantes.index', ['q' => $tag]) }}" class="rounded-full border border-white/25 px-3 py-1.5 text-white transition hover:border-coral-400 hover:bg-white/10">{{ $tag }}</a>
                    @endforeach
                </div>
            </div>

            {{-- Foto con tarjetas flotantes --}}
            <div class="relative flex-[1_1_400px]" data-revelar data-revelar-retraso="200">
                <img src="{{ asset('img/equipo-oficina.jpg') }}" alt="Equipo de profesionales revisando un proyecto en una computadora"
                     class="h-[420px] w-full rounded-3xl object-cover" width="1600" height="900">

                <div class="absolute -bottom-7 left-2 flex items-center gap-3.5 rounded-2xl bg-white px-4 py-3.5 text-slate-900 shadow-2xl sm:-left-6">
                    <div role="img" aria-label="92 por ciento de compatibilidad" data-anillo
                         class="flex h-14 w-14 items-center justify-center rounded-full" style="--pct: 92; --anillo-color: #E4572E">
                        <span class="flex h-11 w-11 items-center justify-center rounded-full bg-white font-display text-[15px] font-bold text-coral-700">92%</span>
                    </div>
                    <div>
                        <p class="font-semibold">Desarrollador Backend Jr.</p>
                        <p class="text-sm text-slate-600">Excelente match</p>
                    </div>
                </div>

                <div class="absolute -right-2 top-6 rounded-2xl bg-coral-700 px-4 py-3 font-semibold text-white shadow-2xl">
                    +<span data-contador="{{ $cifras['vacantes'] }}">{{ number_format($cifras['vacantes'], 0, '.', ' ') }}</span> vacantes activas
                </div>
            </div>
        </div>
    </section>

    {{-- ================= Cifras (cuentan desde 0) ================= --}}
    <section class="relative mx-auto -mt-9 max-w-6xl px-6">
        <dl class="grid gap-4 rounded-3xl border border-[#EDE6E1] bg-white p-6 text-center shadow-lg shadow-carbon/5 sm:grid-cols-3" data-revelar>
            @foreach (['vacantes' => 'vacantes activas', 'empresas' => 'empresas verificadas', 'contratados' => 'personas contratadas'] as $clave => $texto)
                <div class="flex flex-col-reverse">
                    <dt class="text-slate-600">{{ $texto }}</dt>
                    <dd class="font-display text-4xl font-bold text-coral-500" data-contador="{{ $cifras[$clave] }}">{{ number_format($cifras[$clave], 0, '.', ' ') }}</dd>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- ================= Carrusel de empresas ================= --}}
    @if ($empresas->isNotEmpty())
        <section aria-label="Empresas verificadas" class="mx-auto max-w-6xl overflow-hidden px-6 pt-14" data-revelar>
            <p class="mb-4 text-center text-sm font-semibold uppercase tracking-wider text-slate-500">Empresas verificadas que publican en Ludan</p>
            <div class="relative [mask-image:linear-gradient(to_right,transparent,black_8%,black_92%,transparent)]">
                <div class="carrusel-pista flex w-max animate-carrusel gap-4">
                    @foreach ([false, true] as $duplicado)
                        @foreach ($empresas as $empresa)
                            <div class="{{ $duplicado ? 'carrusel-duplicado' : '' }} flex shrink-0 items-center gap-3 rounded-2xl border border-[#EDE6E1] bg-white px-4 py-3"
                                 @if ($duplicado) aria-hidden="true" @endif>
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-coral-100 text-sm font-bold text-coral-900">{{ $empresa->iniciales }}</span>
                                <span class="whitespace-nowrap font-medium">{{ $empresa->razon_social }}</span>
                                <span class="whitespace-nowrap text-xs text-slate-500">{{ $empresa->vacantes_count }} {{ $empresa->vacantes_count === 1 ? 'vacante' : 'vacantes' }}</span>
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ================= Vacantes recientes ================= --}}
    <section class="mx-auto max-w-6xl px-6 pb-6 pt-16">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4" data-revelar>
            <h2 class="font-display text-3xl font-bold">Vacantes recientes</h2>
            <a href="{{ route('postulante.vacantes.index') }}" class="font-semibold text-coral-700 hover:text-coral-900">Ver todas las vacantes →</a>
        </div>
        <div class="grid gap-5 [grid-template-columns:repeat(auto-fill,minmax(min(100%,320px),1fr))]">
            @foreach ($recientes as $i => $vacante)
                @php $href = $enlaceVacante($vacante); @endphp
                <{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" @endif data-revelar data-revelar-retraso="{{ ($i % 3) * 90 }}"
                   class="flex flex-col gap-3.5 rounded-2xl border border-[#EDE6E1] bg-white p-5 {{ $href ? 'transition duration-200 hover:-translate-y-1 hover:border-coral-200 hover:shadow-xl hover:shadow-coral-900/5' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-coral-100 font-bold text-coral-900">{{ $vacante->empresa->iniciales }}</span>
                        <div class="min-w-0">
                            <p class="truncate text-[17px] font-semibold">{{ $vacante->titulo }}</p>
                            <p class="truncate text-sm text-slate-600">{{ $vacante->empresa->razon_social }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="rounded-full bg-[#F5F0EC] px-2.5 py-1 text-slate-700">{{ $vacante->ubicacion->departamento }}</span>
                        <span class="rounded-full bg-[#F5F0EC] px-2.5 py-1 text-slate-700">{{ $vacante->modalidad_texto }}</span>
                        <span class="rounded-full bg-[#F5F0EC] px-2.5 py-1 text-slate-700">{{ $vacante->nivel_texto }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-slate-600">
                        <span>{{ $vacante->salario_texto }}</span>
                        <span>{{ $vacante->publicada_en->diffForHumans() }}</span>
                    </div>
                </{{ $href ? 'a' : 'div' }}>
            @endforeach
        </div>
    </section>

    {{-- ================= Cómo funciona ================= --}}
    <section class="mx-auto max-w-6xl px-6 py-14">
        <h2 class="mb-6 font-display text-3xl font-bold" data-revelar>Cómo funciona</h2>
        <div class="grid gap-5 md:grid-cols-3">
            @foreach ([
                ['Crea tu perfil', 'Registra tu experiencia, estudios y habilidades técnicas. Sube tu CV en PDF.'],
                ['Mira tu compatibilidad', 'Cada vacante muestra un porcentaje de match según las habilidades que pide la empresa.'],
                ['Postula y entrevístate', 'Sigue el estado de tus postulaciones y únete a entrevistas virtuales con un clic.'],
            ] as $i => [$titulo, $texto])
                <div class="group flex flex-col gap-2.5 rounded-2xl border border-[#EDE6E1] bg-white p-6 transition duration-200 hover:-translate-y-1 hover:shadow-lg"
                     data-revelar data-revelar-retraso="{{ $i * 110 }}">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-coral-500 font-display text-lg font-bold text-white transition group-hover:scale-110">{{ $i + 1 }}</span>
                    <h3 class="text-lg font-semibold">{{ $titulo }}</h3>
                    <p class="leading-relaxed text-slate-600">{{ $texto }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ================= Para empresas ================= --}}
    <section class="mx-auto mb-16 max-w-6xl px-6" data-revelar>
        <div class="flex flex-wrap overflow-hidden rounded-3xl bg-carbon text-white">
            <img src="{{ asset('img/entrevista.jpg') }}" alt="Reclutadora saludando a un candidato al final de una entrevista"
                 class="max-h-[360px] min-h-[280px] w-full flex-[1_1_380px] object-cover" width="1600" height="900" loading="lazy">
            <div class="flex flex-[1_1_420px] flex-col justify-center gap-4 p-8 sm:p-11">
                <span class="self-start rounded-full bg-coral-500/15 px-3 py-1 text-sm font-semibold text-coral-300">Para empresas</span>
                <h2 class="font-display text-3xl font-bold">¿Tu empresa busca talento TI?</h2>
                <p class="text-lg leading-relaxed text-slate-300">Publica vacantes y recibe candidatos ordenados por compatibilidad. Agenda entrevistas virtuales sin salir de la plataforma.</p>
                <a href="{{ route('register') }}" class="self-start rounded-lg bg-coral-500 px-6 py-3.5 text-xl font-bold text-white transition hover:bg-coral-600 active:scale-[0.97]">Registrar mi empresa</a>
            </div>
        </div>
    </section>
</main>

<footer class="border-t border-[#EDE6E1] bg-white">
    <div class="mx-auto max-w-6xl px-6 py-7 text-sm text-slate-600">
        <span>© {{ date('Y') }} Ludan · Proyecto final Tecsup</span>
    </div>
</footer>
</body>
</html>
