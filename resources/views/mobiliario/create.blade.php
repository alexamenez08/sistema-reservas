<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('mobiliario.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nuevo Artículo de Mobiliario
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <form action="{{ route('mobiliario.store') }}" method="POST" class="space-y-5">
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
                        <x-input-label for="nombre" value="Nombre del artículo" />
                        <x-text-input id="nombre" name="nombre" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('nombre') }}"
                            placeholder="Ej. Silla tiffany" />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <x-input-label for="descripcion" value="Descripción (opcional)" />
                        <textarea id="descripcion" name="descripcion" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            placeholder="Características del artículo...">{{ old('descripcion') }}</textarea>
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
                    </div>

                    {{-- Categoría --}}
                    <div>
                        <x-input-label for="id_categoriamob" value="Categoría" />
                        <select id="id_categoriamob" name="id_categoriamob"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            <option value="">-- Selecciona una categoría --</option>
                            @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" {{ old('id_categoriamob') == $categoria->id ? 'selected' : '' }}>
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
                                value="{{ old('stock_total') }}"
                                placeholder="Ej. 50" min="1" />
                            <x-input-error :messages="$errors->get('stock_total')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="precio_renta_unitario" value="Precio renta unitario ($)" />
                            <x-text-input id="precio_renta_unitario" name="precio_renta_unitario" type="number"
                                class="mt-1 block w-full"
                                value="{{ old('precio_renta_unitario') }}"
                                placeholder="Ej. 25.00" min="0" step="0.01" />
                            <x-input-error :messages="$errors->get('precio_renta_unitario')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Costo reposición --}}
                    <div>
                        <x-input-label for="costo_reposicion" value="Costo de reposición (opcional)" />
                        <x-text-input id="costo_reposicion" name="costo_reposicion" type="number"
                            class="mt-1 block w-full"
                            value="{{ old('costo_reposicion') }}"
                            placeholder="Ej. 150.00" min="0" step="0.01" />
                        <x-input-error :messages="$errors->get('costo_reposicion')" class="mt-1" />
                    </div>

                    {{-- Nota stock disponible --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex items-start gap-2 text-sm text-blue-700">
                        <span class="material-symbols-outlined text-[18px] mt-0.5">info</span>
                        El stock disponible se establecerá automáticamente igual al stock total al registrar el artículo.
                    </div>

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-3 pt-2">
                        <a href="{{ route('mobiliario.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-800 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            Guardar Artículo
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>