<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('locaciones.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nueva Locación
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <form action="{{ route('locaciones.store') }}" method="POST" class="space-y-5">
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

                    {{-- Nombre --}}
                    <div>
                        <x-input-label for="nombre" value="Nombre de la locación" />
                        <x-text-input id="nombre" name="nombre" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('nombre') }}"
                            placeholder="Ej. Jardín Los Pinos" />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <x-input-label for="descripcion" value="Descripción (opcional)" />
                        <textarea id="descripcion" name="descripcion" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            placeholder="Descripción general de la locación...">{{ old('descripcion') }}</textarea>
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
                    </div>

                    {{-- Capacidad y Precio --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="capacidad" value="Capacidad (personas)" />
                            <x-text-input id="capacidad" name="capacidad" type="number"
                                class="mt-1 block w-full"
                                value="{{ old('capacidad') }}"
                                placeholder="Ej. 200" min="1" />
                            <x-input-error :messages="$errors->get('capacidad')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="precio_base" value="Precio base ($)" />
                            <x-text-input id="precio_base" name="precio_base" type="number"
                                class="mt-1 block w-full"
                                value="{{ old('precio_base') }}"
                                placeholder="Ej. 15000.00" min="0" step="0.01" />
                            <x-input-error :messages="$errors->get('precio_base')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Dirección --}}
                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-sm font-medium text-gray-700 mb-3">Dirección</p>

                        <div class="grid grid-cols-3 gap-4">
                            <div class="col-span-2">
                                <x-input-label for="calle" value="Calle" />
                                <x-text-input id="calle" name="calle" type="text"
                                    class="mt-1 block w-full"
                                    value="{{ old('calle') }}"
                                    placeholder="Ej. Av. Morelos" />
                                <x-input-error :messages="$errors->get('calle')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="numero_exterior" value="Núm. Exterior" />
                                <x-text-input id="numero_exterior" name="numero_exterior" type="text"
                                    class="mt-1 block w-full"
                                    value="{{ old('numero_exterior') }}"
                                    placeholder="Ej. 45" />
                                <x-input-error :messages="$errors->get('numero_exterior')" class="mt-1" />
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <div>
                                <x-input-label for="numero_interior" value="Núm. Interior (opcional)" />
                                <x-text-input id="numero_interior" name="numero_interior" type="text"
                                    class="mt-1 block w-full"
                                    value="{{ old('numero_interior') }}"
                                    placeholder="Ej. A" />
                                <x-input-error :messages="$errors->get('numero_interior')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="colonia" value="Colonia" />
                                <x-text-input id="colonia" name="colonia" type="text"
                                    class="mt-1 block w-full"
                                    value="{{ old('colonia') }}"
                                    placeholder="Ej. Centro" />
                                <x-input-error :messages="$errors->get('colonia')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="codigo_postal" value="Código Postal" />
                                <x-text-input id="codigo_postal" name="codigo_postal" type="text"
                                    class="mt-1 block w-full"
                                    value="{{ old('codigo_postal') }}"
                                    placeholder="Ej. 62000" />
                                <x-input-error :messages="$errors->get('codigo_postal')" class="mt-1" />
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label for="entidad_feder" value="Entidad Federativa" />
                            <x-text-input id="entidad_feder" name="entidad_feder" type="text"
                                class="mt-1 block w-full"
                                value="{{ old('entidad_feder') }}"
                                placeholder="Ej. Morelos" />
                            <x-input-error :messages="$errors->get('entidad_feder')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Coordenadas --}}
                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-sm font-medium text-gray-700 mb-3">Coordenadas geográficas</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="latitud" value="Latitud" />
                                <x-text-input id="latitud" name="latitud" type="number"
                                    class="mt-1 block w-full"
                                    value="{{ old('latitud') }}"
                                    placeholder="Ej. 18.9261" step="0.0000001" />
                                <x-input-error :messages="$errors->get('latitud')" class="mt-1" />
                            </div>
                            <div>
                                <x-input-label for="longitud" value="Longitud" />
                                <x-text-input id="longitud" name="longitud" type="number"
                                    class="mt-1 block w-full"
                                    value="{{ old('longitud') }}"
                                    placeholder="Ej. -99.2342" step="0.0000001" />
                                <x-input-error :messages="$errors->get('longitud')" class="mt-1" />
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Puedes obtener las coordenadas desde Google Maps haciendo clic derecho sobre la ubicación.</p>
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                        <a href="{{ route('locaciones.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-800 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            Guardar Locación
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>