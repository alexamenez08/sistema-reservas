<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between">
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Calendario de disponibilidad</h2>
      @if($puedeGestionar)
      <a href="{{ route('reservaciones.create') }}"
         class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Nueva Reservación
      </a>
      @endif
    </div>
  </x-slot>

  {{-- FullCalendar (CDN) + idioma español (el bundle global NO trae locales) --}}
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/locales/es.global.min.js"></script>

  <style>
    #calendario { min-height: 640px; }
    .fc .fc-toolbar-title { font-weight: 700; color: #111827; font-size: 1.25rem !important; text-transform: capitalize; }
    .fc .fc-button-primary { background-color: #4f46e5 !important; border-color: #4f46e5 !important; }
    .fc .fc-button-primary:hover { background-color: #4338ca !important; }
    .fc .fc-daygrid-day { cursor: pointer; }
    .fc .fc-daygrid-day-number { font-weight: 600; color: #334155; }
    .fc .fc-event { cursor: pointer; font-size: .75rem; }
    .agenda-item { border: 1px solid #e5e7eb; border-radius: .75rem; padding: .65rem .8rem; background: #fff; cursor: pointer; }
    .agenda-item:hover { background: #f8fafc; }
    .dot { width: 9px; height: 9px; border-radius: 9999px; display: inline-block; margin-right: .4rem; flex-shrink: 0; }
  </style>

  <div class="py-8">
    <div class="max-w-[1600px] mx-auto px-4 sm:px-6 lg:px-8">

      {{-- Filtro + leyenda --}}
      <div class="bg-white shadow-sm rounded-lg p-4 mb-6 flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <label for="filtro-locacion" class="text-sm font-medium text-gray-600">Locación</label>
          <select id="filtro-locacion" class="text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <option value="">Todas las locaciones</option>
            @foreach($locaciones as $locacion)
              <option value="{{ $locacion->id }}">{{ $locacion->nombre }}</option>
            @endforeach
          </select>
        </div>

        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm text-gray-600">
          <span class="inline-flex items-center"><span class="dot" style="background:#22c55e"></span>Disponible</span>
          <span class="inline-flex items-center"><span class="dot" style="background:#f59e0b"></span>Apartada (pendiente)</span>
          <span class="inline-flex items-center"><span class="dot" style="background:#ef4444"></span>Reservada y confirmada</span>
          <span class="inline-flex items-center"><span class="dot" style="background:#6b7280"></span>Finalizada</span>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Agenda (se llena con las reservaciones del rango visible) --}}
        <aside class="lg:col-span-3">
          <div class="bg-white shadow-sm rounded-lg p-4">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Agenda</h3>
              <button type="button" id="btn-actualizar"
                      class="px-3 py-1.5 rounded-lg text-xs bg-white ring-1 ring-slate-200 hover:bg-slate-50">
                Actualizar
              </button>
            </div>
            <input type="text" id="buscar-agenda" placeholder="Buscar por cliente o locación…"
                   class="w-full mb-3 px-3 py-2 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <ul id="agenda" class="space-y-2 max-h-[520px] overflow-auto pr-1"></ul>
          </div>
        </aside>

        {{-- Calendario --}}
        <section class="lg:col-span-9">
          <div class="bg-white shadow-sm rounded-lg p-4">
            <div id="calendario"></div>
          </div>
        </section>
      </div>
    </div>
  </div>

  {{-- Modal: resumen rápido del día --}}
  <div id="modal-dia" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg max-h-[85vh] overflow-auto">
      <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
        <h3 id="modal-titulo" class="text-lg font-semibold text-gray-800 capitalize"></h3>
        <button type="button" id="modal-cerrar" class="text-gray-400 hover:text-gray-600">
          <span class="material-symbols-outlined">close</span>
        </button>
      </div>
      <div id="modal-cuerpo" class="p-5 space-y-4"></div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const urlEventos = @json(route('calendario.eventos'));
      const urlNueva   = @json(route('reservaciones.create'));
      const puedeGestionar = @json($puedeGestionar);

      const $calendario = document.getElementById('calendario');
      const $filtro     = document.getElementById('filtro-locacion');
      const $agenda     = document.getElementById('agenda');
      const $buscar     = document.getElementById('buscar-agenda');
      const $modal      = document.getElementById('modal-dia');
      const $titulo     = document.getElementById('modal-titulo');
      const $cuerpo     = document.getElementById('modal-cuerpo');

      // Escapa texto antes de meterlo en innerHTML (nombres de clientes, etc.)
      const esc = (t) => String(t ?? '').replace(/[&<>"']/g, c => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
      ));

      const hoyStr = () => {
        const d = new Date();
        return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
      };

      const reservaciones = () => calendar.getEvents().filter(e => e.display !== 'background');

      /* ---------------- Calendario ---------------- */
      const calendar = new FullCalendar.Calendar($calendario, {
        locale: 'es',
        initialView: 'dayGridMonth',
        height: 'auto',
        firstDay: 1,
        dayMaxEvents: 3,
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,listMonth',
        },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana', list: 'Agenda' },

        // Fuente de datos: se vuelve a pedir al navegar y al llamar refetchEvents()
        eventSources: [{
          url: urlEventos,
          method: 'GET',
          extraParams: () => ($filtro.value ? { locacion_id: $filtro.value } : {}),
          failure: () => alert('No se pudieron cargar las reservaciones. Intenta actualizar.'),
        }],

        // Clic en un día o en una reservación -> resumen rápido
        dateClick: (info) => abrirDia(info.dateStr.slice(0, 10)),
        eventClick: (info) => {
          info.jsEvent.preventDefault();
          abrirDia(info.event.extendedProps.dia);
        },

        // Cada vez que cambian los eventos cargados, se reconstruye la agenda lateral
        eventsSet: () => pintarAgenda(),
      });
      calendar.render();

      /* ---------------- Agenda lateral ---------------- */
      function pintarAgenda() {
        const q = $buscar.value.trim().toLowerCase();
        const items = reservaciones()
          .filter(e => !q || e.title.toLowerCase().includes(q))
          .sort((a, b) => a.start - b.start);

        if (!items.length) {
          $agenda.innerHTML = '<li class="text-sm text-gray-400 text-center py-6">Sin reservaciones en este periodo.</li>';
          return;
        }

        $agenda.innerHTML = items.map(e => {
          const p = e.extendedProps;
          const fecha = e.start.toLocaleDateString('es-MX', { weekday: 'short', day: 'numeric', month: 'short' });
          return `
            <li class="agenda-item" data-dia="${esc(p.dia)}">
              <div class="text-xs text-slate-500">${esc(fecha)} · ${esc(p.horario)}</div>
              <div class="font-semibold text-slate-800 mt-1 flex items-center">
                <span class="dot" style="background:${esc(p.color)}"></span>${esc(p.cliente)}
              </div>
              <div class="text-xs text-slate-500 mt-0.5">${esc(p.locacion)}</div>
            </li>`;
        }).join('');
      }

      $agenda.addEventListener('click', (ev) => {
        const li = ev.target.closest('.agenda-item');
        if (li) abrirDia(li.dataset.dia);
      });
      $buscar.addEventListener('input', pintarAgenda);

      /* ---------------- Modal de resumen ---------------- */
      function abrirDia(dia) {
        const delDia = reservaciones().filter(e => e.extendedProps.dia === dia);
        const fecha = new Date(dia + 'T00:00:00').toLocaleDateString('es-MX', {
          weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
        });
        $titulo.textContent = fecha;

        if (!delDia.length) {
          const pasado = dia < hoyStr();
          $cuerpo.innerHTML = `
            <div class="text-center py-4">
              <span class="material-symbols-outlined text-green-500 text-[40px]">${pasado ? 'event_busy' : 'event_available'}</span>
              <p class="mt-2 text-gray-700 font-medium">${!puedeGestionar ? 'No tienes eventos asignados este día.' : (pasado ? 'Sin reservaciones este día.' : 'Día disponible')}</p>
              ${(pasado || !puedeGestionar) ? '' : `
                <a href="${esc(urlNueva)}?dia_evento=${esc(dia)}"
                   class="inline-flex items-center gap-2 mt-4 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                  <span class="material-symbols-outlined text-[18px]">add</span> Crear reservación
                </a>`}
            </div>`;
        } else {
          $cuerpo.innerHTML = delDia.map(e => {
            const p = e.extendedProps;
            return `
              <div class="border border-gray-200 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                  <span class="inline-flex items-center text-xs font-medium px-2.5 py-0.5 rounded-full text-white"
                        style="background:${esc(p.color)}">${esc(p.estado_etiqueta)}</span>
                  <span class="text-xs text-gray-500">${esc(p.financiero)}</span>
                </div>
                <dl class="grid grid-cols-2 gap-3 text-sm">
                  <div><dt class="text-gray-400">Cliente</dt><dd class="font-medium text-gray-900">${esc(p.cliente)}</dd></div>
                  <div><dt class="text-gray-400">Locación</dt><dd class="font-medium text-gray-900">${esc(p.locacion)}</dd></div>
                  <div><dt class="text-gray-400">Horario</dt><dd class="font-medium text-gray-900">${esc(p.horario)}</dd></div>
                  <div><dt class="text-gray-400">Invitados</dt><dd class="font-medium text-gray-900">${esc(p.invitados)}</dd></div>
                  ${p.paquete ? `<div class="col-span-2"><dt class="text-gray-400">Paquete</dt><dd class="font-medium text-gray-900">${esc(p.paquete)}</dd></div>` : ''}
                </dl>
                <div class="flex gap-3 mt-4 text-sm">
                  ${p.url_reservacion ? `<a href="${esc(p.url_reservacion)}" class="text-indigo-600 hover:text-indigo-800 font-medium">Ver reservación</a>` : ''}
                  ${p.url_evento ? `<a href="${esc(p.url_evento)}" class="text-indigo-600 hover:text-indigo-800 font-medium">Ver evento</a>` : ''}
                </div>
              </div>`;
          }).join('');
        }

        $modal.classList.remove('hidden');
        $modal.classList.add('flex');
      }

      function cerrarModal() {
        $modal.classList.add('hidden');
        $modal.classList.remove('flex');
      }
      document.getElementById('modal-cerrar').addEventListener('click', cerrarModal);
      $modal.addEventListener('click', (ev) => { if (ev.target === $modal) cerrarModal(); });
      document.addEventListener('keydown', (ev) => { if (ev.key === 'Escape') cerrarModal(); });

      /* ---------------- Actualización ---------------- */
      $filtro.addEventListener('change', () => calendar.refetchEvents());
      document.getElementById('btn-actualizar').addEventListener('click', () => calendar.refetchEvents());

      // Refresca solo: al volver a la pestaña y cada 60 s, para reflejar reservas nuevas o canceladas
      document.addEventListener('visibilitychange', () => { if (!document.hidden) calendar.refetchEvents(); });
      setInterval(() => { if (!document.hidden) calendar.refetchEvents(); }, 60000);
    });
  </script>
</x-app-layout>