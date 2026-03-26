document.addEventListener('DOMContentLoaded', () => 
{
    
    const trigger = document.getElementById('infinite-scroll-trigger');
    if (trigger) 
    {
        /*Un "Observador" de cambios, está pendiente del foco para administrar más recetas.. */
        const observer = new IntersectionObserver
        (
            entries => 
            {
                if (entries[0].isIntersecting) loadMoreRecipes();
            }, 
            { threshold: 0.1 }
        );

        observer.observe(trigger);
    }
});

async function loadMoreRecipes() 
{
    if (loading || isFull) 
    {
        return;
    }

    const trigger = document.getElementById('infinite-scroll-trigger');
    loading = true;
    if (trigger) trigger.innerHTML = '<div class="spinner-border text-danger mx-auto d-block my-3"></div>';

    try 
    {
        const res = await fetch(`/App/pages/feed/filtrar?offset=${offset}`, { method: 'POST' });

        if (res.status === 401) 
        {
            window.location.href = '/App/pages/login';
            return;
        }

        const data = await res.json();
        if (data.html && data.html.trim() !== "") 
        {
            if (trigger) trigger.insertAdjacentHTML('beforebegin', data.html);
            offset += limit;

            if (typeof bootstrap !== 'undefined') 
            {
                document.querySelectorAll('[data-bs-toggle="popover"]').forEach(el => new bootstrap.Popover(el));
            }
        } 
        else 
        {
            isFull = true;
            if (trigger) trigger.innerHTML = '<p class="text-muted text-center mt-3 p-4">Has llegado al final del feed.</p>';
        }
    } 
    catch (err) 
    {
        console.error("Error cargando más recetas:", err);
    } 
    finally 
    {
        loading = false;
        if (!isFull && trigger) trigger.innerHTML = '';
    }
}
