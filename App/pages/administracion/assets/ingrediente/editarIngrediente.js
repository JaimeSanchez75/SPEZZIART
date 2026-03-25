document.addEventListener('DOMContentLoaded',function(){
    const modalEditarIngrediente = document.getElementById('modalEditarIngrediente');

    modalEditarIngrediente.addEventListener('show.bs.modal', function(event){
        // botón que hizo click el usuario para abrir el modal
        const boton = event.relatedTarget;

        const id = boton.dataset.id;
        const nombre= boton.dataset.nombre;
        const calorias= boton.dataset.calorias || 0;
        const proteina= boton.dataset.proteina || 0;
        const carbohidratos= boton.dataset.carbohidratos || 0;
        const grasas= boton.dataset.grasas || 0;

        let btnNutricion= document.getElementById('btn-nutricion');
        
        btnNutricion.dataset.calorias=calorias;
        btnNutricion.dataset.proteina=proteina;
        btnNutricion.dataset.carbohidratos=carbohidratos;
        btnNutricion.dataset.grasas=grasas;
        
        document.getElementById('nombreEditar').value=nombre;
        modalEditarIngrediente.querySelector("#ingrediente_id").value = id;
    });
});