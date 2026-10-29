<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('paquetes.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nuevo Paquete
            </h2>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">

                <form action="{{ route('paquetes.store') }}" method="POST" class="space-y-5">
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
                        <x-input-label for="nombre" value="Nombre del paquete" />
                        <x-text-input id="nombre" name="nombre" type="text"
                            class="mt-1 block w-full"
                            value="{{ old('nombre') }}"
                            placeholder="Ej. Paquete Bodas Premium" />
                        <x-input-error :messages="$errors->get('nombre')" class="mt-1" />
                    </div>

                    {{-- Descripción --}}
                    <div>
                        <x-input-label for="descripcion" value="Descripción (opcional)" />
                        <textarea id="descripcion" name="descripcion" rows="3"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm"
                            placeholder="Descripción del paquete...">{{ old('descripcion') }}</textarea>
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-1" />
                    </div>

                    {{-- Locación y Capacidad --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="id_locacion" value="Locación" />
                            <select id="id_locacion" name="id_locacion"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                <option value="">-- Selecciona una locación --</option>
                                @foreach($locaciones as $locacion)
                                    <option value="{{ $locacion->id }}" {{ old('id_locacion') == $locacion->id ? 'selected' : '' }}>
                                        {{ $locacion->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('id_locacion')" class="mt-1" />
                        </div>
                        <div>
                            <x-input-label for="capacidad" value="Capacidad (personas)" />
                            <x-text-input id="capacidad" name="capacidad" type="number"
                                class="mt-1 block w-full"
                                value="{{ old('capacidad') }}"
                                placeholder="Ej. 150" min="1" />
                            <x-input-error :messages="$errors->get('capacidad')" class="mt-1" />
                        </div>
                    </div>

                    {{-- Precio venta --}}
                    <div>
                        <x-input-label for="precio_venta" value="Precio de venta del paquete *" />
                        <x-text-input id="precio_venta" name="precio_venta" type="number"
                            class="mt-1 block w-full"
                            value="{{ old('precio_venta') }}"
                            placeholder="Ej. 25000.00" min="0" step="0.01" />
                        <x-input-error :messages="$errors->get('precio_venta')" class="mt-1" />
                    </div>

                    {{-- Precio por persona extra --}}
                    <div>
                        <x-input-label for="precio_persona_extra" value="Precio por invitado extra (opcional)" />
                        <x-text-input id="precio_persona_extra" name="precio_persona_extra" type="number"
                            class="mt-1 block w-full"
                            value="{{ old('precio_persona_extra') }}"
                            placeholder="Ej. 450.00" min="0" step="0.01" />
                        <p class="text-xs text-gray-400 mt-1">El paquete cubre hasta su capacidad. Cada invitado por encima de ese número se cobra a este precio (platillo y servicio adicional). Déjalo vacío si el paquete no cobra invitados extra.</p>
                        <x-input-error :messages="$errors->get('precio_persona_extra')" class="mt-1" />
                    </div>

                    {{-- Mobiliario --}}
                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-sm font-medium text-gray-700 mb-3">Mobiliario incluido</p>

                        {{-- Selector --}}
                        <div class="flex flex-wrap gap-2 mb-3">
                            <select id="select-categoria-mob"
                                class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 w-40">
                                <option value="">Todas las categorías</option>
                                @foreach($mobiliario->groupBy(fn($m) => $m->categoria->nombre ?? 'Sin categoría') as $cat => $items)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                            <select id="select-mob"
                                class="flex-1 min-w-0 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Selecciona un artículo --</option>
                                @foreach($mobiliario as $articulo)
                                    <option value="{{ $articulo->id }}"
                                        data-nombre="{{ $articulo->nombre }}"
                                        data-categoria="{{ $articulo->categoria->nombre ?? '' }}"
                                        data-stock="{{ $articulo->stock_disponible }}">
                                        {{ $articulo->nombre }} ({{ $articulo->categoria->nombre ?? '' }}) — Stock: {{ $articulo->stock_disponible }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="number" id="cantidad-mob" placeholder="Cant." min="1" value="1"
                                class="w-16 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <button type="button" id="btn-add-mob"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">add</span>
                                Agregar
                            </button>
                        </div>

                        {{-- Lista de mobiliario agregado --}}
                        <div id="lista-mob" class="space-y-2">
                            <p id="mob-empty" class="text-xs text-gray-400 italic">No se ha agregado mobiliario aún.</p>
                        </div>
                    </div>

                    {{-- Platillos --}}
                    <div class="border-t border-gray-100 pt-4">
                        <p class="text-sm font-medium text-gray-700 mb-3">Platillos incluidos</p>

                        {{-- Selector --}}
                        <div class="flex gap-2 mb-3">
                            <select id="select-categoria-plat"
                                class="border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500 w-40">
                                <option value="">Todas las categorías</option>
                                @foreach($platillos->groupBy('categoria') as $cat => $items)
                                    <option value="{{ $cat }}">{{ ucfirst($cat) }}</option>
                                @endforeach
                            </select>
                            <select id="select-plat"
                                class="flex-1 border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">-- Selecciona un platillo --</option>
                                @foreach($platillos as $platillo)
                                    <option value="{{ $platillo->id }}"
                                        data-nombre="{{ $platillo->nombre }}"
                                        data-categoria="{{ $platillo->categoria }}">
                                        {{ $platillo->nombre }} ({{ ucfirst($platillo->categoria) }})
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" id="btn-add-plat"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">add</span>
                                Agregar
                            </button>
                        </div>

                        {{-- Lista de platillos agregados --}}
                        <div id="lista-plat" class="space-y-2">
                            <p id="plat-empty" class="text-xs text-gray-400 italic">No se han agregado platillos aún.</p>
                        </div>
                    </div>

                    {{-- Servicios adicionales --}}
                    @include('paquetes._servicios', ['servicios' => old('servicios', [])])

                    {{-- Botones --}}
                    <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                        <a href="{{ route('paquetes.index') }}"
                           class="text-sm text-gray-600 hover:text-gray-800 transition-colors">
                            Cancelar
                        </a>
                        <button type="submit"
                            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors">
                            Guardar Paquete
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
    // ── MOBILIARIO ────────────────────────────────────────────────────────────
    const mobiliarioAgregado = {};
    let mobIndex = 0;

    document.getElementById('select-categoria-mob').addEventListener('change', function () {
        const cat = this.value;
        const select = document.getElementById('select-mob');
        select.querySelectorAll('option').forEach(opt => {
            if (!opt.value) return;
            opt.style.display = (!cat || opt.dataset.categoria === cat) ? '' : 'none';
        });
        select.value = '';
    });

    document.getElementById('btn-add-mob').addEventListener('click', function () {
        const select = document.getElementById('select-mob');
        const cantidad = parseInt(document.getElementById('cantidad-mob').value) || 1;
        const id = select.value;
        const nombre = select.options[select.selectedIndex]?.dataset.nombre;
        const stock = parseInt(select.options[select.selectedIndex]?.dataset.stock) || 0;

        if (!id) return;
        if (cantidad > stock) {
        alert(`La cantidad ingresada (${cantidad}) supera el stock disponible (${stock}).`);
        return;
    }
        if (mobiliarioAgregado[id]) {
            alert('Este artículo ya fue agregado.');
            return;
        }

        mobiliarioAgregado[id] = true;
        document.getElementById('mob-empty')?.remove();

        const div = document.createElement('div');
        div.id = `mob-item-${id}`;
        div.className = 'flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2';
        div.innerHTML = `
            <span class="text-sm text-gray-700">${nombre}</span>
            <div class="flex items-center gap-2">
                <input type="hidden" name="mobiliario[${mobIndex}][id]" value="${id}">
                <input type="number" name="mobiliario[${mobIndex}][cantidad]" value="${cantidad}" min="1" max="${stock}"
                    data-stock="${stock}"
                    onchange="if(parseInt(this.value) > ${stock}) { alert('La cantidad supera el stock disponible (${stock}).'); this.value = ${stock}; }"
                    class="w-16 border-gray-300 rounded text-sm text-center focus:ring-indigo-500 focus:border-indigo-500">
                <button type="button" onclick="quitarMob('${id}')"
                    class="text-red-400 hover:text-red-600 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>`;
        document.getElementById('lista-mob').appendChild(div);
        mobIndex++;
        select.value = '';
    });

    function quitarMob(id) {
        delete mobiliarioAgregado[id];
        document.getElementById(`mob-item-${id}`)?.remove();
        if (Object.keys(mobiliarioAgregado).length === 0) {
            const p = document.createElement('p');
            p.id = 'mob-empty';
            p.className = 'text-xs text-gray-400 italic';
            p.textContent = 'No se ha agregado mobiliario aún.';
            document.getElementById('lista-mob').appendChild(p);
        }
    }

    // ── PLATILLOS ─────────────────────────────────────────────────────────────
    const platillosAgregados = {};
    let platIndex = 0;

    document.getElementById('select-categoria-plat').addEventListener('change', function () {
        const cat = this.value;
        const select = document.getElementById('select-plat');
        select.querySelectorAll('option').forEach(opt => {
            if (!opt.value) return;
            opt.style.display = (!cat || opt.dataset.categoria === cat) ? '' : 'none';
        });
        select.value = '';
    });

    document.getElementById('btn-add-plat').addEventListener('click', function () {
        const select = document.getElementById('select-plat');
        const id = select.value;
        const nombre = select.options[select.selectedIndex]?.dataset.nombre;

        if (!id) return;
        if (platillosAgregados[id]) {
            alert('Este platillo ya fue agregado.');
            return;
        }

        platillosAgregados[id] = true;
        document.getElementById('plat-empty')?.remove();

        const div = document.createElement('div');
        div.id = `plat-item-${id}`;
        div.className = 'flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2';
        div.innerHTML = `
            <span class="text-sm text-gray-700">${nombre}</span>
            <div class="flex items-center gap-2">
                <input type="hidden" name="platillos[]" value="${id}">
                <button type="button" onclick="quitarPlat('${id}')"
                    class="text-red-400 hover:text-red-600 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>`;
        document.getElementById('lista-plat').appendChild(div);
        platIndex++;
        select.value = '';
    });

    function quitarPlat(id) {
        delete platillosAgregados[id];
        document.getElementById(`plat-item-${id}`)?.remove();
        if (Object.keys(platillosAgregados).length === 0) {
            const p = document.createElement('p');
            p.id = 'plat-empty';
            p.className = 'text-xs text-gray-400 italic';
            p.textContent = 'No se han agregado platillos aún.';
            document.getElementById('lista-plat').appendChild(p);
        }
    }
    </script>
</x-app-layout>