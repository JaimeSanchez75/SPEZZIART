"use strict";

document.getElementById('formEditarperfil').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    try {
        // enviamos
        const response = await fetch('/pages/administracion/usuarios/editar', {
            method: 'POST',
            body: formData
        });

        let result = {};

        try {

            result = await response.json();

        } catch (error) {
            // error
            console.error('Error:',error);

            window.Alertas.error('Error al enviar los datos.');

        }

        // si todo fue bien, cerramos el modal de editar perfil y abrimos el de configuracion
        if (response.ok && (result.success || result.status === 'success')) {

            const modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarperfil'));
            if (modal) modal.hide();

            const modalConfig=bootstrap.Modal.getInstance(document.getElementById('modalConfigAdmin'));
            if (modalConfig) modalConfig.show();

            if (window.Alertas) window.Alertas.exito(result.message || 'Perfil actualizado correctamente.');


        } else {

            const msg = result.message || 'Error al guardar los cambios.';
            window.Alertas.error(msg);

        }
    } catch (error) {

        console.error('Error:', error);
        window.Alertas.error('Error de conexión al guardar los cambios.');

    }
});
