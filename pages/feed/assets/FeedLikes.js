window.FeedApp = window.FeedApp || {};
FeedApp.getCsrfToken = FeedApp.getCsrfToken || function() 
{
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.getAttribute('content') : '';
};
(function() 
{
    'use strict';
    async function toggleLike(idReceta, element) 
    {
        if (!window.isLoggedIn) {window.location.href = '/pages/login'; return;}
        const icon = element.querySelector('.like-icon');
        const count = element.querySelector('.like-count');
        if (!icon || !count) return;
        try 
        {
            const res = await fetch(`/api/receta/like/${idReceta}`, 
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: FeedApp.getCsrfToken() })
            });
            if (res.status === 401) 
            {
                window.location.href = '/pages/login';
                return;
            }
            const data = await res.json();
            if (data.status === 'success') 
            {
                count.innerText = data.newLikes;
                const isNowLiked = icon.classList.toggle('fill-icon');
                icon.classList.toggle('texto-rojo', isNowLiked);
                icon.classList.toggle('text-secondary', !isNowLiked);
                icon.animate([
                    { transform: 'scale(1)' },
                    { transform: 'scale(1.3)' },
                    { transform: 'scale(1)' }
                ], { duration: 200 });
            }
        } 
        catch (err) {console.error('Error en like:', err);}
    }
    FeedApp.toggleLike = toggleLike;
})();