const modalVerUsuario = document.getElementById('verDatos');

modalVerUsuario.addEventListener('show.bs.modal', function (event) {
    const boton = event.relatedTarget;
    const id = boton.getAttribute('data-id');
    const nombre = boton.getAttribute('data-nombre');
    const username = boton.getAttribute('data-username');
    const email = boton.getAttribute('data-email');
    const rol = boton.getAttribute('data-rol');

    document.getElementById('avatarUsuario').textContent = nombre.slice(0, 2).toUpperCase();
    document.getElementById('nombreUsuarioModal').textContent = nombre;
    document.getElementById('usernameUsuarioModal').textContent = `@${username}`;
    document.getElementById('emailUsuarioModal').textContent = email;
    document.getElementById('rolUsuarioModal').textContent = rol;
}
);



