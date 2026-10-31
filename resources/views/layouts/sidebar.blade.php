@php
    $usuario     = auth()->user();
    $esAdmin     = $usuario->hasRole('administrador');
    $esOperativo = $usuario->hasAnyRole(['administrador', 'coordinador']);
    $empresa     = \App\Models\ConfiguracionNegocio::actual();
    $noLeidas    = $usuario->unreadNotifications()->count();

    $enlace  = 'flex items-center px-4 py-3 text-slate-300 hover:bg-[#15335f] hover:text-white rounded-lg transition-colors';
    $activo  = 'bg-[#1d4478] text-white font-semibold';
@endphp

<aside class="w-64 bg-[#0b1f3a] border-r border-[#15335f] min-h-screen hidden sm:block">
    <!-- Logo y nombre de la empresa -->
    <div class="flex items-center justify-center gap-3 h-16 border-b border-[#15335f] px-4">
        @if($empresa->logo_url)
            <img src="{{ $empresa->logo_url }}" alt="Logo" class="h-10 w-auto max-w-[48px] object-contain bg-white rounded-lg p-1">
        @endif
        <span class="text-lg font-bold text-white truncate">{{ $empresa->nombre_mostrar }}</span>
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
 
        <a href="{{ route('notificaciones.index') }}" class="{{ $enlace }} justify-between {{ request()->routeIs('notificaciones.*') ? $activo : '' }}">
            <span class="flex items-center">
                <span class="material-symbols-outlined mr-3 text-[20px]">notifications</span>
                Notificaciones
            </span>
            @if($noLeidas > 0)
                <span class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 rounded-full text-xs font-semibold bg-red-500 text-white">
                    {{ $noLeidas > 99 ? '99+' : $noLeidas }}
                </span>
            @endif
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
 
        {{-- Reportes (solo administrador) --}}
        @if($esAdmin)
            <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Reportes</p>
 
            <a href="{{ route('reportes.ingresos') }}" class="{{ $enlace }} {{ request()->routeIs('reportes.ingresos') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">payments</span>
                Ingresos y saldos
            </a>
 
            <a href="{{ route('reportes.ocupacion') }}" class="{{ $enlace }} {{ request()->routeIs('reportes.ocupacion') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">bar_chart</span>
                Ocupación
            </a>
 
            <a href="{{ route('reportes.paquetes') }}" class="{{ $enlace }} {{ request()->routeIs('reportes.paquetes') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">inventory_2</span>
                Paquetes más pedidos
            </a>
 
            <a href="{{ route('reportes.concentracion') }}" class="{{ $enlace }} {{ request()->routeIs('reportes.concentracion') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">date_range</span>
                Eventos por periodo
            </a>
 
            <p class="px-4 pt-4 pb-1 text-xs font-semibold text-gray-400 uppercase tracking-wider">Administración</p>
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
 
            <a href="{{ route('respaldos.index') }}" class="{{ $enlace }} {{ request()->routeIs('respaldos.*') ? $activo : '' }}">
                <span class="material-symbols-outlined mr-3 text-[20px]">backup</span>
                Respaldos
            </a>
        @endif
 
    </nav>
</aside>