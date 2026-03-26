document.addEventListener('DOMContentLoaded',function(){
    const modalEliminarReceta = document.getElementById('eliminarReceta');

    modalEliminarReceta.addEventListener('show.bs.modal', function(event){
        // botón que hizo click el usuario para abrir el modal
        const boton = event.relatedTarget;

        const id = boton.dataset.id;
        
        document.getElementById('eliminarReceta').addEventListener('click', function() {
            window.location.href = `/App/pages/administracion/receta/eliminar/${id}`;
        });
    })
})