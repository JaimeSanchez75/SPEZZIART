"use strict";

(() => {

    const ENDPOINT = '/pages/administracion/usuarios/cambiarEstado';

    
    function pintarEstadoFila(fila, activa) {

        if (!fila) return;

        fila.dataset.activa = String(activa);

        const badge       = fila.querySelector('.badgeEstadoUsuario');
        const iconoBadge  = fila.querySelector('.iconoBadgeEstado');
        const textoBadge  = fila.querySelector('.textoEstadoUsuario');

        if (badge) {
            badge.classList.remove('bg-verdeClaro', 'texto-verde', 'bg-grisClaro', 'texto-gris');
            if (activa === 1) {
                badge.classList.add('bg-verdeClaro', 'texto-verde');
            } else {
                badge.classList.add('bg-grisClaro', 'texto-gris');
            }
        }
        if (iconoBadge) {
            iconoBadge.classList.remove('bi-check-circle-fill', 'bi-slash-circle-fill');
            iconoBadge.classList.add(activa === 1 ? 'bi-check-circle-fill' : 'bi-slash-circle-fill');
        }
        if (textoBadge) {
            textoBadge.textContent = activa === 1 ? 'Activo' : 'Deshabilitado';
        }

        const btn        = fila.querySelector('.btnAlternarEstadoUsuario');
        const iconoMenu  = fila.querySelector('.iconoEstadoUsuario');
        const etiqueta   = fila.querySelector('.etiquetaEstadoUsuario');

        if (iconoMenu) {
            iconoMenu.classList.remove('bi-slash-circle', 'bi-check-circle');
            iconoMenu.classList.add(activa === 1 ? 'bi-slash-circle' : 'bi-check-circle');
        }
        if (etiqueta) {
            etiqueta.textContent = activa === 1 ? 'Deshabilitar usuario' : 'Activar usuario';
        }
        if (btn) {
            btn.dataset.estado = String(activa);
        }
    }

    document.addEventListener('click', async function (e) {

        const boton = e.target.closest('.btnAlternarEstadoUsuario');
        if (!boton) return;

        e.preventDefault();

        const fila = boton.closest('.filaUsuario');
        if (!fila) return;

        const idUsuario = parseInt(boton.dataset.idUsuario || fila.dataset.idUsuario, 10);
        if (!idUsuario || idUsuario <= 0) {
            if (window.Alertas) window.Alertas.error('Usuario no identificado.');
            return;
        }

        const estadoActual = parseInt(fila.dataset.activa || '1', 10);
        const nuevoEstado  = estadoActual === 1 ? 0 : 1;

        const nombre = boton.dataset.nombre || '';
        const accion = nuevoEstado === 0 ? 'Deshabilitar' : 'Activar';

        const ok = await window.Confirmacion.preguntar({
                titulo: `${accion} usuario`,
                mensaje: nuevoEstado === 0
                    ? `¿Seguro que deseas deshabilitar a ${nombre}?`
                    : `¿Seguro que deseas activar a ${nombre}?`,
                subtexto: nuevoEstado === 0
                    ? 'No podrá iniciar sesión hasta que vuelvas a activar la cuenta.'
                    : 'Podrá volver a iniciar sesión inmediatamente.',
                textoConfirmar: accion,
                icono: nuevoEstado === 0 ? 'bi-slash-circle' : 'bi-check-circle',
            });

        if (!ok) return;

        try {
            const respuesta = await fetch(ENDPOINT, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: idUsuario, activa: nuevoEstado }),
            });

            const json = await respuesta.json().catch(() => ({}));

            if (!respuesta.ok || json.success === false) {
                throw new Error(json.message || 'No se pudo cambiar el estado del usuario.');
            }

            const activaConfirmada = typeof json.activa === 'number' ? json.activa : nuevoEstado;
            pintarEstadoFila(fila, activaConfirmada);

            if (window.Alertas) {
                window.Alertas.exito(json.message || 'Estado del usuario actualizado.');
            }
        } catch (err) {
            console.error('[cambiarEstadoUsuario]', err);
            if (window.Alertas) {
                window.Alertas.error(err.message || 'Error al cambiar el estado del usuario.');
            }
        }
    });

})();
