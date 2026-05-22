async function gestionarSeguimiento(idDestino) {
    if (!window.isLoggedIn) {
        window.location.href = '/pages/login';
        return;
    }
    window.Alertas = window.Alertas || {
        error: function(mensaje) {
            console.error(mensaje);
            mostrarAlertaSeguimiento(mensaje, 'danger');
        },
        success: function(mensaje) {
            mostrarAlertaSeguimiento(mensaje, 'success');
        }
    };

    function mostrarAlertaSeguimiento(mensaje, tipo) {
        let contenedor = document.getElementById('seguimiento-alertas');

        if (!contenedor) {
            contenedor = document.createElement('div');
            contenedor.id = 'seguimiento-alertas';
            contenedor.className = 'position-fixed top-0 start-50 translate-middle-x p-3';
            contenedor.style.zIndex = '9999';
            document.body.appendChild(contenedor);
        }

        const alerta = document.createElement('div');
        alerta.className = tipo === 'success'
            ? 'bg-white border border-success text-success sombra rounded-4 p-3 mb-2 texto'
            : 'bg-white border border-rojo texto-rojo sombra rounded-4 p-3 mb-2 texto';

        alerta.textContent = mensaje;
        contenedor.appendChild(alerta);

        setTimeout(() => {
            alerta.remove();
            if (!contenedor.hasChildNodes()) contenedor.remove();
        }, 3500);
    }
    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (!metaTag) {
        console.error('Meta etiqueta CSRF no encontrada');
        window.Alertas.error('Error de seguridad. Recarga la página e intenta de nuevo.');
        return;
    }
    const csrfToken = metaTag.getAttribute('content');
    const btn = event.currentTarget;

    try {
        const response = await fetch(`/pages/perfil/seguir/${idDestino}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ csrf_token: csrfToken })
        });
        const text = await response.text();
        let data;

        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('Respuesta no JSON del servidor:', text);
            window.Alertas.error('Error del servidor. Revisa los logs.');
            return;
        }
        if (data.status === 'success') 
        {
            const accion = data.accion;
            const contadorSpan = document.querySelector('.seguidores-count');
            
            if (accion === 'followed') 
            {
                // Comenzó a seguir
                btn.innerText = 'Siguiendo';
                btn.classList.remove('border-0', 'bg-white', 'text-secondary');
                btn.classList.add('border', 'text-white', 'bg-transparent');
                btn.disabled = false;
                if (contadorSpan) 
                {
                    let current = parseInt(contadorSpan.innerText) || 0;
                    contadorSpan.innerText = current + 1;
                }
            } 
            else if (accion === 'unfollowed') 
            {
                // Dejó de seguir
                btn.innerText = 'Seguir';
                btn.classList.remove('border', 'text-white', 'bg-transparent');
                btn.classList.add('border-0', 'bg-white', 'text-secondary');
                btn.disabled = false;
                if (contadorSpan) 
                {
                    let current = parseInt(contadorSpan.innerText) || 0;
                    contadorSpan.innerText = Math.max(0, current - 1);
                }
            } 
            else if (accion === 'solicitado') 
            {
                btn.innerText = 'Cancelar solicitud';
                btn.classList.remove('border-0', 'bg-white', 'text-secondary', 'text-white', 'bg-transparent');
                btn.classList.add('border', 'text-secondary', 'bg-white');
                btn.disabled = false;
            } 
            else if (accion === 'solicitud_cancelada') 
            {
                btn.innerText = 'Seguir';
                btn.classList.remove('border', 'text-white', 'bg-transparent', 'text-secondary');
                btn.classList.add('border-0', 'bg-white', 'text-secondary');
                btn.disabled = false;
            }
        } else {
            // Error manejado desde el servidor
            console.error('Error al gestionar seguimiento:', data.message);
            window.Alertas.error(data.message || 'Error al procesar la solicitud');
        }
    } catch (err) {
        console.error('Error en la petición:', err);
        window.Alertas.error('Ocurrió un error. Inténtalo de nuevo más tarde.');
    }
}
