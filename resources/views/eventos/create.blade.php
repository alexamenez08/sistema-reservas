<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('eventos.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nuevo Evento
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <form action="{{ route('eventos.store') }}" method="POST" class="space-y-5">
                    @csrf

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Reservación --}}
                    <div>
                        <x-input-label for="reservacion_id" value="Reservación confirmada" />
                        <select id="reservacion_id" name="reservacion_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">-- Selecciona una reservación --</option>
                            @forelse($reservaciones as $reservacion)
                                <option value="{{ $reservacion->id }}" {{ old('reservacion_id') == $reservacion->id ? 'selected' : '' }}>
                                    {{ $reservacion->cliente->nombre_completo }} —
                                    {{ $reservacion->dia_evento->format('d/m/Y') }} —
                                    {{ $reservacion->locacion->nombre }}
                                </option>
                            @empty
                            @endforelse
                        </select>
                        <x-input-error :messages="$errors->get('reservacion_id')" class="mt-1" />
                        @if($reservaciones->isEmpty())
                            <p class="text-xs text-yellow-600 mt-1">No hay reservaciones confirmadas sin evento asignado.</p>
                        @endif
                    </div>

                    {{-- Estado montaje --}}
                    <div>
                        <x-input-label for="estado_montaje" value="Estado del montaje" />
                        <select id="estado_montaje" name="estado_montaje"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="pendiente" {{ old('estado_montaje') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="en proceso" {{ old('estado_montaje') == 'en proceso' ? 'selected' : '' }}>En proceso</option>
                            <option value="terminado" {{ old('estado_montaje') == 'terminado' ? 'selected' : '' }}>Terminado</option>
                        </select>
                        <x-input-error :messages="$errors->get('estado_montaje')" class="mt-1" />
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('eventos.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-800 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            Crear Evento
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>