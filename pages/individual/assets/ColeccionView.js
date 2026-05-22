"use strict";

document.addEventListener('DOMContentLoaded', function ()
{
    // ── Modal eliminar receta de colección ──
    document.getElementById('confirmDeleteModal')?.addEventListener('show.bs.modal', function (event)
    {
        const button = event.relatedTarget;
        document.getElementById('modalIdReceta').value    = button.getAttribute('data-idreceta');
        document.getElementById('modalIdColeccion').value = button.getAttribute('data-idcoleccion');
        document.getElementById('deleteForm').action      = '/pages/individual/coleccion/eliminar-receta';
    });

    // ── Overlay de comentarios ──
    document.querySelector('.js-close-comments')?.addEventListener('click', function ()
    {
        if (typeof FeedApp !== 'undefined') FeedApp.closeComments();
    });

    document.querySelector('.comments-input__form')?.addEventListener('submit', function (e)
    {
        e.preventDefault();
        if (typeof FeedApp !== 'undefined' && typeof FeedApp.sendComment === 'function') FeedApp.sendComment(e);
    });

    document.querySelector('.comments-input__field')?.addEventListener('input', function ()
    {
        const counter = this.closest('form')?.querySelector('.comments-input__counter');
        if (counter) counter.textContent = this.value.length + '/200';
    });
});
