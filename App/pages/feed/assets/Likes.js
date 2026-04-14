//===============================================================
// Likes.js - Gestión de "me gusta"
//===============================================================
//--------------------------------------------------------
//| Última edición: 2026-04-08 por Jaime Sánchez Soteras |
//--------------------------------------------------------
// Función para manejar el toggle de "me gusta" en las recetas
(function() 
{
    async function toggleLike(idReceta, element) 
    {
        const icon = element.querySelector('.like-icon');
        const count = element.querySelector('.like-count');
        try 
        {
            if (!window.isLoggedIn) {window.location.href = '/App/pages/login'; return;}
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch(`/App/api/receta/like/${idReceta}`, 
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ csrf_token: csrfToken })
            });
            const data = await res.json();
            if (data.status === 'success') 
            {
                count.innerText = data.newLikes;
                icon.classList.toggle('fill-icon');
                icon.animate([{ transform: 'scale(1)' }, { transform: 'scale(1.3)' }, { transform: 'scale(1)' }], { duration: 200 });
            }
        } 
        catch (err) {console.error('Error en like:', err);}
    }
    window.FeedApp.toggleLike = toggleLike;
})();