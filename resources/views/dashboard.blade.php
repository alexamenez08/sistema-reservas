<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
            <div class="flex items-center gap-3 text-sm text-gray-500">
                <span>Actualizado a las {{ $actualizado }}</span>
                <a href="{{ route('dashboard') }}"
                   class="px-3 py-1.5 rounded-lg bg-white ring-1 ring-slate-200 hover:bg-slate-50 text-gray-700 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">refresh</span> Actualizar
                </a>
            </div>
        </div>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <p class="text-sm text-gray-500">Resumen de <strong class="text-gray-700">{{ $mesActual }}</strong></p>

            {{-- Tarjetas de métricas --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-indigo-500">
                    <p class="text-sm font-medium text-gray-500 mb-1">Ingresos del mes</p>
                    <h3 class="text-2xl font-bold text-gray-800">${{ number_format($ingresosMes, 2) }}</h3>
                    <p class="text-xs text-gray-400 mt-2">Anticipos y pagos recibidos este mes</p>
                    <p class="text-xs text-gray-500 mt-1">Por cobrar (reservas vigentes): <strong>${{ number_format($saldoPorCobrar, 2) }}</strong></p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-green-500">
                    <p class="text-sm font-medium text-gray-500 mb-1">Reservaciones confirmadas</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $confirmadas }}</h3>
                    <p class="text-xs text-gray-400 mt-2">de {{ $totalMes }} del mes (incluye en curso y finalizadas)</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-amber-500">
                    <p class="text-sm font-medium text-gray-500 mb-1">Reservaciones pendientes</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $pendientes }}</h3>
                    <p class="text-xs text-gray-400 mt-2">de {{ $totalMes }} del mes</p>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6 border-l-4 border-red-500">
                    <p class="text-sm font-medium text-gray-500 mb-1">Reservaciones canceladas</p>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $canceladas }}</h3>
                    <p class="text-xs text-gray-400 mt-2">de {{ $totalMes }} del mes</p>
                </div>
            </div>

            {{-- Gráficas --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Ingresos de los últimos 6 meses</h3>
                    <div style="height: 280px;"><canvas id="grafica-ingresos"></canvas></div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-1">Locaciones con mayor demanda</h3>
                    <p class="text-xs text-gray-400 mb-4">Reservaciones de los últimos 12 meses (sin canceladas)</p>
                    @if(count($demandaValores))
                        <div style="height: 260px;"><canvas id="grafica-demanda"></canvas></div>
                    @else
                        <p class="text-sm text-gray-400 italic py-12 text-center">Aún no hay reservaciones para mostrar.</p>
                    @endif
                </div>
            </div>

            {{-- Próximos eventos --}}
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Próximos eventos (siguientes 7 días)</h3>

                @forelse($proximos as $r)
                    @php
                        $colores = ['pendiente' => '#f59e0b', 'confirmada' => '#ef4444', 'en curso' => '#ef4444'];
                    @endphp
                    <a href="{{ route('reservaciones.show', $r) }}"
                       class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0 hover:bg-gray-50 px-2 rounded">
                        <div class="flex items-center gap-4">
                            <div class="w-20 text-sm font-semibold text-gray-700">
                                {{ ucfirst($r->dia_evento->locale('es')->translatedFormat('D d M')) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $r->cliente->nombre_completo ?? 'Cliente eliminado' }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $r->locacion->nombre ?? 'Locación eliminada' }} ·
                                    {{ $r->hora_inicio->format('H:i') }} – {{ $r->hora_fin->format('H:i') }}
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center text-xs text-gray-600 capitalize">
                            <span style="display:inline-block;width:9px;height:9px;border-radius:9999px;margin-right:6px;background:{{ $colores[$r->status] ?? '#6b7280' }}"></span>
                            {{ $r->status }}
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-gray-400 italic">No hay eventos programados para los próximos 7 días.</p>
                @endforelse
            </div>

        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const dinero = (n) => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n);

            // Ingresos por mes (líneas)
            new Chart(document.getElementById('grafica-ingresos'), {
                type: 'line',
                data: {
                    labels: @json($serieMeses),
                    datasets: [{
                        label: 'Ingresos',
                        data: @json($serieIngreso),
                        borderColor: '#4f46e5',
                        backgroundColor: 'rgba(79, 70, 229, 0.12)',
                        fill: true,
                        tension: 0.3,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: { callbacks: { label: (c) => dinero(c.parsed.y) } },
                    },
                    scales: { y: { beginAtZero: true, ticks: { callback: (v) => dinero(v) } } },
                },
            });

            // Locaciones con mayor demanda (barras)
            const lienzo = document.getElementById('grafica-demanda');
            if (lienzo) {
                new Chart(lienzo, {
                    type: 'bar',
                    data: {
                        labels: @json($demandaEtiquetas),
                        datasets: [{
                            label: 'Reservaciones',
                            data: @json($demandaValores),
                            backgroundColor: '#6366f1',
                            borderRadius: 6,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
                    },
                });
            }
        });
    </script>
</x-app-layout>