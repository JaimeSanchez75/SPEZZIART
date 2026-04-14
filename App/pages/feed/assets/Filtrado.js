//==============================================================
// Filtrado.js - Chips dinámicos de etiquetas y modal de filtros
//==============================================================
//--------------------------------------------------------
//| Última edición: 2026-04-08 por Jaime Sánchez Soteras |
//--------------------------------------------------------
/* Lógica para manejar los chips dinámicos de etiquetas y el modal de filtros */
document.addEventListener('DOMContentLoaded', function() 
{
    const clearBtn = document.getElementById('clear-filters');
    const modalTagsList = document.getElementById('modal-tags-list');
    const modal = document.getElementById('modalEtiquetas');

    function getEtiquetas() {return FeedApp.filters.etiquetas || [];}

    function setEtiquetas(tags) {FeedApp.filters.etiquetas = tags;}

    // Función para renderizar los chips de etiquetas en el feed
    window.renderChips = function() 
    {
        const wrapperDesktop = document.getElementById('chips-wrapper');
        const extraDesktop = document.getElementById('extra-chips-badge');
        const wrapperMobile = document.getElementById('chips-wrapper-mobile');
        const extraMobile = document.getElementById('extra-chips-badge-mobile');
        const tags = getEtiquetas();

        const renderInContainer = (wrapper, extraBadge) => 
        {
            if (!wrapper) return;
            wrapper.innerHTML = '';
            if (tags.length === 0) { if (extraBadge) extraBadge.classList.add('d-none'); return;}
            const container = wrapper.closest('.etiquetas-filtro');
            if (!container) return;

            const isMobile = window.innerWidth <= 768;
            const chipMaxWidth = isMobile ? 100 : 140;

            const createChipElement = (tag, includeClose = true) => // Crea una etiqueta  botón de cierre
            {
                // Contenido de las etiquetas, con estilos para manejar desbordamientos y el botón de cierre.
                const chip = document.createElement('span');
                chip.className = 'badge bg-danger rounded-pill d-flex align-items-center px-3 py-2';
                chip.style.cssText = `
                    max-width: ${chipMaxWidth}px;
                    overflow: hidden;
                    white-space: nowrap;
                    flex-shrink: 0;
                    padding: 5px 12px;
                    display: inline-flex;
                    align-items: center;
                `;
                const textSpan = document.createElement('span');
                textSpan.className = 'chip-text';
                textSpan.style.cssText = `
                    overflow: hidden;
                    text-overflow: ellipsis;
                    white-space: nowrap;
                    flex: 1 1 auto;
                    min-width: 0;
                `;
                textSpan.textContent = tag;
                chip.appendChild(textSpan);
                if (includeClose) 
                {
                    const closeSpan = document.createElement('span');
                    closeSpan.className = 'chip-close cursor-pointer';
                    closeSpan.setAttribute('data-tag', tag);
                    closeSpan.style.cssText = `
                        margin-left: 6px;
                        font-weight: bold;
                        cursor: pointer;
                        flex-shrink: 0;
                    `;
                    closeSpan.innerHTML = '&times;';
                    chip.appendChild(closeSpan);
                }
                return chip;
            };
            // Cálculo de cuántas etiquetas caben
            const measureWrapper = document.createElement('div');
            measureWrapper.style.cssText = `
                position: absolute;
                visibility: hidden;
                display: flex;
                flex-wrap: nowrap;
                gap: ${getComputedStyle(wrapper).gap || '8px'};
                width: auto;
                white-space: nowrap;
            `;
            document.body.appendChild(measureWrapper);
            const testChips = tags.map(tag => createChipElement(tag, true));
            testChips.forEach(chip => measureWrapper.appendChild(chip));
            measureWrapper.offsetHeight;
            const addButton = container.querySelector('.btn-add-tag');
            const addButtonWidth = addButton ? addButton.offsetWidth : 42;
            let extraBadgeWidth = 0;
            if (extraBadge && tags.length > 0) 
            {
                const testBadge = document.createElement('span');
                testBadge.className = 'badge rounded-pill bg-secondary';
                testBadge.textContent = '+99';
                testBadge.style.cssText = 'position:absolute;visibility:hidden;white-space:nowrap;';
                document.body.appendChild(testBadge);
                extraBadgeWidth = testBadge.offsetWidth;
                document.body.removeChild(testBadge);
            }
            const gap = parseInt(getComputedStyle(wrapper).gap) || 8;
            const containerStyle = getComputedStyle(container);
            const paddingLeft = parseFloat(containerStyle.paddingLeft) || 0;
            const paddingRight = parseFloat(containerStyle.paddingRight) || 0;
            const availableWidth = container.clientWidth - paddingLeft - paddingRight - addButtonWidth - gap * 2 - extraBadgeWidth;
            let totalWidth = 0, visibleCount = 0;
            for (let i = 0; i < testChips.length; i++) 
            {
                const chipWidth = testChips[i].offsetWidth + gap;
                if (totalWidth + chipWidth <= availableWidth) { totalWidth += chipWidth; visibleCount++;} else break; //Esto para evitar desbordamientos.
            }
            document.body.removeChild(measureWrapper);
            if (visibleCount === 0 && tags.length > 0) visibleCount = 1;

            for (let i = 0; i < visibleCount; i++) {wrapper.appendChild(createChipElement(tags[i], true));}

            const hiddenCount = tags.length - visibleCount;
            if (extraBadge) 
            {
                if (hiddenCount > 0) { extraBadge.textContent = `+${hiddenCount}`; extraBadge.classList.remove('d-none');} 
                else { extraBadge.classList.add('d-none');}
            }
            wrapper.offsetHeight; 
            wrapper.querySelectorAll('.chip-close').forEach(btn => 
            { btn.addEventListener('click', e => { e.stopPropagation(); window.eliminarEtiqueta(btn.dataset.tag);});});

            requestAnimationFrame(() => 
            {
                const maxAllowedWidth = container.clientWidth - paddingLeft - paddingRight - addButtonWidth - gap - (extraBadge ? extraBadge.offsetWidth : 0);
                if (wrapper.scrollWidth > maxAllowedWidth) 
                {
                    const chips = wrapper.children;
                    if (chips.length > 0) 
                    {
                        chips[chips.length - 1].remove();
                        const newHidden = tags.length - (chips.length - 1);
                        if (extraBadge) { extraBadge.textContent = `+${newHidden}`; extraBadge.classList.remove('d-none');}
                    }
                }
            });
        };
        renderInContainer(wrapperDesktop, extraDesktop);
        renderInContainer(wrapperMobile, extraMobile);
    };

    window.eliminarEtiqueta = function(tag) 
    {
        let tags = getEtiquetas().filter(t => t !== tag);
        setEtiquetas(tags);
        window.renderChips();
        window.actualizarPorFiltros();

        const chipInModal = document.querySelector(`#modal-tags-list .chip-selectable[data-name="${tag}"]`);
        if (chipInModal) { chipInModal.classList.remove('active', 'btn-danger', 'text-white'); chipInModal.classList.add('btn-outline-danger');}
        if (clearBtn) clearBtn.classList.toggle('d-none', tags.length === 0);
    };

    // Manejo de selección de etiquetas en el modal de filtros
    if (modalTagsList) 
    {
        modalTagsList.addEventListener('click', function(e) 
        {
            const chip = e.target.closest('.chip-selectable');
            if (!chip) return;
            const tagName = chip.dataset.name;
            let tags = getEtiquetas();

            chip.classList.toggle('active');
            chip.classList.toggle('btn-outline-danger');
            chip.classList.toggle('btn-danger');
            chip.classList.toggle('text-white');

            if (tags.includes(tagName)) {tags = tags.filter(t => t !== tagName);} 
            else {tags.push(tagName);}
            setEtiquetas(tags);
            if (clearBtn) clearBtn.classList.toggle('d-none', tags.length === 0);
            window.renderChips();
        });
    }
    if (clearBtn) 
    {
        clearBtn.addEventListener('click', function() 
        {
            document.querySelectorAll('#modal-tags-list .chip-selectable').forEach(chip => {chip.classList.remove('active', 'btn-danger', 'text-white'); chip.classList.add('btn-outline-danger');});
            setEtiquetas([]);
            clearBtn.classList.add('d-none');
            window.renderChips();
            window.actualizarPorFiltros();
        });
    }
    const aplicarBtn = modal?.querySelector('.btn-danger[data-bs-dismiss="modal"]');
    // Al cerrar el modal, aplicar los filtros automáticamente.
    if (aplicarBtn) {aplicarBtn.addEventListener('click', function() {window.actualizarPorFiltros();});}
    if (modal) {modal.addEventListener('hidden.bs.modal', function() {window.actualizarPorFiltros();});}

    window.actualizarEtiquetas = window.renderChips; 
    // Re-renderizar etiquetas al cambiar el tamaño de la ventana para asegurar que se ajusten correctamente.
    let resizeTimer;
    window.addEventListener('resize', () => { clearTimeout(resizeTimer); resizeTimer = setTimeout(() => {if (getEtiquetas().length > 0) window.renderChips();}, 150);});
    window.renderChips(); //Renderizado inicial de etiquetas al cargar la página.
});