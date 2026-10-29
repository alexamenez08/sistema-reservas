<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('reservaciones.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="material-symbols-outlined">arrow_back</span>
                </a>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalle de Reservación
                </h2>
            </div>
            <a href="{{ route('reservaciones.edit', $reservacion) }}"
               class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">edit</span>
                Editar
            </a>
            <a href="{{ route('reservaciones.cotizacion', $reservacion) }}"
               class="bg-white hover:bg-gray-50 text-gray-700 ring-1 ring-gray-200 text-sm font-medium px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                Cotización PDF
            </a>
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

            {{-- Info general --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Información General</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-400">Cliente</p>
                        <p class="font-medium text-gray-900">{{ $reservacion->cliente->nombre_completo ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Estado</p>
                        @php
                            $colores = [
                                'pendiente'  => 'bg-yellow-100 text-yellow-700',
                                'confirmada' => 'bg-green-100 text-green-700',
                                'en curso'   => 'bg-blue-100 text-blue-700',
                                'finalizada' => 'bg-gray-100 text-gray-700',
                                'cancelada'  => 'bg-red-100 text-red-700',
                            ];
                        @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colores[$reservacion->status] ?? '' }}">
                            {{ ucfirst($reservacion->status) }}
                        </span>
                    </div>
                    <div>
                        <p class="text-gray-400">Fecha del evento</p>
                        <p class="font-medium text-gray-900">{{ $reservacion->dia_evento->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Horario</p>
                        <p class="font-medium text-gray-900">
                            {{ $reservacion->hora_inicio->format('d/m/Y H:i') }} — {{ $reservacion->hora_fin->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-gray-400">Locación</p>
                        <p class="font-medium text-gray-900">{{ $reservacion->locacion->nombre ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Paquete</p>
                        <p class="font-medium text-gray-900">{{ $reservacion->paquete->nombre ?? 'Sin paquete' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400">Número de invitados</p>
                        <p class="font-medium text-gray-900">{{ $reservacion->contador_invitados }}</p>
                    </div>
                </div>
            </div>

            {{-- Resumen financiero --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">Resumen Financiero</h3>
                @if($reservacion->descuento > 0)
                    <p class="text-sm text-green-700 mb-3">
                        Descuento aplicado: -${{ number_format($reservacion->descuento, 2) }}
                        @if($reservacion->motivo_descuento)
                            <span class="text-gray-500">({{ $reservacion->motivo_descuento }})</span>
                        @endif
                    </p>
                @endif
                <div class="grid grid-cols-3 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-400 mb-1">Costo Total</p>
                        <p class="text-lg font-semibold text-gray-900">${{ number_format($reservacion->costo_total, 2) }}</p>
                    </div>
                    <div class="bg-green-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-400 mb-1">Total Anticipado</p>
                        <p class="text-lg font-semibold text-green-700">${{ number_format($reservacion->anticipos->sum('monto'), 2) }}</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 text-center">
                        <p class="text-xs text-gray-400 mb-1">Saldo Pendiente</p>
                        <p class="text-lg font-semibold text-red-600">${{ number_format($reservacion->saldo_pendiente, 2) }}</p>
                    </div>
                </div>
            </div>

            {{-- Anticipos --}}
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Anticipos</h3>
                </div>

                {{-- Formulario nuevo anticipo --}}
                @if($reservacion->saldo_pendiente > 0 && $reservacion->status !== 'cancelada')
                    <form action="{{ route('anticipos.store', $reservacion) }}" method="POST"
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

                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <x-input-label for="monto" value="Monto ($)" />
                                <x-text-input id="monto" name="monto" type="number"
                                    class="mt-1 block w-full"
                                    value="{{ old('monto') }}"
                                    placeholder="Ej. 5000.00" min="0.01" step="0.01" />
                            </div>
                            <div>
                                <x-input-label for="fecha_pago" value="Fecha de pago" />
                                <x-text-input id="fecha_pago" name="fecha_pago" type="date"
                                    class="mt-1 block w-full"
                                    value="{{ old('fecha_pago', now()->format('Y-m-d')) }}" />
                            </div>
                            <div>
                                <x-input-label for="notas" value="Notas (opcional)" />
                                <x-text-input id="notas" name="notas" type="text"
                                    class="mt-1 block w-full"
                                    value="{{ old('notas') }}"
                                    placeholder="Ej. Transferencia bancaria" />
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                                Registrar Anticipo
                            </button>
                        </div>
                    </form>
                @endif

                {{-- Lista de anticipos --}}
                @forelse($reservacion->anticipos as $anticipo)
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-gray-900">${{ number_format($anticipo->monto, 2) }}</p>
                            <p class="text-xs text-gray-400">{{ $anticipo->fecha_pago->format('d/m/Y') }} {{ $anticipo->notas ? '— ' . $anticipo->notas : '' }}</p>
                        </div>
                        <form action="{{ route('anticipos.destroy', $anticipo) }}" method="POST"
                              onsubmit="return confirm('¿Eliminar este anticipo?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-400 hover:text-red-600 transition-colors">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-sm text-gray-400 italic">No se han registrado anticipos.</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>