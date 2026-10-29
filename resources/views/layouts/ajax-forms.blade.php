{{--
  Envía los formularios de crear/editar por AJAX y muestra los errores de validación
  junto a cada campo, SIN recargar la página (así no se pierden las filas de mobiliario,
  comida, servicios, etc.).

  - Aplica solo a formularios POST. Se excluyen: los de eliminar (_method=DELETE), los de
    autenticación/perfil y los que tengan el atributo data-no-ajax.
  - La validación nativa del navegador (required, min, max...) sigue funcionando antes del envío.
  - Requiere el middleware App\Http\Middleware\AjaxFormResponses.
--}}
<script>
(function () {
    const EXCLUIR = ['/login', '/logout', '/register', '/forgot-password', '/reset-password',
                     '/password', '/profile', '/email', '/confirm-password', '/verify-email'];

    const ESTILO_ERROR = 'color:#dc2626;font-size:.875rem;margin-top:.25rem;';

    function aplica(form) {
        if (form.hasAttribute('data-no-ajax')) return false;
        if ((form.method || '').toLowerCase() !== 'post') return false;

        const metodo = form.querySelector('input[name="_method"]');
        if (metodo && metodo.value.toUpperCase() === 'DELETE') return false;

        let ruta = '';
        try { ruta = new URL(form.action, location.origin).pathname; } catch (e) { return false; }
        return !EXCLUIR.some(p => ruta === p || ruta.startsWith(p + '/'));
    }

    function limpiar(form) {
        form.querySelectorAll('.ajax-error').forEach(el => el.remove());
        form.querySelectorAll('.ajax-invalido').forEach(el => {
            el.classList.remove('ajax-invalido');
            el.style.borderColor = '';
        });
    }

    // 'mobiliario.0.cantidad' -> 'mobiliario[0][cantidad]'
    function nombreCampo(clave) {
        const [base, ...resto] = clave.split('.');
        return base + resto.map(p => '[' + p + ']').join('');
    }

    function buscarCampo(form, clave) {
        const campos = Array.from(form.elements).filter(el => el.name);
        const exacto = nombreCampo(clave);
        const base   = clave.split('.')[0];

        return campos.find(el => el.name === exacto)
            || campos.find(el => el.name === exacto + '[]')
            || campos.find(el => el.name === base)
            || campos.find(el => el.name === base + '[]')
            || campos.find(el => el.name.startsWith(base + '['))
            || null;
    }

    function crearMensaje(texto) {
        const p = document.createElement('p');
        p.className = 'ajax-error';
        p.setAttribute('style', ESTILO_ERROR);
        p.textContent = texto;
        return p;
    }

    function mensajeGeneral(form, texto) {
        const caja = document.createElement('div');
        caja.className = 'ajax-error';
        caja.setAttribute('style', 'background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:.5rem;padding:.6rem .9rem;font-size:.875rem;margin-bottom:1rem;');
        caja.textContent = texto;
        form.insertBefore(caja, form.firstChild);
        caja.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function mostrarErrores(form, errores) {
        let primero = null;
        const sinCampo = [];

        Object.entries(errores).forEach(([clave, mensajes]) => {
            const campo = buscarCampo(form, clave);
            const texto = Array.isArray(mensajes) ? mensajes[0] : String(mensajes);

            if (!campo) { sinCampo.push(texto); return; }

            if (campo.type !== 'hidden') {
                campo.classList.add('ajax-invalido');
                campo.style.borderColor = '#ef4444';
            }

            // Si el campo está dentro de una fila flex (mobiliario, servicios), el mensaje va debajo de la fila
            const padre = campo.parentElement;
            const enFila = padre && getComputedStyle(padre).display === 'flex';
            const ancla = campo.type === 'hidden' || enFila ? padre : campo;
            ancla.insertAdjacentElement('afterend', crearMensaje(texto));

            if (!primero && campo.type !== 'hidden') primero = campo;
        });

        sinCampo.forEach(t => mensajeGeneral(form, t));

        if (primero) {
            primero.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(() => primero.focus({ preventScroll: true }), 300);
        }
    }

    // Al corregir un campo se quita su mensaje
    document.addEventListener('input', (e) => {
        const el = e.target;
        if (!el.classList || !el.classList.contains('ajax-invalido')) return;
        el.classList.remove('ajax-invalido');
        el.style.borderColor = '';
        const sig = el.nextElementSibling;
        if (sig && sig.classList.contains('ajax-error')) sig.remove();
    });

    document.addEventListener('submit', async (e) => {
        const form = e.target;
        if (!(form instanceof HTMLFormElement) || e.defaultPrevented || !aplica(form)) return;

        e.preventDefault();
        if (form.dataset.enviando === '1') return;
        form.dataset.enviando = '1';
        limpiar(form);

        const boton = e.submitter || form.querySelector('[type="submit"]');
        const textoOriginal = boton ? boton.innerHTML : '';
        if (boton) { boton.disabled = true; boton.textContent = 'Guardando…'; }

        try {
            const resp = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });

            const esJson = (resp.headers.get('content-type') || '').includes('application/json');
            const datos  = esJson ? await resp.json() : null;

            if (resp.status === 422 && datos && datos.errors) {
                mostrarErrores(form, datos.errors);
            } else if (resp.ok && datos && datos.redirect) {
                window.location.href = datos.redirect;
                return;
            } else if (resp.status === 419) {
                alert('Tu sesión expiró. Se recargará la página.');
                location.reload();
                return;
            } else if (resp.status === 401) {
                window.location.href = '/login';
                return;
            } else if (resp.ok) {
                location.reload();
                return;
            } else {
                mensajeGeneral(form, (datos && datos.message) || 'Ocurrió un error al guardar. Intenta de nuevo.');
            }
        } catch (err) {
            mensajeGeneral(form, 'No se pudo conectar con el servidor. Revisa tu conexión e intenta de nuevo.');
        } finally {
            form.dataset.enviando = '0';
            if (boton && boton.isConnected) { boton.disabled = false; boton.innerHTML = textoOriginal; }
        }
    });
})();
</script>