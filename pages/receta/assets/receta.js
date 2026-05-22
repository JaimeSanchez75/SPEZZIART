window.abrirRecetaModal = async function(recetaId) {
    const modalEl = document.getElementById('recipeModal');
    if (!modalEl) return;

    const modal = new bootstrap.Modal(modalEl);
    const content = document.getElementById('recipeModalContent');
    content.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-danger"></div><p>Cargando receta...</p></div>';
    modal.show();

    try {
        const res = await fetch(`/api/receta/${recetaId}`);
        const data = await res.json();
        if (data.success) {
            content.innerHTML = renderRecetaAislada(data.receta);
            initModalSidebar(data.receta.ID_Receta);
        } else {
            content.innerHTML = `<div class="alert alert-danger m-4">${escapeHtml(data.error)}</div>`;
        }
    } catch (e) {
        console.error(e);
        content.innerHTML = `<div class="alert alert-danger m-4">Error de conexión</div>`;
    }
};

function initModalSidebar(recetaId) {
    const sidebar = document.querySelector('#modalRecetaUnica .modal-sidebar');
    if (!sidebar) return;

    const likeBtn = sidebar.querySelector('.like-btn');
    const likeCount = sidebar.querySelector('.like-count');
    const likeIcon = sidebar.querySelector('.like-icon');
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

    // Me gusta
    if (likeBtn) {
        likeBtn.addEventListener('click', async (e) => {
            e.stopPropagation();
            if (!window.isLoggedIn) { window.location.href = '/pages/login'; return; }
            try {
                const res = await fetch(`/api/receta/like/${recetaId}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ csrf_token: csrfToken })
                });
                if (res.status === 401) { window.location.href = '/pages/login'; return; }
                const data = await res.json();
                if (data.status === 'success') {
                    likeCount.textContent = data.newLikes;
                    likeIcon.classList.toggle('fill-icon', data.action === 'added');
                }
            } catch (err) { console.error('Error en like:', err); }
        });
    }

    // Comentarios → abre el overlay del feed encima del modal
    const commentBtn = sidebar.querySelector('.comment-btn');
    if (commentBtn) {
        commentBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (!window.isLoggedIn) { window.location.href = '/pages/login'; return; }
            // Desactivar el focus trap del modal para permitir interacción con el overlay
            const recipeModal = document.getElementById('recipeModal');
            const modalInstance = recipeModal ? bootstrap.Modal.getInstance(recipeModal) : null;
            if (modalInstance && modalInstance._focustrap) {
                modalInstance._focustrap.deactivate();
            }
            // Abrir comentarios
            if (typeof FeedApp !== 'undefined' && FeedApp.openComments) {
                FeedApp.openComments(recetaId);
            }
            // Restaurar el focus trap cuando se cierren los comentarios
            const originalClose = FeedApp.closeComments;
            FeedApp.closeComments = function() 
            {
                // Re-activar focus trap
                if (modalInstance && modalInstance._focustrap) {modalInstance._focustrap.activate();}
                // Llamar a la función original
                originalClose.apply(this, arguments);
                // Restaurar la función original para futuras aperturas
                FeedApp.closeComments = originalClose;
            };
        });
    }
    document.addEventListener('click', function(e) 
    {
        const btn = e.target.closest('[data-requiere-login="true"]');

        if (!btn) return;

        if (!window.isLoggedIn) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();

            window.location.href = '/pages/login';
        }
    }, true);
    // Guardar
    const saveBtn = sidebar.querySelector('.save-btn');
    if (saveBtn) 
    {
        saveBtn.addEventListener('click', (e) => 
        {
            e.stopPropagation();
            const hiddenBtn = document.createElement('button');
            hiddenBtn.setAttribute('data-bs-toggle', 'modal');
            hiddenBtn.setAttribute('data-bs-target', '#saveModal');
            hiddenBtn.setAttribute('data-id', recetaId);
            document.body.appendChild(hiddenBtn);
            hiddenBtn.click();
            document.body.removeChild(hiddenBtn);
        });
    }
    // Reportar
    const reportBtn = sidebar.querySelector('.report-btn');
    if (reportBtn) 
    {
        reportBtn.addEventListener('click', (e) => 
        {
            e.stopPropagation();
            const hiddenBtn = document.createElement('button');
            hiddenBtn.setAttribute('data-bs-toggle', 'modal');
            hiddenBtn.setAttribute('data-bs-target', '#reportModal');
            hiddenBtn.setAttribute('data-id', recetaId);
            hiddenBtn.setAttribute('data-report-type', 'receta');
            document.body.appendChild(hiddenBtn);
            hiddenBtn.click();
            document.body.removeChild(hiddenBtn);
        });
    }
}
function renderRecetaAislada(receta) 
{
    const formatearNumero = (valor) => 
    {
        const num = parseFloat(valor) || 0;
        return num % 1 === 0 ? num.toString() : num.toFixed(1);
    };
    const normalizarImagen = (imagen) => 
    {
        if (!imagen) return '';
        if (imagen.startsWith('http')) return imagen;
        if (imagen.startsWith('/')) return imagen;
        return '/' + imagen;
    };
    const imagenes = Array.isArray(receta.Imagenes) && receta.Imagenes.length
        ? receta.Imagenes.map((img) => normalizarImagen(String(img).trim())).filter(Boolean)
        : String(receta.Imagen || '')
            .split(',')
            .map((img) => normalizarImagen(img.trim()))
            .filter(Boolean);

    const portadaImagen = imagenes[0] || '/uploads/NoImg.jpg';  
    const imagenesSecundarias = imagenes.slice(1);
    const tieneImagen = true; 
    const esFit = receta.EsFit == 1;
    const dioLike = receta.DioLike == 1;
    const fotoAutor = receta.FotoPerfil || receta.Foto_Perfil || receta.UserFoto || null;
    const nombreAutor = escapeHtml(receta.Name || receta.Username || '');
    const avatarHtml = fotoAutor ? `<img src="${escapeHtml(normalizarImagen(fotoAutor))}" class="recipe-author-avatar" alt="${nombreAutor}" onerror="this.onerror=null;this.style.display='none'">` : `<span class="recipe-author-initial">${(receta.Name || receta.Username || '?')[0].toUpperCase()}</span>`;
    const likes = receta.Likes || 0;
    const numComentarios = receta.TotalComentarios || 0;
    // ========== CÁLCULO NUTRICIONAL CORREGIDO ==========
    const nutricion = { calorias: 0, proteina: 0, carbohidratos: 0, grasas: 0 };
    if (receta.ingredientes) {
        receta.ingredientes.forEach((ing) => {
            // Cantidad real (número)
            const cantidad = parseFloat(ing.Cantidad) || 0;
            const factor = cantidad / 100;

            // Valores ajustados por la cantidad real
            ing.__calorias      = (parseFloat(ing.Calorias)      || 0) * factor;
            ing.__proteina      = (parseFloat(ing.Proteina)      || 0) * factor;
            ing.__carbohidratos = (parseFloat(ing.Carbohidratos) || 0) * factor;
            ing.__grasas        = (parseFloat(ing.Grasas)        || 0) * factor;

            // Texto con cantidad + unidad (si Unidad_Base existe)
            ing._cantidadConUnidad = ing.Cantidad && ing.Unidad_Base
                ? `${ing.Cantidad} ${ing.Unidad_Base}`
                : (ing.Cantidad || '');

            // Acumular totales
            nutricion.calorias      += ing.__calorias;
            nutricion.proteina      += ing.__proteina;
            nutricion.carbohidratos += ing.__carbohidratos;
            nutricion.grasas        += ing.__grasas;
        });
    }
    const mostrarNutricion = esFit || Object.values(nutricion).some((v) => v > 0);
    return `
        <div id="modalRecetaUnica">
            <div class="container-fluid h-100">
                <div class="row g-0 h-100">
                    <!-- Contenido principal -->
                    <div class="col-12 overflow-auto p-3 p-md-4 recipe-modal-main">
                        <div class="card shadow-sm border-0 glass-card">
                            <div class="card-body p-3 p-md-4 py-4">
                                <div class="recipe-overview">
                                    ${tieneImagen ? `
                                    <aside class="recipe-media">
                                        <div class="recipe-img-frame">
                                            <img src="${escapeHtml(portadaImagen)}" class="recipe-img" alt="Portada de la receta" onerror="this.onerror=null; this.src='/uploads/NoImg.jpg'">
                                        </div>
                                    </aside>
                                    ` : ''}
                                    <div>
                                        <div class="recipe-hero">
                                            <div>
                                                <h2 class="fw-bold mb-2">${escapeHtml(receta.Titulo)}</h2>
                                                <div class="recipe-meta">
                                                    <div class="recipe-meta-pill recipe-meta-author">
                                                        ${avatarHtml} ${nombreAutor}
                                                    </div>
                                                    <div class="recipe-meta-pill">
                                                        <span class="material-symbols-outlined">schedule</span> ${receta.Tiempo || 0} min
                                                    </div>
                                                    <div class="recipe-meta-pill">
                                                        <span class="material-symbols-outlined">restaurant</span> ${receta.Porciones || 0} porciones
                                                    </div>
                                                </div>
                                                ${receta.etiquetas?.length ? `
                                                <div class="recipe-tags">
                                                    ${receta.etiquetas.map((e) => `<span class="recipe-tag">#${escapeHtml(e.Nombre)}</span>`).join('')}
                                                </div>
                                                ` : ''}
                                            </div>
                                            ${esFit ? `<div class="recipe-fit-badge">FIT</div>` : ''}
                                        </div>
                                        ${mostrarNutricion ? `
                                        <div class="row g-3 mb-4 mt-1">
                                            <div class="col-6 col-md-3"><div class="nutrition-box"><div class="text-muted small">Calorías</div><div class="nutrition-value">${formatearNumero(nutricion.calorias)} kcal</div></div></div>
                                            <div class="col-6 col-md-3"><div class="nutrition-box"><div class="text-muted small">Proteína</div><div class="nutrition-value">${formatearNumero(nutricion.proteina)} g</div></div></div>
                                            <div class="col-6 col-md-3"><div class="nutrition-box"><div class="text-muted small">Carbohidratos</div><div class="nutrition-value">${formatearNumero(nutricion.carbohidratos)} g</div></div></div>
                                            <div class="col-6 col-md-3"><div class="nutrition-box"><div class="text-muted small">Grasas</div><div class="nutrition-value">${formatearNumero(nutricion.grasas)} g</div></div></div>
                                        </div>
                                        ` : ''}
                                    </div>
                                </div>
                                ${receta.DescripcionVisible ? `
                                <div class="description-card mb-4">
                                    <h5 class="section-title">Descripción</h5>
                                    <p>${escapeHtml(receta.DescripcionVisible)}</p>
                                </div>
                                ` : ''}
                                ${imagenesSecundarias.length ? `
                                <div class="description-card mb-4">
                                    <h5 class="section-title mb-3">Más fotos</h5>
                                    <div class="recipe-gallery-grid">
                                        ${imagenesSecundarias.map((img) => `
                                        <figure class="recipe-gallery-card mb-0">
                                            <img src="${escapeHtml(img)}" class="recipe-gallery-thumb" alt="Imagen adicional de la receta" onerror="this.onerror=null; this.src='/uploads/NoImg.jpg'">
                                        </figure>
                                        `).join('')}
                                    </div>
                                </div>
                                ` : ''}
                                ${receta.ingredientes?.length ? `
                                <div class="ingredients-card mb-4">
                                    <h5 class="section-title">Ingredientes</h5>
                                    <div class="ingredients-grid">
                                        ${receta.ingredientes.map((ing) => `
                                        <div class="ingredient-card">
                                            <div class="ingredient-head">
                                                <div class="ingredient-name">${escapeHtml(ing.Nombre)}</div>
                                                ${ing._cantidadConUnidad ? `<div class="ingredient-qty">${escapeHtml(ing._cantidadConUnidad)}</div>` : ''}
                                            </div>
                                            <details class="ingredient-nutrition-toggle">
                                                <summary>Valores nutricionales <span class="material-symbols-outlined">expand_more</span></summary>
                                                <div class="ingredient-nutrition-body">
                                                    <div class="ingredient-macros">
                                                        <div class="macro-chip"><div class="macro-chip-label">Calorías</div><div>${formatearNumero(ing.__calorias)} kcal</div></div>
                                                        <div class="macro-chip"><div class="macro-chip-label">Proteína</div><div>${formatearNumero(ing.__proteina)} g</div></div>
                                                        <div class="macro-chip"><div class="macro-chip-label">Carbohidratos</div><div>${formatearNumero(ing.__carbohidratos)} g</div></div>
                                                        <div class="macro-chip"><div class="macro-chip-label">Grasas</div><div>${formatearNumero(ing.__grasas)} g</div></div>
                                                    </div>
                                                </div>
                                            </details>
                                        </div>
                                        `).join('')}
                                    </div>
                                </div>
                                ` : ''}
                                ${receta.Pasos?.length ? `
                                <div class="steps-card mb-5">
                                    <h5 class="section-title">Pasos</h5>
                                    <div class="steps-list">
                                        ${receta.Pasos.map((paso, idx) => `
                                        <div class="step-card">
                                            <div class="step-number">${idx + 1}</div>
                                            <div>${escapeHtml(paso)}</div>
                                        </div>
                                        `).join('')}
                                    </div>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Barra lateral flotante -->
            <div class="modal-sidebar d-flex flex-lg-column align-items-center justify-content-center gap-3">
                <button class="btn btn-outline-danger like-btn d-flex flex-column align-items-center p-2">
                    <span class="material-symbols-outlined like-icon ${dioLike ? 'fill-icon' : ''}">favorite</span>
                    <span class="like-count small fw-bold">${likes}</span>
                </button>
                <button class="btn btn-outline-secondary comment-btn d-flex flex-column align-items-center p-2" data-requiere-login="true">
                    <span class="material-symbols-outlined">chat_bubble</span>
                    <span class="small fw-bold">${numComentarios}</span>
                </button>
                <button class="btn btn-outline-secondary save-btn d-flex flex-column align-items-center  p-2" data-requiere-login="true">
                    <span class="material-symbols-outlined">bookmark</span>
                    <span class="small">Guardar</span>
                </button>
                ${!window.isIndividualView ? `
                <button class="btn btn-outline-secondary report-btn d-flex flex-column align-items-center p-2" data-requiere-login="true">
                    <span class="material-symbols-outlined">flag</span>
                    <span class="small">Reportar</span>
                </button>
                ` : ''}
            </div>
        </div>
    `;
}
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
