"use strict";
let cantidadItemsIngredientes = 0;
let cantidadItemsPasos = 0;
let ingredientes = [];
let currentTrigger = null;
let currentMode = 'crear';

let imagenesExistentesEstado = [];
let imagenesNuevasEstado = [];

async function obtenerIngredientes() {
    const respuesta = await fetch('/pages/administracion/ingredientes/json');
    ingredientes = await respuesta.json();
}

function crearCombobox(contenedor, opciones) {
    const config = Object.assign({
        mode: 'single',
        items: [],
        name: '',
        placeholder: 'Buscar...',
        initialIds: [],
        onchange:null,
    }, opciones);

    contenedor.innerHTML = '';
    contenedor.classList.add('combobox');

    const control = document.createElement('div');
    control.className = 'combobox-control';

    const chips = document.createElement('div');
    chips.className = 'combobox-chips';

    const search = document.createElement('input');
    search.type = 'text';
    search.className = 'combobox-search';
    search.placeholder = config.placeholder;
    search.autocomplete = 'off';

    const dropdown = document.createElement('ul');
    dropdown.className = 'combobox-dropdown';
    dropdown.hidden = true;

    const hiddenWrap = document.createElement('div');
    hiddenWrap.hidden = true;

    control.appendChild(chips);
    control.appendChild(search);
    contenedor.appendChild(control);
    contenedor.appendChild(dropdown);
    contenedor.appendChild(hiddenWrap);

    let seleccionados = [];
    let activo = -1;

    function getItem(id) {
        return config.items.find(it => String(it.id) === String(id));
    }

    function sincronizarHidden() {
        hiddenWrap.innerHTML = '';
        if (config.mode === 'single') {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = config.name;
            input.value = seleccionados[0] ?? '';
            hiddenWrap.appendChild(input);
        } else {
            seleccionados.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = config.name;
                input.value = id;
                hiddenWrap.appendChild(input);
            });
        }
    }

    function pintarChips() {
        chips.innerHTML = '';
        if (config.mode === 'single') {
            const id = seleccionados[0];
            if (id) {
                const item = getItem(id);
                if (item) {
                    search.value = item.nombre;
                } else {
                    search.value = '';
                }
            } else {
                search.value = '';
            }
            return;
        }
        seleccionados.forEach(id => {
            const item = getItem(id);
            if (!item) return;
            const chip = document.createElement('span');
            chip.className = 'combobox-chip';
            chip.dataset.id = id;
            chip.innerHTML = `<span>${escapeHtml(item.nombre)}</span><span class="combobox-chip-remove" aria-label="Quitar">&times;</span>`;
            chip.querySelector('.combobox-chip-remove').addEventListener('click', (e) => {
                e.stopPropagation();
                quitar(id);
            });
            chips.appendChild(chip);
        });
    }

    function filtrar(termino) {
        const t = termino.trim().toLowerCase();
        return config.items.filter(it => {
            const coincide = !t || it.nombre.toLowerCase().includes(t);
            const yaEsta = seleccionados.includes(String(it.id));
            if (config.mode === 'multi') return coincide && !yaEsta;
            return coincide;
        });
    }

    function pintarDropdown() {
        const termino = config.mode === 'multi' ? search.value : (
            (() => {
                const sel = getItem(seleccionados[0]);
                if (sel && search.value === sel.nombre) return '';
                return search.value;
            })()
        );
        const resultados = filtrar(termino).slice(0, 50);
        dropdown.innerHTML = '';
        activo = -1;

        if (resultados.length === 0) {
            const li = document.createElement('li');
            li.className = 'combobox-option is-empty';
            li.textContent = 'Sin resultados';
            dropdown.appendChild(li);
            dropdown.hidden = false;
            return;
        }

        resultados.forEach((item, idx) => {
            const li = document.createElement('li');
            li.className = 'combobox-option';
            if (config.mode === 'single' && String(seleccionados[0]) === String(item.id)) {
                li.classList.add('is-selected');
            }
            li.dataset.id = item.id;
            li.textContent = item.nombre;
            li.addEventListener('mousedown', (e) => { e.preventDefault(); });
            li.addEventListener('click', () => elegir(item.id));
            dropdown.appendChild(li);
        });
        dropdown.hidden = false;
    }

    function cerrarDropdown() {
        dropdown.hidden = true;
        activo = -1;
    }

    function elegir(id) {
        const sid = String(id);
        if (config.mode === 'single') {
            seleccionados = [sid];
            pintarChips();
            sincronizarHidden();
            cerrarDropdown();
            search.blur();
            if (config.onChange) config.onChange(sid, getItem(sid));
            return;
        }
        if (!seleccionados.includes(sid)) {
            seleccionados.push(sid);
            pintarChips();
            sincronizarHidden();
        }
        search.value = '';
        pintarDropdown();
        search.focus();
    }

    function quitar(id) {
        const sid = String(id);
        seleccionados = seleccionados.filter(x => x !== sid);
        pintarChips();
        sincronizarHidden();
        if (document.activeElement === search) pintarDropdown();
    }

    search.addEventListener('focus', pintarDropdown);
    search.addEventListener('input', () => {
        if (config.mode === 'single') {
            seleccionados = [];
            sincronizarHidden();
        }
        pintarDropdown();
    });
    search.addEventListener('keydown', (e) => {
        const opts = dropdown.querySelectorAll('.combobox-option:not(.is-empty)');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            if (dropdown.hidden) pintarDropdown();
            activo = Math.min(activo + 1, opts.length - 1);
            marcarActivo(opts);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activo = Math.max(activo - 1, 0);
            marcarActivo(opts);
        } else if (e.key === 'Enter') {
            if (!dropdown.hidden && activo >= 0 && opts[activo]) {
                e.preventDefault();
                elegir(opts[activo].dataset.id);
                
            }
        } else if (e.key === 'Escape') {
            cerrarDropdown();
        } else if (e.key === 'Backspace' && config.mode === 'multi' && search.value === '' && seleccionados.length) {
            quitar(seleccionados[seleccionados.length - 1]);
        }
    });
    control.addEventListener('click', (e) => {
        if (e.target === control || e.target === chips) search.focus();
    });
    document.addEventListener('click', (e) => {
        if (!contenedor.contains(e.target)) cerrarDropdown();
    });

    function marcarActivo(opts) {
        opts.forEach((o, i) => o.classList.toggle('is-active', i === activo));
        if (activo >= 0 && opts[activo]) {
            opts[activo].scrollIntoView({ block: 'nearest' });
        }
    }

    if (config.initialIds && config.initialIds.length) {
        seleccionados = config.initialIds.map(String);
    }
    pintarChips();
    sincronizarHidden();

    return {
        setSeleccion(ids) {
            seleccionados = (ids || []).map(String);
            pintarChips();
            sincronizarHidden();
        },
        limpiar() {
            seleccionados = [];
            search.value = '';
            pintarChips();
            sincronizarHidden();
            cerrarDropdown();
        },
        getSeleccion() {
            return seleccionados.slice();
        }
    };
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

let comboEtiquetasAPI = null;

function inicializarEtiquetas() {
    const contenedor = document.getElementById('comboEtiquetas');
    if (!contenedor) return;
    let items = [];
    try {
        items = JSON.parse(contenedor.dataset.etiquetas || '[]');
    } catch (e) {
        items = [];
    }
    comboEtiquetasAPI = crearCombobox(contenedor, {
        mode: 'multi',
        items: items,
        name: 'datos[Etiquetas][]',
        placeholder: 'Busca y selecciona etiquetas...',
    });
}

function pintarIngrediente(valorId = '', cantidad = '') {
    const contenedor = document.getElementById('contenedorIngredientes');
    cantidadItemsIngredientes++;
    const nuevoIngrediente = document.createElement('div');
    nuevoIngrediente.classList.add('ingrediente-item', 'd-flex','w-100', 'gap-2', 'mb-2');

    nuevoIngrediente.innerHTML = `
        <div class="flex-grow-1">
            <div class="input-group rounded-3 flex-grow-1 flex-nowrap ">
                <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0 h-auto">
                    <i class="bi bi-search texto-rojo"></i>
                </span>
                <div class="combobox combobox-ingrediente combobox-admin flex-grow-1"></div>
            </div>
        </div>
        <div class="w-auto">
            <div class="input-group rounded-3 flex-grow-1">
                <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0">
                    <i class="bi bi-rulers texto-rojo"></i>
                </span>
                <input type="number" class="form-control rounded-3 rounded-start-0 texto text-secondary" name="datos[Cantidad][]" placeholder="Ej: 200" value="${escapeHtml(cantidad)}" required min="0.01" max="9999" step="0.01" oninput="if(this.value > 9999) this.value = 9999; if(this.value < 0) this.value = 0.01;">
            </div>
            <div class="text-end"><div class="badge bg-rojoClaro texto-rojo textoPequeno" id="unidadIngrediente">-</div></div>
        </div>
        <div>
            <button type="button" class="btn-eliminar-ingrediente btn border bg-white rounded-3 px-3 d-flex align-items-center" title="Eliminar ingrediente">
                <i class="bi bi-trash text-secondary"></i>
            </button>
        </div>
    `;
    contenedor.appendChild(nuevoIngrediente);

    const combo = nuevoIngrediente.querySelector('.combobox-ingrediente');
    const items = ingredientes.map(i => ({ id: String(i.ID_Ingrediente), nombre: i.Nombre }));
    const initial = valorId ? [String(valorId)] : [];
    crearCombobox(combo, {
        mode: 'single',
        items: items,
        name: 'datos[Ingrediente][]',
        placeholder: 'Busca un ingrediente...',
        initialIds: initial,
        onChange: (id, item) => { 
            const badge = nuevoIngrediente.querySelector('#unidadIngrediente');
            if (!badge) return;
            const ing = ingredientes.find(i => String(i.ID_Ingrediente) === String(id));
            badge.textContent = ing?.Unidad_Base ?? '-';
        }
    });

    if (valorId) {
        const badge = nuevoIngrediente.querySelector('#unidadIngrediente');
        const ing = ingredientes.find(i => String(i.ID_Ingrediente) === String(valorId));
        if (badge) badge.textContent = ing?.Unidad_Base ?? '-';
    }
}

function eliminarIngredienteDe(boton) {
    const item = boton.closest('.ingrediente-item');
    if (!item) return;
    item.remove();
    cantidadItemsIngredientes--;
    actualizarEtiquetasIngrediente();
}

function actualizarEtiquetasIngrediente() {
    cantidadItemsIngredientes = document.querySelectorAll('.ingrediente-item').length;
}

function pintarPaso(contenido = '') {
    const contenedor = document.getElementById('contenedorPasos');
    cantidadItemsPasos++;
    const nuevopaso = document.createElement('div');
    nuevopaso.classList.add('paso-item', 'd-flex', 'gap-2', 'align-items-start', 'mb-2');
    nuevopaso.innerHTML = `
        <div class="paso-numero d-inline-flex align-items-center justify-content-center bg-rojoClaro texto-rojo fw-bold rounded-3 cajaW40 flex-shrink-0">${cantidadItemsPasos}</div>
        <textarea name="datos[paso][]" class="form-control flex-grow-1 rounded-3" rows="2" placeholder="Describe el paso..." required minlength="3" maxlength="500">${escapeHtml(contenido)}</textarea>
        <button type="button" class="btn-eliminar-paso btn border bg-white rounded-3 px-3 d-flex align-items-center align-self-stretch" title="Eliminar paso">
            <i class="bi bi-trash text-secondary"></i>
        </button>
    `;
    contenedor.appendChild(nuevopaso);
}

function eliminarPasoDe(boton) {
    const item = boton.closest('.paso-item');
    if (!item) return;
    item.remove();
    cantidadItemsPasos--;
    actualizarEtiquetasPasos();
}

function actualizarEtiquetasPasos() {
    const pasos = document.querySelectorAll('.paso-item');
    cantidadItemsPasos = pasos.length;
    pasos.forEach((paso, index) => {
        const numero = paso.querySelector('.paso-numero');
        if (numero) numero.textContent = String(index + 1);
    });
}

function inputImagenesEl()        { return document.getElementById('inputImagenesReceta'); }
function gestorImagenesEl()       { return document.getElementById('gestorImagenesReceta'); }
function hiddenImagenesEl()       { return document.getElementById('imagenesRecetaHiddenInputs'); }
function inputPortadaEl()         { return document.getElementById('inputPortadaImagen'); }

function sincronizarArchivosInput() {
    const input = inputImagenesEl();
    if (!input) return;
    const dataTransfer = new DataTransfer();
    imagenesNuevasEstado.forEach((img) => {
        if (img?.file) dataTransfer.items.add(img.file);
    });
    input.files = dataTransfer.files;
}

function asegurarPortadaValida() {
    const input = inputPortadaEl();
    if (!input) return;
    const ids = [
        ...imagenesExistentesEstado,
        ...imagenesNuevasEstado.map((_, i) => `__new__:${i}`)
    ];
    if (!ids.length) {
        input.value = '';
        return;
    }
    if (!ids.includes(input.value)) {
        input.value = ids[0];
    }
}

function eliminarImagenExistente(src) {
    imagenesExistentesEstado = imagenesExistentesEstado.filter((s) => s !== src);
    asegurarPortadaValida();
    renderizarGestorImagenes();
}

function eliminarImagenNueva(indice) {
    const eliminada = imagenesNuevasEstado[indice];
    if (eliminada?.previewUrl) URL.revokeObjectURL(eliminada.previewUrl);
    imagenesNuevasEstado = imagenesNuevasEstado.filter((_, i) => i !== indice);
    sincronizarArchivosInput();
    asegurarPortadaValida();
    renderizarGestorImagenes();
}

function elegirPortada(valor) {
    const input = inputPortadaEl();
    if (!input) return;
    input.value = valor;
    renderizarGestorImagenes();
}

function renderizarGestorImagenes() {
    const gestor = gestorImagenesEl();
    const hidden = hiddenImagenesEl();
    const portadaInput = inputPortadaEl();
    if (!gestor || !hidden || !portadaInput) return;

    asegurarPortadaValida();
    gestor.innerHTML = '';
    hidden.innerHTML = '';

    const items = [
        ...imagenesExistentesEstado.map((src) => ({ tipo: 'existing', src, id: src })),
        ...imagenesNuevasEstado.map((img, i) => ({ tipo: 'new', src: img.previewUrl, id: `__new__:${i}`, indice: i }))
    ];

    if (!items.length) {
        const empty = document.createElement('div');
        empty.className = 'col-12';
        empty.innerHTML = '<div class="text-center text-secondary small p-3 border rounded-3 bg-light">Aun no hay imagenes cargadas.</div>';
        gestor.appendChild(empty);
        return;
    }

    imagenesExistentesEstado.forEach((src) => {
        const inp = document.createElement('input');
        inp.type = 'hidden';
        inp.name = 'imagenes_existentes[]';
        inp.value = src;
        hidden.appendChild(inp);
    });

    items.forEach((item) => {
        const col = document.createElement('div');
        col.className = 'col';
        const esPortada = portadaInput.value === item.id;

        const card = document.createElement('div');
        card.className = 'position-relative border rounded-3 overflow-hidden ' + (esPortada ? 'border-2 border-danger' : '');
        card.style.cursor = 'pointer';
        card.title = esPortada ? 'Portada actual' : 'Pulsa para usar como portada';
        card.addEventListener('click', (ev) => {
            if (ev.target.closest('.btn-eliminar-imagen')) return;
            elegirPortada(item.id);
        });

        const img = document.createElement('img');
        img.src = item.src;
        img.alt = 'Imagen receta';
        img.className = 'w-100';
        img.style.height = '110px';
        img.style.objectFit = 'cover';
        card.appendChild(img);

        if (esPortada) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-danger position-absolute top-0 start-0 m-1';
            badge.textContent = 'Portada';
            card.appendChild(badge);
        }

        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn-eliminar-imagen btn btn-sm btn-light position-absolute top-0 end-0 m-1 rounded-circle border';
        btn.innerHTML = '<i class="bi bi-x"></i>';
        btn.setAttribute('aria-label', 'Quitar imagen');
        btn.addEventListener('click', (ev) => {
            ev.stopPropagation();
            if (item.tipo === 'existing') eliminarImagenExistente(item.src);
            else eliminarImagenNueva(item.indice);
        });
        card.appendChild(btn);

        col.appendChild(card);
        gestor.appendChild(col);
    });
}

function limpiarImagenesReceta() {
    imagenesNuevasEstado.forEach((img) => {
        if (img?.previewUrl) URL.revokeObjectURL(img.previewUrl);
    });
    imagenesExistentesEstado = [];
    imagenesNuevasEstado = [];
    const input = inputImagenesEl();
    if (input) input.value = '';
    const portada = inputPortadaEl();
    if (portada) portada.value = '';
    renderizarGestorImagenes();
}

function inicializarImagenesReceta() {
    const input = inputImagenesEl();
    if (!input || input.dataset.bound === 'true') return;
    input.dataset.bound = 'true';
    input.addEventListener('change', () => {
        const archivos = Array.from(input.files || []);
        archivos.forEach((file) => {
            imagenesNuevasEstado.push({ file, previewUrl: URL.createObjectURL(file) });
        });
        sincronizarArchivosInput();
        renderizarGestorImagenes();
    });
}

function limpiarFormularioReceta() {
    document.getElementById('Titulo').value = '';
    document.getElementById('Descripcion').value = '';
    document.getElementById('Tiempo').value = '';
    document.getElementById('Porciones').value = '';
    document.getElementById('receta_id').value = '0';

    if (comboEtiquetasAPI) comboEtiquetasAPI.limpiar();
    limpiarImagenesReceta();

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
    if (form) form.action = '/pages/administracion/receta/crear';

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
    if (form) form.action = '/pages/administracion/receta/editar';

    limpiarFormularioReceta();

    const response = await fetch(`/pages/administracion/receta/json/${id}`);
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

    if (comboEtiquetasAPI && Array.isArray(receta.Etiquetas)) {
        comboEtiquetasAPI.setSeleccion(receta.Etiquetas.map(String));
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

    const imagenStr = (receta.Imagen ?? '').toString().trim();
    if (imagenStr !== '') {
        imagenesExistentesEstado = imagenStr.split(',')
            .map((s) => s.trim())
            .filter((s) => s !== '');
    } else {
        imagenesExistentesEstado = [];
    }
    const portadaInput = inputPortadaEl();
    if (portadaInput) portadaInput.value = imagenesExistentesEstado[0] || '';
    renderizarGestorImagenes();
}

async function enviarFormularioReceta(form) {

    const botonSubmit = form.querySelector('button[type="submit"]');
    const textoOriginal = botonSubmit ? botonSubmit.textContent : '';

    if (botonSubmit) {
        botonSubmit.disabled = true;
        botonSubmit.textContent = 'Guardando...';
    }

    try {

        const formData = new FormData(form);
        const respuesta = await fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        });

        let json = null;
        try {
            json = await respuesta.json();
        } catch (_) {
            json = null;
        }

        if (!respuesta.ok || !json || json.success === false) {
            const mensaje = (json && json.message) ? json.message : 'No se pudo guardar la receta. Revisa los datos.';
            if (window.Alertas) window.Alertas.error(mensaje);
            return;
        }

        if (window.Alertas) window.Alertas.exito(json.message || 'Receta guardada.');
        const modalEl = document.getElementById('modalCrearReceta');
        const instancia = modalEl ? bootstrap.Modal.getInstance(modalEl) : null;
        if (instancia) instancia.hide();

    } catch (err) {

        console.error('[recetas:enviar]', err);
        if (window.Alertas) window.Alertas.error('Error de conexión al guardar la receta.');

    } finally {

        if (botonSubmit) {
            botonSubmit.disabled = false;
            botonSubmit.textContent = textoOriginal;
        }
    }
}

document.addEventListener('DOMContentLoaded', async function () {
    await obtenerIngredientes();
    inicializarEtiquetas();
    inicializarImagenesReceta();
    renderizarGestorImagenes();

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

    const formReceta = document.getElementById('formCrearReceta');
    if (formReceta) {
        formReceta.addEventListener('submit', function (event) {
            event.preventDefault();
            if (!formReceta.checkValidity()) {
                formReceta.reportValidity();
                return;
            }
            enviarFormularioReceta(formReceta);
        });
    }

    document.addEventListener('click', function (event) {

        const t = event.target;
        if (!t || !t.closest) return;

        if (t.closest('#agregarIngrediente')) { pintarIngrediente(); return; }
        if (t.closest('#agregarPaso'))        { pintarPaso(); return; }

        const btnElimIng = t.closest('.btn-eliminar-ingrediente');
        if (btnElimIng) { eliminarIngredienteDe(btnElimIng); return; }

        const btnElimPaso = t.closest('.btn-eliminar-paso');
        if (btnElimPaso) { eliminarPasoDe(btnElimPaso); return; }
    });
});
