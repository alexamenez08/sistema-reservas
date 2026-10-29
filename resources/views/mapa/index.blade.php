<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Mapa de locaciones</h2>
      <a href="{{ route('locaciones.index') }}"
         class="text-sm text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-1">
        <span class="material-symbols-outlined text-[18px]">list</span>
        Ver listado
      </a>
    </div>
  </x-slot>

  {{-- Leaflet (CSS y JS) ya se carga en layouts/app.blade.php. Mapa base: OpenStreetMap (gratuito, sin API key). --}}
  <style>
    #mapa-locaciones { height: 620px; border-radius: .5rem; z-index: 0; }
    .loc-item { cursor: pointer; border: 1px solid #e5e7eb; border-radius: .75rem; padding: .65rem .8rem; background: #fff; }
    .loc-item:hover, .loc-item.activo { background: #eef2ff; border-color: #c7d2fe; }
  </style>

  <div class="py-8">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Lista lateral --}}
        <aside class="lg:col-span-3">
          <div class="bg-white shadow-sm rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-3">
              Locaciones <span class="text-gray-400 font-normal">({{ $locaciones->count() }})</span>
            </h3>
            <input type="text" id="buscar-locacion" placeholder="Buscar por nombre o dirección…"
                   class="w-full mb-3 px-3 py-2 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <ul id="lista-locaciones" class="space-y-2 max-h-[540px] overflow-auto pr-1"></ul>
          </div>
        </aside>

        {{-- Mapa --}}
        <section class="lg:col-span-9">
          <div class="bg-white shadow-sm rounded-lg p-4">
            @if($locaciones->isEmpty())
              <div class="text-center text-gray-400 py-24">
                <span class="material-symbols-outlined text-[48px]">location_off</span>
                <p class="mt-2">No hay locaciones activas con coordenadas registradas.</p>
              </div>
            @else
              <div id="mapa-locaciones"></div>
            @endif
          </div>
        </section>

      </div>
    </div>
  </div>

  @if($locaciones->isNotEmpty())
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const locaciones = @json($locaciones);

      const esc = (t) => String(t ?? '').replace(/[&<>"']/g, c => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
      ));

      const mapa = L.map('mapa-locaciones');
      L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      }).addTo(mapa);

      // Un marcador por locación
      const marcadores = {};
      locaciones.forEach(l => {
        const m = L.marker([l.lat, l.lng]).addTo(mapa);
        m.bindPopup(`
          <div style="min-width:200px">
            <strong style="font-size:14px">${esc(l.nombre)}</strong>
            <div style="margin-top:4px;color:#475569;font-size:12px">${esc(l.direccion)}</div>
            <div style="margin-top:4px;font-size:12px">Capacidad: <strong>${esc(l.capacidad)}</strong> personas</div>
            <a href="${esc(l.url)}" style="display:inline-block;margin-top:8px;color:#4f46e5;font-weight:600;font-size:12px">
              Ver perfil completo →
            </a>
          </div>`);
        m.on('click', () => resaltar(l.id));
        marcadores[l.id] = m;
      });

      // Encuadra todos los marcadores
      const limites = L.latLngBounds(locaciones.map(l => [l.lat, l.lng]));
      mapa.fitBounds(limites, { padding: [40, 40], maxZoom: 15 });

      /* ---------- Lista lateral ---------- */
      const $lista  = document.getElementById('lista-locaciones');
      const $buscar = document.getElementById('buscar-locacion');

      function pintarLista() {
        const q = $buscar.value.trim().toLowerCase();
        const items = locaciones.filter(l =>
          !q || l.nombre.toLowerCase().includes(q) || (l.direccion || '').toLowerCase().includes(q));

        $lista.innerHTML = items.length ? items.map(l => `
          <li class="loc-item" data-id="${l.id}">
            <div class="font-semibold text-slate-800 text-sm">${esc(l.nombre)}</div>
            <div class="text-xs text-slate-500 mt-0.5">${esc(l.direccion)}</div>
            <div class="text-xs text-slate-400 mt-0.5">Capacidad: ${esc(l.capacidad)}</div>
          </li>`).join('')
          : '<li class="text-sm text-gray-400 text-center py-6">Sin resultados.</li>';
      }

      function resaltar(id) {
        document.querySelectorAll('.loc-item').forEach(li =>
          li.classList.toggle('activo', Number(li.dataset.id) === Number(id)));
      }

      $lista.addEventListener('click', (ev) => {
        const li = ev.target.closest('.loc-item');
        if (!li) return;
        const id = Number(li.dataset.id);
        const l = locaciones.find(x => x.id === id);
        mapa.setView([l.lat, l.lng], 16);
        marcadores[id].openPopup();
        resaltar(id);
      });

      $buscar.addEventListener('input', pintarLista);
      pintarLista();
    });
  </script>
  @endif
</x-app-layout>