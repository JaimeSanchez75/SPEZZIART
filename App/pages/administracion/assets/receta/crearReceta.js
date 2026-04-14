let cantidadItemsIngredientes = 0;
let cantidadItemsPasos = 0;
let ingredientes = [];
let currentTrigger = null;
let currentMode = 'crear';

async function obtenerIngredientes() {
    const respuesta = await fetch('/App/pages/administracion/ingredientes/json');
    ingredientes = await respuesta.json();
}

function crearOpcionesIngredientes(selectedId = '') {
    return ingredientes.map((item) => {
        const selected = item.ID_Ingrediente == selectedId ? ' selected' : '';
        return `<option value="${item.ID_Ingrediente}"${selected}>${item.Nombre_Base}</option>`;
    }).join('');
}

function pintarIngrediente(valorId = '', cantidad = '') {
    const contenedor = document.getElementById('contenedorIngredientes');
    cantidadItemsIngredientes++;
    const nuevoIngrediente = document.createElement('div');
    nuevoIngrediente.classList.add('ingrediente-item');
    nuevoIngrediente.innerHTML = `<label for="Ingrediente" class="form-label text-dark fw-semibold">Ingrediente ${cantidadItemsIngredientes}</label>
                            <select name="datos[Ingrediente][]" class="form-control">
                                ${crearOpcionesIngredientes(valorId)}
                            </select>
                            <div class="invalid-feedback">Por favor ingresa los ingredientes.</div>

                            <input type="text" class="form-control mt-2" name="datos[Cantidad][]" placeholder="Cantidad (Ejemplo: 200g)" value="${cantidad}" required>

                            <button type="button" class="btn btn-outline-secondary mt-2" id="eliminarIngrediente">Eliminar Ingrediente</button>`;
    contenedor.appendChild(nuevoIngrediente);
}

function eliminarIngrediente(event) {
    if (event.target && event.target.id === 'eliminarIngrediente') {
        const ingredienteItem = event.target.closest('.ingrediente-item');
        if (ingredienteItem) {
            ingredienteItem.remove();
            cantidadItemsIngredientes--;
            actualizarEtiquetasIngrediente();
        }
    }
}

function actualizarEtiquetasIngrediente() {
    const ingredientesItems = document.querySelectorAll('.ingrediente-item');

    ingredientesItems.forEach((ingrediente, index) => {
        const label = ingrediente.querySelector('label');
        const select = ingrediente.querySelector('select');
        const inputCantidad = ingrediente.querySelector('input[type="text"]');

        label.textContent = `Ingrediente ${index + 1}`;
        select.id = `Ingrediente ${index + 1}`;
        inputCantidad.id = `Cantidad ${index + 1}`;
    });
}

function pintarPaso(contenido = '') {
    const contenedor = document.getElementById('contenedorPasos');
    cantidadItemsPasos++;
    const nuevopaso = document.createElement('div');
    nuevopaso.classList.add('paso-item');
    nuevopaso.innerHTML = `<label for="Paso" class="form-label text-dark fw-semibold">Paso nº ${cantidadItemsPasos}</label>
                            <textarea name="datos[paso][]" class="form-control" required>${contenido}</textarea>
                            <div class="invalid-feedback">Por favor ingresa los pasos.</div>

                            <button type="button" class="btn btn-outline-secondary mt-2" id="eliminarPaso">Eliminar Paso</button>`;
    contenedor.appendChild(nuevopaso);
}

function eliminarPaso(event) {
    if (event.target && event.target.id === 'eliminarPaso') {
        const pasoItem = event.target.closest('.paso-item');
        if (pasoItem) {
            pasoItem.remove();
            cantidadItemsPasos--;
            actualizarEtiquetasPasos();
        }
    }
}

function actualizarEtiquetasPasos() {
    const pasos = document.querySelectorAll('.paso-item');

    pasos.forEach((paso, index) => {
        const label = paso.querySelector('label');
        const textarea = paso.querySelector('textarea');

        label.textContent = `Paso nª ${index + 1}`;
        textarea.id = `Paso ${index + 1}`;
    });
}

function limpiarFormularioReceta() {
    document.getElementById('Titulo').value = '';
    document.getElementById('Descripcion').value = '';
    document.getElementById('Tiempo').value = '';
    document.getElementById('Porciones').value = '';
    document.getElementById('receta_id').value = '0';

    const etiquetaSelect = document.getElementById('Etiquetas');
    if (etiquetaSelect) {
        Array.from(etiquetaSelect.options).forEach(option => option.selected = false);
    }

    document.getElementById('inputCalorias').value = 0;
    document.getElementById('inputProteina').value = 0;
    document.getElementById('inputCarbohidratos').value = 0;
    document.getElementById('inputGrasas').value = 0;
    const esfitCheckbox = document.getElementById('esfit');
    if (esfitCheckbox) {
        esfitCheckbox.checked = true;
    }

    const btnNutricion = document.getElementById('btnAbrirNutricionReceta');
    if (btnNutricion) {
        btnNutricion.dataset.calorias = 0;
        btnNutricion.dataset.proteina = 0;
        btnNutricion.dataset.carbohidratos = 0;
        btnNutricion.dataset.grasas = 0;
    }

    const contenedorIngredientes = document.getElementById('contenedorIngredientes');
    const contenedorPasos = document.getElementById('contenedorPasos');
    if (contenedorIngredientes) contenedorIngredientes.innerHTML = '';
    if (contenedorPasos) contenedorPasos.innerHTML = '';
    cantidadItemsIngredientes = 0;
    cantidadItemsPasos = 0;
}

async function cargarModoCrear() {
    currentMode = 'crear';
    const modalTitulo = document.querySelector('#modalCrearReceta .modal-title');
    const botonSubmit = document.querySelector('#modalCrearReceta button[type="submit"]');
    const form = document.getElementById('formCrearReceta');

    if (modalTitulo) modalTitulo.textContent = 'Crear Receta';
    if (botonSubmit) botonSubmit.textContent = 'Crear receta';
    if (form) form.action = '/App/pages/administracion/receta/crear';

    limpiarFormularioReceta();
    pintarIngrediente();
    pintarPaso();
}

async function cargarModoEditar(id) {
    currentMode = 'editar';
    const modalTitulo = document.querySelector('#modalCrearReceta .modal-title');
    const botonSubmit = document.querySelector('#modalCrearReceta button[type="submit"]');
    const form = document.getElementById('formCrearReceta');

    if (modalTitulo) modalTitulo.textContent = 'Editar Receta';
    if (botonSubmit) botonSubmit.textContent = 'Guardar cambios';
    if (form) form.action = '/App/pages/administracion/receta/editar';

    limpiarFormularioReceta();

    const response = await fetch(`/App/pages/administracion/receta/json/${id}`);
    if (!response.ok) {
        await cargarModoCrear();
        return;
    }

    const receta = await response.json();
    document.getElementById('Titulo').value = receta.Titulo || '';
    document.getElementById('Descripcion').value = receta.Descripcion || '';
    document.getElementById('Tiempo').value = receta.Tiempo || '';
    document.getElementById('Porciones').value = receta.Porciones || '';
    document.getElementById('receta_id').value = receta.ID_Receta || '0';
    

    const etiquetaSelect = document.getElementById('Etiquetas');
    if (etiquetaSelect && receta.Etiquetas) {
        Array.from(etiquetaSelect.options).forEach(option => {
            option.selected = receta.Etiquetas.includes(option.value.toString());
        });
    }

    const calorias = receta.Calorias ?? 0;
    const proteina = receta.Proteina ?? 0;
    const carbohidratos = receta.Carbohidratos ?? 0;
    const grasas = receta.Grasas ?? 0;

    document.getElementById('inputCalorias').value = calorias;
    document.getElementById('inputProteina').value = proteina;
    document.getElementById('inputCarbohidratos').value = carbohidratos;
    document.getElementById('inputGrasas').value = grasas;
    const esfitCheckbox = document.getElementById('esfit');
    if (esfitCheckbox) {
        esfitCheckbox.checked = receta.EsFit == 1 || receta.EsFit === '1';
    }

    const btnNutricion = document.getElementById('btnAbrirNutricionReceta');
    if (btnNutricion) {
        btnNutricion.dataset.calorias = calorias;
        btnNutricion.dataset.proteina = proteina;
        btnNutricion.dataset.carbohidratos = carbohidratos;
        btnNutricion.dataset.grasas = grasas;
    }

    const pasos = Array.isArray(receta.paso) ? receta.paso : [];
    if (pasos.length > 0) {
        pasos.forEach((paso) => pintarPaso(paso || ''));
    } else {
        pintarPaso();
    }

    const ingredientesReceta = Array.isArray(receta.ingredientes) ? receta.ingredientes : [];
    if (ingredientesReceta.length > 0) {
        ingredientesReceta.forEach((item) => {
            pintarIngrediente(item.ID_Ingrediente ?? '', item.Cantidad ?? '');
        });
    } else {
        pintarIngrediente();
    }
}

document.addEventListener('DOMContentLoaded', async function () {
    await obtenerIngredientes();

    const modalCrear = document.getElementById('modalCrearReceta');
    if (modalCrear) {
        modalCrear.addEventListener('show.bs.modal', async function (event) {
            if (modalCrear.dataset.restoreState === 'true') {
                delete modalCrear.dataset.restoreState;
                return;
            }

            currentTrigger = event.relatedTarget;
            const mode = currentTrigger?.dataset.mode || 'crear';
            const id = currentTrigger?.dataset.id;
            if (mode === 'editar' && id) {
                await cargarModoEditar(id);
            } else {
                await cargarModoCrear();
            }
        });
    }

    document.addEventListener('click', function (event) {
        if (!event.target) return;
        const btn = event.target;
        if (!btn.id) return;

        switch (btn.id) {
            case 'agregarIngrediente':
                pintarIngrediente();
                break;
            case 'eliminarIngrediente':
                eliminarIngrediente(event);
                break;
            case 'eliminarPaso':
                eliminarPaso(event);
                break;
            case 'agregarPaso':
                pintarPaso();
                break;
        }
    });
}); 
