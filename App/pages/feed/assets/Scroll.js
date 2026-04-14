//===============================================================
// Scroll.js - Lógica de scroll infinito para el feed de recetas
//===============================================================
//--------------------------------------------------------
//| Última edición: 2026-04-08 por Jaime Sánchez Soteras |
//--------------------------------------------------------
//---- Definición de Variables. ----
let observer;

window.reinitObserver = function() //Recarga del Observador para el Scroll Infinito.
{
    if (modoActual !== 'lista') return;
    const trigger = document.getElementById('infinite-scroll-trigger');
    if (!trigger) return;
    if (observer) observer.disconnect();
    observer = new IntersectionObserver(entries => {if (entries[0].isIntersecting) window.loadMore();}, { threshold: 0.1 });
    observer.observe(trigger);
};

window.loadMore = async function() //Función para cargar más recetas al hacer scroll tras llegar al final.
{
    if (modoActual !== 'lista') return;
    if (FeedApp.state.loading || FeedApp.state.isFull) return;
    FeedApp.state.loading = true;
    const trigger = document.getElementById('infinite-scroll-trigger');
    if (trigger) trigger.innerHTML = '<div class="spinner-border text-danger mx-auto d-block my-3"></div>';
    await window.fetchFeed(false);
    FeedApp.state.loading = false;
};