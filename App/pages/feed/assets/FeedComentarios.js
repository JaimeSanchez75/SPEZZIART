async function openComments(idReceta) 
{
    
    if (overlayAbierto && overlayAbierto !== idReceta) 
    {
        const overlayAnterior = document.getElementById(`comments-${overlayAbierto}`);
        if (overlayAnterior) 
        {
            overlayAnterior.classList.remove('active');
            setTimeout(() => {overlayAnterior.classList.add('d-none');}, 300);
        }
    }
    
    document.body.style.overflow = 'hidden';
    const feedContainer = document.querySelector('#feed-container');
    if (feedContainer) feedContainer.style.overflow = 'hidden';

    const overlay = document.getElementById(`comments-${idReceta}`);
    if (!overlay) {return;}

    overlay.classList.remove('d-none');
    void overlay.offsetHeight;
    
    overlay.classList.add('active');
    
    overlayAbierto = idReceta;

    const body = overlay.querySelector('.comments-body');
    if (body.children.length === 0 || body.innerHTML.includes('Cargando')) 
    {
        body.innerHTML = '<div class="text-center p-3">Cargando...</div>';
        try 
        {
            const respuesta = await fetch(`/App/pages/feed/comentarios/${idReceta}`);
            
            if (respuesta.status === 401) 
            {
                window.location.href = '/App/pages/login';
                return;
            }

            const comentarios = await respuesta.json();

            if (comentarios.length) 
            {
                body.innerHTML = comentarios.map(c => 
                    `<div class="comment-item">
                        <div class="comment-avatar">${c.Username ? c.Username.charAt(0).toUpperCase() : 'U'}</div>
                        <div class="comment-content">
                            <div class="comment-header">
                                <span class="comment-username">${c.Username}</span>
                                <span class="comment-time">${c.Fecha || 'ahora'}</span>
                            </div>
                            <div class="comment-text">${c.Descripcion}</div>
                        </div>
                    </div>`
                ).join('');
            } 
            else 
            {
                body.innerHTML = '<p class="text-muted text-center p-4">No hay comentarios</p>';
            }
            body.scrollTop = body.scrollHeight;
        } 
        catch (e) 
        {
            console.error('Error cargando comentarios:', e);
            body.innerHTML = '<p class="text-danger text-center">Error al cargar</p>';
        }
    } else {}
    setTimeout(() => 
    {
        const input = overlay.querySelector('input');
        if (input) 
        {
            input.focus();
        }
    }, 300);
    
}
/*Envío de Comentario */
async function sendInlineComment(event, idReceta) 
{
    event.preventDefault();
    const form = event.target;
    const input = form.querySelector('input');
    const btn = form.querySelector('button');
    const text = input.value.trim();
    
    if (!text || btn.disabled) 
    {
        return;
    }

    btn.disabled = true;

    const formData = new FormData();
    formData.append('id_receta', idReceta);
    formData.append('comentario', text);

    try 
    {
        const res = await fetch('/App/api/receta/comentar', { method: 'POST', body: formData });
        
        if (res.status === 401) 
        {
            window.location.href = '/App/pages/login';
            return;
        }
        
        if (res.ok) 
        {
            input.value = '';
            const overlay = document.getElementById(`comments-${idReceta}`);
            const list = overlay.querySelector('.comments-body');
            
            // Quitar mensaje vacío si existe
            if (list.querySelector('p')) 
            {
                list.innerHTML = '';
            }

            // Añadir comentario
            list.insertAdjacentHTML('beforeend', 
                `<div class="comment-item">
                    <div class="comment-avatar">T</div>
                    <div class="comment-content">
                        <div class="comment-header">
                            <span class="comment-username">Tú</span>
                            <span class="comment-time">ahora</span>
                        </div>
                        <div class="comment-text">${text}</div>
                    </div>
                </div>`
            );
            
            list.scrollTop = list.scrollHeight;

            // Actualizar contador
            const countSpan = document.querySelector(`.comment-count-${idReceta}`);
            if (countSpan) 
            {
                const nuevoContador = (parseInt(countSpan.innerText) || 0) + 1;
                countSpan.innerText = nuevoContador;
            }
        }
    } 
    catch (e) {console.error('❌ Error enviando comentario:', e)} 
    finally {btn.disabled = false;}
}

/*Para Cerrar Comentarios para lo siguiente:*/
    // --- Cambio de tamaño de ventana ---
    window.addEventListener('resize', function() 
    {
        
        if (overlayAbierto) 
        {
            closeComments(overlayAbierto);
        }
        
    });

    // --- Cambio de Orientación del Móvil---
    window.addEventListener('orientationchange', function() 
    {
        if (overlayAbierto) 
        {
            closeComments(overlayAbierto);
        }
    });

     // Cerrar con Escape
    document.addEventListener('keydown', (e) => 
    {
        if (e.key === 'Escape' && overlayAbierto) 
        {
            closeComments(overlayAbierto);
        }
    });

    // Click fuera
    document.querySelectorAll('.comments-overlay').forEach(overlay => 
    {
        overlay.addEventListener('click', (e) => 
        {
            if (e.target === overlay && overlayAbierto) 
            {
                closeComments(overlay.id.replace('comments-', ''));
            }
        });
    });
