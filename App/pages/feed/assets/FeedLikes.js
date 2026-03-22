async function toggleLike(idReceta, element) 
{
    const icon = element.querySelector('.material-symbols-outlined');
    const count = element.querySelector('span:last-child');

    try 
    {
        const res = await fetch(`/App/api/receta/like/${idReceta}`, { method: 'POST' });

        if (res.status === 401 || res.redirected) 
        {
            window.location.href = '/App/pages/login';
            return;
        }

        const data = await res.json();
        
        if (data.status === 'success') 
        {
            count.innerText = data.newLikes;
            icon.classList.toggle('text-danger');
            icon.classList.toggle('fill-icon');

            icon.animate(
                [
                    { transform: 'scale(1)' },
                    { transform: 'scale(1.3)' },
                    { transform: 'scale(1)' }
                ], 
                { duration: 200 });
        }
    } 
    catch (err) 
    {
        window.location.href = '/App/pages/login';
    }
}