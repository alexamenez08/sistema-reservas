<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Reporte de ingresos y saldos pendientes</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Filtros --}}
            <form method="GET" action="{{ route('reportes.ingresos') }}" class="bg-white shadow-sm rounded-lg p-4 flex flex-wrap items-end gap-4">
                <div>
                    <label for="desde" class="block text-sm font-medium text-gray-600">Eventos desde</label>
                    <input type="date" id="desde" name="desde" value="{{ $desde->toDateString() }}"
                           class="mt-1 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label for="hasta" class="block text-sm font-medium text-gray-600">Hasta</label>
                    <input type="date" id="hasta" name="hasta" value="{{ $hasta->toDateString() }}"
                           class="mt-1 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                @include('reportes._acciones', ['ruta' => 'reportes.ingresos'])
            </form>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">
                    @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
                </div>
            @endif

            {{-- Resumen --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-indigo-500">
                    <p class="text-xs text-gray-500">Total de los eventos</p>
                    <p class="text-xl font-bold text-gray-800">${{ number_format($resumen['total'], 2) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-green-500">
                    <p class="text-xs text-gray-500">Cobrado (anticipos y pagos)</p>
                    <p class="text-xl font-bold text-gray-800">${{ number_format($resumen['pagado'], 2) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-amber-500">
                    <p class="text-xs text-gray-500">Por cobrar</p>
                    <p class="text-xl font-bold text-gray-800">${{ number_format($resumen['saldo'], 2) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow-sm p-5 border-l-4 border-red-500">
                    <p class="text-xs text-gray-500">Saldo vencido</p>
                    <p class="text-xl font-bold text-gray-800">${{ number_format($resumen['monto_vencido'], 2) }}</p>
                    <p class="text-xs text-gray-400">{{ $resumen['vencidas'] }} cuenta(s)</p>
                </div>
            </div>

            {{-- Listado --}}
            <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-4 py-3">Fecha</th>
                            <th class="px-4 py-3">Cliente</th>
                            <th class="px-4 py-3">Locación</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-right">Pagado</th>
                            <th class="px-4 py-3 text-right">Saldo</th>
                            <th class="px-4 py-3">Estado de pago</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($filas as $f)
                            @php
                                $colores = [
                                    'Liquidado'    => 'bg-green-100 text-green-700',
                                    'Con anticipo' => 'bg-indigo-100 text-indigo-700',
                                    'Sin anticipo' => 'bg-amber-100 text-amber-700',
                                    'Vencido'      => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap">{{ $f->fecha->format('d/m/Y') }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('reservaciones.show', $f->id) }}" class="text-indigo-600 hover:text-indigo-800">{{ $f->cliente }}</a>
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $f->locacion }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($f->total, 2) }}</td>
                                <td class="px-4 py-3 text-right">${{ number_format($f->pagado, 2) }}</td>
                                <td class="px-4 py-3 text-right font-semibold">${{ number_format($f->saldo, 2) }}</td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $colores[$f->estado] }}">{{ $f->estado }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-10 text-center text-gray-400 italic">No hay eventos en el rango seleccionado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="text-xs text-gray-400">
                "Vencido" significa que el evento ya se realizó y todavía hay saldo por pagar. Se excluyen las reservaciones canceladas.
            </p>
        </div>
    </div>
</x-app-layout>