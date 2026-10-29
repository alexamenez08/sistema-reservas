@php
    $puedeGestionar = auth()->user()->hasAnyRole(['administrador', 'coordinador']);
@endphp
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('eventos.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalle del Evento
                </h2>
            </div>
@if($puedeGestionar)
            <a href="{{ route('eventos.edit', $evento) }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">edit</span>
                Actualizar Montaje
            </a>
@endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    {{ session('success') }}
                </div>
            @endif

            {{-- Resumen del evento --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Resumen del Evento</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-400">Cliente</p>
                        <p class="font-medium text-gray-900">{{ $evento->reservacion->cliente->nombre_completo ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Estado de Montaje</p>
                        @php
                            $colores = [
                                'pendiente'  => 'bg-yellow-100 text-yellow-700',
                                'en proceso' => 'bg-blue-100 text-blue-700',
                                'terminado'  => 'bg-green-100 text-green-700',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colores[$evento->estado_montaje] ?? '' }}">
                            {{ ucfirst($evento->estado_montaje) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-400">Fecha del Evento</p>
                        <p class="font-medium text-gray-900">{{ $evento->reservacion->dia_evento->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Horario</p>
                        {{ $evento->reservacion->hora_inicio->format('d/m/Y H:i') }} — 
                        {{ $evento->reservacion->hora_fin->format('d/m/Y H:i') }}
                    </div>
                    <div>
                        <p class="text-gray-400">Locación</p>
                        <p class="font-medium text-gray-900">{{ $evento->reservacion->locacion->nombre ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Paquete</p>
                        <p class="font-medium text-gray-900">{{ $evento->reservacion->paquete->nombre ?? 'Sin paquete' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Número de invitados</p>
                        <p class="font-medium text-gray-900">{{ $evento->reservacion->contador_invitados }}</p>
                    </div>
                </div>
            </div>

            {{-- Itinerario / Actividades --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Itinerario</h3>

@if($puedeGestionar)
                {{-- Formulario nueva actividad --}}
                <form action="{{ route('actividades.store', $evento) }}" method="POST"
                      class="bg-gray-50 rounded-lg p-4 mb-4 space-y-3">
                    @csrf

                    @if($errors->any())
                        <div class="bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-sm text-red-700">
                            <ul class="list-disc list-inside">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-3">
                        <div class="col-span-2">
                            <x-input-label for="actividad" value="Actividad" />
                            <x-text-input id="actividad" name="actividad" type="text"
                                class="mt-1 block w-full"
                                value="{{ old('actividad') }}"
                                placeholder="Ej. Recepción de invitados" />
                        </div>
                        <div>
                            <x-input-label for="hora_inicio" value="Fecha y hora inicio" />
                            <x-text-input id="hora_inicio" name="hora_inicio" type="datetime-local"
                                class="mt-1 block w-full"
                                value="{{ old('hora_inicio') }}" />
                        </div>
                        <div>
                            <x-input-label for="hora_fin" value="Fecha y hora fin" />
                            <x-text-input id="hora_fin" name="hora_fin" type="datetime-local"
                                class="mt-1 block w-full"
                                value="{{ old('hora_fin') }}" />
                        </div>
                        <div class="col-span-2">
                            <x-input-label for="responsable" value="Responsable" />
                            <select id="responsable" name="responsable"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">-- Selecciona un responsable --</option>
                                @foreach(\App\Models\User::where('is_active', true)->get() as $usuario)
                                    <option value="{{ $usuario->id }}" {{ old('responsable') == $usuario->id ? 'selected' : '' }}>
                                        {{ $usuario->name }} {{ $usuario->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <x-input-label for="descripcion" value="Descripción (opcional)" />
                            <textarea id="descripcion" name="descripcion" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                                placeholder="Detalles adicionales...">{{ old('descripcion') }}</textarea>
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                            Agregar Actividad
                        </button>
                    </div>
                </form>
@endif

                {{-- Lista de actividades --}}
                @forelse($evento->actividades as $actividad)
                    <div class="flex items-start justify-between py-3 border-b border-gray-100 last:border-0">
                        <div class="flex items-start gap-3">
                            <div class="text-xs text-gray-400 mt-0.5 w-32 shrink-0">
                                {{ $actividad->hora_inicio->format('d/m H:i') }} — {{ $actividad->hora_fin->format('d/m H:i') }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $actividad->actividad }}</p>
                                @if($actividad->descripcion)
                                    <p class="text-xs text-gray-400">{{ $actividad->descripcion }}</p>
                                @endif
                                <p class="text-xs text-indigo-600 mt-0.5">
                                    Responsable: {{ $actividad->responsableUsuario->name ?? '—' }} {{ $actividad->responsableUsuario->last_name ?? '' }}
                                </p>
                            </div>
                        </div>
@if($puedeGestionar)
                        <form action="{{ route('actividades.destroy', $actividad) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar esta actividad?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </form>
@endif
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">No hay actividades registradas en el itinerario.</p>
                @endforelse
            </div>

            {{-- Asignación de Staff --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Staff Asignado</h3>

@if($puedeGestionar)
                {{-- Formulario nueva asignación --}}
                <form action="{{ route('staff.store', $evento) }}" method="POST"
                    class="bg-gray-50 rounded-lg p-4 mb-4 space-y-3">
                    @csrf

                    @if($errors->has('empleado_id'))
                        <div class="bg-red-50 border border-red-200 rounded-lg px-3 py-2 text-sm text-red-700">
                            {{ $errors->first('empleado_id') }}
                        </div>
                    @endif

                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <x-input-label for="empleado_id" value="Empleado" />
                            <select id="empleado_id" name="empleado_id"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">-- Selecciona --</option>
                                @foreach(\App\Models\User::where('is_active', true)->get() as $usuario)
                                    <option value="{{ $usuario->id }}">
                                        {{ $usuario->name }} {{ $usuario->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="rol_asignado" value="Rol en el evento" />
                            <select id="rol_asignado" name="rol_asignado"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">-- Selecciona --</option>
                                <option value="Mesero">Mesero</option>
                                <option value="Coordinador">Coordinador</option>
                                <option value="Limpieza">Limpieza</option>
                                <option value="Cocina">Cocina</option>
                                <option value="Seguridad">Seguridad</option>
                            </select>
                            <x-input-error :messages="$errors->get('rol_asignado')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="pago_evento" value="Pago ($)" />
                            <x-text-input id="pago_evento" name="pago_evento" type="number"
                                class="mt-1 block w-full"
                                value="{{ old('pago_evento') }}"
                                placeholder="Ej. 500.00" min="0" step="0.01" />
                            <x-input-error :messages="$errors->get('pago_evento')" class="mt-1" />
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                            Asignar Staff
                        </button>
                    </div>
                </form>
@endif

                {{-- Lista de staff asignado --}}
                @php
                    $staff = $evento->staff ?? collect();
                    $totalPago = $staff->sum('pago_evento');
                @endphp

                @forelse($evento->staff as $asignacion)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-semibold text-xs">
                                {{ strtoupper(substr($asignacion->empleado->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $asignacion->empleado->name ?? '—' }} {{ $asignacion->empleado->last_name ?? '' }}
                                </p>
                                <p class="text-xs text-gray-400">{{ $asignacion->rol_asignado }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
@if($puedeGestionar)
                            <span class="text-sm font-medium text-gray-700">${{ number_format($asignacion->pago_evento, 2) }}</span>
                            <form action="{{ route('staff.destroy', $asignacion) }}" method="POST"
                                onsubmit="return confirm('¿Eliminar esta asignación?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </form>
@endif
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">No hay staff asignado a este evento.</p>
                @endforelse

@if($puedeGestionar)
                @if(isset($evento->staff) && $evento->staff->count() > 0)
                    <div class="mt-3 pt-3 border-t border-gray-200 flex justify-end">
                        <p class="text-sm font-semibold text-gray-700">
                            Total a pagar: <span class="text-indigo-600">${{ number_format($totalPago, 2) }}</span>
                        </p>
                    </div>
                @endif
@endif
            </div>
        </div>
    </div>
</x-app-layout>