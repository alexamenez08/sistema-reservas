<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Respaldo y restauración de la base de datos</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 text-sm rounded-lg px-4 py-3">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3">{{ session('error') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                {{-- Generar respaldo --}}
                <div class="bg-white shadow-sm rounded-lg p-6">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-2">Generar respaldo</h3>
                    <p class="text-sm text-gray-500 mb-4">
                        Crea un archivo <strong>.sql</strong> con toda la información del sistema (clientes, reservaciones, paquetes, usuarios, etc.)
                        que puedes descargar y guardar.
                    </p>
                    <form action="{{ route('respaldos.store') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">backup</span>
                            Generar respaldo ahora
                        </button>
                    </form>
                    <p class="text-xs text-gray-400 mt-3">
                        Solo respalda la base de datos. Las imágenes (platillos, logo) están en la carpeta <code>storage</code> y se copian aparte.
                    </p>
                </div>

                {{-- Restaurar --}}
                <div class="bg-white shadow-sm rounded-lg p-6 border border-red-100">
                    <h3 class="text-sm font-semibold text-red-700 uppercase tracking-wider mb-2">Restaurar respaldo</h3>
                    <div class="text-sm bg-red-50 text-red-700 rounded-lg px-3 py-2 mb-4">
                        Esta acción <strong>reemplaza todos los datos actuales</strong> por los del archivo. Antes se guarda una copia de
                        seguridad automática para poder deshacerlo.
                    </div>

                    <form action="{{ route('respaldos.restaurar') }}" method="POST" enctype="multipart/form-data" class="space-y-4"
                          onsubmit="return confirm('¿Seguro que quieres restaurar? Se reemplazarán todos los datos actuales.');">
                        @csrf
                        <div>
                            <label for="archivo" class="block text-sm font-medium text-gray-600">Archivo de respaldo (.sql)</label>
                            <input type="file" id="archivo" name="archivo" accept=".sql"
                                   class="mt-1 block w-full text-sm text-gray-600 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                        </div>
                        <div>
                            <label for="confirmacion" class="block text-sm font-medium text-gray-600">Para confirmar, escribe RESTAURAR</label>
                            <input type="text" id="confirmacion" name="confirmacion" autocomplete="off"
                                   class="mt-1 block w-full text-sm rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500">
                        </div>
                        <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition-colors flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px]">restore</span>
                            Restaurar base de datos
                        </button>
                    </form>
                </div>
            </div>

            {{-- Respaldos guardados --}}
            <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">Respaldos guardados en el servidor</h3>
                </div>
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <th class="px-6 py-3">Archivo</th>
                            <th class="px-6 py-3">Fecha y hora</th>
                            <th class="px-6 py-3">Tamaño</th>
                            <th class="px-6 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($respaldos as $r)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-3">
                                    {{ $r['nombre'] }}
                                    @if($r['previo'])
                                        <span class="ml-2 inline-flex px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700">previo a restauración</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-gray-600">{{ $r['fecha']->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-3 text-gray-600">{{ $r['tamano'] }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('respaldos.descargar', $r['nombre']) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">Descargar</a>
                                        <form action="{{ route('respaldos.destroy', $r['nombre']) }}" method="POST"
                                              onsubmit="return confirm('¿Eliminar este respaldo?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-6 py-10 text-center text-gray-400 italic">Todavía no hay respaldos. Genera el primero.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>