
let esEditar=false;

document.addEventListener('DOMContentLoaded',function(){
    const modalNutricion = document.getElementById('modalNutricion');

    modalNutricion.addEventListener('show.bs.modal', function(event){
        // botón que hizo click el usuario para abrir el modal
        const boton = event.relatedTarget;

        document.getElementById('calorias').value= boton.dataset.calorias || 0;
        document.getElementById('proteina').value= boton.dataset.proteina || 0;
        document.getElementById('carbohidratos').value= boton.dataset.carbohidratos || 0;
        document.getElementById('grasas').value= boton.dataset.grasas || 0;
        
    });
});

let btnNutricion= document.getElementById('btnNutricion');

btnNutricion.addEventListener('click',function(){
    document.getElementById('inputCalorias').value = document.getElementById('calorias').value;
    document.getElementById('inputProteina').value = document.getElementById('proteina').value;
    document.getElementById('inputCarbohidratos').value = document.getElementById('carbohidratos').value;
    document.getElementById('inputGrasas').value = document.getElementById('grasas').value;

    const modal = bootstrap.Modal.getInstance(document.getElementById('modalNutricion'));
    modal.hide();

    const modalCrearIngrediente = new bootstrap.Modal(document.getElementById('modalResumen'));
    siguienteModal.show();

    
})