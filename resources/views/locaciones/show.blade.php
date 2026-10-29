<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <a href="{{ route('locaciones.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
          <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Perfil de la Locación</h2>
      </div>
      <a href="{{ route('locaciones.edit', $locacion) }}"
         class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">edit</span>
        Editar
      </a>
    </div>
  </x-slot>

  <div class="py-8">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

      {{-- Datos generales --}}
      <div class="bg-white shadow-sm rounded-lg p-6">
        <div class="flex items-start justify-between mb-4">
          <h3 class="text-xl font-semibold text-gray-900">{{ $locacion->nombre }}</h3>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                       {{ $locacion->activo ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
            {{ $locacion->activo ? 'Activa' : 'Inactiva' }}
          </span>
        </div>

        @if($locacion->descripcion)
          <p class="text-sm text-gray-600 mb-4">{{ $locacion->descripcion }}</p>
        @endif

        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 text-sm">
          <div>
            <p class="text-gray-400">Capacidad máxima</p>
            <p class="font-medium text-gray-900">{{ $locacion->capacidad }} personas</p>
          </div>
          <div>
            <p class="text-gray-400">Precio base</p>
            <p class="font-medium text-gray-900">${{ number_format($locacion->precio_base, 2) }}</p>
          </div>
          <div class="col-span-2 md:col-span-3">
            <p class="text-gray-400">Dirección</p>
            <p class="font-medium text-gray-900">{{ $locacion->direccion }}</p>
          </div>
        </div>
      </div>

      {{-- Ubicación --}}
      @if($locacion->latitud && $locacion->longitud)
        <div class="bg-white shadow-sm rounded-lg p-6">
          <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Ubicación</h3>
          <div id="mapa-perfil" style="height: 320px; border-radius: .5rem; z-index: 0;"></div>
        </div>
        <script>
          document.addEventListener('DOMContentLoaded', function () {
            const pos = [{{ (float) $locacion->latitud }}, {{ (float) $locacion->longitud }}];
            const mapa = L.map('mapa-perfil').setView(pos, 16);
            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
              maxZoom: 19,
              attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            }).addTo(mapa);
            L.marker(pos).addTo(mapa);
          });
        </script>
      @endif

      {{-- Próximas reservaciones --}}
      <div class="bg-white shadow-sm rounded-lg p-6">
        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Próximas reservaciones</h3>
        @forelse($proximas as $r)
          <a href="{{ route('reservaciones.show', $r) }}"
             class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 px-2 rounded">
            <div>
              <p class="text-sm font-medium text-gray-900">{{ $r->cliente->nombre_completo ?? '—' }}</p>
              <p class="text-xs text-gray-400">{{ $r->dia_evento->format('d/m/Y') }} · {{ $r->hora_inicio->format('H:i') }} – {{ $r->hora_fin->format('H:i') }}</p>
            </div>
            <span class="text-xs text-gray-500 capitalize">{{ $r->status }}</span>
          </a>
        @empty
          <p class="text-sm text-gray-400">No hay reservaciones próximas para esta locación.</p>
        @endforelse
      </div>

    </div>
  </div>
</x-app-layout>