<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('eventos.show', $evento) }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Estado de Montaje
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <form action="{{ route('eventos.update', $evento) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

                    {{-- Info del evento --}}
                    <div class="bg-gray-50 rounded-lg p-4 text-sm space-y-1">
                        <p class="text-gray-500">Cliente: <span class="font-medium text-gray-900">{{ $evento->reservacion->cliente->nombre_completo ?? '—' }}</span></p>
                        <p class="text-gray-500">Fecha: <span class="font-medium text-gray-900">{{ $evento->reservacion->dia_evento->format('d/m/Y') }}</span></p>
                        <p class="text-gray-500">Locación: <span class="font-medium text-gray-900">{{ $evento->reservacion->locacion->nombre ?? '—' }}</span></p>
                    </div>

                    {{-- Estado montaje --}}
                    <div>
                        <x-input-label for="estado_montaje" value="Estado del montaje" />
                        <select id="estado_montaje" name="estado_montaje"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="pendiente" {{ $evento->estado_montaje == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="en proceso" {{ $evento->estado_montaje == 'en proceso' ? 'selected' : '' }}>En proceso</option>
                            <option value="terminado" {{ $evento->estado_montaje == 'terminado' ? 'selected' : '' }}>Terminado</option>
                        </select>
                        <x-input-error :messages="$errors->get('estado_montaje')" class="mt-1" />
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('eventos.show', $evento) }}"
                           class="text-sm text-gray-600 hover:text-gray-800 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            Guardar Cambios
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>