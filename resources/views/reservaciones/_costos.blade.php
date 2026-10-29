{{--
  Descuento + resumen de costos en vivo para el formulario de reservaciones.
  El total NO se captura: lo calcula el sistema (CotizadorReservacion) con los precios de venta.
  Lee los campos del formulario por id: locacion_id, paquete_id, contador_invitados.
--}}
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    <div>
        <x-input-label for="descuento" value="Descuento ($) (opcional)" />
        <x-text-input id="descuento" name="descuento" type="number"
            class="mt-1 block w-full"
            value="{{ old('descuento', ($reservacion->descuento ?? 0) > 0 ? $reservacion->descuento : '') }}"
            placeholder="Ej. 1500.00" min="0" step="0.01" />
        <x-input-error :messages="$errors->get('descuento')" class="mt-1" />
    </div>
    <div>
        <x-input-label for="motivo_descuento" value="Motivo del descuento" />
        <x-text-input id="motivo_descuento" name="motivo_descuento" type="text" maxlength="150"
            class="mt-1 block w-full"
            value="{{ old('motivo_descuento', $reservacion->motivo_descuento ?? '') }}"
            placeholder="Ej. Cliente frecuente" />
        <x-input-error :messages="$errors->get('motivo_descuento')" class="mt-1" />
    </div>
</div>

<div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Resumen de costos (calculado automáticamente)</p>
    <div id="resumen-costos" class="text-sm text-gray-700">
        <p class="text-xs text-gray-400 italic">Selecciona una locación para ver el cálculo.</p>
    </div>
</div>

<script>
(function () {
    const url   = @json(route('cotizador.calcular'));
    const $loc  = document.getElementById('locacion_id');
    const $paq  = document.getElementById('paquete_id');
    const $inv  = document.getElementById('contador_invitados');
    const $desc = document.getElementById('descuento');
    const $box  = document.getElementById('resumen-costos');

    const dinero = (n) => new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(n);
    const esc = (t) => String(t ?? '').replace(/[&<>"']/g, c => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]
    ));

    let temporizador;
    let ultimaPeticion = 0;

    async function actualizar() {
        if (!$loc.value) {
            $box.innerHTML = '<p class="text-xs text-gray-400 italic">Selecciona una locación para ver el cálculo.</p>';
            return;
        }

        const params = new URLSearchParams({
            locacion_id: $loc.value,
            paquete_id: $paq.value || '',
            contador_invitados: $inv.value || 0,
            descuento: $desc.value || 0,
        });

        const id = ++ultimaPeticion; // evita que una respuesta vieja pise a una más reciente
        try {
            const r = await fetch(url + '?' + params.toString(), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!r.ok) throw new Error('HTTP ' + r.status);
            const d = await r.json();
            if (id === ultimaPeticion) pintar(d);
        } catch (e) {
            if (id === ultimaPeticion) {
                $box.innerHTML = '<p class="text-xs text-red-500">No se pudo calcular el resumen. El total se calculará al guardar.</p>';
            }
        }
    }

    function pintar(d) {
        const aviso = d.problema
            ? `<p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded px-2 py-1 mb-2">${esc(d.problema)}</p>`
            : '';
        const nota = d.aviso
            ? `<p class="text-xs text-blue-700 bg-blue-50 border border-blue-200 rounded px-2 py-1 mb-2">${esc(d.aviso)}</p>`
            : '';
        const filas = d.lineas.map(l => `
            <div class="flex justify-between py-1">
                <span>${esc(l.concepto)} <span class="text-xs text-gray-400">${esc(l.detalle)}</span></span>
                <span>${dinero(l.importe)}</span>
            </div>`).join('');

        const descuento = d.descuento > 0 ? `
            <div class="flex justify-between py-1 text-green-700">
                <span>Descuento</span><span>-${dinero(d.descuento)}</span>
            </div>` : '';

        $box.innerHTML = `
            ${aviso}
            ${nota}
            ${filas}
            <div class="flex justify-between py-1 border-t border-gray-200 mt-1">
                <span>Subtotal</span><span>${dinero(d.subtotal)}</span>
            </div>
            ${descuento}
            <div class="flex justify-between pt-2 mt-1 border-t-2 border-gray-300 text-base font-semibold text-gray-900">
                <span>Total</span><span>${dinero(d.total)}</span>
            </div>`;
    }

    // Pequeña espera: así corre DESPUÉS de los otros scripts del formulario
    // (por ejemplo el que fija la locación al elegir un paquete).
    function programar() {
        clearTimeout(temporizador);
        temporizador = setTimeout(actualizar, 300);
    }

    [$loc, $paq, $inv, $desc].forEach(el => {
        el.addEventListener('change', programar);
        el.addEventListener('input', programar);
    });

    document.addEventListener('DOMContentLoaded', actualizar);
})();
</script>