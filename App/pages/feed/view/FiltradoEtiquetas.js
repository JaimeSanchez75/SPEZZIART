document.addEventListener('DOMContentLoaded', function () 
{

    const modal = document.getElementById('modalEtiquetas');
    const chipsWrapper = document.getElementById('chips-wrapper');
    const extraBadge = document.getElementById('extra-chips-badge');
    const clearBtn = document.getElementById('clear-filters');

    function getEtiquetas() {return FeedApp.filters.etiquetas || [];}
    function setEtiquetas(tags) {FeedApp.filters.etiquetas = tags;}

    // ==================== Click en la Etiqueta ====================
    document.getElementById('modal-tags-list').addEventListener('click', function (e) 
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

        clearBtn.classList.toggle('d-none', tags.length === 0);

        actualizarEtiquetas();
    });

    // ==================== Limpiar Etiquetas ====================
    clearBtn.addEventListener('click', function () 
    {

        document.querySelectorAll('#modal-tags-list .chip-selectable').forEach(chip => 
        {
            chip.classList.remove('active', 'btn-danger', 'text-white');
            chip.classList.add('btn-outline-danger');
        });

        setEtiquetas([]);
        clearBtn.classList.add('d-none');

        actualizarEtiquetas();

        FeedApp.fetchFeed(true);
    });

    // ==================== Aplicar Filtro ====================
    document.querySelector('#modalEtiquetas .btn-danger[data-bs-dismiss="modal"]').addEventListener('click', function () { FeedApp.fetchFeed(true);});

    modal.addEventListener('hidden.bs.modal', function () {FeedApp.fetchFeed(true);});

    // ==================== Etiquetas Visibles ====================
    function actualizarEtiquetas() 
    {

        const tags = getEtiquetas();

        chipsWrapper.innerHTML = '';
        const maxVisible = 3;

        if (tags.length === 0) 
        {
            extraBadge.classList.add('d-none');
            return;
        }

        tags.slice(0, maxVisible).forEach(tag => 
        {
            const chip = document.createElement('span');
            chip.className = 'badge bg-danger rounded-pill d-flex align-items-center px-3 py-2';
            chip.innerHTML = `${tag} <span class="ms-2 cursor-pointer" data-tag="${tag}">&times;</span>`;
            chipsWrapper.appendChild(chip);
        });

        if (tags.length > maxVisible) 
        {
            extraBadge.textContent = `+${tags.length - maxVisible}`;
            extraBadge.classList.remove('d-none');
        } 
        else {extraBadge.classList.add('d-none');}

        chipsWrapper.querySelectorAll('span[data-tag]').forEach(closeBtn => 
        {
            closeBtn.addEventListener('click', function (e) 
            {
                e.stopPropagation();
                eliminarEtiqueta(this.dataset.tag);
            });
        });
    }

    // ==================== Eliminar Etiqueta ====================
    function eliminarEtiqueta(tag) 
    {

        let tags = getEtiquetas().filter(t => t !== tag);
        setEtiquetas(tags);

        const chipInModal = document.querySelector(`#modal-tags-list .chip-selectable[data-name="${tag}"]`);

        if (chipInModal) {
            chipInModal.classList.remove('active', 'btn-danger', 'text-white');
            chipInModal.classList.add('btn-outline-danger');
        }

        clearBtn.classList.toggle('d-none', tags.length === 0);

        actualizarEtiquetas();

        FeedApp.fetchFeed(true);
    }

});