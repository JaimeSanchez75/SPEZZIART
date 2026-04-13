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
    if(rol === 'Usuario') {
        document.getElementById('iconoRol').classList.remove('bi-shield');
        document.getElementById('iconoRol').classList.add('bi-people');
    }
}
);

document.getElementById('btnCopiar').addEventListener('click', copiarAlPortapapeles);

function copiarAlPortapapeles(event) {
    const texto = document.getElementById('emailUsuarioModal').textContent;

    navigator.clipboard.writeText(texto)
    .then(() => {
        const btn = document.getElementById("btnCopiar");
        btn.classList.add("activo","texto-verde","bg-verdeClaro");
        btn.innerHTML = '<i class="bi bi-check"></i>';

        setTimeout(() => {
            btn.classList.remove("activo","texto-verde","bg-verdeClaro");
            btn.innerHTML = '<i class="bi bi-clipboard"></i>';
        }, 2000);
    })
    .catch(err => {
        console.error('Error al copiar al portapapeles: ', err);
    });
}




