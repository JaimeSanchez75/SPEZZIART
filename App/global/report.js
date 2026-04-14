const reportReasons =
{
    receta: 
    [
        "Contenido inapropiado",
        "Spam o publicidad",
        "Violencia o abuso",
        "Información falsa",
        "Derechos de autor",
        "Otro"
    ],
    comentario: 
    [
        "Lenguaje ofensivo",
        "Spam",
        "Acoso",
        "Información personal",
        "Otro"
    ],
    usuario: 
    [
        "Perfil falso",
        "Spam",
        "Acoso",
        "Contenido inapropiado en perfil",
        "Suplantación de identidad",
        "Otro"
    ]
};
document.addEventListener('DOMContentLoaded', function() 
{
    const reportModal = document.getElementById('reportModal');
    if (!reportModal) return;
    const selectReason = document.getElementById('reportReason');
    const modalTitle = document.getElementById('reportModalTitle');
    const reportId = document.getElementById('reportId');
    const reportType = document.getElementById('reportType');
    const form = document.getElementById('reportForm');
    const errorDiv = document.getElementById('reportError');
    const successDiv = document.getElementById('reportSuccess');

    // Cuando se abre el modal, cargar los motivos según el tipo
    reportModal.addEventListener('show.bs.modal', function(event) 
    {
        const button = event.relatedTarget;
        const type = button.getAttribute('data-report-type');
        const id = button.getAttribute('data-id');
        reportType.value = type;
        reportId.value = id;
        // Cambiar título
        if (type === 'receta') modalTitle.innerText = 'Reportar publicación';
        else if (type === 'comentario') modalTitle.innerText = 'Reportar comentario';
        else if (type === 'usuario') modalTitle.innerText = 'Reportar usuario';
        // Cargar opciones del select
        selectReason.innerHTML = '<option value="">Selecciona un motivo...</option>';
        const reasons = reportReasons[type] || reportReasons.receta; // fallback
        reasons.forEach(reason => 
        {
            const option = document.createElement('option');
            option.value = reason;
            option.textContent = reason;
            selectReason.appendChild(option);
        });
        // Limpiar mensajes y formulario
        errorDiv.classList.add('d-none');
        successDiv.classList.add('d-none');
        form.reset();
    });

    // Envío del reporte
    form.addEventListener('submit', async function(e) 
    {
    e.preventDefault();

    const type = reportType.value;
    const id = reportId.value;
    const reason = selectReason.value;
    const details = form.querySelector('[name="details"]').value;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    if (!reason) 
    {
        errorDiv.textContent = 'Selecciona un motivo';
        errorDiv.classList.remove('d-none');
        return;
    }

    errorDiv.classList.add('d-none');
    successDiv.classList.add('d-none');
    
    // Construir el payload
    let url = '';
    let body = {};

    if (type === 'receta') 
    {
        url = '/App/api/reportar/receta';
        body = { id_receta: id, motivo: reason, detalles: details, csrf_token: csrfToken };
    } 
    else if (type === 'comentario') 
    {
        url = '/App/api/reportar/comentario';
        body = { id_comentario: id, motivo: reason, detalles: details, csrf_token: csrfToken };
    } 
    else if (type === 'usuario') 
    {
        url = '/App/api/reportar/usuario';
        body = { id_usuario: id, motivo: reason, detalles: details, csrf_token: csrfToken };
    } 
    else 
    {
        errorDiv.textContent = 'Tipo de reporte inválido';
        errorDiv.classList.remove('d-none');
        return;
    }
    const formData = new URLSearchParams(body);
    try 
    {
        const res = await fetch(url, 
        {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded'},
            body: formData
        });
        const data = await res.json();
        if (data.status === 'success') 
        {
            successDiv.textContent = data.message;
            successDiv.classList.remove('d-none');
            setTimeout(() => {bootstrap.Modal.getInstance(reportModal).hide();}, 1500);
        } 
        else 
        {
            errorDiv.textContent = data.message;
            errorDiv.classList.remove('d-none');
        }
    } 
    catch (err) 
    {
        console.error('Error en fetch:', err);
        errorDiv.textContent = 'Error al enviar el reporte';
        errorDiv.classList.remove('d-none');
    }
});
});