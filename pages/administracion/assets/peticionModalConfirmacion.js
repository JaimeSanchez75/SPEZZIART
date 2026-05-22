"use strict";

(function () {
    const eliminarUsuario = document.getElementById('eliminarUsuario');
    if (!eliminarUsuario || eliminarUsuario.dataset.peticionInit) return;
    eliminarUsuario.dataset.peticionInit = '1';

    eliminarUsuario.addEventListener('show.bs.modal', function (event) {

        const boton  = event.relatedTarget;
        const id     = boton.getAttribute('data-id');
        const nombre = boton.getAttribute('data-nombre');

        document.getElementById('nombreUsuario').textContent = nombre;

        const btnEliminar = document.getElementById('EliminarUsuario');
        const nuevoBtn    = btnEliminar.cloneNode(true);
        btnEliminar.parentNode.replaceChild(nuevoBtn, btnEliminar);

        nuevoBtn.addEventListener('click', function () {
            const csrf  = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const form  = document.createElement('form');
            form.method = 'POST';
            form.action = '/pages/administracion/usuarios/confirmarEliminacion';

            const csrfInput  = document.createElement('input');
            csrfInput.type   = 'hidden';
            csrfInput.name   = 'csrf_token';
            csrfInput.value  = csrf;
            form.appendChild(csrfInput);

            const idInput  = document.createElement('input');
            idInput.type   = 'hidden';
            idInput.name   = 'id';
            idInput.value  = id;
            form.appendChild(idInput);

            document.body.appendChild(form);
            form.submit();
        });

    });
})();
