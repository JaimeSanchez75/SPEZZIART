(()=>{
    let currentForm = null;
    let currentTrigger = null;
let previousModalElement = null;

function getHiddenNutritionInputs(form) {
    return {
        calorias: form?.querySelector('[name="datos[calorias]"]') || document.getElementById('inputCalorias'),
        proteina: form?.querySelector('[name="datos[proteina]"]') || document.getElementById('inputProteina'),
        carbohidratos: form?.querySelector('[name="datos[carbohidratos]"]') || document.getElementById('inputCarbohidratos'),
        grasas: form?.querySelector('[name="datos[grasas]"]') || document.getElementById('inputGrasas'),
    };
}

document.addEventListener('DOMContentLoaded', function () {
    const modalNutricion = document.getElementById('modalNutricion');
    if (!modalNutricion) return;

    modalNutricion.addEventListener('show.bs.modal', function (event) {
        currentTrigger = event.relatedTarget;
        previousModalElement = currentTrigger?.closest('.modal');
        currentForm = currentTrigger?.closest('form');
        const boton = currentTrigger;
        const form = currentForm;

        const caloriasValor = boton?.dataset.calorias ?? form?.querySelector('[name="datos[calorias]"]')?.value ?? 0;
        const proteinaValor = boton?.dataset.proteina ?? form?.querySelector('[name="datos[proteina]"]')?.value ?? 0;
        const carbohidratosValor = boton?.dataset.carbohidratos ?? form?.querySelector('[name="datos[carbohidratos]"]')?.value ?? 0;
        const grasasValor = boton?.dataset.grasas ?? form?.querySelector('[name="datos[grasas]"]')?.value ?? 0;

        document.getElementById('calorias').value = caloriasValor;
        document.getElementById('proteina').value = proteinaValor;
        document.getElementById('carbohidratos').value = carbohidratosValor;
        document.getElementById('grasas').value = grasasValor;
    });

    modalNutricion.addEventListener('hidden.bs.modal', function () {
        if (previousModalElement) {
            previousModalElement.dataset.restoreState = 'true';
            const previousModal = bootstrap.Modal.getOrCreateInstance(previousModalElement);
            previousModal.show();
            previousModalElement = null;
            currentTrigger = null;
        }
    });
});

const btnNutricion = document.getElementById('btnGuardarNutricion') || document.getElementById('btnNutricion');
if (btnNutricion) {
    btnNutricion.addEventListener('click', function () {
        
        const inputs = getHiddenNutritionInputs(currentForm);
        const calorias = document.getElementById('calorias').value;
        const proteina = document.getElementById('proteina').value;
        const carbohidratos = document.getElementById('carbohidratos').value;
        const grasas = document.getElementById('grasas').value;

        if (inputs.calorias) inputs.calorias.value = calorias;
        if (inputs.proteina) inputs.proteina.value = proteina;
        if (inputs.carbohidratos) inputs.carbohidratos.value = carbohidratos;
        if (inputs.grasas) inputs.grasas.value = grasas;

        if (currentTrigger) {
            currentTrigger.dataset.calorias = calorias;
            currentTrigger.dataset.proteina = proteina;
            currentTrigger.dataset.carbohidratos = carbohidratos;
            currentTrigger.dataset.grasas = grasas;
        }

        const modal = bootstrap.Modal.getInstance(document.getElementById('modalNutricion'));
        if (modal) modal.hide();
    });
}
})();
