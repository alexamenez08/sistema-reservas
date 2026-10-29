<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('reservaciones.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nueva Reservación
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <form action="{{ route('reservaciones.store') }}" method="POST" class="space-y-5">
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

                    {{-- Cliente --}}
                    <div>
                        <x-input-label for="cliente_id" value="Cliente" />
                        <select id="cliente_id" name="cliente_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">-- Selecciona un cliente --</option>
                            @foreach($clientes as $cliente)
                                <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                    {{ $cliente->nombre_completo }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('cliente_id')" class="mt-1" />
                    </div>

                    {{-- Locación --}}
                    <div>
                        <x-input-label for="locacion_id" value="Locación" />
                        <select id="locacion_id" name="locacion_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">-- Selecciona una locación --</option>
                            @foreach($locaciones as $locacion)
                                <option value="{{ $locacion->id }}"
                                    data-capacidad="{{ $locacion->capacidad }}"
                                    {{ old('locacion_id') == $locacion->id ? 'selected' : '' }}>
                                    {{ $locacion->nombre }} (Cap. {{ $locacion->capacidad }})
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('locacion_id')" class="mt-1" />
                    </div>

                    {{-- Paquete --}}
                    <div>
                        <x-input-label for="paquete_id" value="Paquete (opcional)" />
                        <select id="paquete_id" name="paquete_id"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">-- Sin paquete --</option>
                            @foreach($paquetes as $paquete)
                                <option value="{{ $paquete->id }}"
                                    data-precio="{{ $paquete->precio_venta }}"
                                    data-locacion="{{ $paquete->id_locacion }}"
                                    data-capacidad="{{ $paquete->capacidad }}"
                                    {{ old('paquete_id') == $paquete->id ? 'selected' : '' }}>
                                    {{ $paquete->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('paquete_id')" class="mt-1" />
                    </div>

                    {{-- Fecha y horario --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="dia_evento" value="Fecha del evento" />
                            <x-text-input id="dia_evento" name="dia_evento" type="date"
                                class="mt-1 block w-full"
                                value="{{ old('dia_evento') }}" />
                            <x-input-error :messages="$errors->get('dia_evento')" class="mt-1" />
                        </div>
                        <div>
                            {{-- campo oculto para compatibilidad --}}
                        </div>
                        <div>
                            <x-input-label for="hora_inicio" value="Fecha y hora de inicio" />
                            <x-text-input id="hora_inicio" name="hora_inicio" type="datetime-local"
                                class="mt-1 block w-full"
                                value="{{ old('hora_inicio') }}" />
                            <x-input-error :messages="$errors->get('hora_inicio')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="hora_fin" value="Fecha y hora de fin" />
                            <x-text-input id="hora_fin" name="hora_fin" type="datetime-local"
                                class="mt-1 block w-full"
                                value="{{ old('hora_fin') }}" />
                            <x-input-error :messages="$errors->get('hora_fin')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Invitados --}}
                    <div>
                        <x-input-label for="contador_invitados" value="Número de invitados" />
                        <x-text-input id="contador_invitados" name="contador_invitados" type="number"
                            class="mt-1 block w-full"
                            value="{{ old('contador_invitados') }}"
                            placeholder="Ej. 150"
                            min="1" />
                        <x-input-error :messages="$errors->get('contador_invitados')" class="mt-1" />
                    </div>

                    {{-- Descuento y resumen de costos (el total se calcula automáticamente) --}}
                    @include('reservaciones._costos')

                    {{-- Status --}}
                    <div>
                        <x-input-label for="status" value="Estado" />
                        <select id="status" name="status"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="pendiente" {{ old('status') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                            <option value="confirmada" {{ old('status') == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                            <option value="en curso" {{ old('status') == 'en curso' ? 'selected' : '' }}>En curso</option>
                            <option value="finalizada" {{ old('status') == 'finalizada' ? 'selected' : '' }}>Finalizada</option>
                            <option value="cancelada" {{ old('status') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1" />
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('reservaciones.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-800 transition-colors">
                            Cancelar
                        </a>

                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            Guardar Reservación
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const paqueteSelect  = document.getElementById('paquete_id');
        const locacionSelect = document.getElementById('locacion_id');
        const invitadosInput = document.getElementById('contador_invitados');

        paqueteSelect.addEventListener('change', function () {
            const opt       = this.options[this.selectedIndex];
            const precio    = opt?.dataset.precio;
            const locacion  = opt?.dataset.locacion;
            const capacidad = opt?.dataset.capacidad;

            if (this.value) {
                if (locacion) {
                    locacionSelect.value = locacion;
                    locacionSelect.style.pointerEvents = 'none';
                    locacionSelect.style.opacity = '0.6';
                }
                // El paquete cubre hasta su capacidad; se permiten invitados extra hasta la capacidad de la locación
                const optLoc = locacionSelect.options[locacionSelect.selectedIndex];
                if (optLoc?.dataset.capacidad) invitadosInput.max = optLoc.dataset.capacidad;
            } else {
                locacionSelect.style.pointerEvents = '';
                locacionSelect.style.opacity = '';
                locacionSelect.value = '';
                invitadosInput.removeAttribute('max');
            }
        });

        locacionSelect.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const capacidad = opt?.dataset.capacidad;
            if (capacidad) invitadosInput.max = capacidad;
        });
    });
    </script>
</x-app-layout>