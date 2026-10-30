<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Concentración de eventos por periodo</h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <form method="GET" action="{{ route('reportes.concentracion') }}" class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap items-end gap-4">
                <div>
                    <label for="anio" class="block text-sm font-medium text-gray-600">Año</label>
                    <select id="anio" name="anio" class="mt-1 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach($anios as $a)
                            <option value="{{ $a }}" @selected($a === $anio)>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="text-sm text-gray-400 pb-2">o un rango:</div>
                <div>
                    <label for="desde" class="block text-sm font-medium text-gray-600">Desde</label>
                    <input type="date" id="desde" name="desde" value="{{ $usaRango ? $desde->toDateString() : '' }}"
                           class="mt-1 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="hasta" class="block text-sm font-medium text-gray-600">Hasta</label>
                    <input type="date" id="hasta" name="hasta" value="{{ $usaRango ? $hasta->toDateString() : '' }}"
                           class="mt-1 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                @include('reportes._acciones', ['ruta' => 'reportes.concentracion'])
            </form>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            <p class="text-sm text-gray-500">
                Periodo: {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}.
                Solo cuentan las reservaciones con estado <strong>confirmada</strong> o <strong>finalizada</strong>.
                Si indicas un rango, tiene prioridad sobre el año.
            </p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-indigo-500">
                    <p class="text-xs text-gray-500">Total de eventos</p>
                    <p class="text-xl font-bold text-gray-800">{{ $total }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-green-500">
                    <p class="text-xs text-gray-500">Mes de mayor actividad</p>
                    <p class="text-lg font-bold text-gray-800">{{ $total > 0 ? $mayor['etiqueta'] : '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $total > 0 ? $mayor['total'] . ' evento(s)' : '' }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-amber-500">
                    <p class="text-xs text-gray-500">Mes de menor actividad</p>
                    <p class="text-lg font-bold text-gray-800">{{ $total > 0 ? $menor['etiqueta'] : '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $total > 0 ? $menor['total'] . ' evento(s)' : '' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <th class="px-4 py-3">Mes</th>
                                <th class="px-4 py-3 text-right">Eventos</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($meses as $m)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ $m['etiqueta'] }}</td>
                                    <td class="px-4 py-3 text-right font-semibold">{{ $m['total'] }}</td>
                                </tr>
                            @endforeach
                            <tr class="bg-gray-50 font-bold">
                                <td class="px-4 py-3">Total</td>
                                <td class="px-4 py-3 text-right">{{ $total }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Eventos por mes</h3>
                    <div style="height: 340px;"><canvas id="grafica-meses"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('grafica-meses'), {
                type: 'bar',
                data: {
                    labels: @json(collect($meses)->pluck('etiqueta')),
                    datasets: [{ label: 'Eventos', data: @json(collect($meses)->pluck('total')), backgroundColor: '#6366f1', borderRadius: 6 }],
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