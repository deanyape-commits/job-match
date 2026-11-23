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
    <title>JobMatch · Empleos del sector tecnológico en el Perú</title>
    <meta name="description" content="Encuentra empleos de tecnología que coinciden con tus habilidades. JobMatch calcula tu porcentaje de compatibilidad con cada vacante.">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|space-grotesk:500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-900 antialiased">

<header class="border-b border-slate-200 bg-white">
    <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-4 px-6 py-4">
        <a href="{{ route('inicio') }}"><x-application-logo /></a>
        <nav aria-label="Principal" class="flex flex-wrap items-center gap-6 text-[15px] font-medium">
            <a href="{{ route('postulante.vacantes.index') }}" class="text-slate-700 hover:text-slate-900">Buscar empleos</a>
            @auth
                <a href="{{ route('dashboard') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-white hover:bg-blue-800">Ir a mi panel</a>
            @else
                <a href="{{ route('register') }}" class="text-slate-700 hover:text-slate-900">Para empresas</a>
                <a href="{{ route('login') }}" class="text-blue-700 hover:text-blue-900">Iniciar sesión</a>
                <a href="{{ route('register') }}" class="rounded-lg bg-blue-700 px-4 py-2.5 text-white hover:bg-blue-800">Crear cuenta</a>
            @endauth
        </nav>
    </div>
</header>

<main>
    {{-- Hero con buscador --}}
    <section class="bg-marino text-white">
        <div class="mx-auto flex max-w-6xl flex-col gap-7 px-6 pb-24 pt-20">
            <span class="self-start rounded-full bg-white/10 px-3.5 py-1.5 text-sm font-medium">Empleos del sector tecnológico en todo el Perú</span>
            <h1 class="max-w-3xl font-display text-4xl font-bold leading-[1.05] sm:text-6xl">Encuentra el trabajo que coincide con tus habilidades</h1>
            <p class="max-w-2xl text-lg leading-relaxed text-slate-300 sm:text-xl">JobMatch compara tu perfil con cada vacante y te muestra tu porcentaje de compatibilidad antes de postular.</p>

            <form method="GET" action="{{ route('postulante.vacantes.index') }}" class="flex max-w-4xl flex-wrap gap-2.5 rounded-2xl bg-white p-2.5">
                <label class="flex flex-[2_1_260px] items-center gap-2.5 rounded-xl bg-slate-100 px-3.5 text-slate-600">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="7" /><path d="m20 20-3.5-3.5" /></svg>
                    <span class="sr-only">Puesto, empresa o habilidad</span>
                    <input type="text" name="q" placeholder="Puesto, empresa o habilidad (ej. Laravel)" class="h-[52px] w-full border-0 bg-transparent text-base text-slate-900 focus:ring-0">
                </label>
                <button type="submit" class="min-h-[52px] flex-none rounded-xl bg-blue-700 px-7 text-base font-semibold text-white hover:bg-blue-800">Buscar empleos</button>
            </form>

            <div class="flex flex-wrap items-center gap-2.5 text-sm text-slate-300">
                <span>Populares:</span>
                @foreach ($populares as $tag)
                    <a href="{{ route('postulante.vacantes.index', ['q' => $tag]) }}" class="rounded-full border border-white/30 px-3 py-1.5 text-white hover:bg-white/10">{{ $tag }}</a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Cifras reales de la base de datos --}}
    <section class="mx-auto -mt-10 max-w-6xl px-6">
        <dl class="grid gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:grid-cols-3">
            @foreach (['vacantes' => 'vacantes activas', 'empresas' => 'empresas verificadas', 'contratados' => 'personas contratadas'] as $clave => $texto)
                <div class="text-center">
                    <dd class="font-display text-4xl font-bold text-blue-700">{{ number_format($cifras[$clave], 0, '.', ' ') }}</dd>
                    <dt class="text-slate-600">{{ $texto }}</dt>
                </div>
            @endforeach
        </dl>
    </section>

    {{-- Vacantes recientes --}}
    <section class="mx-auto max-w-6xl px-6 pb-6 pt-16">
        <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
            <h2 class="font-display text-3xl font-bold">Vacantes recientes</h2>
            <a href="{{ route('postulante.vacantes.index') }}" class="font-semibold text-blue-700 hover:text-blue-900">Ver todas las vacantes →</a>
        </div>
        <div class="grid gap-5 [grid-template-columns:repeat(auto-fill,minmax(min(100%,320px),1fr))]">
            @foreach ($recientes as $vacante)
                @php $href = $enlaceVacante($vacante); @endphp
                <{{ $href ? 'a' : 'div' }} @if ($href) href="{{ $href }}" @endif
                   class="flex flex-col gap-3.5 rounded-2xl border border-slate-200 bg-white p-5 {{ $href ? 'transition hover:border-blue-300 hover:shadow-sm' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-100 font-bold text-blue-900">{{ $vacante->empresa->iniciales }}</span>
                        <div class="min-w-0">
                            <p class="truncate text-[17px] font-semibold">{{ $vacante->titulo }}</p>
                            <p class="truncate text-sm text-slate-600">{{ $vacante->empresa->razon_social }}</p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs">
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">{{ $vacante->ubicacion->departamento }}</span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">{{ $vacante->modalidad_texto }}</span>
                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-slate-700">{{ $vacante->nivel_texto }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm text-slate-600">
                        <span>{{ $vacante->salario_texto }}</span>
                        <span>{{ $vacante->publicada_en->diffForHumans() }}</span>
                    </div>
                </{{ $href ? 'a' : 'div' }}>
            @endforeach
        </div>
    </section>

    {{-- Cómo funciona --}}
    <section class="mx-auto max-w-6xl px-6 py-14">
        <h2 class="mb-6 font-display text-3xl font-bold">Cómo funciona</h2>
        <div class="grid gap-5 md:grid-cols-3">
            @foreach ([
                ['Crea tu perfil', 'Registra tu experiencia, estudios y habilidades técnicas. Sube tu CV en PDF.'],
                ['Mira tu compatibilidad', 'Cada vacante muestra un porcentaje de match según las habilidades que pide la empresa.'],
                ['Postula y entrevístate', 'Sigue el estado de tus postulaciones y únete a entrevistas virtuales con un clic.'],
            ] as $i => [$titulo, $texto])
                <div class="flex flex-col gap-2.5 rounded-2xl border border-slate-200 bg-white p-6">
                    <span class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800">{{ $i + 1 }}</span>
                    <h3 class="text-lg font-semibold">{{ $titulo }}</h3>
                    <p class="leading-relaxed text-slate-600">{{ $texto }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Llamado para empresas --}}
    <section class="mx-auto mb-16 max-w-6xl px-6">
        <div class="flex flex-wrap items-center justify-between gap-6 rounded-3xl bg-teal-700 p-8 text-white sm:p-11">
            <div class="max-w-2xl space-y-2">
                <h2 class="font-display text-3xl font-bold">¿Tu empresa busca talento TI?</h2>
                <p class="text-lg leading-relaxed text-teal-50">Publica vacantes y recibe candidatos ordenados por compatibilidad. Agenda entrevistas virtuales sin salir de la plataforma.</p>
            </div>
            <a href="{{ route('register') }}" class="rounded-lg bg-white px-6 py-3.5 font-semibold text-teal-800 hover:bg-teal-50">Registrar mi empresa</a>
        </div>
    </section>
</main>

<footer class="border-t border-slate-200 bg-white">
    <div class="mx-auto flex max-w-6xl flex-wrap justify-between gap-3 px-6 py-7 text-sm text-slate-600">
        <span>© {{ date('Y') }} JobMatch · Proyecto final Tecsup</span>
        <span>Hecho con Laravel, Blade y PostgreSQL</span>
    </div>
</footer>
</body>
</html>
