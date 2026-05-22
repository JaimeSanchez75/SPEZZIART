"use strict";

document.addEventListener('DOMContentLoaded', function () {

    const modal     = document.getElementById('eliminarRecetaModal');
    const btnEliminar = document.getElementById('eliminarReceta');
    const titulo    = document.getElementById('tituloReceta');

    let idReceta = null;

    modal.addEventListener('show.bs.modal', function (event) {
        const boton  = event.relatedTarget;
        idReceta     = boton.dataset.id;
        titulo.textContent = boton.dataset.nombre || '';
    });

    btnEliminar.addEventListener('click', function () {
        if (!idReceta) return;

        const csrf  = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const form  = document.createElement('form');
        form.method = 'POST';
        form.action = '/pages/administracion/receta/eliminar';

        const csrfInput  = document.createElement('input');
        csrfInput.type   = 'hidden';
        csrfInput.name   = 'csrf_token';
        csrfInput.value  = csrf;
        form.appendChild(csrfInput);

        const idInput  = document.createElement('input');
        idInput.type   = 'hidden';
        idInput.name   = 'id';
        idInput.value  = idReceta;
        form.appendChild(idInput);

        document.body.appendChild(form);
        form.submit();
    });

});
