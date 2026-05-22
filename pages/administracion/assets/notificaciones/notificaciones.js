"use strict";

(() => {

    let intervaloRefresco = null;
    const OBTENER_NOTIFICACIONES = '/pages/administracion/notificaciones/obtener';
    const LEER_NOTIFICACIONES = '/pages/administracion/notificaciones/leer';
    const ELIMINAR_NOTIFICACION = '/pages/administracion/notificaciones/eliminar';
    const ELIMINAR_NOTIFICACIONES = '/pages/administracion/notificaciones/limpiar';

    let cache = [];

    dayjs.extend(dayjs_plugin_relativeTime);
    dayjs.locale('es');

    function csrf() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function iconoTipo(tipo) {

        switch ((tipo || '').toLowerCase()) {

            case 'reporte':

                return '<i class="bi bi-flag-fill text-danger me-1"></i>';

            case 'login':

                return '<i class="bi bi-box-arrow-in-right text-primary me-1"></i>';

            default:

                return '<i class="bi bi-bell text-secondary me-1"></i>';
        }

    }

    function renderLista(notificaciones) {

        const contenedor = document.getElementById('notifAdminLista');

        if (!contenedor) return;

        if (!notificaciones || notificaciones.length === 0) {

            contenedor.innerHTML = '<div class="text-center text-muted py-3">No hay notificaciones</div>';
            return;

        }

        contenedor.innerHTML = '';

        notificaciones.forEach(n => {

            let DIV = document.createElement('div');
            DIV.className = 'd-flex justify-content-between align-items-start border-bottom py-2 px-1 notif-admin-item';
            DIV.dataset.id = n.ID_Notificacion;

            if (!Number(n.Leida)) DIV.classList.add('fw-semibold');

            let contenedorMensaje = document.createElement('div');
            contenedorMensaje.className = 'me-2 pe-2';
            
            let contenedorInfo = document.createElement('div');
            contenedorInfo.className = 'small text-muted text-truncate';
            contenedorInfo.innerHTML = iconoTipo(n.Tipo);

            let spanOrigen = document.createElement('span');
            spanOrigen.className = 'me-1';
            spanOrigen.textContent = n.ApodoOrigen || 'Sistema';

            let spanDestino = document.createElement('span');
            spanDestino.className = 'text-secondary';
            spanDestino.textContent = `→ ${n.ApodoDestino || ''}`;

            contenedorInfo.append(spanOrigen);
            contenedorInfo.append(spanDestino);

            let contenedorTexto = document.createElement('div');
            contenedorTexto.className = 'text-break';
            contenedorTexto.textContent = n.Mensaje || '';

            let contenedorFecha = document.createElement('div');
            contenedorFecha.className = 'small text-muted';
            contenedorFecha.textContent = dayjs(n.Fecha).fromNow();

            contenedorMensaje.append(contenedorInfo);
            contenedorMensaje.append(contenedorTexto);
            contenedorMensaje.append(contenedorFecha);

            let btnEliminar = document.createElement('button');
            btnEliminar.type = 'button';
            btnEliminar.className = 'btn btn-sm btn-link text-danger p-0 btn-eliminar-notif-admin';
            btnEliminar.dataset.id = n.ID_Notificacion;
            btnEliminar.innerHTML = 'x';
            
            DIV.append(contenedorMensaje);
            DIV.append(btnEliminar);

            contenedor.append(DIV);

        });

      
        
    }

    function actualizarContador(n) {

        const badge = document.getElementById('contadorNotificacionesAdmin');

        if (!badge) return;

        if (n > 0) {

            badge.textContent = n > 15 ? '15+' : String(n);
            badge.classList.remove('d-none');
            badge.classList.add('d-inline-block');

        } else {

            badge.textContent = '0';
            badge.classList.add('d-none');
            badge.classList.remove('d-inline-block');

        }
    }

    async function cargarNotificaciones() {

        try {

            const response = await fetch(OBTENER_NOTIFICACIONES, {

                credentials: 'same-origin',

                headers: { 'X-Requested-With': 'XMLHttpRequest' }

            });

            if (!response.ok) throw new Error(` ${response.status}`);

            const data = await response.json();

            cache = Array.isArray(data.notificaciones) ? data.notificaciones : [];

            actualizarContador(Number(data.noLeidas) || 0);

            renderLista(cache);

        } catch (err) {

            console.error('Error al cargar notificaciones admin:', err);

            const contenedor = document.getElementById('notifAdminLista');

            if (contenedor) {

                contenedor.innerHTML = '<div class="text-center text-danger py-3">Error al cargar</div>';

            }

        }
    }

    async function marcarLeidas() {

        try {

            await fetch(LEER_NOTIFICACIONES, {

                method: 'POST',
                credentials: 'same-origin',

                headers: {

                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrf()

                }
            });

            actualizarContador(0);
        } catch (err) {

            console.error('Error al marcar leídas:', err);

        }
    }

    async function eliminarNotificacion(id) {

        try {

            const res = await fetch(ELIMINAR_NOTIFICACION, {
                method: 'POST',

                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrf(),
                    'Content-Type': 'application/json'

                },
                body: JSON.stringify({ id })

            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);

            cache = cache.filter(n => String(n.ID_Notificacion) !== String(id));
            renderLista(cache);

        } catch (err) {
            
            console.error('Error al eliminar notificación:', err);

        }
    }

    async function limpiarTodas() {

        const ok = window.Confirmacion
            ? await window.Confirmacion.preguntar({
                titulo: 'Limpiar notificaciones',
                mensaje: '¿Seguro que deseas eliminar todas las notificaciones?',
                subtexto: 'Esta acción no se puede deshacer.',
                textoConfirmar: 'Eliminar todas',
                icono: 'bi-trash',
            })
            : window.confirm('¿Eliminar todas las notificaciones?');

        if (!ok) return;

        try {

            const res = await fetch(ELIMINAR_NOTIFICACIONES, {

                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrf()
                }

            });

            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            
            cache = [];

            actualizarContador(0);
            renderLista(cache);

        } catch (err) {

            console.error('Error al limpiar notificaciones:', err);

        }
    }

    function inicializar() {

        const btn = document.getElementById('btnNotificaciones');
        const menu = document.getElementById('dropdownNotificacionesAdmin');

        if (!btn || !menu) return;

        intervaloRefresco = setInterval(cargarNotificaciones, 1500); 

        btn.addEventListener('show.bs.dropdown', async () => {
            await cargarNotificaciones();
            marcarLeidas();
        });

        
        menu.addEventListener('click', async (e) => {

            const btnElim = e.target.closest('.btn-eliminar-notif-admin');

            if (btnElim) {

                e.preventDefault();
                e.stopPropagation();

                await eliminarNotificacion(btnElim.dataset.id);
                return;
            }

            const btnLimpiar = e.target.closest('#btnLimpiarNotifAdmin');

            if (btnLimpiar) {

                e.preventDefault();
                e.stopPropagation();

                await limpiarTodas();

            }
        });
    }

    document.addEventListener('DOMContentLoaded', inicializar);

})();
