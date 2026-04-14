//=============================================================
// Comentarios.js - Overlay de comentarios y notificaciones
//=============================================================
//--------------------------------------------------------
//| Última edición: 2026-04-08 por Jaime Sánchez Soteras |
//--------------------------------------------------------
//---- Lógica para manejar el overlay de comentarios y notificaciones ----
(function() 
{
    let currentRecipeId = null;
    async function openComments(idReceta) // Abrir el overlay de comentarios para una receta específica
    {
        if (!window.isLoggedIn) {window.location.href = '/App/pages/login'; return;}

        currentRecipeId = idReceta;
        const overlay = document.getElementById('comments-overlay');
        const body = overlay?.querySelector('.comments-body');
        if (!overlay || !body) return;

        document.body.style.overflow = 'hidden';
        overlay.classList.remove('d-none');
        void overlay.offsetHeight;
        overlay.classList.add('active');
        body.innerHTML = '<div class="text-center p-3">Cargando...</div>';

        try 
        {
            const res = await fetch(`/App/pages/feed/comentarios/${idReceta}`);
            const comentarios = await res.json();
            if (comentarios.length) 
            {   // Plantilla de cada comentario
                body.innerHTML = comentarios.map(c => ` 
                    <div class="comment-item">
                        <div class="comment-avatar">${c.Username ? c.Username.charAt(0).toUpperCase() : 'U'}</div>
                        <div class="comment-content">
                            <div class="comment-header">
                                <span class="comment-username">${c.Username}</span>
                                <span class="comment-time">${c.Fecha}</span>
                            </div>
                            <div class="comment-text">${c.Descripcion}</div>
                        </div>
                        <button class="btn btn-link text-muted" data-bs-toggle="modal" data-bs-target="#reportModal" data-report-type="comentario" data-id="${c.ID_Comentario}" onclick="if(!window.isLoggedIn) { event.preventDefault(); window.location.href='/App/pages/login'; }">
                            <span class="material-symbols-outlined">flag</span>
                        </button>
                    </div>
                `).join('');
            } 
            else {body.innerHTML = '<p class="text-muted text-center p-4">No hay comentarios</p>';}
        } 
        catch (e) {console.error(e); body.innerHTML = '<p class="text-danger text-center">Error al cargar</p>';}
    }
    async function cargarNotificaciones() {
    const lista = document.querySelector('#notificaciones-lista');
    const contador = document.querySelector('#contadorNotificaciones');

    if (!lista || !contador) {
        console.warn('No se encontraron los elementos de notificaciones');
        return;
    }

    try {
        const res = await fetch('/App/api/notificaciones');
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const data = await res.json();

        lista.innerHTML = '';
        if (!data.notificaciones || data.notificaciones.length === 0) {
            const emptyMsg = document.createElement('div');
            emptyMsg.className = 'text-muted text-center p-3';
            emptyMsg.innerText = 'No hay notificaciones';
            lista.appendChild(emptyMsg);
        } else {
            data.notificaciones.forEach(n => {
                const item = document.createElement('div');
                item.className = 'dropdown-item notificacion-item position-relative pe-4';
                if (!n.Leida) item.classList.add('fw-bold');
                
                // Contenido de la notificación
                item.innerHTML = `
                    <small class="text-muted">${n.Username ?? 'Sistema'}</small><br>
                    <span>${n.Mensaje}</span>
                    <button class="btn-eliminar-notificacion" data-id="${n.ID_Notificacion}" title="Eliminar">&times;</button>
                `;
                
                lista.appendChild(item);
            });
        }

        const noLeidas = data.noLeidas || 0;
        contador.innerText = noLeidas > 0 ? noLeidas : '';
        contador.style.display = noLeidas > 0 ? 'inline-block' : 'none';
    } catch (error) {
        console.error('Error al cargar notificaciones:', error);
        lista.innerHTML = '<div class="text-danger text-center p-3">Error al cargar</div>';
    }
}
 async function marcarLeidas() {
        try {
            await fetch('/App/api/notificaciones/leer', { method: 'POST' });
            await cargarNotificaciones();
        } catch (e) {
            console.error('Error al marcar leídas', e);
        }
    }
// Eliminar una notificación individual
async function eliminarNotificacion(id) {
    try {
        const res = await fetch(`/App/api/notificaciones/eliminar/${id}`, { method: 'DELETE' });
        if (res.ok) {
            await cargarNotificaciones();
        } else {
            console.error('Error al eliminar notificación');
        }
    } catch (e) {
        console.error('Error al eliminar notificación', e);
    }
}

// Limpiar todas las notificaciones
async function limpiarNotificaciones() {
    if (!confirm('¿Eliminar todas las notificaciones?')) return;
    try {
        const res = await fetch('/App/api/notificaciones/limpiar', { method: 'DELETE' });
        if (res.ok) {
            await cargarNotificaciones();
        } else {
            console.error('Error al limpiar notificaciones');
        }
    } catch (e) {
        console.error('Error al limpiar notificaciones', e);
    }
}

// ---------- INICIALIZACIÓN (modificar la sección existente) ----------
document.addEventListener('DOMContentLoaded', () => {
    // Notificaciones
    if (window.isLoggedIn) {
        cargarNotificaciones();
        setInterval(cargarNotificaciones, 10000);
    }

    setupDrag();

    const campanaBtn = document.querySelector('#campana');
    if (campanaBtn) {
        campanaBtn.addEventListener('show.bs.dropdown', () => {
            if (window.isLoggedIn) marcarLeidas();
        });
    }

    // Evento delegado para eliminar notificación individual
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest('.btn-eliminar-notificacion');
        if (btn) {
            e.preventDefault();
            e.stopPropagation();
            const id = btn.dataset.id;
            await eliminarNotificacion(id);
        }
    });

    // Botón limpiar todas
    const btnLimpiar = document.getElementById('btn-limpiar-notificaciones');
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            limpiarNotificaciones();
        });
    }

    // Cerrar overlay de comentarios con ESC
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeComments();
    });

    const overlay = document.getElementById('comments-overlay');
    if (overlay) {
        overlay.addEventListener('click', e => {
            if (e.target.id === 'comments-overlay') closeComments();
        });
    }
});
    async function sendComment(event) // Enviar un nuevo comentario
    {
        event.preventDefault();
        if (!window.isLoggedIn) {window.location.href = '/App/pages/login'; return;}

        const form = event.target;
        const input = form.querySelector('input');
        const text = input.value.trim();
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        if (!text || !currentRecipeId) return;

        const formData = new FormData();
        formData.append('id_receta', currentRecipeId);
        formData.append('comentario', text);
        formData.append('csrf_token', csrfToken);

        try 
        {
            const res = await fetch('/App/api/receta/comentar', { method: 'POST', body: formData });
            if (!res.ok) {const errorData = await res.json().catch(() => ({})); throw new Error(errorData.message || `Error ${res.status}: No se pudo publicar el comentario`);}
            input.value = '';
            const body = document.querySelector('#comments-overlay .comments-body');
            if (body.querySelector('p')) body.innerHTML = '';
            // Plantilla de comentario propio 
            body.insertAdjacentHTML('beforeend', `
                <div class="comment-item">
                    <div class="comment-avatar">T</div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="comment-username">Tú</span>
                            <span class="comment-time">ahora</span>
                        </div>
                        <div class="comment-text">${text}</div>
                    </div>
                </div>
            `);
            body.scrollTop = body.scrollHeight;
            const countSpan = document.querySelector(`.comment-count-${currentRecipeId}`);
            if (countSpan) countSpan.innerText = (parseInt(countSpan.innerText) || 0) + 1;
        } 
        catch (e) {console.error(e);}
    }
    function closeComments() // Cerrar el overlay de comentarios
    {
        const overlay = document.getElementById('comments-overlay');
        if (!overlay) return;
        overlay.classList.remove('active');
        setTimeout(() => {overlay.classList.add('d-none'); document.body.style.overflow = '';}, 300);
        currentRecipeId = null;
    }
    function setupDrag() // Configurar arrastre para cerrar el overlay de comentarios
    {
        const overlay = document.getElementById('comments-overlay');
        if (!overlay) return;
        const sheet = overlay.querySelector('.comments-sheet');
        if (!sheet) return;

        let startY = 0, dragging = false, isTouch = false;
        function start(y, touch = false) 
        {
            startY = y;
            dragging = true;
            isTouch = touch;
            sheet.style.transition = 'none';
        }
        function move(y) 
        {
            if (!dragging) return;
            const diff = y - startY;
            if (diff > 0) sheet.style.transform = `translateY(${diff}px)`;
        }
        function end(y) 
        {
            if (!dragging) return;
            dragging = false;
            const diff = y - startY;
            sheet.style.transition = '';
            if (diff > 100) closeComments();
            else sheet.style.transform = '';
        }
        sheet.addEventListener('touchstart', e => start(e.touches[0].clientY, true));
        sheet.addEventListener('touchmove', e => move(e.touches[0].clientY));
        sheet.addEventListener('touchend', e => end(e.changedTouches[0].clientY));
        sheet.addEventListener('mousedown', e => start(e.clientY));
        window.addEventListener('mousemove', e => { if (!dragging || isTouch) return; move(e.clientY); });
        window.addEventListener('mouseup', e => { if (!dragging || isTouch) return; end(e.clientY); });
    }
    document.addEventListener('DOMContentLoaded', () => 
    {
        cargarNotificaciones();
        if (window.isLoggedIn) {setInterval(cargarNotificaciones, 10000);}
        setupDrag();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeComments(); });
    document.getElementById('comments-overlay')?.addEventListener('click', e => { if (e.target.id === 'comments-overlay') closeComments();});
    document.querySelector('#campana')?.addEventListener('click', async () => { await fetch('/App/api/notificaciones/leer', { method: 'POST' }); cargarNotificaciones();});

    window.FeedApp.openComments = openComments;
    window.FeedApp.closeComments = closeComments;
    window.FeedApp.sendComment = sendComment;
})();