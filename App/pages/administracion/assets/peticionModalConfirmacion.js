const eliminarUsuario = document.getElementById('eliminarUsuario');

eliminarUsuario.addEventListener('show.bs.modal', function (event) {

    const boton = event.relatedTarget;

    const id = boton.getAttribute('data-id');
    const nombre = boton.getAttribute('data-nombre');

    document.getElementById('nombreUsuario').textContent = nombre;

    document.getElementById('EliminarUsuario').addEventListener('click', function() {
        window.location.href = `/App/pages/administracion/usuarios/confirmarEliminacion/${id}`;
    });

});