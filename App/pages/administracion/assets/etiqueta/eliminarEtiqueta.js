document.addEventListener('DOMContentLoaded',function(){
    const modalEliminarEtiqueta = document.getElementById('modalEliminarEtiqueta');

    modalEliminarEtiqueta.addEventListener('show.bs.modal', function(event){
        // botón que hizo click el usuario para abrir el modal
        const boton = event.relatedTarget;

        const id = boton.dataset.id;
        document.getElementById('nombreEtiqueta').textContent = boton.dataset.nombre;
        document.getElementById('eliminarEtiqueta').addEventListener('click', function() {
            window.location.href = `/App/pages/administracion/etiquetas/eliminar/${id}`;
        });
    })
})