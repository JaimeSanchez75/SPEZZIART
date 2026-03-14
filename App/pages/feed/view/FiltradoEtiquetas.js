document.addEventListener('DOMContentLoaded', () => {
    const selectedTags = new Set();
    const searchInput = document.getElementById('search-input');
    const chipsWrapper = document.getElementById('chips-wrapper');
    const extraBadge = document.getElementById('extra-chips-badge');
    const clearBtn = document.getElementById('clear-filters');
    const modalButtons = document.querySelectorAll('.chip-selectable');
    let searchTimeout;

    const getLimit = () => {
        if(window.innerWidth < 320) return 1;
        if(window.innerWidth < 768) return 2;
        if(window.innerWidth < 992) return 4;
        return 5;
    };

    function renderChips() {
        chipsWrapper.innerHTML = '';
        const tagsArray = Array.from(selectedTags);
        const currentLimit = getLimit();
        const visibleTags = tagsArray.slice(0, currentLimit);
        const extraCount = tagsArray.length - currentLimit;

        visibleTags.forEach(tagName => {
            const chip = document.createElement('div');
            chip.className = 'btn btn-sm rounded-pill chip-active d-flex align-items-center gap-2';
            chip.innerHTML = `${tagName} <span class="chip-remove" style="cursor:pointer" data-name="${tagName}"><span class="material-symbols-outlined" style="font-size:16px">close</span></span>`;
            chipsWrapper.appendChild(chip);
        });

        if (extraCount > 0) {
            extraBadge.textContent = `+${extraCount}`;
            extraBadge.classList.remove('d-none');
        } else {
            extraBadge.classList.add('d-none');
        }

        if (clearBtn) {
            tagsArray.length > 0 ? clearBtn.classList.remove('d-none') : clearBtn.classList.add('d-none');
        }

        modalButtons.forEach(btn => {
            selectedTags.has(btn.dataset.name) ? btn.classList.add('active') : btn.classList.remove('active');
        });

        fetchFilteredFeed(tagsArray);
    }

    // --- Lógica del Buscador ---
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim();
        
        // Si hay texto, limpiamos las etiquetas para dar prioridad a la búsqueda global
        if (query.length > 0) {
            selectedTags.clear();
            // Limpieza visual inmediata sin disparar fetchFilteredFeed todavía
            chipsWrapper.innerHTML = '';
            modalButtons.forEach(btn => btn.classList.remove('active'));
            if (clearBtn) clearBtn.classList.add('d-none');
            extraBadge.classList.add('d-none');
        }

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            fetchFilteredFeed(Array.from(selectedTags));
        }, 400);
    });

    // --- Servidor ---
    async function fetchFilteredFeed(tags) {
        const busqueda = searchInput.value.trim();
        const formData = new FormData();
        
        if (busqueda.length > 0) {
            formData.append('busqueda', busqueda);
        } else {
            tags.forEach(tag => formData.append('etiquetas[]', tag));
        }

        try {
            const response = await fetch('/App/pages/feed/filtrar', {
                method: 'POST',
                body: formData
            });
            const recetas = await response.json();
            renderFeed(recetas);
        } catch (error) {
            console.error("Error filtrando el feed:", error);
        }
    }

    // --- Listeners de Etiquetas ---
    window.addEventListener('resize', renderChips);
    modalButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            if (!selectedTags.has(btn.dataset.name)) {
                searchInput.value = ''; // Al elegir etiqueta, limpiamos buscador
                selectedTags.add(btn.dataset.name);
                renderChips();
            }
        });
    });

    chipsWrapper.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.chip-remove');
        if (removeBtn) {
            selectedTags.delete(removeBtn.dataset.name);
            renderChips();
        }
    });

    if (clearBtn) {
        clearBtn.addEventListener('click', () => {
            selectedTags.clear();
            renderChips();
        });
    }

    function renderFeed(recetas) {
        const container = document.getElementById('feed-container');
        const query = searchInput.value.toLowerCase();

        if (recetas.length === 0) {
            container.innerHTML = '<p class="text-center mt-5">No hemos encontrado nada que coincida.</p>';
            return;
        }

        let html = "";
        if (query && recetas[0].Username.toLowerCase().includes(query)) {
            html += `<div class="alert alert-light border-0 shadow-sm rounded-4 mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="bg-danger text-white rounded-circle d-flex align-items-center justify-content-center" style="width:50px; height:50px;">
                                <span class="material-symbols-outlined">person</span>
                            </div>
                            <div>
                                <h6 class="m-0 fw-bold">Publicaciones de <a href="/App/pages/perfil/${recetas[0].ID_Creador}" class="text-decoration-none text-danger">
                                @${recetas[0].Username}
                            </a></h6>
                                <small class="text-muted">Hemos encontrado recetas de este chef</small>
                            </div>
                        </div>
                    </div>`;
        }

        container.innerHTML = html + recetas.map(r => `
            <div class="card feed-card mb-4 p-3 border-0 shadow-sm rounded-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <a href="/App/pages/perfil/${r.ID_Creador}" class="text-decoration-none">
                        <div class="username text-danger fw-bold">👤 @${r.Username}</div>
                    </a>
                    <div class="text-muted small">${r.FechaCreacion}</div>
                </div>
                <div class="mb-2">
                    ${r.EtiquetasNombres ? r.EtiquetasNombres.split(',').map(tag => `<span class="badge badge-tag">${tag}</span>`).join(' ') : ''}
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        ${r.Imagen ? `<img src="/App/uploads/${r.Imagen}" class="feed-img">` : `<div class="feed-img bg-light d-flex align-items-center justify-content-center text-muted border">Sin imagen</div>`}
                    </div>
                    <div class="col-md-8 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold mb-1">${r.Titulo}</h5>
                            <p class="text-muted small">${r.Descripcion.substring(0, 150)}...</p>
                        </div>
                        <div class="d-flex justify-content-end gap-3">
                            <span class="material-symbols-outlined cursor-pointer">favorite</span>
                            <span class="material-symbols-outlined cursor-pointer">chat</span>
                            <span class="material-symbols-outlined cursor-pointer">bookmark</span>
                        </div>
                    </div>
                </div>
            </div>`).join('');
    }
});