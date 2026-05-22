"use strict";

document.addEventListener('DOMContentLoaded', function () {

    const modalEditarEtiqueta = document.getElementById('modalEditarEtiqueta');

    modalEditarEtiqueta.addEventListener('show.bs.modal', function (event) {

        const boton = event.relatedTarget;
        const id    = boton.dataset.id;

        document.getElementById('nombreEditar').value = boton.dataset.nombre;
        modalEditarEtiqueta.querySelector("#etiqueta_id").value = id;
    });
});
