async function gestionarSeguimiento(idDestino) {
    if (!window.isLoggedIn) {
        window.location.href = '/App/pages/login';
        return;
    }

    const metaTag = document.querySelector('meta[name="csrf-token"]');
    if (!metaTag) {
        console.error('Meta etiqueta CSRF no encontrada');
        alert('Error de seguridad. Recarga la página e intenta de nuevo.');
        return;
    }
    const csrfToken = metaTag.getAttribute('content');
    const btn = event.currentTarget;

    try {
        const response = await fetch(`/App/pages/perfil/seguir/${idDestino}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ csrf_token: csrfToken })
        });

        const text = await response.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error('El servidor no devolvió JSON válido:', text);
            alert('Error del servidor. Consulta la consola para más detalles.');
            return;
        }

        if (data.status === 'success') {
            if (data.accion === 'followed') {
                btn.innerText = 'Siguiendo';
                btn.classList.remove('btn-light');
                btn.classList.add('btn-outline-light');
                const contadorSpan = document.querySelector('.seguidores-count');
                if (contadorSpan) {
                    let current = parseInt(contadorSpan.innerText) || 0;
                    contadorSpan.innerText = current + 1;
                }
            } else if (data.accion === 'unfollowed') {
                btn.innerText = 'Seguir';
                btn.classList.remove('btn-outline-light');
                btn.classList.add('btn-light');
                const contadorSpan = document.querySelector('.seguidores-count');
                if (contadorSpan) {
                    let current = parseInt(contadorSpan.innerText) || 0;
                    contadorSpan.innerText = Math.max(0, current - 1);
                }
            }
        } else {
            alert(data.message || 'Error al procesar la solicitud');
        }
    } catch (err) {
        console.error('Error en la petición:', err);
        alert('Ocurrió un error. Inténtalo de nuevo más tarde.');
    }
}