document.addEventListener('DOMContentLoaded', function() 
{
    /*El Modal Emergente de las Etiquetas en las Recetas*/
    if (typeof bootstrap === 'undefined') return;

    function initPopovers() 
    {
        document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => 
        {
            if (!el.dataset.popoverInitialized) 
            {
                new bootstrap.Popover(el, 
                {
                    trigger: 'hover focus',
                    html: true,
                    sanitize: false
                });
                el.dataset.popoverInitialized = 'true';
            }
        });
    }
    initPopovers();
    const observer = new MutationObserver(initPopovers);
    observer.observe(document.getElementById('feed-container'), { childList: true, subtree: true });
});