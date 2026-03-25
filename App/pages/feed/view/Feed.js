// ==================== Controles de Estados Globales ====================
window.FeedApp = window.FeedApp || {};

FeedApp.state = 
{
    offset: 0,
    limit: 5,
    loading: false,
    isFull: false
};

FeedApp.filters = 
{
    busqueda: '',
    etiquetas: []
};

let observer;
let lastRequestId = 0;

// ==================== Auxiliares ====================
function initUIExtras() {if (typeof bootstrap !== 'undefined') {document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));}}
// ==================== Observador de Cambios ====================
function reinitObserver() 
{
    const trigger = document.getElementById('infinite-scroll-trigger');
    if (!trigger) return;

    if (observer) observer.disconnect();

    observer = new IntersectionObserver(entries => {if (entries[0].isIntersecting) loadMore();}, { threshold: 0.1 });

    observer.observe(trigger);
}

// ==================== Fetch Central de Posts ====================
async function fetchFeed(reset = true) 
{
    const feedContainer = document.getElementById('feed-container');

    if (reset) 
    {
        feedContainer.innerHTML = '<div class="text-center my-5"><div class="spinner-border text-danger"></div></div>';
        FeedApp.state.offset = 0;
        FeedApp.state.isFull = false;
    }

    const requestId = ++lastRequestId;

    try 
    {
        const res = await fetch(`/App/pages/feed/filtrar?offset=${FeedApp.state.offset}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(
            {
                busqueda: FeedApp.filters.busqueda,
                etiquetas: FeedApp.filters.etiquetas
            })
        });

        if (res.status === 401) 
        {
            window.location.href = '/App/pages/login';
            return;
        }

        const data = await res.json();

        if (requestId !== lastRequestId) return;

        if (data.html && data.html.trim() !== "") 
        {
            if (reset) {feedContainer.innerHTML = data.html + '<div id="infinite-scroll-trigger" style="height:10px;"></div>';} 
            else 
            {   
                const trigger = document.getElementById('infinite-scroll-trigger');
                if (trigger) {trigger.insertAdjacentHTML('beforebegin', data.html);}
            }

            FeedApp.state.offset += data.count;

            const trigger = document.getElementById('infinite-scroll-trigger');

            if (data.count < FeedApp.state.limit) 
            {
                FeedApp.state.isFull = true;
                if (trigger) {trigger.innerHTML = '<p class="text-muted text-center mt-3 p-4">Has llegado al final del feed.</p>';}
            } 
            else {if (trigger) trigger.innerHTML = '';}

            initUIExtras();
            reinitObserver();
        } 
        else {feedContainer.innerHTML = '<p class="text-center text-muted">No se encontraron recetas.</p>';}
    } 
    catch (err) {console.error("Error en fetchFeed:", err);}
}

FeedApp.fetchFeed = fetchFeed;

// ==================== Scroll Infinito de las Publicaciones ====================
async function loadMore() 
{
    if (FeedApp.state.loading || FeedApp.state.isFull) return;

    FeedApp.state.loading = true;

    const trigger = document.getElementById('infinite-scroll-trigger');
    if (trigger) {trigger.innerHTML = '<div class="spinner-border text-danger mx-auto d-block my-3"></div>';}

    await fetchFeed(false);

    FeedApp.state.loading = false;
}

document.addEventListener('DOMContentLoaded', reinitObserver);


// ==================== Filtrado por Texto (Búsqueda) ====================
(function () 
{
    const searchInput = document.getElementById('search-input');
    if (!searchInput) return;

    let timeout;

    searchInput.addEventListener('input', function () 
    {
        clearTimeout(timeout);

        const query = this.value.trim();

        timeout = setTimeout(() => 
        {
            FeedApp.filters.busqueda = query;
            fetchFeed(true);
        }, 400);
    });
})();

// ==================== Función de Likes ====================
(function () 
{
    async function toggleLike(idReceta, element) 
    {
        const icon = element.querySelector('.material-symbols-outlined');
        const count = element.querySelector('span:last-child');

        try 
        {
            if (!window.isLoggedIn) 
            {
                window.location.href = '/App/pages/login';
                return;
            }

            const res = await fetch(`/App/api/receta/like/${idReceta}`, { method: 'POST' });
            const data = await res.json();

            if (data.status === 'success') 
            {
                count.innerText = data.newLikes;

                icon.classList.toggle('text-danger');
                icon.classList.toggle('fill-icon');

                icon.animate(
                [
                    { transform: 'scale(1)' },
                    { transform: 'scale(1.3)' },
                    { transform: 'scale(1)' }
                ], { duration: 200 });
            }

        } 
        catch (err) {console.error('Error en like:', err);}
    }

    FeedApp.toggleLike = toggleLike;
})();

// ==================== Sección de Comentarios ====================
(function () 
{

    let currentRecipeId = null;

    async function openComments(idReceta) 
    {
        if (!window.isLoggedIn) 
        {
            window.location.href = '/App/pages/login';
            return;
        }

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
            {
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
                    </div>
                `).join('');
            } 
            else {body.innerHTML = '<p class="text-muted text-center p-4">No hay comentarios</p>';}
        } 
        catch (e) 
        {
            console.error(e);
            body.innerHTML = '<p class="text-danger text-center">Error al cargar</p>';
        }
    }
    async function cargarNotificaciones() 
    {
        const res = await fetch('/App/api/notificaciones');
        const data = await res.json();

        const lista = document.querySelector('#dropdownNotificaciones');
        const contador = document.querySelector('#contadorNotificaciones');

        if (!lista || !contador) return;

        lista.innerHTML = '';

        data.notificaciones.forEach(n => {
            const item = document.createElement('div');
            item.classList.add('dropdown-item');

            if (!n.Leida) item.classList.add('fw-bold');

            item.innerHTML = `
                <small class="text-muted">${n.Username ?? 'Sistema'}</small><br>
                ${n.Mensaje}
            `;

            lista.appendChild(item);
        });

        contador.innerText = data.noLeidas > 0 ? data.noLeidas : '';
    }
    
    async function sendComment(event) 
    {
        event.preventDefault();

        if (!window.isLoggedIn) 
        {
            window.location.href = '/App/pages/login';
            return;
        }

        const form = event.target;
        const input = form.querySelector('input');
        const text = input.value.trim();

        if (!text || !currentRecipeId) return;

        const formData = new FormData();
        formData.append('id_receta', currentRecipeId);
        formData.append('comentario', text);

        try 
        {
            const res = await fetch('/App/api/receta/comentar', 
            {
                method: 'POST',
                body: formData
            });

            if (res.ok) 
            {
                input.value = '';

                const body = document.querySelector('#comments-overlay .comments-body');

                if (body.querySelector('p')) body.innerHTML = '';

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
                if (countSpan) {countSpan.innerText = (parseInt(countSpan.innerText) || 0) + 1;}
            }
        } 
        catch (e) {console.error(e);}
    }

    function closeComments() 
    {
        const overlay = document.getElementById('comments-overlay');
        if (!overlay) return;

        overlay.classList.remove('active');

        setTimeout(() => 
        {
            overlay.classList.add('d-none');
            document.body.style.overflow = '';
        }, 300);

        currentRecipeId = null;
    }

    // ==================== DRAG ====================
    function setupDrag() 
    {
        const overlay = document.getElementById('comments-overlay');
        if (!overlay) return;

        const sheet = overlay.querySelector('.comments-sheet');
        if (!sheet) return;

        let startY = 0;
        let dragging = false;
        let isTouch = false;

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

        // Toque en móvil
        sheet.addEventListener('touchstart', e => start(e.touches[0].clientY, true));
        sheet.addEventListener('touchmove', e => move(e.touches[0].clientY));
        sheet.addEventListener('touchend', e => end(e.changedTouches[0].clientY));

        // Movimiento de ratón en PC
        sheet.addEventListener('mousedown', e => start(e.clientY));
        window.addEventListener('mousemove', e => 
        {
            if (!dragging || isTouch) return;
            move(e.clientY);
        });
        window.addEventListener('mouseup', e => 
        {
            if (!dragging || isTouch) return;
            end(e.clientY);
        });
    }
    document.addEventListener('DOMContentLoaded', () => 
        {
    cargarNotificaciones();
    setInterval(cargarNotificaciones, 10000);
    });
    document.addEventListener('DOMContentLoaded', setupDrag);
    document.addEventListener('keydown', e => {if (e.key === 'Escape') closeComments();});
    document.getElementById('comments-overlay')?.addEventListener('click', e => {if (e.target.id === 'comments-overlay') closeComments();});
    document.querySelector('#campana')?.addEventListener('click', async () => {await fetch('/App/api/notificaciones/leer', { method: 'POST' });
     cargarNotificaciones();});
    FeedApp.openComments = openComments;
    FeedApp.closeComments = closeComments;
    FeedApp.sendComment = sendComment;
    
})();