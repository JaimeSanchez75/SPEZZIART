"use strict";

(function () {

    if (window.Confirmacion) return;

    function obtenerElementos() {
        return {
            modal: document.getElementById('modalConfirmacionGenerico'),
            titulo: document.getElementById('confirmGenericoTitulo'),
            mensaje: document.getElementById('confirmGenericoMensaje'),
            subtexto: document.getElementById('confirmGenericoSubtexto'),
            icono: document.getElementById('confirmGenericoIcono'),
            btnAceptar: document.getElementById('confirmGenericoAceptar'),
            btnCancelar: document.getElementById('confirmGenericoCancelar'),
        };
    }

    function preguntar(opciones = {}) {

        const {
            titulo = '¿Confirmar acción?',
            mensaje = '¿Seguro que deseas continuar?',
            subtexto = '',
            textoConfirmar = 'Confirmar',
            textoCancelar = 'Cancelar',
            icono = 'bi-question-circle',
        } = opciones;

        const elementos = obtenerElementos();

        if (!elementos.modal) {
            return Promise.resolve(window.confirm(mensaje));
        }

        elementos.titulo.textContent = titulo;
        elementos.mensaje.textContent = mensaje;
        elementos.btnAceptar.textContent = textoConfirmar;
        elementos.btnCancelar.textContent = textoCancelar;

        if (subtexto && subtexto.trim() !== '') {
            elementos.subtexto.textContent = subtexto;
            elementos.subtexto.classList.remove('d-none');
        } else {
            elementos.subtexto.textContent = '';
            elementos.subtexto.classList.add('d-none');
        }

        elementos.icono.className = `${icono} texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande`;

        const commentsOverlay = document.getElementById('comments-overlay');
        const contenedor = (commentsOverlay && commentsOverlay.classList.contains('active'))
            ? commentsOverlay
            : document.body;
        contenedor.appendChild(elementos.modal);

        elementos.modal.style.zIndex = '9999';
        elementos.modal.style.willChange = 'transform';

        const instancia = bootstrap.Modal.getOrCreateInstance(elementos.modal);
        const btnCerrarX = elementos.modal.querySelector('.btn-close');

        return new Promise((resolve) => {

            let resuelto = false;

            function limpiar() {
                elementos.btnAceptar.removeEventListener('click', alAceptar);
                elementos.btnCancelar.removeEventListener('click', alCancelar);
                if (btnCerrarX) btnCerrarX.removeEventListener('click', alCancelar);
                elementos.modal.removeEventListener('hidden.bs.modal', alCerrar);
            }

            function alAceptar() {
                if (resuelto) return;
                resuelto = true;
                limpiar();
                resolve(true);
                instancia.hide();
            }

            function alCancelar() {
                if (resuelto) return;
                resuelto = true;
                limpiar();
                resolve(false);
                // Bootstrap maneja el hide via data-bs-dismiss, no hace falta llamar instancia.hide()
            }

            function alCerrar() {
                if (resuelto) return;
                resuelto = true;
                limpiar();
                resolve(false);
            }

            elementos.btnAceptar.addEventListener('click', alAceptar);
            elementos.btnCancelar.addEventListener('click', alCancelar);
            if (btnCerrarX) btnCerrarX.addEventListener('click', alCancelar);
            elementos.modal.addEventListener('hidden.bs.modal', alCerrar);

            instancia.show();

        });
    }

    window.Confirmacion = { preguntar };

})();
