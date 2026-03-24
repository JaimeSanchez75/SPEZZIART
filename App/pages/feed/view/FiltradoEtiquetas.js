document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalEtiquetas');
    const chipsWrapper = document.getElementById('chips-wrapper');
    const extraBadge = document.getElementById('extra-chips-badge');
    const clearBtn = document.getElementById('clear-filters');
    
    let selectedTags = [];

    // Escuchar clicks en los chips del modal
    document.getElementById('modal-tags-list').addEventListener('click', function(e) {
        const chip = e.target.closest('.chip-selectable');
        if (!chip) return;

        const tagName = chip.dataset.name;
        
        chip.classList.toggle('active');
        chip.classList.toggle('btn-outline-danger');
        chip.classList.toggle('btn-danger');
        chip.classList.toggle('text-white');

        if (selectedTags.includes(tagName)) {
            selectedTags = selectedTags.filter(t => t !== tagName);
        } else {
            selectedTags.push(tagName);
        }

        clearBtn.classList.toggle('d-none', selectedTags.length === 0);
        updateChips();
    });

    clearBtn.addEventListener('click', function() {
        document.querySelectorAll('#modal-tags-list .chip-selectable').forEach(chip => {
            chip.classList.remove('active', 'btn-danger', 'text-white');
            chip.classList.add('btn-outline-danger');
        });
        selectedTags = [];
        clearBtn.classList.add('d-none');
        updateChips();
        applyFilter();
    });

    document.querySelector('#modalEtiquetas .btn-danger[data-bs-dismiss="modal"]').addEventListener('click', function() {
        applyFilter();
    });

    modal.addEventListener('hidden.bs.modal', function() {
        applyFilter();
    });

    function updateChips() {
        chipsWrapper.innerHTML = '';
        const maxVisible = 3;

        if (selectedTags.length === 0) {
            extraBadge.classList.add('d-none');
            return;
        }

        selectedTags.slice(0, maxVisible).forEach(tag => {
            const chip = document.createElement('span');
            chip.className = 'badge bg-danger rounded-pill d-flex align-items-center px-3 py-2';
            chip.innerHTML = `${tag} <span class="ms-2 cursor-pointer" data-tag="${tag}">&times;</span>`;
            chipsWrapper.appendChild(chip);
        });

        if (selectedTags.length > maxVisible) {
            extraBadge.textContent = `+${selectedTags.length - maxVisible}`;
            extraBadge.classList.remove('d-none');
        } else {
            extraBadge.classList.add('d-none');
        }

        chipsWrapper.querySelectorAll('span[data-tag]').forEach(closeBtn => {
            closeBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                removeTag(this.dataset.tag);
            });
        });
    }

    function removeTag(tag) {
        const chipInModal = document.querySelector(`#modal-tags-list .chip-selectable[data-name="${tag}"]`);
        if (chipInModal) {
            chipInModal.classList.remove('active', 'btn-danger', 'text-white');
            chipInModal.classList.add('btn-outline-danger');
        }
        selectedTags = selectedTags.filter(t => t !== tag);
        clearBtn.classList.toggle('d-none', selectedTags.length === 0);
        updateChips();
        applyFilter();
    }

    function applyFilter() {
        const feedContainer = document.getElementById('feed-container');
        const trigger = document.getElementById('infinite-scroll-trigger');

        feedContainer.innerHTML = '<div class="text-center my-5"><div class="spinner-border text-danger"></div></div>';

        if (window.FeedApp && FeedApp.state) {
            FeedApp.state.offset = 0;
            FeedApp.state.isFull = false;
        }

        fetch('/App/pages/feed/filtrar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ etiquetas: selectedTags })
        })
        .then(res => {
            if (res.status === 401) {
                window.location.href = '/App/pages/login';
                return;
            }
            return res.json();
        })
        .then(data => {
            if (data && data.html) {
                feedContainer.innerHTML = data.html;
                if (trigger) feedContainer.appendChild(trigger);

                if (typeof bootstrap !== 'undefined') {
                    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));
                }

                if (window.FeedApp && FeedApp.state) {
                    FeedApp.state.offset = data.count;
                    if (data.count < 5) FeedApp.state.isFull = true;
                }

                // Reinicializar drag en nuevos overlays
                if (window.initDragOnOverlays) initDragOnOverlays();
            } else {
                feedContainer.innerHTML = '<p class="text-center text-muted">No se encontraron recetas.</p>';
            }
        })
        .catch(err => {
            console.error('Error al filtrar:', err);
            feedContainer.innerHTML = '<p class="text-center text-danger">Error al cargar las recetas.</p>';
        });
    }
});