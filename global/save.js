"use strict";
document.addEventListener('DOMContentLoaded', () => // Esperar a que el DOM esté listo
{
    const modal = document.getElementById('saveModal');
    if (!modal) return;
    let idRecetaActual = null;
    modal.addEventListener('show.bs.modal', async (event) => // Cuando se abre el modal
    {
        const btn = event.relatedTarget;
        idRecetaActual = btn.getAttribute('data-id');
        document.getElementById('saveRecipeId').value = idRecetaActual;
        const contenedor = document.getElementById('cols-list');
        contenedor.innerHTML = '<div class="text-secondary texto text-center">Cargando colecciones...</div>';
        try  // Intentar obtener las colecciones del usuario
        {
            const res = await fetch(`/api/colecciones?receta_id=${encodeURIComponent(idRecetaActual)}`);
            if (!res.ok) throw new Error('Error al obtener colecciones');
            const cols = await res.json();
            if (cols.length === 0)
            {
                contenedor.innerHTML = '<div class="text-secondary texto">No tienes colecciones. <a href="/pages/individual">Crea una</a> primero.</div>';
                document.querySelector('#saveForm button[type="submit"]').disabled = true;
            }
            else
            {
                contenedor.innerHTML = '';
                cols.forEach(c =>
                {
                    const yaGuardada = !!c.tieneReceta;
                    const div = document.createElement('div');
                    div.className = 'form-check d-flex align-items-center gap-2 w-100 overflow-hidden';

                    div.innerHTML =
                    `<input class="form-check-input flex-shrink-0 m-0" type="checkbox" value="${c.ID_Coleccion}" id="col_${c.ID_Coleccion}"${yaGuardada ? ' checked' : ''}>
                    <label class="form-check-label texto text-secondary d-flex align-items-center gap-2 flex-grow-1 w-100 overflow-hidden m-0" for="col_${c.ID_Coleccion}">
                        <span class="text-truncate d-block flex-grow-1 overflow-hidden" title="${escapeHtml(c.Nombre)}">${escapeHtml(c.Nombre)}</span>
                        ${yaGuardada ? '<span class="badge text-bg-success flex-shrink-0 small">Guardada</span>' : ''}
                    </label>`;
                    contenedor.appendChild(div);
                });
                document.querySelector('#saveForm button[type="submit"]').disabled = false;
            }
        }
        catch (err) {contenedor.innerHTML = '<div class="text-danger">Error al cargar colecciones. Recarga la página.</div>';}
    });
    const form = document.getElementById('saveForm');
    form.addEventListener('submit', async (e) => // Cuando se envía el formulario
    {
        e.preventDefault();
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const idReceta = document.getElementById('saveRecipeId').value;
        const seleccionadas = Array.from(document.querySelectorAll('#cols-list input[type="checkbox"]:checked')).map(cb => cb.value);
        if (seleccionadas.length === 0) {window.Alertas.error('Selecciona al menos una colección'); return;}
        const errorDiv = document.getElementById('saveError');
        const successDiv = document.getElementById('saveSuccess');
        errorDiv.classList.add('d-none');
        successDiv.classList.add('d-none');
        try // Intentar guardar la receta en las colecciones seleccionadas
        {
            const res = await fetch('/api/receta/guardar', 
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify
                ({
                    csrf_token: csrfToken,
                    id_receta: idReceta,
                    colecciones: seleccionadas
                })
            });
            const data = await res.json();
            if (data.ok) 
            {
                successDiv.textContent = data.msg || 'Receta guardada correctamente';
                successDiv.classList.remove('d-none');
                setTimeout(() => {bootstrap.Modal.getInstance(modal).hide(); document.querySelectorAll('#cols-list input[type="checkbox"]').forEach(cb => cb.checked = false);}, 1000);
            } 
            else 
            {
                errorDiv.textContent = data.msg || 'Error al guardar';
                errorDiv.classList.remove('d-none');
            }
        } 
        catch (err) 
        {
            errorDiv.textContent = 'Error de conexión. Inténtalo de nuevo.';
            errorDiv.classList.remove('d-none');
        }
    });
    function escapeHtml(str)
    {
        return String(str ?? '').replace(/[&<>"']/g, function(m) 
        {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            if (m === '"') return '&quot;';
            if (m === "'") return '&#039;';
            return m;
        });
    }
});