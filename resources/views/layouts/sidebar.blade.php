@php
    $usuario     = auth()->user();
    $esAdmin     = $usuario->hasRole('administrador');
    $esOperativo = $usuario->hasAnyRole(['administrador', 'coordinador']);
    $empresa     = \App\Models\ConfiguracionNegocio::actual();

    $enlace  = 'flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 hover:text-gray-900 rounded-lg transition-colors';
    $activo  = 'bg-gray-100 text-gray-900 font-semibold';
@endphp

<aside class="w-64 bg-white border-r border-gray-100 min-h-screen hidden sm:block">
    <!-- Logo y nombre de la empresa -->
    <div class="flex items-center justify-center gap-3 h-16 border-b border-gray-100 px-4">
        @if($empresa->logo_url)
            <img src="{{ $empresa->logo_url }}" alt="Logo" class="h-9 w-auto max-w-[44px] object-contain">
        @endif
        <span class="text-lg font-bold text-gray-800 truncate">{{ $empresa->nombre_mostrar }}</span>
    </div>

    <!-- Enlaces del menú (cada rol ve solo lo suyo) -->
    <nav class="mt-6 px-4 space-y-2">

        @if($esAdmin)
            <a href="{{ route('dashboard') }}" class="{{ $enlace }} {{ request()->routeIs('dashboard') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">dashboard</span>
                Dashboard
            </a>
        @endif

        {{-- Todos los roles --}}
        <a href="{{ route('calendario.index') }}" class="{{ $enlace }} {{ request()->routeIs('calendario.*') ? $activo : '' }}">
            <span class="material-symbols-outlined mr-3 text-[20px]">calendar_month</span>
            Calendario
        </a>

        <a href="{{ route('eventos.index') }}" class="{{ $enlace }} {{ request()->routeIs('eventos.*') ? $activo : '' }}">
            <span class="material-symbols-outlined mr-3 text-[20px]">event</span>
            Eventos
        </a>

        {{-- Administrador y coordinador --}}
        @if($esOperativo)
            <a href="{{ route('mapa.index') }}" class="{{ $enlace }} {{ request()->routeIs('mapa.*') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">map</span>
                Mapa
            </a>

            <a href="{{ route('reservaciones.index') }}" class="{{ $enlace }} {{ request()->routeIs('reservaciones.*') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">event_note</span>
                Reservaciones
            </a>

            <a href="{{ route('clientes.index') }}" class="{{ $enlace }} {{ request()->routeIs('clientes.*') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">badge</span>
                Clientes
            </a>

            <a href="{{ route('paquetes.index') }}" class="{{ $enlace }} {{ request()->routeIs('paquetes.*') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">redeem</span>
                Paquetes
            </a>

            <a href="{{ route('locaciones.index') }}" class="{{ $enlace }} {{ request()->routeIs('locaciones.*') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">location_on</span>
                Locaciones
            </a>

            <a href="{{ route('mobiliario.index') }}" class="{{ $enlace }} {{ request()->routeIs('mobiliario.*', 'categorias-mobiliario.*') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">chair</span>
                Mobiliario
            </a>

            <a href="{{ route('catalogo-platillos.index') }}" class="{{ $enlace }} {{ request()->routeIs('catalogo-platillos.*') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">restaurant</span>
                Platillos
            </a>
        @endif

        {{-- Solo administrador --}}
        @if($esAdmin)
            <a href="{{ route('usuarios.index') }}" class="{{ $enlace }} {{ request()->routeIs('usuarios.*') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">group</span>
                Usuarios
            </a>

            <a href="{{ route('empresa.edit') }}" class="{{ $enlace }} {{ request()->routeIs('empresa.*') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">business</span>
                Empresa
            </a>
        @endif

    </nav>
</aside>