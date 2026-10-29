{{--
  Servicios adicionales del paquete (filas dinámicas concepto + precio).
  Uso:
    create: @include('paquetes._servicios', ['servicios' => old('servicios', [])])
    edit:   @include('paquetes._servicios', ['servicios' => old('servicios', $paquete->servicios->map(fn ($s) => ['concepto' => $s->concepto, 'precio' => $s->precio])->all())])
--}}
<div>
    <x-input-label value="Servicios adicionales (opcional)" />
    <p class="text-xs text-gray-400 mt-1">
        Servicios que incluye el paquete y que aparecen en la cotización. Ej. Montaje y decoración, DJ, Coordinación del evento. El precio es opcional y solo informativo: el total lo define el precio de venta del paquete.
    </p>

    <div id="lista-servicios" class="space-y-2 mt-3"></div>

    <button type="button" id="btn-add-servicio"
        class="mt-3 inline-flex items-center gap-1 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
        <span class="material-symbols-outlined text-[18px]">add</span>
        Agregar servicio
    </button>
</div>

<script>
(function () {
    const inicial = @json(array_values($servicios ?? []));
    const lista = document.getElementById('lista-servicios');
    let indice = 0;

    function agregarFila(concepto = '', precio = '') {
        const i = indice++;
        const fila = document.createElement('div');
        fila.className = 'flex items-center gap-2';
        fila.innerHTML = `
            <input type="text" name="servicios[${i}][concepto]" maxlength="120" placeholder="Concepto"
                   class="flex-1 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <input type="number" name="servicios[${i}][precio]" min="0" step="0.01" placeholder="Precio (opcional)"
                   class="w-32 text-sm rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
            <button type="button" class="text-red-400 hover:text-red-600" title="Quitar">
                <span class="material-symbols-outlined text-[20px]">delete</span>
            </button>`;
        // .value (no innerHTML) para que un texto con comillas o < > no rompa el formulario
        fila.querySelector('input[type=text]').value = concepto ?? '';
        fila.querySelector('input[type=number]').value = precio ?? '';
        fila.querySelector('button').addEventListener('click', () => fila.remove());
        lista.appendChild(fila);
    }

    inicial.forEach(s => agregarFila(s.concepto, s.precio));
    document.getElementById('btn-add-servicio').addEventListener('click', () => agregarFila());
})();
</script>