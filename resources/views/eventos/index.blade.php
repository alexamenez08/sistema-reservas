@php
    $puedeGestionar = auth()->user()->hasAnyRole(['administrador', 'coordinador']);
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $puedeGestionar ? 'Gestión de Eventos' : 'Mis Eventos' }}
            </h2>
@if($puedeGestionar)
            <a href="{{ route('eventos.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span>
                Nuevo Evento
            </a>
@endif
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado Montaje</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($eventos as $evento)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    {{ $evento->reservacion->cliente->nombre_completo ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $evento->reservacion->dia_evento->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $evento->reservacion->locacion->nombre ?? '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $colores = [
                                            'pendiente'  => 'bg-yellow-100 text-yellow-700',
                                            'en proceso' => 'bg-blue-100 text-blue-700',
                                            'terminado'  => 'bg-green-100 text-green-700',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colores[$evento->estado_montaje] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst($evento->estado_montaje) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('eventos.show', $evento) }}"
                                           class="text-gray-500 hover:text-gray-700 transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                                        </a>
@if($puedeGestionar)
                                        <a href="{{ route('eventos.edit', $evento) }}"
                                           class="text-indigo-600 hover:text-indigo-800 transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                        <form action="{{ route('eventos.destroy', $evento) }}" method="POST"
                                              onsubmit="return confirm('¿Estás segura de eliminar este evento?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 transition-colors">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
@endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                    <span class="material-symbols-outlined text-4xl block mb-2">event_busy</span>
                                    {{ $puedeGestionar ? 'No hay eventos registrados.' : 'Aún no tienes eventos asignados.' }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($eventos->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $eventos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>