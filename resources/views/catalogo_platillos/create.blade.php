<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('catalogo-platillos.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nuevo Platillo
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <form action="{{ route('catalogo-platillos.store') }}" method="POST"
                      enctype="multipart/form-data" class="space-y-5">
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
                        <x-input-label for="nombre" value="Nombre del platillo" />
                        <x-text-input id="nombre" name="nombre" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('nombre') }}"
                            placeholder="Ej. Pollo en salsa verde" />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
                    </div>

                    {{-- Categoría --}}
                    <div>
                        <x-input-label for="categoria" value="Categoría" />
                        <select id="categoria" name="categoria"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">-- Selecciona una categoría --</option>
                            <option value="entrada" {{ old('categoria') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                            <option value="plato fuerte" {{ old('categoria') == 'plato fuerte' ? 'selected' : '' }}>Plato fuerte</option>
                            <option value="postre" {{ old('categoria') == 'postre' ? 'selected' : '' }}>Postre</option>
                            <option value="bebida" {{ old('categoria') == 'bebida' ? 'selected' : '' }}>Bebida</option>
                            <option value="menu infantil" {{ old('categoria') == 'menu infantil' ? 'selected' : '' }}>Menú infantil</option>
                        </select>
                        <x-input-error :messages="$errors->get('categoria')" class="mt-1" />
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <x-input-label for="descripcion" value="Descripción (opcional)" />
                        <textarea id="descripcion" name="descripcion" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            placeholder="Descripción y características del platillo...">{{ old('descripcion') }}</textarea>
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
                    </div>

                    {{-- Costo preparación --}}
                    <div>
                        <x-input-label for="costo_preparacion" value="Costo de preparación (opcional)" />
                        <x-text-input id="costo_preparacion" name="costo_preparacion" type="number"
                            class="mt-1 block w-full"
                            value="{{ old('costo_preparacion') }}"
                            placeholder="Ej. 85.00" min="0" step="0.01" />
                        <x-input-error :messages="$errors->get('costo_preparacion')" class="mt-1" />
                    </div>

                    {{-- Precio de venta --}}
                    <div>
                        <x-input-label for="precio_venta" value="Precio de venta por persona (opcional)" />
                        <x-text-input id="precio_venta" name="precio_venta" type="number"
                            class="mt-1 block w-full"
                            value="{{ old('precio_venta') }}"
                            placeholder="Ej. 150.00" min="0" step="0.01" />
                        <p class="text-xs text-gray-400 mt-1">Lo que se cobra al cliente. El costo de preparación es solo interno y no sale en la cotización.</p>
                        <x-input-error :messages="$errors->get('precio_venta')" class="mt-1" />
                    </div>

                    {{-- Imagen --}}
                    <div>
                        <x-input-label for="imagen" value="Imagen (opcional)" />
                        <input id="imagen" name="imagen" type="file" accept="image/*"
                            class="mt-1 block w-full text-sm text-gray-600
                                   file:mr-4 file:py-2 file:px-4 file:rounded-lg
                                   file:border-0 file:text-sm file:font-medium
                                   file:bg-indigo-50 file:text-indigo-700
                                   hover:file:bg-indigo-100 transition-colors" />
                        <p class="text-xs text-gray-400 mt-1">PNG, JPG o WEBP. Máximo 2MB.</p>
                        <x-input-error :messages="$errors->get('imagen')" class="mt-1" />
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('catalogo-platillos.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-800 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            Guardar Platillo
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>