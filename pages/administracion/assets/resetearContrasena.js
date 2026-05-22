"use strict";

document.addEventListener('click', async function (e) {

    const boton = e.target.closest('.botonResetearContraseña');
    if (!boton) return;

    e.preventDefault();

    const idUsuario = boton.getAttribute('data-id-usuario');
    if (!idUsuario) {

        if (window.Alertas) window.Alertas.error('Usuario no identificado.');
        return;

    }

    const confirmar = window.Confirmacion
        ? await window.Confirmacion.preguntar({
            titulo: 'Resetear contraseña',
            mensaje: '¿Seguro que deseas enviar un email de recuperación a este usuario?',
            subtexto: 'Recibirá un enlace para crear una nueva contraseña.',
            textoConfirmar: 'Enviar email',
            icono: 'bi-key-fill',
        })
        : window.confirm('¿Estás seguro de que quieres enviar un email de recuperación a este usuario?');

    if (!confirmar) return;

    const formData = new FormData();
    formData.append('id', idUsuario);

    fetch('/pages/administracion/usuarios/resetearContrasena', {
        method: 'POST',
        body: formData
    })
    .then(async res => {
        const json = await res.json().catch(() => ({}));
        return { ok: res.ok, json };
    })
    .then(({ ok, json }) => {

        if (ok && json.status === 'success') {
            window.Alertas.exito(json.message || 'Email enviado correctamente.');
        } else {
            window.Alertas.error(json.message || 'No se pudo enviar el correo.');
        }
    })
    .catch(error => {

        console.error('Error:', error);
        if (window.Alertas) window.Alertas.error('Error de conexión al enviar el correo.');

    });
});
