<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reporte de paquetes más pedidos</h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <form method="GET" action="{{ route('reportes.paquetes') }}" class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap items-end gap-4">
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
                @include('reportes._acciones', ['ruta' => 'reportes.paquetes'])
            </form>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-1">Frecuencia de cada paquete</h3>
                <p class="text-xs text-gray-400 mb-4">
                    Reservaciones registradas del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }} (sin canceladas).
                    {{ $sinPaquete }} reservación(es) sin paquete no se incluyen en la gráfica.
                </p>
                @if(count($filas))
                    <div style="height: {{ max(220, count($filas) * 42) }}px;"><canvas id="grafica-paquetes"></canvas></div>
                @else
                    <p class="text-sm text-gray-400 italic py-12 text-center">No hay reservaciones con paquete en este periodo.</p>
                @endif
            </div>

            <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Paquete</th>
                            <th class="px-4 py-3">Locación</th>
                            <th class="px-4 py-3 text-right">Reservaciones</th>
                            <th class="px-4 py-3 text-right">% del total</th>
                            <th class="px-4 py-3 text-right">Valor reservado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($filas as $i => $f)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $f->nombre }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $f->locacion }}</td>
                                <td class="px-4 py-3 text-right">{{ $f->reservas }}</td>
                                <td class="px-4 py-3 text-right">{{ $f->porcentaje }}%</td>
                                <td class="px-4 py-3 text-right">${{ number_format($f->monto, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-gray-400 italic">Sin datos en el rango seleccionado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if(count($filas))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('grafica-paquetes'), {
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
        });
    </script>
    @endif
</x-app-layout>