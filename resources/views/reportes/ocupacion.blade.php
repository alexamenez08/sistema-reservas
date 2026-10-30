<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reporte de ocupación de locaciones</h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <form method="GET" action="{{ route('reportes.ocupacion') }}" class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap items-end gap-4">
                <div>
                    <label for="locacion_id" class="block text-sm font-medium text-gray-600">Locación</label>
                    <select id="locacion_id" name="locacion_id" class="mt-1 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todas las locaciones</option>
                        @foreach($locacionesLista as $l)
                            <option value="{{ $l->id }}" @selected((string) $locacionId === (string) $l->id)>{{ $l->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="desde" class="block text-sm font-medium text-gray-600">Desde</label>
                    <input type="date" id="desde" name="desde" value="{{ $desde->toDateString() }}"
                           class="mt-1 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="hasta" class="block text-sm font-medium text-gray-600">Hasta</label>
                    <input type="date" id="hasta" name="hasta" value="{{ $hasta->toDateString() }}"
                           class="mt-1 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                @include('reportes._acciones', ['ruta' => 'reportes.ocupacion'])
            </form>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <p class="text-sm text-gray-500">
                {{ $totalReservas }} reservación(es) en el periodo (sin canceladas).
                "Ocupación" = días con evento entre los días del rango.
            </p>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Reservaciones por locación</h3>
                    @if($totalReservas > 0)
                        <div style="height: 280px;"><canvas id="grafica-locaciones"></canvas></div>
                    @else
                        <p class="text-sm text-gray-400 italic py-12 text-center">Sin reservaciones en este periodo.</p>
                    @endif
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Temporadas: reservaciones por mes</h3>
                    <div style="height: 280px;"><canvas id="grafica-meses"></canvas></div>
                </div>
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-3">Locación</th>
                            <th class="px-4 py-3 text-right">Reservaciones</th>
                            <th class="px-4 py-3 text-right">Días ocupados</th>
                            <th class="px-4 py-3 text-right">Ocupación</th>
                            <th class="px-4 py-3 text-right">Invitados</th>
                            <th class="px-4 py-3 text-right">Valor reservado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($filas as $f)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $f->nombre }}</td>
                                <td class="px-4 py-3 text-right">{{ $f->reservas }}</td>
                                <td class="px-4 py-3 text-right">{{ $f->dias }}</td>
                                <td class="px-4 py-3 text-right">{{ $f->ocupacion }}%</td>
                                <td class="px-4 py-3 text-right">{{ number_format($f->invitados) }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($f->monto, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400 italic">No hay locaciones para mostrar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const locaciones = document.getElementById('grafica-locaciones');
            if (locaciones) {
                new Chart(locaciones, {
                    type: 'bar',
                    data: {
                        labels: @json($filas->pluck('nombre')),
                        datasets: [{ label: 'Reservaciones', data: @json($filas->pluck('reservas')), backgroundColor: '#6366f1', borderRadius: 6 }],
                    },
                    options: {
                        indexAxis: 'y',
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
                    },
                });
            }

            new Chart(document.getElementById('grafica-meses'), {
                type: 'line',
                data: {
                    labels: @json(collect($meses)->pluck('etiqueta')),
                    datasets: [{
                        label: 'Reservaciones',
                        data: @json(collect($meses)->pluck('total')),
                        borderColor: '#4f46e5', backgroundColor: 'rgba(79, 70, 229, 0.12)', fill: true, tension: 0.3,
                    }],
                },
                options: {
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                },
            });
        });
    </script>
</x-app-layout>