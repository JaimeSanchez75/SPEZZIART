
// Función auxiliar para escapar HTML (evita XSS)
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// ========== MODAL DE DETALLE DEL LOGRO ==========
document.addEventListener('DOMContentLoaded', function() {
    const logroCards = document.querySelectorAll('.logro-card');
    
    logroCards.forEach(card => {
        card.addEventListener('click', async function(e) {
            // Evita que el clic en el checkbox (si estuviera dentro) también dispare el modal
            if (e.target.closest('.btn-check')) return;
            
            const logroId = this.dataset.id;
            if (!logroId) return;
            
            const modalElement = document.getElementById('logroDetailModal');
            if (!modalElement) {
                console.error('No se encontró el modal #logroDetailModal');
                return;
            }
            
            const modal = new bootstrap.Modal(modalElement);
            const contentDiv = document.getElementById('logroDetailContent');
            if (!contentDiv) return;
            
            contentDiv.innerHTML = '<div class="spinner-border text-danger" role="status"></div>';
            modal.show();
            
            try {
                const resp = await fetch(`/pages/perfil/logro-detalle?id=${logroId}&user_id=${window.profileUserId}`);
                const data = await resp.json();
                if (data.error) throw new Error(data.error);
                
                const imageStateClass = data.desbloqueado ? 'rounded-circle bg-rojoClaro p-2' : 'rounded-circle bg-light p-2 opacity-75';
                const iconStateClass = data.desbloqueado ? 'texto-rojo' : 'text-secondary';
                let html = `
                    <div class="text-center">
                        ${data.ImagenURL ? 
                            `<img src="${data.ImagenURL}" class="img-fluid mb-3 ${imageStateClass}" style="max-height: 150px; object-fit: contain;">` : 
                            `<span class="material-symbols-outlined fs-1 mb-2 ${iconStateClass}">${escapeHtml(data.Icono)}</span>`
                        }
                        <h4 class="fw-bold">${escapeHtml(data.Nombre)}</h4>
                        <p class="text-muted small">${escapeHtml(data.Descripcion || 'Sin descripción')}</p>
                `;
                
                if (data.desbloqueado) {
                    const fecha = new Date(data.fecha).toLocaleDateString('es-ES', { year: 'numeric', month: 'long', day: 'numeric' });
                    html += `<div class="alert border-light d-flex align-items-center mt-3">
                                <span class="material-symbols-outlined text-success me-1">check_circle</span>
                                Desbloqueado el ${fecha}
                             </div>`;
                } else {
                    const progreso = data.progreso || 0;
                    html += `
                        <div class="mt-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Progreso</span>
                                <span>${data.actual || 0} / ${data.meta || 1}</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: ${progreso}%;"></div>
                            </div>
                        </div>
                    `;
                }
                html += `</div>`;
                contentDiv.innerHTML = html;
            } catch (err) {
                console.error('Error al cargar el logro:', err);
                contentDiv.innerHTML = `<div class="alert alert-danger">Error al cargar el logro: ${err.message}</div>`;
            }
        });
    });
});
function actualizarContador() 
{
    const checkboxes = document.querySelectorAll('#modalVitrina input[name="logros[]"]');
    const checked = document.querySelectorAll('#modalVitrina input[name="logros[]"]:checked');
    const counterSpan = document.getElementById('selectedCounter');
    checkboxes.forEach(cb => {
        const label = document.querySelector(`label[for="${cb.id}"]`);
        if (!label) return;

        label.classList.toggle('bg-rojoClaro', cb.checked);
        label.classList.toggle('texto-rojo', cb.checked);
        label.classList.toggle('border-rojo', cb.checked);
        label.classList.toggle('bg-white', !cb.checked);
        label.classList.toggle('text-secondary', !cb.checked);

        const selectedIcon = label.querySelector('[data-selected-icon="true"]');
        if (selectedIcon) selectedIcon.classList.toggle('d-none', !cb.checked);
    });
    if (counterSpan) 
    {
        counterSpan.textContent = `${checked.length}/8 seleccionados`;
        if (checked.length >= 8) {checkboxes.forEach(cb => {if (!cb.checked) cb.disabled = true;});} 
        else {checkboxes.forEach(cb => cb.disabled = false);}
    }
}
const vitrinaModal = document.getElementById('modalVitrina');
if (vitrinaModal) 
{
    vitrinaModal.addEventListener('shown.bs.modal', function () 
    {
        const checkboxes = document.querySelectorAll('#modalVitrina input[name="logros[]"]');
        checkboxes.forEach(cb => cb.addEventListener('change', actualizarContador));
        actualizarContador();
    });
    vitrinaModal.addEventListener('hidden.bs.modal', function () 
    {
        const checkboxes = document.querySelectorAll('#modalVitrina input[name="logros[]"]');
        checkboxes.forEach(cb => cb.removeEventListener('change', actualizarContador));
    });
}
