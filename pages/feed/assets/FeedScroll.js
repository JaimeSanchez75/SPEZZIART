(function() 
{
    'use strict';
    let observer;                
    let animationObserver;      
    const state = FeedApp.state;
    const cardSelector = '.feed-card, .tarjeta-receta';
    const pendingCardSelector = '.feed-card:not(.visible), .tarjeta-receta:not(.visible)';
    const loadedIds = new Set(); 
    const MIN_LOADING_MS = 450;
    const endMessage = '<p class="text-secondary texto text-center m-0">Has llegado al final del feed.</p>';
    let statusTimeout = null;
    state.offset = 0;

    function getFeedStatus() 
    {
        let status = document.getElementById('feed-scroll-status');
        if (!status) 
        {
            status = document.createElement('div');
            status.id = 'feed-scroll-status';
            status.className = 'feed-scroll-status d-none';
            document.body.appendChild(status);
        }
        return status;
    }

    function showFeedStatus(html, persist = false) 
    {
        clearTimeout(statusTimeout);
        statusTimeout = null;
        const status = getFeedStatus();
        status.innerHTML = html;
        status.dataset.persist = persist ? 'true' : 'false';
        status.classList.remove('d-none');
    }

    function hideFeedStatus(force = false) 
    {
        const status = document.getElementById('feed-scroll-status');
        if (status && (force || status.dataset.persist !== 'true')) status.classList.add('d-none');
    }

    function showTemporaryFeedStatus(html, duration = 1000) 
    {
        showFeedStatus(html, false);
        statusTimeout = setTimeout(() => hideFeedStatus(true), duration);
    }

    function esperar(ms) 
    {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    function ensureEndTriggerMessage() 
    {
        const trigger = document.getElementById('infinite-scroll-trigger');
        if (!trigger) return null;
        trigger.classList.remove('feed-end-slide');
        trigger.innerHTML = '';
        return trigger;
    }

    function revealEndOfFeed() 
    {
        if (!state.isFull) return;
        ensureEndTriggerMessage();
        showTemporaryFeedStatus(endMessage, 1000);
    }

    function animateCardsOnScroll() 
    {
        if (animationObserver) animationObserver.disconnect();
        animationObserver = new IntersectionObserver
        (   (entries) => 
        {
            entries.forEach(entry => 
            {
                if (entry.isIntersecting) 
                {
                    setTimeout(() => {entry.target.classList.add('visible');}, 50);
                    animationObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.25, rootMargin: '0px 0px -20px 0px' });
        const cards = document.querySelectorAll(pendingCardSelector);
        cards.forEach(card => animationObserver.observe(card));
    }
    function initLoadedIds() 
    {
        const cards = document.querySelectorAll(cardSelector);
        if (cards.length === 0) return;
        cards.forEach(card => 
        {
            const id = card.dataset.id;
            if (id) loadedIds.add(id);
        });
        state.offset = loadedIds.size;
    }
    async function loadMore() 
    {
        if (state.loading) return;
        if (state.isFull) 
        {
            revealEndOfFeed();
            return;
        }
        const trigger = document.getElementById('infinite-scroll-trigger');
        if (!trigger) return;
        const loadingStartedAt = performance.now();
        let pendingStatusHtml = null;
        let pendingStatusDuration = 1000;
        state.loading = true;
        trigger.innerHTML = '<div class="spinner-border text-danger mx-auto d-block my-3" role="status"></div>';
        showFeedStatus('<div class="spinner-border text-danger" role="status" aria-label="Cargando"></div>');
        try 
        {
            const url = `/pages/feed/filtrar?offset=${state.offset}`;
            const body = JSON.stringify
            ({
                etiquetas: FeedApp.filters.etiquetas,
                busqueda: FeedApp.filters.busqueda,
                orden: state.orden,
                seed: window.feedSeed ?? 1
            });
            const res = await fetch(url, 
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: body
            });
            if (!res.ok) throw new Error(`HTTP ${res.status}: ${res.statusText}`);
            const data = await res.json();
            if (data.html && data.html.trim() !== '') 
            {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.html;
                const allCards = tempDiv.querySelectorAll(cardSelector);
                let added = 0;
                for (const card of allCards) 
                {
                    const id = card.dataset.id;
                    if (id && !loadedIds.has(id)) 
                    {
                        loadedIds.add(id);
                        trigger.insertAdjacentHTML('beforebegin', card.outerHTML);
                        const insertedCard = trigger.previousElementSibling;
                        if (insertedCard) insertedCard.classList.remove('visible');
                        added++;
                    }
                }
                state.offset += allCards.length;  // incrementa con el total devuelto (no solo insertados)
                if (allCards.length < state.limit) 
                {
                    state.isFull = true;
                    ensureEndTriggerMessage();
                   
                } 
                else 
                {
                    trigger.classList.remove('feed-end-slide');
                    trigger.innerHTML = '';
                    hideFeedStatus();
                }
                animateCardsOnScroll();
                if (FeedApp.initPopovers) FeedApp.initPopovers();
            } 
            else 
            {
                state.isFull = true;
                ensureEndTriggerMessage();
                
            }
        } 
        catch (err) 
        {
            console.error('Error cargando más recetas:', err);
            trigger.innerHTML = '<p class="texto-rojo texto text-center">Error al cargar. Intenta de nuevo más tarde.</p>';
            pendingStatusHtml = '<p class="texto-rojo texto text-center m-0">Error al cargar. Intenta de nuevo mas tarde.</p>';
            pendingStatusDuration = 1600;
        } 
        finally 
        {
            const elapsed = performance.now() - loadingStartedAt;
            if (elapsed < MIN_LOADING_MS) await esperar(MIN_LOADING_MS - elapsed);
            state.loading = false;
            hideFeedStatus(true);
            if (pendingStatusHtml) showTemporaryFeedStatus(pendingStatusHtml, pendingStatusDuration);
        }
    }
    function setupInfiniteScroll() 
    {
        const trigger = document.getElementById('infinite-scroll-trigger');
        if (!trigger) return;
        if (observer) observer.disconnect();
        observer = new IntersectionObserver(entries => {if (entries[0].isIntersecting && !state.loading && !state.isFull) {loadMore();}}, { threshold: 0.1, rootMargin: '0px 0px 200px 0px' });
        observer.observe(trigger);
    }
    document.addEventListener('DOMContentLoaded', () => 
    {
        initLoadedIds();
        setupInfiniteScroll();
        animateCardsOnScroll();
        setTimeout(() => 
        {
            document.querySelectorAll(cardSelector).forEach(card => 
            {
                const rect = card.getBoundingClientRect();
                if (rect.top < window.innerHeight - 100) 
                {
                    card.classList.add('visible');
                    if (animationObserver) animationObserver.unobserve(card);
                }
            });
        }, 100);
        // Carga extra inicial si la pantalla no está llena
        const trigger = document.getElementById('infinite-scroll-trigger');
        if (trigger && !state.isFull && !state.loading) 
        {
            const rect = trigger.getBoundingClientRect();
            if (rect.bottom <= window.innerHeight) {loadMore();}
        }
    });
    FeedApp.resetInfiniteScroll = function() 
    {
        state.offset = 0;
        state.isFull = false;
        state.loading = false;
        loadedIds.clear();
        const status = document.getElementById('feed-scroll-status');
        if (status) 
        {
            status.dataset.persist = 'false';
            status.classList.add('d-none');
        }
        const trigger = document.getElementById('infinite-scroll-trigger');
        if (trigger) 
        {
            trigger.classList.remove('feed-end-slide');
            trigger.innerHTML = '';
        }
        initLoadedIds();
        setupInfiniteScroll();
        animateCardsOnScroll();
    };
    FeedApp.loadMoreRecipes = loadMore;
    FeedApp.revealEndOfFeed = revealEndOfFeed;
})();
