// feed.js - Versión final con búsqueda y drag mejorado

// ==================== ESTADO GLOBAL ====================
window.FeedApp = window.FeedApp || {};
FeedApp.state = FeedApp.state || {
    offset: 0,
    limit: 5,
    loading: false,
    isFull: false,
    overlayAbierto: null
};

// ==================== SCROLL INFINITO ====================
(function() {
    const trigger = document.getElementById('infinite-scroll-trigger');
    if (trigger) {
        const observer = new IntersectionObserver(entries => {
            if (entries[0].isIntersecting) loadMoreRecipes();
        }, { threshold: 0.1 });
        observer.observe(trigger);
    }

    async function loadMoreRecipes() {
        if (FeedApp.state.loading || FeedApp.state.isFull) return;

        FeedApp.state.loading = true;
        if (trigger) trigger.innerHTML = '<div class="spinner-border text-danger mx-auto d-block my-3"></div>';

        try {
            const res = await fetch(`/App/pages/feed/filtrar?offset=${FeedApp.state.offset}`, { method: 'POST' });
            if (res.status === 401) {
                window.location.href = '/App/pages/login';
                return;
            }

            const data = await res.json();
            if (data.html && data.html.trim() !== "") {
                if (trigger) trigger.insertAdjacentHTML('beforebegin', data.html);
                FeedApp.state.offset += data.count; // Usar count real
                if (data.count < FeedApp.state.limit) FeedApp.state.isFull = true;

                if (typeof bootstrap !== 'undefined') {
                    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));
                }

                // Inicializar drag en nuevos overlays
                initDragOnOverlays();
            } else {
                FeedApp.state.isFull = true;
                if (trigger) trigger.innerHTML = '<p class="text-muted text-center mt-3 p-4">Has llegado al final del feed.</p>';
            }
        } catch (err) {
            console.error("Error cargando más recetas:", err);
        } finally {
            FeedApp.state.loading = false;
            if (!FeedApp.state.isFull && trigger) trigger.innerHTML = '';
        }
    }
    FeedApp.loadMoreRecipes = loadMoreRecipes;
})();

// ==================== LIKES ====================
(function() {
    async function toggleLike(idReceta, element) {
        const icon = element.querySelector('.material-symbols-outlined');
        const count = element.querySelector('span:last-child');

        try {
            const res = await fetch(`/App/api/receta/like/${idReceta}`, { method: 'POST' });
            if (res.status === 401) {
                window.location.href = '/App/pages/login';
                return;
            }

            const data = await res.json();
            if (data.status === 'success') {
                count.innerText = data.newLikes;
                icon.classList.toggle('text-danger');
                icon.classList.toggle('fill-icon');

                icon.animate(
                    [
                        { transform: 'scale(1)' },
                        { transform: 'scale(1.3)' },
                        { transform: 'scale(1)' }
                    ],
                    { duration: 200 }
                );
            }
        } catch (err) {
            console.error('Error en like:', err);
            window.location.href = '/App/pages/login';
        }
    }
    FeedApp.toggleLike = toggleLike;
})();

// ==================== COMENTARIOS (OVERLAY) ====================
(function() {
    async function openComments(idReceta) {
        if (FeedApp.state.overlayAbierto && FeedApp.state.overlayAbierto !== idReceta) {
            closeComments(FeedApp.state.overlayAbierto);
        }

        document.body.style.overflow = 'hidden';
        const feedContainer = document.querySelector('#feed-container');
        if (feedContainer) feedContainer.style.overflow = 'hidden';

        const overlay = document.getElementById(`comments-${idReceta}`);
        if (!overlay) return;

        overlay.classList.remove('d-none');
        void overlay.offsetHeight;
        overlay.classList.add('active');
        FeedApp.state.overlayAbierto = idReceta;

        const body = overlay.querySelector('.comments-body');
        if (body.children.length === 0 || body.innerHTML.includes('Cargando')) {
            body.innerHTML = '<div class="text-center p-3">Cargando...</div>';
            try {
                const respuesta = await fetch(`/App/pages/feed/comentarios/${idReceta}`);
                if (respuesta.status === 401) {
                    window.location.href = '/App/pages/login';
                    return;
                }

                const comentarios = await respuesta.json();
                if (comentarios.length) {
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
                } else {
                    body.innerHTML = '<p class="text-muted text-center p-4">No hay comentarios</p>';
                }
                body.scrollTop = body.scrollHeight;
            } catch (e) {
                console.error('Error cargando comentarios:', e);
                body.innerHTML = '<p class="text-danger text-center">Error al cargar</p>';
            }
        }

        setTimeout(() => {
            const input = overlay.querySelector('input');
            if (input) input.focus();
        }, 300);
    }

    async function sendInlineComment(event, idReceta) {
        event.preventDefault();
        const form = event.target;
        const input = form.querySelector('input');
        const btn = form.querySelector('button');
        const text = input.value.trim();

        if (!text || btn.disabled) return;

        btn.disabled = true;

        const formData = new FormData();
        formData.append('id_receta', idReceta);
        formData.append('comentario', text);

        try {
            const res = await fetch('/App/api/receta/comentar', { method: 'POST', body: formData });
            if (res.status === 401) {
                window.location.href = '/App/pages/login';
                return;
            }

            if (res.ok) {
                input.value = '';
                const overlay = document.getElementById(`comments-${idReceta}`);
                const list = overlay.querySelector('.comments-body');

                if (list.querySelector('p')) {
                    list.innerHTML = '';
                }

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

                const countSpan = document.querySelector(`.comment-count-${idReceta}`);
                if (countSpan) {
                    countSpan.innerText = (parseInt(countSpan.innerText) || 0) + 1;
                }
            }
        } catch (e) {
            console.error('Error enviando comentario:', e);
        } finally {
            btn.disabled = false;
        }
    }

    function closeComments(idReceta) {
        const overlay = document.getElementById(`comments-${idReceta}`);
        if (!overlay) return;

        overlay.classList.remove('active');
        setTimeout(() => {
            overlay.classList.add('d-none');
            document.body.style.overflow = '';
            const feedContainer = document.querySelector('#feed-container');
            if (feedContainer) feedContainer.style.overflow = '';
        }, 300);

        if (FeedApp.state.overlayAbierto === idReceta) {
            FeedApp.state.overlayAbierto = null;
        }
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && FeedApp.state.overlayAbierto) {
            closeComments(FeedApp.state.overlayAbierto);
        }
    });

    document.querySelectorAll('.comments-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay && FeedApp.state.overlayAbierto) {
                closeComments(overlay.id.replace('comments-', ''));
            }
        });
    });

    window.addEventListener('resize', () => {
        if (FeedApp.state.overlayAbierto) {
            closeComments(FeedApp.state.overlayAbierto);
        }
    });
    window.addEventListener('orientationchange', () => {
        if (FeedApp.state.overlayAbierto) {
            closeComments(FeedApp.state.overlayAbierto);
        }
    });

    FeedApp.openComments = openComments;
    FeedApp.sendInlineComment = sendInlineComment;
    FeedApp.closeComments = closeComments;
})();

// ==================== ARRASTRE PARA CERRAR COMENTARIOS ====================
(function() {
    function setupDrag(overlay) {
        const sheet = overlay.querySelector('.comments-sheet');
        if (!sheet) return;

        let startY = 0;
        let currentY = 0;
        let dragging = false;
        let isTouch = false;

        // Función para iniciar el arrastre (táctil o ratón)
        function startDrag(clientY, isTouchEvent = false) {
            startY = clientY;
            dragging = true;
            isTouch = isTouchEvent;
            sheet.style.transition = 'none'; // Quitar transición durante arrastre
            // Prevenir selección de texto
            document.body.style.userSelect = 'none';
        }

        // Función para mover
        function moveDrag(clientY) {
            if (!dragging) return;
            const diff = clientY - startY;
            if (diff > 0) {
                sheet.style.transform = `translateY(${diff}px)`;
            }
        }

        // Función para terminar arrastre
        function endDrag(clientY) {
            if (!dragging) return;
            dragging = false;
            document.body.style.userSelect = '';
            sheet.style.transition = ''; // Restaurar transición
            const diff = clientY - startY;
            if (diff > 100) {
                const id = overlay.id.split('-')[1];
                FeedApp.closeComments(id);
            } else {
                sheet.style.transform = '';
            }
        }

        // Eventos táctiles
        sheet.addEventListener('touchstart', (e) => {
            e.preventDefault();
            startDrag(e.touches[0].clientY, true);
        });

        sheet.addEventListener('touchmove', (e) => {
            if (!dragging) return;
            e.preventDefault();
            moveDrag(e.touches[0].clientY);
        });

        sheet.addEventListener('touchend', (e) => {
            if (!dragging) return;
            e.preventDefault();
            endDrag(e.changedTouches[0].clientY);
        });

        // Eventos de ratón
        sheet.addEventListener('mousedown', (e) => {
            e.preventDefault();
            startDrag(e.clientY, false);
        });

        window.addEventListener('mousemove', (e) => {
            if (!dragging || isTouch) return; // Solo si arrastramos con ratón
            e.preventDefault();
            moveDrag(e.clientY);
        });

        window.addEventListener('mouseup', (e) => {
            if (!dragging || isTouch) return;
            e.preventDefault();
            endDrag(e.clientY);
        });
    }

    function initDragOnOverlays() {
        document.querySelectorAll('.comments-overlay').forEach(overlay => {
            if (!overlay.dataset.dragInitialized) {
                setupDrag(overlay);
                overlay.dataset.dragInitialized = 'true';
            }
        });
    }

    // Exponer globalmente para usar desde otros módulos
    window.initDragOnOverlays = initDragOnOverlays;

    document.addEventListener('DOMContentLoaded', initDragOnOverlays);

    const observer = new MutationObserver(initDragOnOverlays);
    const container = document.getElementById('comments-overlays-container');
    if (container) {
        observer.observe(container, { childList: true, subtree: true });
    }
})();

// ==================== BÚSQUEDA ====================
(function() {
    const searchInput = document.getElementById('search-input');
    if (!searchInput) return;

    let searchTimeout;
    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        searchTimeout = setTimeout(() => {
            performSearch(query);
        }, 500);
    });

    async function performSearch(query) {
        const feedContainer = document.getElementById('feed-container');
        const trigger = document.getElementById('infinite-scroll-trigger');

        feedContainer.innerHTML = '<div class="text-center my-5"><div class="spinner-border text-danger"></div></div>';

        if (window.FeedApp && FeedApp.state) {
            FeedApp.state.offset = 0;
            FeedApp.state.isFull = false;
        }

        try {
            const res = await fetch('/App/pages/feed/filtrar', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ busqueda: query, etiquetas: [] })
            });

            if (res.status === 401) {
                window.location.href = '/App/pages/login';
                return;
            }

            const data = await res.json();
            if (data.html) {
                feedContainer.innerHTML = data.html;
                if (trigger) feedContainer.appendChild(trigger);

                if (typeof bootstrap !== 'undefined') {
                    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));
                }

                if (window.FeedApp && FeedApp.state) {
                    FeedApp.state.offset = data.count;
                    if (data.count < 5) FeedApp.state.isFull = true;
                }

                initDragOnOverlays();
            } else {
                feedContainer.innerHTML = '<p class="text-center text-muted">No se encontraron recetas.</p>';
            }
        } catch (err) {
            console.error('Error en búsqueda:', err);
            feedContainer.innerHTML = '<p class="text-center text-danger">Error al buscar.</p>';
        }
    }
})();