// PopUps.js

document.addEventListener('DOMContentLoaded', function() {
    if (typeof bootstrap === 'undefined') return;

    function initPopovers() {
        document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => {
            // Evitar duplicados
            if (!el.dataset.popoverInitialized) {
                new bootstrap.Popover(el, {
                    trigger: 'hover focus',
                    html: true,
                    sanitize: false
                });
                el.dataset.popoverInitialized = 'true';
            }
        });
    }

    initPopovers();

    // Observar nuevos elementos
    const observer = new MutationObserver(initPopovers);
    observer.observe(document.getElementById('feed-container'), { childList: true, subtree: true });
});