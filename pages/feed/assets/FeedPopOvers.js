// =============================================================
// feed-popovers.js - Inicialización de popovers de Bootstrap
// =============================================================
(function() 
{
    'use strict';
    function initPopovers() 
    {
        if (typeof bootstrap === 'undefined') return;
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
    document.addEventListener('DOMContentLoaded', function() 
    {
        initPopovers();
        // Observer para nuevo contenido cargado vía scroll infinito
        const feedContainer = document.getElementById('feed-container');
        if (feedContainer) 
        {
            const observer = new MutationObserver(initPopovers);
            observer.observe(feedContainer, { childList: true, subtree: true });
        }
    });
    FeedApp.initPopovers = initPopovers;
})();