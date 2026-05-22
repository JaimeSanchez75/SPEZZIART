"use strict";

(() => {

    const ENDPOINT = '/pages/administracion/usuarios/cambiarRol';

    function pintarRolFila(fila, esAdmin) {
        if (!fila) return;

        const tdRol = fila.querySelector('[data-label="Rol"]');
        if (tdRol) {
            const contenido = tdRol.querySelector('.celda-contenido');
            if (contenido) {
                contenido.innerHTML = esAdmin
                    ? '<i class="bi bi-shield me-2 texto-rojo"></i><span>Administrador</span>'
                    : '<i class="bi bi-person me-2 text-secondary"></i><span>Usuario</span>';
            }
        }

        const icono    = fila.querySelector('.iconoRolUsuario');
        const etiqueta = fila.querySelector('.etiquetaRolUsuario');
        const btn      = fila.querySelector('.btnCambiarRol');

        if (icono) {
            icono.classList.remove('bi-shield', 'bi-person');
            icono.classList.add(esAdmin ? 'bi-person' : 'bi-shield');
        }
        if (etiqueta) {
            etiqueta.textContent = esAdmin ? 'Hacer usuario' : 'Hacer admin';
        }
        if (btn) {
            btn.dataset.esAdmin = String(esAdmin ? 1 : 0);
        }

        fila.dataset.nombre = String(esAdmin ? 1 : 0);
    }

    document.addEventListener('click', async function (e) {

        const boton = e.target.closest('.btnCambiarRol');
        if (!boton) return;

        e.preventDefault();

        const fila = boton.closest('.filaUsuario');
        if (!fila) return;

        const idUsuario = parseInt(boton.dataset.idUsuario || fila.dataset.idUsuario, 10);
        if (!idUsuario || idUsuario <= 0) {
            if (window.Alertas) window.Alertas.error('Usuario no identificado.');
            return;
        }

        const esAdminActual = parseInt(boton.dataset.esAdmin, 10) === 1;
        const nuevoRol      = esAdminActual ? 0 : 1;
        const nombre        = boton.dataset.nombre || '';
        const accion        = nuevoRol === 1 ? 'Hacer administrador' : 'Hacer usuario';

        const ok = await window.Confirmacion.preguntar({
            titulo:          accion,
            mensaje:         nuevoRol === 1
                ? `¿Seguro que deseas dar permisos de administrador a ${nombre}?`
                : `¿Seguro que deseas quitar los permisos de administrador a ${nombre}?`,
            subtexto:        nuevoRol === 1
                ? 'Tendrá acceso completo al panel de administración.'
                : 'Perderá el acceso al panel de administración.',
            textoConfirmar:  accion,
            icono:           nuevoRol === 1 ? 'bi-shield' : 'bi-person',
        });

        if (!ok) return;

        try {
            const respuesta = await fetch(ENDPOINT, {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ id: idUsuario, esAdmin: nuevoRol }),
            });

            const json = await respuesta.json().catch(() => ({}));

            if (!respuesta.ok || json.success === false) {
                throw new Error(json.message || 'No se pudo cambiar el rol del usuario.');
            }

            const esAdminConfirmado = typeof json.esAdmin === 'number' ? json.esAdmin === 1 : nuevoRol === 1;
            pintarRolFila(fila, esAdminConfirmado);

            if (window.Alertas) window.Alertas.exito(json.message || 'Rol actualizado correctamente.');

        } catch (err) {
            console.error('[cambiarRolUsuario]', err);
            if (window.Alertas) window.Alertas.error(err.message || 'Error al cambiar el rol del usuario.');
        }
    });

})();
