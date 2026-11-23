@php
    // Enlaces del postulante: [texto, nombre de ruta, patrón para marcar activo]
    $enlaces = [
        ['Buscar empleos', 'postulante.vacantes.index', 'postulante.vacantes.*'],
        ['Mis postulaciones', 'postulante.postulaciones.index', 'postulante.postulaciones.*'],
        ['Guardados', 'postulante.guardados.index', 'postulante.guardados.*'],
        ['Mi perfil', 'postulante.perfil.edit', 'postulante.perfil.*'],
    ];
    $iniciales = Auth::user()->iniciales;
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <a href="{{ route('dashboard') }}" class="shrink-0 flex items-center">
                    <x-application-logo />
                </a>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @foreach ($enlaces as [$texto, $ruta, $patron])
                        @if (Route::has($ruta))
                            <x-nav-link :href="route($ruta)" :active="request()->routeIs($patron)">{{ $texto }}</x-nav-link>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6 sm:gap-4">
                <x-campanita />
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500" aria-label="Menú de usuario">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-teal-100 font-bold text-teal-800">{{ $iniciales }}</span>
                            <svg class="fill-current h-4 w-4 text-slate-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-sm font-semibold text-slate-900 truncate">{{ Auth::user()->nombre }}</p>
                            <p class="text-xs text-slate-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <x-dropdown-link :href="route('profile.edit')">Mi cuenta</x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                Cerrar sesión
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" aria-label="Abrir menú" class="inline-flex items-center justify-center p-2 rounded-md text-slate-500 hover:bg-slate-100 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            @foreach ($enlaces as [$texto, $ruta, $patron])
                @if (Route::has($ruta))
                    <x-responsive-nav-link :href="route($ruta)" :active="request()->routeIs($patron)">{{ $texto }}</x-responsive-nav-link>
                @endif
            @endforeach
        </div>

        <div class="pt-4 pb-1 border-t border-slate-200">
            <div class="px-4">
                <div class="font-medium text-base text-slate-800">{{ Auth::user()->nombre }}</div>
                <div class="font-medium text-sm text-slate-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('notificaciones.index')" :active="request()->routeIs('notificaciones.*')">
                    Notificaciones
                    @if ($sinLeer = Auth::user()->unreadNotifications()->count())
                        <span class="ms-1 rounded-full bg-orange-700 px-1.5 text-xs font-bold text-white">{{ $sinLeer }}</span>
                    @endif
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('profile.edit')">Mi cuenta</x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                        Cerrar sesión
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
