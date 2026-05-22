(function() 
{
    'use strict';
    let currentRecipeId = null;
    const MAX_COMMENT_LENGTH = 200;
    async function openComments(idReceta) 
    {
        if (!window.isLoggedIn) 
        {
            window.location.href = '/pages/login';
            return;
        }
        currentRecipeId = idReceta;
        const overlay = document.getElementById('comments-overlay');
        const body = overlay?.querySelector('.comments-body');
        if (!overlay || !body) 
        {
            console.warn('No se encontró el overlay de comentarios');
            return;
        }
        body.innerHTML = '<div class="text-center p-3"><div class="spinner-border text-danger"></div></div>';
        overlay.classList.remove('d-none');
        void overlay.offsetHeight;
        overlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        try 
        {
            await cargarUsuarioActual();
            const url = `/pages/feed/comentarios/${idReceta}`;
            const res = await fetch(url);
            if (!res.ok) throw new Error(`Error HTTP ${res.status}`);
            const comentarios = await res.json();
            const lista = Array.isArray(comentarios) ? comentarios : (comentarios.comentarios || []);
            if (lista.length > 0) 
            {
                body.innerHTML = lista.map(c => 
                {
                    const tieneFoto = c.FotoPerfil && c.FotoPerfil !== '';
                    const avatarHtml = tieneFoto? `<img src="${c.FotoPerfil}" class="w-100 h-100 object-fit-cover" alt="Foto de perfil">`: `<span class="material-symbols-outlined text-white textoMediano">person</span>`;
                    const puedeBorrar = String(c.ID_Creador) === String(window.currentUserId);
                    const deleteButtonHtml = puedeBorrar? `<button class="btnAccionAdmin danger flex-shrink-0 btn-delete-comment" data-id="${c.ID_Comentario}" title="Eliminar comentario"><i class="bi bi-trash"></i></button>`: '';    
                    return `<article class="comment-item d-flex align-items-start p-3 sombra border rounded rounded-4 gap-3 bg-white efectoEscala mb-3">
                        <a href="/pages/perfil/${c.ID_Creador}" class="text-decoration-none">
                            <div class="comment-avatar circuloPerfil bg-rojo text-white d-flex align-items-center justify-content-center fw-semibold flex-shrink-0 overflow-hidden">
                                ${avatarHtml}
                            </div>
                        </a>
                        <div class="comment-content flex-grow-1">
                            <div class="comment-header d-flex align-items-baseline gap-2 flex-wrap mb-1">
                                <a href="/pages/perfil/${c.ID_Creador}" class="text-decoration-none comment-username texto-rojo fw-semibold texto">${escapeHtml(c.Nombre)}</a>
                                <span class="comment-time textoPequeno text-secondary">${c.Fecha}</span>
                            </div>
                            <div class="comment-text texto text-secondary text-break">${escapeHtml(c.Descripcion)}</div>
                        </div>
                        <div class="d-flex gap-2 flex-shrink-0">
                            ${deleteButtonHtml}
                            <button class="btnAccionAdmin danger flex-shrink-0" data-bs-toggle="modal" data-bs-target="#reportModal" data-report-type="comentario" data-id="${c.ID_Comentario}">
                                <i class="bi bi-flag"></i>
                            </button>
                        </div>
                    </article>`;
                }).join('');
            } 
            else {body.innerHTML = '<p class="text-secondary texto text-center p-4">No hay comentarios aún. ¡Sé el primero!</p>';}
        } 
        catch (e) 
        {
            console.error('Error al cargar comentarios:', e);
            body.innerHTML = '<p class="texto-rojo text-center p-4">Error al cargar comentarios</p>';
        }
    }
    async function deleteComment(idComentario, button) 
    {
        if (!window.isLoggedIn) 
        {
            window.location.href = '/pages/login';
            return;
        }
        const okComentario = window.Confirmacion
            ? await window.Confirmacion.preguntar({
                titulo: 'Eliminar comentario',
                mensaje: '¿Seguro que deseas eliminar este comentario?',
                subtexto: 'Esta acción no se puede deshacer.',
                textoConfirmar: 'Eliminar',
                icono: 'bi-trash',
            })
            : confirm('¿Seguro que deseas eliminar este comentario?')
        if (!okComentario) return;
        const formData = new FormData();
        formData.append('id_comentario', idComentario);
        formData.append('csrf_token', FeedApp.getCsrfToken());
        try 
        {
            const res = await fetch('/api/comentario/eliminar', 
            {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            });
            const data = await res.json();
            if (!res.ok || !data.success) 
            {
                window.Alertas?.error(data.error || 'No se pudo eliminar el comentario');
                return;
            }
            const item = button.closest('.comment-item');
            if (item) item.remove();
            const countSpan = document.querySelector(`.comment-count-${currentRecipeId}`);
            if (countSpan) 
            {
                countSpan.innerText = Math.max((parseInt(countSpan.innerText) || 1) - 1, 0);
            }
            const body = document.querySelector('#comments-overlay .comments-body');
            if (body && !body.querySelector('.comment-item')) 
            {
                body.innerHTML = '<p class="text-secondary texto text-center p-4">No hay comentarios aún. ¡Sé el primero!</p>';
            }
        } 
        catch (e) 
        {
            console.error(e);
            window.Alertas?.error('Error al eliminar el comentario');
        }
    }
    async function sendComment(event) 
    {
        event.preventDefault();
        if (!window.isLoggedIn) {window.location.href = '/pages/login'; return;}
        const form = event.target;
        const input = form.querySelector('input');
        const text = input.value.trim();

        if (!text || !currentRecipeId) return;

        if (text.length > MAX_COMMENT_LENGTH) 
        {
            window.Alertas?.error(`El comentario no puede superar los ${MAX_COMMENT_LENGTH} caracteres`);
            return;
        }
        const formData = new FormData();
        formData.append('id_receta', currentRecipeId);
        formData.append('comentario', text);
        formData.append('csrf_token', FeedApp.getCsrfToken());         
        try
        {
            const res = await fetch('/api/receta/comentar', { method: 'POST', body: formData });
            if (!res.ok) throw new Error('Error al publicar');
            const data = await res.json();
            if (!data || data.status !== 'success') throw new Error('Error al publicar');
            input.value = '';
            const contador = form.querySelector('.comment-char-counter');
            if (contador) actualizarContadorComentario(input, contador);
            const body = document.querySelector('#comments-overlay .comments-body');
            if (body.querySelector('p')) body.innerHTML = '';
            const tieneFoto = window.currentUserFoto && window.currentUserFoto !== '';
            const avatarHtml = tieneFoto ? `<img src="${window.currentUserFoto}" class="w-100 h-100 object-fit-cover" alt="Foto de perfil">` : `<span class="material-symbols-outlined text-white textoMediano">person</span>`;
            const deleteButtonHtml = data.id_comentario
                ? `<button class="btnAccionAdmin danger flex-shrink-0 btn-delete-comment" data-id="${data.id_comentario}" title="Eliminar comentario"><i class="bi bi-trash"></i></button>`
                : '';
            body.insertAdjacentHTML
            (   'beforeend',
                `<article class="comment-item d-flex align-items-start p-3 sombra border rounded rounded-4 gap-3 bg-white efectoEscala mb-3">
                    <a href="/pages/perfil/${window.currentUserId}" class="text-decoration-none ">
                        <div class="comment-avatar circuloPerfil bg-rojo text-white d-flex align-items-center justify-content-center fw-semibold flex-shrink-0 overflow-hidden">
                            ${avatarHtml}
                        </div>
                    </a>
                    <div class="comment-content flex-grow-1">
                        <div class="comment-header d-flex align-items-baseline gap-2 flex-wrap mb-1">
                            <a href="/pages/perfil/${window.currentUserId}" class="text-decoration-none comment-username texto-rojo fw-semibold texto">${escapeHtml(window.currentUsername || 'Tú')}</a>
                            <span class="comment-time textoPequeno text-secondary">ahora</span>
                        </div>
                        <div class="comment-text texto text-secondary text-break">${escapeHtml(text)}</div>
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        ${deleteButtonHtml}
                    </div>
                </article>`
            );
            body.scrollTop = body.scrollHeight;
            const countSpan = document.querySelector(`.comment-count-${currentRecipeId}`);
            if (countSpan) countSpan.innerText = (parseInt(countSpan.innerText) || 0) + 1;
        } 
        catch (e) 
        {
            console.error(e);
            window.Alertas.error('No se pudo enviar el comentario');
        }
    }
    function closeComments() 
    {
        const overlay = document.getElementById('comments-overlay');
        if (!overlay) return;
        overlay.classList.remove('active');
        setTimeout(() => 
        {
            overlay.classList.add('d-none');
            const body = overlay.querySelector('.comments-body');
            if (body) body.innerHTML = '';
            document.body.style.overflow = '';
        }, 300);
        currentRecipeId = null;
    }
    function setupDrag() 
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
    function iconoNotificacion(tipo)
    {
        switch ((tipo || '').toLowerCase())
        {
            case 'solicitud_seguimiento': return '<i class="bi bi-person-plus text-primary me-1"></i>';
            case 'comentario':            return '<i class="bi bi-chat text-secondary me-1"></i>';
            case 'like':                  return '<i class="bi bi-heart text-danger me-1"></i>';
            case 'reporte':               return '<i class="bi bi-flag-fill text-danger me-1"></i>';
            default:                      return '<i class="bi bi-bell text-secondary me-1"></i>';
        }
    }

    function formatearFechaNotif(fecha)
    {
        if (!fecha) return '';
        const d = new Date(fecha);
        if (isNaN(d.getTime())) return '';
        const diff = Math.floor((Date.now() - d) / 1000);
        if (diff < 60)    return 'ahora';
        if (diff < 3600)  return `hace ${Math.floor(diff / 60)} min`;
        if (diff < 86400) return `hace ${Math.floor(diff / 3600)} h`;
        return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short' });
    }

    async function cargarNotificaciones()
    {
        const lista    = document.querySelector('#notificaciones-lista');
        const contador = document.querySelector('#contadorNotificaciones');
        if (!lista || !contador) return;
        try
        {
            const res = await fetch('/api/notificaciones');
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            const data = await res.json();
            lista.innerHTML = '';

            if (!data.notificaciones || data.notificaciones.length === 0)
            {
                lista.innerHTML = '<div class="text-center text-muted py-3">No hay notificaciones</div>';
            }
            else
            {
                data.notificaciones.forEach(n =>
                {
                    const item = document.createElement('div');
                    item.className = 'd-flex justify-content-between align-items-start border-bottom py-2 px-1';
                    item.dataset.id = n.ID_Notificacion;
                    if (!Number(n.Leida)) item.classList.add('fw-semibold');

                    // Columna izquierda: info + mensaje + fecha + botones solicitud
                    const colMsg = document.createElement('div');
                    colMsg.className = 'me-2 flex-grow-1';

                    const info = document.createElement('div');
                    info.className = 'small text-muted';
                    info.innerHTML = `${iconoNotificacion(n.Tipo)}<span>${escapeHtml(n.Nombre ?? 'Sistema')}</span>`;

                    const msg = document.createElement('div');
                    msg.className = 'texto text-break';
                    msg.textContent = n.Mensaje || '';

                    const fecha = document.createElement('div');
                    fecha.className = 'small text-muted';
                    fecha.textContent = formatearFechaNotif(n.Fecha);

                    colMsg.appendChild(info);
                    colMsg.appendChild(msg);
                    colMsg.appendChild(fecha);

                    if (n.Tipo === 'solicitud_seguimiento')
                    {
                        const acciones = document.createElement('div');
                        acciones.className = 'mt-2 d-flex gap-2';
                        acciones.innerHTML =
                        `<button class="bg-rojo text-white border-0 px-2 py-1 texto rounded-3 aceptar-solicitud" data-solicitante="${n.ID_Usuario_Origen}" data-notificacion="${n.ID_Notificacion}">Aceptar</button>
                        <button class="border text-secondary bg-white px-2 py-1 texto rounded-3 rechazar-solicitud" data-solicitante="${n.ID_Usuario_Origen}" data-notificacion="${n.ID_Notificacion}">Rechazar</button>`;
                        colMsg.appendChild(acciones);
                    }

                    // Botón eliminar
                    const btnElim = document.createElement('button');
                    btnElim.type = 'button';
                    btnElim.className = 'btn btn-sm btn-link text-danger p-0 flex-shrink-0 btn-eliminar-notificacion';
                    btnElim.dataset.id = n.ID_Notificacion;
                    btnElim.textContent = '×';

                    item.appendChild(colMsg);
                    item.appendChild(btnElim);
                    lista.appendChild(item);
                });

                document.querySelectorAll('.aceptar-solicitud').forEach(btn =>
                {
                    btn.removeEventListener('click', manejarAceptar);
                    btn.addEventListener('click', manejarAceptar);
                });
                document.querySelectorAll('.rechazar-solicitud').forEach(btn =>
                {
                    btn.removeEventListener('click', manejarRechazar);
                    btn.addEventListener('click', manejarRechazar);
                });
            }

            const noLeidas = Number(data.noLeidas) || 0;
            if (noLeidas > 0)
            {
                contador.textContent = noLeidas > 15 ? '15+' : String(noLeidas);
                contador.classList.remove('d-none');
                contador.classList.add('d-inline-block');
            }
            else
            {
                contador.textContent = '0';
                contador.classList.add('d-none');
                contador.classList.remove('d-inline-block');
            }
        }
        catch (error)
        {
            console.error('Error al cargar notificaciones:', error);
            lista.innerHTML = '<div class="text-danger text-center py-3">Error al cargar</div>';
        }
    }
    async function manejarAceptar(e) 
    {
        e.stopPropagation();
        const btn = e.currentTarget;
        const idSolicitante = btn.dataset.solicitante;
        const idNotificacion = btn.dataset.notificacion;  
        const csrfToken = FeedApp.getCsrfToken();
        try 
        {
            const res = await fetch('/api/solicitud/aceptar', 
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_solicitante: idSolicitante, id_notificacion: idNotificacion, csrf_token: csrfToken })
            });
            const text = await res.text();
            
            if (res.ok) 
            {
                await cargarNotificaciones();
                if (window.location.pathname.includes('/pages/perfil/')) location.reload();
            } else {console.error('Error al aceptar (' + res.status + '):', text);}
        } 
        catch (err) {console.error('Error de red al aceptar:', err);}
    }
    async function manejarRechazar(e) 
    {
        e.stopPropagation();
        const btn = e.currentTarget;
        const idSolicitante = btn.dataset.solicitante;
        const idNotificacion = btn.dataset.notificacion;  
        const csrfToken = FeedApp.getCsrfToken();
        try 
        {
            const res = await fetch('/api/solicitud/rechazar', 
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_solicitante: idSolicitante, id_notificacion: idNotificacion, csrf_token: csrfToken })
            });
            const text = await res.text();
            console.log('Rechazar solicitud:', res.status, text);
            if (res.ok) {await cargarNotificaciones();} 
            else {console.error('Error al rechazar (' + res.status + '):', text);}
        } 
        catch (err) {console.error('Error de red al rechazar:', err);}
    }
    async function marcarLeidas() 
    {
        try 
        {
            await fetch('/api/notificaciones/leer', { method: 'POST' });
            await cargarNotificaciones();
        } 
        catch (e) { console.error(e); }
    }
    async function eliminarNotificacion(id) 
    {
        try 
        {
            const res = await fetch(`/api/notificaciones/eliminar/${id}`, { method: 'DELETE' });
            if (res.ok) await cargarNotificaciones();
        } 
        catch (e) { console.error(e); }
    }
    async function limpiarNotificaciones() 
    {
        const okLimpiar = window.Confirmacion
            ? await window.Confirmacion.preguntar({
                titulo: 'Limpiar notificaciones',
                mensaje: '¿Seguro que deseas eliminar todas las notificaciones?',
                subtexto: 'Esta acción no se puede deshacer.',
                textoConfirmar: 'Eliminar todas',
                icono: 'bi-trash',
            })
            : confirm('¿Eliminar todas las notificaciones?');
        if (!okLimpiar) return;
        try 
        {
            const res = await fetch('/api/notificaciones/limpiar', { method: 'DELETE' });
            if (res.ok) await cargarNotificaciones();
        } 
        catch (e) { console.error(e); }
    }
    function actualizarContadorComentario(input, contador) 
    {
        const usados = input.value.length;
        contador.textContent = `${usados}/${MAX_COMMENT_LENGTH}`;

        contador.classList.toggle('texto-rojo', usados >= MAX_COMMENT_LENGTH);
        contador.classList.toggle('text-secondary', usados < MAX_COMMENT_LENGTH);
    }

    function setupContadorComentarios() 
    {
        const overlay = document.getElementById('comments-overlay');
        const form = overlay?.querySelector('form');
        const input = form?.querySelector('input');

        if (!input || !form) return;

        input.setAttribute('maxlength', MAX_COMMENT_LENGTH);

        let contador = form.querySelector('.comment-char-counter');

        if (!contador) 
        {
            contador = document.createElement('div');
            contador.className = 'comment-char-counter textoPequeno text-secondary text-end mt-1';
            input.insertAdjacentElement('afterend', contador);
        }

        actualizarContadorComentario(input, contador);

        input.addEventListener('input', () => 
        {
            actualizarContadorComentario(input, contador);
        });
    }
    document.addEventListener('DOMContentLoaded', () => 
    {
        setupDrag();
        setupContadorComentarios();
        if (window.isLoggedIn) 
        {
            cargarNotificaciones();
            setInterval(cargarNotificaciones, 30000);
        }
        document.addEventListener('click', async (e) => 
        {
            const btn = e.target.closest('.btn-delete-comment');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            await deleteComment(btn.dataset.id, btn);
        });
        const campana = document.querySelector('#campana');
        if (campana) {campana.addEventListener('show.bs.dropdown', () => { if (window.isLoggedIn) marcarLeidas(); });}
        document.addEventListener('click', async (e) => 
        {
            const btn = e.target.closest('.btn-eliminar-notificacion');
            if (btn) 
            {
                e.preventDefault();
                e.stopPropagation();
                await eliminarNotificacion(btn.dataset.id);
            }
        });
        const btnLimpiar = document.getElementById('btn-limpiar-notificaciones');
        if (btnLimpiar) 
        {
            btnLimpiar.addEventListener
            (   'click', (e) => 
            {
                e.preventDefault();
                e.stopPropagation();
                limpiarNotificaciones();
            });
        }
        
    });
    function escapeHtml(str) 
    {
        if (!str) return '';
        return str.replace(/[&<>]/g, function(m) 
        {
            if (m === '&') return '&amp;';
            if (m === '<') return '&lt;';
            if (m === '>') return '&gt;';
            return m;
        });
    }
    async function cargarUsuarioActual()
    {
        if (window._usuarioCargado) { return; }
        // Si ya tenemos foto real (truthy) no hace falta fetch
        if (window.currentUserId && window.currentUserFoto)
        {
            window._usuarioCargado = true;
            return;
        }
        try
        {
            const res = await fetch('/api/usuario/actual');
            const data = await res.json();
            if (data.success)
            {
                window.currentUserId   = data.id;
                window.currentUsername = data.nombre;
                window.currentUserFoto = data.foto_perfil;
            }
            else { console.error('Error cargando usuario:', data.error); }
        }
        catch (e) { console.error('Error en fetch usuario actual:', e); }
        finally   { window._usuarioCargado = true; }
    }
    window.FeedApp = window.FeedApp || {};
    FeedApp.openComments = openComments;
    FeedApp.sendComment = sendComment;
    FeedApp.closeComments = closeComments;
    FeedApp.cargarNotificaciones = cargarNotificaciones;
})();
