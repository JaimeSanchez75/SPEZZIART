document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('saveModal');
    if (!modal) return;

    modal.addEventListener('show.bs.modal', async (event) => {
        const btn = event.relatedTarget;
        idRecetaActual = btn.getAttribute('data-id');
        document.getElementById('saveRecipeId').value = idRecetaActual;

        const contenedor = document.getElementById('cols-list');
        contenedor.innerHTML = '<div class="text-muted text-center">Cargando...</div>';

        try {
            const res = await fetch('/App/api/colecciones');
            if (!res.ok) throw new Error();
            const cols = await res.json();

            if (cols.length === 0) {
                contenedor.innerHTML = '<div class="text-muted">No tienes colecciones. Se guardará en "Guardadas" por defecto.</div>';
                document.getElementById('guardarDefecto').checked = true;
                document.getElementById('guardarDefecto').disabled = true;
            } else {
                contenedor.innerHTML = '';
                cols.forEach(c => {
                    const div = document.createElement('div');
                    div.className = 'form-check';
                    div.innerHTML = `
                        <input class="form-check-input" type="checkbox" value="${c.ID_Coleccion}" id="col_${c.ID_Coleccion}">
                        <label class="form-check-label" for="col_${c.ID_Coleccion}">${escapeHtml(c.Nombre)}</label>
                    `;
                    contenedor.appendChild(div);
                });
                document.getElementById('guardarDefecto').disabled = false;
                document.getElementById('guardarDefecto').checked = true;
            }
        } catch (err) {
            contenedor.innerHTML = '<div class="text-danger">Error al cargar colecciones. Recarga la página.</div>';
        }
    });

    const form = document.getElementById('saveForm');
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const idReceta = document.getElementById('saveRecipeId').value;
        const seleccionadas = Array.from(document.querySelectorAll('#cols-list input[type="checkbox"]:checked')).map(cb => cb.value);
        const guardarDefecto = document.getElementById('guardarDefecto').checked;

        const errorDiv = document.getElementById('saveError');
        const successDiv = document.getElementById('saveSuccess');
        errorDiv.classList.add('d-none');
        successDiv.classList.add('d-none');

        try {
            const res = await fetch('/App/api/receta/guardar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    csrf_token: csrfToken,
                    id_receta: idReceta,
                    colecciones: seleccionadas,
                    guardarPorDefecto: guardarDefecto
                })
            });
            const data = await res.json();
            if (data.ok) {
                successDiv.textContent = data.msg;
                successDiv.classList.remove('d-none');
                setTimeout(() => bootstrap.Modal.getInstance(modal).hide(), 1500);
            } else {
                errorDiv.textContent = data.msg;
                errorDiv.classList.remove('d-none');
            }
        } catch (err) {
            errorDiv.textContent = 'Error al guardar. Inténtalo de nuevo.';
            errorDiv.classList.remove('d-none');
        }
    });
});

function escapeHtml(str) {
    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}