(function() {
    'use strict';

    window.FeedApp = window.FeedApp || {};

    function usuarioLogueado() {
        return window.isLoggedIn === true || window.isLoggedIn === 'true';
    }

    function redirigirLogin() {
        if (window._redirigiendoLogin) return;

        window._redirigiendoLogin = true;
        limpiarModalesProtegidos();
        window.location.href = '/pages/login';
    }

    document.addEventListener('click', function(e) {
        const elementoProtegido = e.target.closest(
            '[data-requiere-login="true"]'
        );

        if (!elementoProtegido) return;
        if (usuarioLogueado()) return;

        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();

        redirigirLogin();
    }, true);

    document.addEventListener('show.bs.modal', function(e) {
        const modalProtegido =
            e.target && (e.target.id === 'saveModal' || e.target.id === 'reportModal');

        if (!modalProtegido) return;
        if (usuarioLogueado()) return;

        e.preventDefault();
        e.stopPropagation();

        redirigirLogin();
    }, true);

    window.addEventListener('pageshow', function() {
        window._redirigiendoLogin = false;

        if (!usuarioLogueado()) {
            limpiarModalesProtegidos();
        }
    });

    function limpiarModalesProtegidos() {
        document.querySelectorAll('#reportModal, #saveModal').forEach(modal => {
            if (typeof bootstrap !== 'undefined') {
                const instancia = bootstrap.Modal.getInstance(modal);
                if (instancia) {
                    instancia.hide();
                    instancia.dispose();
                }
            }

            modal.classList.remove('show');
            modal.setAttribute('aria-hidden', 'true');
            modal.removeAttribute('aria-modal');
            modal.removeAttribute('role');
            modal.style.display = 'none';
        });

        document.querySelectorAll('.modal-backdrop').forEach(backdrop => backdrop.remove());

        document.body.classList.remove('modal-open');
        document.body.style.removeProperty('overflow');
        document.body.style.removeProperty('padding-right');
    }
})();