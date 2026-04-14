

function guardarVitrina() 
{
    const form = document.getElementById('formVitrina');
    const formData = new FormData(form);

    fetch('/App/pages/perfil/guardar-vitrina', 
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
            
            location.reload();
        } 
    })
    .catch(err => {
        console.error('Error en vitrina:', err);
        alert('Error de conexión al guardar la vitrina');
    });
}