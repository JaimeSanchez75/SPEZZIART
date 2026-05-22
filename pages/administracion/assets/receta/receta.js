document.getElementById('btn-volver')?.addEventListener('click',()=> window.history.back());

(function () {
    const init = () => {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
            if (!bootstrap.Tooltip.getInstance(el)) new bootstrap.Tooltip(el);
        });
    };
    if (document.readyState !== 'loading') init();
    else document.addEventListener('DOMContentLoaded', init);
})();