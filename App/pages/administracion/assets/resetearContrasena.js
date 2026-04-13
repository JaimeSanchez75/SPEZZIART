document.addEventListener('click', function (e) {
    const boton = e.target.closest('.botonResetearContraseña');

    if (!boton) return;

    e.preventDefault();
    
    const idUsuario = boton.getAttribute('data-id-usuario');

    if (confirm('¿Estás seguro de que quieres enviar un email de recuperación a este usuario?')) {
        const formData = new FormData();
        formData.append('id', idUsuario);


        fetch('/App/pages/administracion/usuarios/resetearContrasena', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
        })
        .catch(error => console.error('Error:', error));
    }
});
