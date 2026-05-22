"use strict";

document.addEventListener('DOMContentLoaded', function () {

    const modalEliminarEtiqueta = document.getElementById('modalEliminarEtiqueta');

    modalEliminarEtiqueta.addEventListener('show.bs.modal', function (event) {

        const boton = event.relatedTarget;
        const id    = boton.dataset.id;

        document.getElementById('nombreEtiqueta').textContent = boton.dataset.nombre;

        const btnOriginal = document.getElementById('eliminarEtiqueta');
        const btn         = btnOriginal.cloneNode(true);
        btnOriginal.parentNode.replaceChild(btn, btnOriginal);

        btn.addEventListener('click', function () {
            const csrf  = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
            const form  = document.createElement('form');
            form.method = 'POST';
            form.action = '/pages/administracion/etiquetas/eliminar';

            const csrfInput = document.createElement('input');
            csrfInput.type  = 'hidden';
            csrfInput.name  = 'csrf_token';
            csrfInput.value = csrf;
            form.appendChild(csrfInput);

            const idInput = document.createElement('input');
            idInput.type  = 'hidden';
            idInput.name  = 'id';
            idInput.value = id;
            form.appendChild(idInput);

            document.body.appendChild(form);
            form.submit();
        });
    });
});
