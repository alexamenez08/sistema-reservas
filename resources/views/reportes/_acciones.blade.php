{{-- Botones comunes de los reportes: aplicar filtros, limpiar y descargar el PDF con los mismos filtros --}}
<div class="flex items-end gap-2">
    <button type="submit"
            class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        Aplicar filtros
    </button>
    <a href="{{ route($ruta) }}"
       class="px-4 py-2 rounded-lg text-sm bg-white ring-1 ring-slate-200 hover:bg-slate-50 text-gray-700">
        Limpiar
    </a>
    <a href="{{ request()->fullUrlWithQuery(['pdf' => 1]) }}"
       class="px-4 py-2 rounded-lg text-sm bg-white ring-1 ring-slate-200 hover:bg-slate-50 text-gray-700 flex items-center gap-1">
        <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span> Descargar PDF
    </a>
</div>