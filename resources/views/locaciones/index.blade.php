<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestión de Locaciones
            </h2>
            <a href="{{ route('locaciones.create') }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add_location</span>
                Nueva Locación
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio Base</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dirección</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($locaciones as $locacion)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $locacion->nombre }}</p>
                                        <p class="text-xs text-gray-400">{{ Str::limit($locacion->descripcion, 50) }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px] text-gray-400">group</span>
                                        {{ $locacion->capacidad }} personas
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    ${{ number_format($locacion->precio_base, 2) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">
                                    {{ $locacion->calle }} {{ $locacion->numero_exterior }},
                                    {{ $locacion->colonia }}, {{ $locacion->codigo_postal }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $locacion->activo ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $locacion->activo ? 'Activa' : 'Inactiva' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('locaciones.edit', $locacion) }}"
                                           class="text-indigo-600 hover:text-indigo-800 transition-colors">
                                            <span class="material-symbols-outlined text-[20px]">edit</span>
                                        </a>
                                        <form action="{{ route('locaciones.destroy', $locacion) }}" method="POST"
                                              onsubmit="return confirm('¿Estás segura de eliminar esta locación?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 transition-colors">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                    <span class="material-symbols-outlined text-4xl block mb-2">location_off</span>
                                    No hay locaciones registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                @if($locaciones->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $locaciones->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>