document.addEventListener('DOMContentLoaded',function(){
    const modalEditarEtiqueta = document.getElementById('modalEditarEtiqueta');

    modalEditarEtiqueta.addEventListener('show.bs.modal', function(event){
        // botón que hizo click el usuario para abrir el modal
        const boton = event.relatedTarget;

        const id = boton.dataset.id;
        
        modalEditarEtiqueta.querySelector("#etiqueta_id").value = id;
    })
})