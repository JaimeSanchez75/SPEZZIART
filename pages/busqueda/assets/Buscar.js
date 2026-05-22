(function()
{
    'use strict';
    if (!window.BuscarApp)
    {
        console.error('window.BuscarApp no está definido.');
        return;
    }
    function escapeHtml(texto)
    {
        return String(texto || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function limitarTextoMostrar(texto, limite = 45)
    {
        const limpio = String(texto || '').trim();

        if (limpio.length <= limite) return escapeHtml(limpio);

        return escapeHtml(limpio.substring(0, limite) + '...');
    }
    function inicializarTooltips(contenedor) {
        const elementos = (contenedor || document).querySelectorAll('[data-bs-toggle="tooltip"]');
        elementos.forEach(el => {
            if (!bootstrap.Tooltip.getInstance(el)) {
                new bootstrap.Tooltip(el, {
                    trigger: 'hover',
                    html: true,
                    sanitize: false,
                    container: 'body'
                });
            }
        });
    }
    const searchInput = document.getElementById('search-input');
    const resultadosContainer = document.getElementById('resultados-container');
    const resultadosHeader = document.getElementById('resultados-header');
    const trigger = document.getElementById('infinite-scroll-trigger');
    const chipsWrapper = document.getElementById('chips-wrapper');
    const extraBadge = document.getElementById('extra-chips-badge');
    const clearBtn = document.getElementById('clear-filters');
    const modal = document.getElementById('modalEtiquetas');
    const modalTagsList = document.getElementById('modal-tags-list');
    const state = window.BuscarApp.state;
    const filters = window.BuscarApp.filters;
    let currentCols = 3;
    let observer;
    let debounceTimer;
    let fetchInProgress = false;
    let mostrandoRecomendaciones = false;
    const welcomeSection = document.querySelector('.welcome-section');
    function getCsrfToken() 
    {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }
    function colsParaPantalla(deseadas)
    {
        const w = window.innerWidth;
        if (w < 768) return 1;    // móvil (<md): siempre 1
        if (w < 992) return 2;    // tablet (md→lg): siempre 2
        return deseadas;          // escritorio (≥lg): botón de grid
    }
    function aplicarGrid()
    {
        if (!resultadosContainer) return;
        const cols = colsParaPantalla(currentCols);
        resultadosContainer.classList.remove('grid-1', 'grid-2', 'grid-3', 'grid-4');
        resultadosContainer.classList.add(`grid-${cols}`);
        resultadosContainer.style.display = 'grid';
        resultadosContainer.style.gridTemplateColumns = `repeat(${cols}, 1fr)`;
        resultadosContainer.style.gap = '1.5rem';
        void resultadosContainer.offsetHeight;
    }
    function renderChips() 
    {
        if (!chipsWrapper) return;
        chipsWrapper.innerHTML = '';
        if (filters.etiquetas.length === 0) 
        {
            if (extraBadge) extraBadge.classList.add('d-none');
            if (clearBtn) clearBtn.classList.add('d-none');
            return;
        }
        if (clearBtn) clearBtn.classList.remove('d-none');
        filters.etiquetas.forEach(tag => 
        {
            const chip = document.createElement('span');
            chip.className = 'badge bg-rojoClaro texto-rojo rounded-3 d-flex align-items-center px-3 py-2';
            chip.style.maxWidth = '120px';
            chip.innerHTML = `<span class="text-truncate">${tag}</span><span class="chip-close ms-2" data-tag="${tag}" style="cursor:pointer;">&times;</span>`;
            chipsWrapper.appendChild(chip);
        });
        if (extraBadge) extraBadge.classList.add('d-none');
        document.querySelectorAll('.chip-close').forEach(btn => 
        {
            btn.addEventListener('click', () => 
            {
                const tag = btn.dataset.tag;
                filters.etiquetas = filters.etiquetas.filter(t => t !== tag);
                renderChips();
                aplicarFiltros(true);
                const chipModal = modalTagsList?.querySelector(`.chip-selectable[data-name="${tag}"]`);
                if (chipModal) 
                {
                    chipModal.classList.remove('bg-rojo', 'text-white');
                    chipModal.classList.add('bg-white', 'texto-rojo');
                }
            });
        });
    }

    function syncModalChips() 
    {
        if (!modalTagsList) return;
        modalTagsList.querySelectorAll('.chip-selectable').forEach(chip => 
        {
            const tag = chip.dataset.name;
            if (filters.etiquetas.includes(tag)) 
            {
                chip.classList.add('bg-rojo', 'text-white');
                chip.classList.remove('bg-white', 'texto-rojo');
            } 
            else 
            {
                chip.classList.remove('bg-rojo', 'text-white');
                chip.classList.add('bg-white', 'texto-rojo');
            }
        });
    }
    function mostrarError(mensaje) 
    {
        if (resultadosContainer) 
        {
            resultadosContainer.innerHTML = `<div class="col-12 text-center py-5 texto-rojo">
                <span class="material-symbols-outlined fs-1">error</span>
                <p class="mt-3">${mensaje}</p></div>`;
        }
    }
    async function cargarRecomendaciones() 
    {
        if (!resultadosContainer) return;
        resultadosContainer.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-danger"></div></div>';
        if (resultadosHeader) resultadosHeader.innerHTML = '';
        try 
        {
            const res = await fetch('/pages/buscar/recomendaciones');
            if (!res.ok) throw new Error('Error del servidor');
            const data = await res.json();
            if (data.error) throw new Error(data.error);
            if (resultadosHeader) {resultadosHeader.innerHTML = `<div><h5 class="fw-bold text-secondary">✨ Recomendaciones para ti</h5><p class="text-muted">Descubre estas recetas populares</p></div>`;}
            resultadosContainer.innerHTML = data.html || '<div class="col-12 text-muted text-center py-5">No hay recomendaciones disponibles</div>';
            mostrandoRecomendaciones = true;
            state.isFull = true;
            if (trigger) trigger.innerHTML = '';
            if (welcomeSection) welcomeSection.style.display = '';   // <-- NUEVO
            aplicarGrid();
        } 
        catch (err) 
        {
            console.error(err);
            mostrarError('Error al cargar recomendaciones. Inténtalo de nuevo.');
        }
    }
    async function fetchResultados(reset = true) 
    {
        if (!resultadosContainer) return;
        if (fetchInProgress) return;
        fetchInProgress = true;
        if (reset) 
        {
            // Eliminar cualquier contenedor de recomendaciones previo
            const prevRecWrapper = document.getElementById('recomendaciones-wrapper');
            if (prevRecWrapper) prevRecWrapper.remove();
            // Restaurar la cuadrícula original para las recetas
            resultadosContainer.style.display = 'grid';
            state.offset = 0;
            state.isFull = false;
            resultadosContainer.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-danger"></div></div>';
            if (resultadosHeader) resultadosHeader.innerHTML = '';
        } 
        else 
        {
            if (state.loading || state.isFull) 
            {
                fetchInProgress = false;
                return;
            }
            state.loading = true;
            if (trigger) trigger.innerHTML = '<div class="spinner-border text-danger d-block mx-auto my-3"></div>';
        }
        let busqueda = filters.busqueda.trim();
        let esBusquedaUsuario = busqueda.startsWith('@');
        let usuarioBuscado = esBusquedaUsuario ? busqueda.substring(1) : '';
        const url = `/pages/buscar/filtrar?offset=${state.offset}`;
        const body = JSON.stringify({
        busqueda: busqueda,
        etiquetas: filters.etiquetas,
        esfit: filters.esfit ? 1 : 0,
        csrf_token: getCsrfToken()
        });
        try 
        {
            const res = await fetch(url, 
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: body
            });
            if (!res.ok) 
            {
                let errorMsg = 'Error del servidor';
                try { const errData = await res.json(); errorMsg = errData.error || errorMsg; } catch (e) {}
                throw new Error(errorMsg);
            }
            const data = await res.json();
            if (reset) 
            {
                if (data.count === 0 && !data.userHtml) 
                {
                    // Cabecera
                    if (resultadosHeader) {
                        resultadosHeader.innerHTML = `<div><h5 class="fw-bold text-secondary">🔍 Resultados de tu búsqueda</h5><p class="text-muted">0 recetas encontradas</p></div>`;
                    }

                    // Limpiamos cualquier grid y mostramos el mensaje como bloque
                    resultadosContainer.style.display = 'block';
                    const termino = (filters.busqueda || '').trim();
                    const terminoSeguro = limitarTextoMostrar(termino, 50);
                    resultadosContainer.innerHTML = `
                        <div class="alert bg-white text-center py-4 rounded-4 sombra border-0">
                            <span class="material-symbols-outlined fs-1 d-block mb-2">search_off</span>
                            <h5 class="fw-bold">No se encontraron resultados</h5>
                            <p class="mb-0">No se encontraron resultados para "<strong>${terminoSeguro || '...'}</strong>"</p>
                        </div>
                    `;

                    // Construimos un contenedor independiente para las recomendaciones
                    const recWrapper = document.createElement('div');
                    recWrapper.id = 'recomendaciones-wrapper';
                    recWrapper.className = 'mt-4';
                    // Lo insertamos justo después del contenedor de resultados
                    resultadosContainer.parentNode.insertBefore(recWrapper, resultadosContainer.nextSibling);

                    // Mostramos un spinner mientras cargan las recomendaciones
                    recWrapper.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-danger"></div></div>';

                    try {
                        const resRecom = await fetch('/pages/buscar/recomendaciones');
                        if (resRecom.ok) {
                            const dataRecom = await resRecom.json();
                            if (dataRecom.html && dataRecom.html.trim()) {
                                // Encabezado + grid propio para las tarjetas de recomendación
                                recWrapper.innerHTML = `
                                    <h5 class="fw-bold text-secondary mb-3">✨ Te recomendamos...</h5>
                                    <div style="display: grid; grid-template-columns: repeat(${colsParaPantalla(currentCols)}, 1fr); gap: 1.5rem;">
                                        ${dataRecom.html}
                                    </div>
                                `;
                            } else {
                                recWrapper.innerHTML = '<div class="text-muted text-center py-4">No hay recomendaciones disponibles en este momento.</div>';
                            }
                        } else {
                            throw new Error('Error del servidor');
                        }
                    } catch (err) {
                        console.error(err);
                        recWrapper.innerHTML = '<div class="text-muted text-center py-4">No se pudieron cargar las recomendaciones.</div>';
                    }

                    mostrandoRecomendaciones = true;
                    state.isFull = true;
                    if (trigger) trigger.innerHTML = '';
                    if (welcomeSection) welcomeSection.style.display = '';
                    fetchInProgress = false;
                    return;
                }
                let headerHtml = '';
                if (esBusquedaUsuario && data.userHtml) 
                {
                    headerHtml = `<div class="alert bg-white rounded-4 sombra border-0 d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined">person_search</span>
                        Mostrando recetas de <strong>@${limitarTextoMostrar(usuarioBuscado, 30)}</strong>
                        <button class="border border-rojo bg-white texto-rojo py-1 px-3 rounded-3 texto fw-medium ms-auto" onclick="window.BuscarApp.clearFilters()">Limpiar</button>
                    </div>`;
                } 
                else if (filters.busqueda || filters.etiquetas.length > 0) {headerHtml = `<div><h5 class="fw-bold text-secondary">🔍 Resultados de tu búsqueda</h5><p class="text-muted">${data.count} receta(s) encontrada(s)</p></div>`;} 
                else {headerHtml = `<div><h5 class="fw-bold text-secondary">🍳 Descubre nuevas recetas</h5><p class="text-muted">Inspírate con estas delicias</p></div>`;}
                if (resultadosHeader) resultadosHeader.innerHTML = headerHtml;
                resultadosContainer.innerHTML = '';
                if (data.userHtml && data.userHtml.trim().length > 0) {resultadosContainer.innerHTML = data.userHtml;}
                resultadosContainer.insertAdjacentHTML('beforeend', data.html);
                state.offset = data.count;
                mostrandoRecomendaciones = false;
                if (welcomeSection) welcomeSection.style.display = 'none';   // <-- NUEVO
                aplicarGrid();
            } 
            else 
            {
                if (data.html && data.html.trim()) 
                {
                    resultadosContainer.insertAdjacentHTML('beforeend', data.html);
                    state.offset += state.limit;
                    aplicarGrid();
                } 
                else 
                {
                    state.isFull = true;
                    if (trigger) trigger.innerHTML = '<p class="text-muted text-center mt-3">No hay más recetas</p>';
                }
            }
            if (typeof bootstrap !== 'undefined')
            {
                document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el =>
                {
                    if (!el.dataset.popoverInitialized)
                    {
                        new bootstrap.Popover(el, { trigger: 'hover focus', html: true, sanitize: false });
                        el.dataset.popoverInitialized = 'true';
                    }
                });
                inicializarTooltips(resultadosContainer);
            }
        } 
        catch (err) 
        {
            console.error(err);
            if (reset) {mostrarError(err.message || 'Error al cargar los resultados.');} 
            else {if (trigger) trigger.innerHTML = `<p class="texto-rojo text-center mt-3 small">Error al cargar más. Reintenta.</p>`;}
        } 
        finally 
        {
            if (!reset) state.loading = false;
            fetchInProgress = false;
            if (!state.isFull && trigger && !reset) trigger.innerHTML = '';
        }
    }

    function aplicarFiltros(reset = true) 
    {
        fetchResultados(reset);
        if (observer) observer.disconnect();
        setupInfiniteScroll();
    }

    function setupInfiniteScroll() 
    {
        if (!trigger) return;
        if (observer) observer.disconnect();
        observer = new IntersectionObserver(entries => {if (entries[0].isIntersecting) fetchResultados(false);}, { threshold: 0.1 });
        observer.observe(trigger);
    }
    function cambiarColumnas(cols) 
    {
        currentCols = cols;
        aplicarGrid();
        localStorage.setItem('buscar-columnas', cols);
        document.querySelectorAll('[data-cols]').forEach(btn => {
            const activo = parseInt(btn.dataset.cols) === cols;
            btn.classList.toggle('active', activo);
            btn.classList.toggle('bg-rojoClaro', activo);
            btn.classList.toggle('texto-rojo', activo);
            btn.classList.toggle('border-rojo', activo);
            btn.classList.toggle('bg-white', !activo);
            btn.classList.toggle('text-secondary', !activo);
            btn.classList.toggle('border-gris', !activo);
        });
    }
    window.BuscarApp.clearFilters = function() 
    {
        filters.etiquetas = [];
        filters.busqueda = '';
        if (searchInput) searchInput.value = '';
        renderChips();
        syncModalChips();
        state.offset = 0;
        state.isFull = false;
        aplicarFiltros(true);
    };
    document.addEventListener('DOMContentLoaded', () => 
    {
        const savedCols = localStorage.getItem('buscar-columnas') || '3';
        cambiarColumnas(parseInt(savedCols));
        document.querySelectorAll('[data-cols]').forEach(btn => {btn.addEventListener('click', () => cambiarColumnas(parseInt(btn.dataset.cols)));});
        let resizeTimer;
        window.addEventListener('resize', () => { clearTimeout(resizeTimer); resizeTimer = setTimeout(aplicarGrid, 150); });
        if (typeof bootstrap !== 'undefined') inicializarTooltips();
        if (searchInput) 
        {
            searchInput.addEventListener('input', () => 
            {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => 
                {
                    filters.busqueda = searchInput.value.trim();
                    aplicarFiltros(true);
                }, 500);
            });
        }
        if (modalTagsList) 
        {
            modalTagsList.addEventListener('click', e => 
            {
                const chip = e.target.closest('.chip-selectable');
                if (!chip) return;
                e.preventDefault();
                const tag = chip.dataset.name;
                if (filters.etiquetas.includes(tag)) 
                {
                    filters.etiquetas = filters.etiquetas.filter(t => t !== tag);
                    chip.classList.remove('bg-rojo', 'text-white');
                    chip.classList.add('bg-white', 'texto-rojo');
                } 
                else 
                {
                    filters.etiquetas.push(tag);
                    chip.classList.remove('bg-white', 'texto-rojo');
                    chip.classList.add('bg-rojo', 'text-white');
                }
                renderChips();
                aplicarFiltros(true);
            });
        }
        if (clearBtn) {clearBtn.addEventListener('click', () => {window.BuscarApp.clearFilters();});}
        if (modal) {modal.addEventListener('show.bs.modal', () => {syncModalChips();});}
    
        const params = new URLSearchParams(window.location.search);

        if (params.get('esfit') === '1') 
        {
            filters.esfit = true;

            if (!filters.etiquetas.includes('Fit')) 
            {
                filters.etiquetas.push('Fit');
            }
        }
        else 
        {
            filters.esfit = false;
        }
        setupInfiniteScroll();
        renderChips();
        aplicarGrid();
    });
})();
