document.addEventListener('DOMContentLoaded',function(){
    const modalEliminarEtiqueta = document.getElementById('eliminarEtiqueta');

    modalEliminarEtiqueta.addEventListener('show.bs.modal', function(event){
        // botón que hizo click el usuario para abrir el modal
        const boton = event.relatedTarget;

        const id = boton.dataset.id;
        
        document.getElementById('eliminarEtiqueta').addEventListener('click', function() {
            window.location.href = `/App/pages/administracion/etiquetas/eliminar/${id}`;
        });
    })
})