"use strict";
document.addEventListener('DOMContentLoaded', function ()
{
    // ── Filtrado client-side (feedback instantáneo, sin recarga) ──
    function filtrarColecciones(t)
    {
        const chips = document.querySelectorAll('.collection-chip');
        chips.forEach(function (chip)
        {
            const nombre = (chip.querySelector('.collection-chip__name')?.textContent ?? '').toLowerCase();
            chip.style.display = (t === '' || nombre.includes(t)) ? '' : 'none';
        });
        if (t !== '' && chips.length > 0)
        {
            document.getElementById('collections-list-collapse')?.classList.add('show');
        }
    }

    // ── Búsqueda server-side sin recarga (fetch + replace) ──
    let fetchController = null;
    let fetchTimer      = null;
    const recipeSection = document.querySelector('.recipe-section');

    function buscarEnServidor(termino)
    {
        if (fetchController) fetchController.abort();
        fetchController = new AbortController();

        const url = new URL('/pages/individual', window.location.origin);
        if (termino.trim() !== '') url.searchParams.set('q', termino.trim());

        if (recipeSection) recipeSection.style.opacity = '0.6';

        fetch(url.toString(), { signal: fetchController.signal, credentials: 'same-origin' })
            .then(function (r) { return r.text(); })
            .then(function (html)
            {
                const parser  = new DOMParser();
                const doc     = parser.parseFromString(html, 'text/html');

                const nuevoGrid = doc.querySelector('.recipe-section .row');
                const viejoGrid = document.querySelector('.recipe-section .row');
                if (nuevoGrid && viejoGrid) viejoGrid.replaceWith(nuevoGrid);

                const nuevaPag = doc.querySelector('.recipe-pagination');
                const viejaPag = document.querySelector('.recipe-pagination');
                if (nuevaPag && viejaPag)      { viejaPag.replaceWith(nuevaPag); }
                else if (nuevaPag)             { recipeSection?.appendChild(nuevaPag); }
                else if (viejaPag)             { viejaPag.remove(); }

                history.replaceState(null, '', url.toString());

                if (recipeSection) recipeSection.style.opacity = '';
            })
            .catch(function (e)
            {
                if (e.name !== 'AbortError' && recipeSection) recipeSection.style.opacity = '';
            });
    }

    // ── Inputs de búsqueda ──
    const searchInputs = document.querySelectorAll('.js-receta-search-input');

    searchInputs.forEach(function (input)
    {
        input.addEventListener('input', function ()
        {
            const val = input.value;

            searchInputs.forEach(function (other)
            {
                if (other !== input) other.value = val;
            });

            filtrarColecciones(val.toLowerCase().trim());

            clearTimeout(fetchTimer);
            fetchTimer = setTimeout(function ()
            {
                buscarEnServidor(val);
            }, 420);
        });
    });

    if (searchInputs.length > 0)
    {
        filtrarColecciones(searchInputs[0].value.toLowerCase().trim());
    }

    // ── Modals ──
    document.getElementById('confirmDeleteModal')?.addEventListener('show.bs.modal', function (e)
    {
        document.getElementById('modalIdReceta').value = e.relatedTarget.getAttribute('data-id');
    });

    document.getElementById('confirmDeleteColeccionModal')?.addEventListener('show.bs.modal', function (e)
    {
        document.getElementById('deleteColeccionId').value = e.relatedTarget.getAttribute('data-id');
    });


    // ── Interceptor del modal de guardar receta (requiere alertas.js) ──
    const saveForm = document.getElementById('saveForm');
    if (saveForm)
    {
        saveForm.addEventListener('submit', (e) =>
        {
            const seleccionadas = Array.from(document.querySelectorAll('#cols-list input[type="checkbox"]:checked'));
            if (seleccionadas.length === 0)
            {
                e.preventDefault();
                e.stopImmediatePropagation();
                window.Alertas.error('Selecciona al menos una colección.');
            }
        }, true);
    }

    // ── Overlay de comentarios ──
    document.querySelector('.js-close-comments')?.addEventListener('click', function ()
    {
        if (typeof FeedApp !== 'undefined') FeedApp.closeComments();
    });

    document.querySelector('.comments-input__form')?.addEventListener('submit', function (e)
    {
        e.preventDefault();
        if (typeof FeedApp !== 'undefined' && typeof FeedApp.sendComment === 'function') FeedApp.sendComment(e);
    });

    document.querySelector('.comments-input__field')?.addEventListener('input', function ()
    {
        const counter = this.closest('form')?.querySelector('.comments-input__counter');
        if (counter) counter.textContent = this.value.length + '/200';
    });
});
