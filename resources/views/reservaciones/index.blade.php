<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Reservaciones
            </h2>
            <a href="{{ route('reservaciones.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Nueva Reservación
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Evento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Locación</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paquete</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Costo Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Saldo Pendiente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($reservaciones as $reservacion)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-medium text-gray-900">{{ $reservacion->cliente->nombre_completo ?? '—' }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $reservacion->dia_evento->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $reservacion->locacion->nombre ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $reservacion->paquete->nombre ?? 'Sin paquete' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    ${{ number_format($reservacion->costo_total, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <span class="font-medium {{ $reservacion->saldo_pendiente > 0 ? 'text-red-600' : 'text-green-600' }}">
                                        ${{ number_format($reservacion->saldo_pendiente, 2) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $colores = [
                                            'pendiente'  => 'bg-yellow-100 text-yellow-700',
                                            'confirmada' => 'bg-green-100 text-green-700',
                                            'en curso'   => 'bg-blue-100 text-blue-700',
                                            'finalizada' => 'bg-gray-100 text-gray-700',
                                            'cancelada'  => 'bg-red-100 text-red-700',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colores[$reservacion->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($reservacion->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('reservaciones.show', $reservacion) }}"
                                           class="text-gray-500 hover:text-gray-700 transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                                        </a>
                                        <a href="{{ route('reservaciones.edit', $reservacion) }}"
                                           class="text-indigo-600 hover:text-indigo-800 transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                        <form action="{{ route('reservaciones.destroy', $reservacion) }}" method="POST"
                                              onsubmit="return confirm('¿Estás segura de cancelar esta reservación?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 transition-colors">
                                                <span class="material-symbols-outlined text-[20px]">cancel</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-gray-400">
                                    <span class="material-symbols-outlined text-4xl block mb-2">event_busy</span>
                                    No hay reservaciones registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($reservaciones->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $reservaciones->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>