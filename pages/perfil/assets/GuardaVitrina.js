function guardarVitrina() 
{
    const form = document.getElementById('formVitrina');
    const formData = new FormData(form);

    fetch('/pages/perfil/guardar-vitrina', 
    {
        method: 'POST',
        body: formData
    })
    .then(res => {
        if (!res.ok) throw new Error('Error en la respuesta del servidor');
        return res.json();
    })
    .then(data => {
        if (data.status === 'success') {
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalVitrina'));
            if (modal) modal.hide();
            location.reload();
        } else {
            window.Alertas.error('Error: ' + (data.message || 'No se pudo guardar la vitrina'));
        }
    })
    .catch(err => {
        console.error('Error en vitrina:', err);
        window.Alertas.error('Error de conexión al guardar la vitrina');
    });
}