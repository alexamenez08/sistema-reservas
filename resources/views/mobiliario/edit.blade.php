<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('mobiliario.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Editar Artículo de Mobiliario
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <form action="{{ route('mobiliario.update', $mobiliario) }}" method="POST" class="space-y-5">
                    @csrf
                    @method('PUT')

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
                        <x-input-label for="nombre" value="Nombre del artículo" />
                        <x-text-input id="nombre" name="nombre" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('nombre', $mobiliario->nombre) }}" />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <x-input-label for="descripcion" value="Descripción (opcional)" />
                        <textarea id="descripcion" name="descripcion" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('descripcion', $mobiliario->descripcion) }}</textarea>
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
                    </div>

                    {{-- Categoría --}}
                    <div>
                        <x-input-label for="id_categoriamob" value="Categoría" />
                        <select id="id_categoriamob" name="id_categoriamob"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">-- Selecciona una categoría --</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}"
                                    {{ old('id_categoriamob', $mobiliario->id_categoriamob) == $categoria->id ? 'selected' : '' }}>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('id_categoriamob')" class="mt-1" />
                    </div>

                    {{-- Stock --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="stock_total" value="Stock total" />
                            <x-text-input id="stock_total" name="stock_total" type="number"
                                class="mt-1 block w-full"
                                value="{{ old('stock_total', $mobiliario->stock_total) }}"
                                min="1" />
                            <x-input-error :messages="$errors->get('stock_total')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="precio_renta_unitario" value="Precio renta unitario ($)" />
                            <x-text-input id="precio_renta_unitario" name="precio_renta_unitario" type="number"
                                class="mt-1 block w-full"
                                value="{{ old('precio_renta_unitario', $mobiliario->precio_renta_unitario) }}"
                                min="0" step="0.01" />
                            <x-input-error :messages="$errors->get('precio_renta_unitario')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Stock disponible (solo lectura) --}}
                    <div>
                        <x-input-label value="Stock disponible actual" />
                        <div class="mt-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-md text-sm text-gray-600">
                            {{ $mobiliario->stock_disponible }} unidades disponibles
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Se ajusta automáticamente al modificar el stock total.</p>
                    </div>

                    {{-- Costo reposición --}}
                    <div>
                        <x-input-label for="costo_reposicion" value="Costo de reposición (opcional)" />
                        <x-text-input id="costo_reposicion" name="costo_reposicion" type="number"
                            class="mt-1 block w-full"
                            value="{{ old('costo_reposicion', $mobiliario->costo_reposicion) }}"
                            min="0" step="0.01" />
                        <x-input-error :messages="$errors->get('costo_reposicion')" class="mt-1" />
                    </div>

                    {{-- Estado --}}
                    <div class="flex items-center gap-3">
                        <input type="hidden" name="activo" value="0">
                        <input type="checkbox" id="activo" name="activo" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            {{ $mobiliario->activo ? 'checked' : '' }}>
                        <x-input-label for="activo" value="Artículo activo" />
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('mobiliario.index') }}"
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