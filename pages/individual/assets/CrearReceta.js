"use strict";
document.addEventListener('DOMContentLoaded', () =>
{
    const ingredientesDisponibles        = window.RecetaData.ingredientesDisponibles;
    const etiquetasDisponibles           = window.RecetaData.etiquetasDisponibles;
    const listaIngredientes              = document.getElementById('listaIngredientes');
    const listaPasos                     = document.getElementById('listaPasos');
    const ingredienteTemplate            = document.getElementById('ingredienteTemplate');
    const pasoTemplate                   = document.getElementById('pasoTemplate');
    const fitCheckbox                    = document.getElementById('fit');
    const abrirNutricion                 = document.getElementById('abrirNutricion');
    const selectorEtiquetas              = document.getElementById('selectorEtiquetas');
    const abrirEtiquetas                 = document.getElementById('abrirEtiquetas');
    const etiquetasSeleccionadasPreview  = document.getElementById('etiquetasSeleccionadasPreview');
    const etiquetasHiddenInputs          = document.getElementById('etiquetasHiddenInputs');

    const ingredienteModalEl             = document.getElementById('ingredienteModal');
    const buscadorIngredienteModal       = document.getElementById('buscadorIngredienteModal');
    const listaSugerenciasIngredientes   = document.getElementById('listaSugerenciasIngredientes');
    const usarTextoIngrediente           = document.getElementById('usarTextoIngrediente');
    const ingredienteModal               = new bootstrap.Modal(ingredienteModalEl);

    const etiquetaModalEl                = document.getElementById('etiquetaModal');
    const buscadorEtiquetaModal          = document.getElementById('buscadorEtiquetaModal');
    const listaSugerenciasEtiquetas      = document.getElementById('listaSugerenciasEtiquetas');
    const etiquetaModal                  = new bootstrap.Modal(etiquetaModalEl);

    const nutricionModalEl               = document.getElementById('nutricionModal');
    const nutricionModal                 = new bootstrap.Modal(nutricionModalEl);
    const modalCalorias                  = document.getElementById('modalCalorias');
    const modalProteina                  = document.getElementById('modalProteina');
    const modalCarbohidratos             = document.getElementById('modalCarbohidratos');
    const modalGrasas                    = document.getElementById('modalGrasas');
    const resumenNutricionItem           = document.getElementById('resumenNutricionItem');
    const inputCalorias                  = document.getElementById('inputCalorias');
    const inputProteina                  = document.getElementById('inputProteina');
    const inputCarbohidratos             = document.getElementById('inputCarbohidratos');
    const inputGrasas                    = document.getElementById('inputGrasas');
    const formReceta                     = document.getElementById('formReceta');
    const inputTitulo                    = formReceta.querySelector('input[name="titulo"]');
    const inputDescripcion               = document.getElementById('descripcionReceta');
    const descripcionWordCount           = document.getElementById('descripcionWordCount');
    const ingredientesError              = document.getElementById('ingredientesError');
    const inputTiempo                    = formReceta.querySelector('input[name="tiempo"]');
    const inputPorciones                 = formReceta.querySelector('input[name="porciones"]');
    const inputImagenesReceta            = document.getElementById('inputImagenesReceta');
    const gestorImagenesReceta           = document.getElementById('gestorImagenesReceta');
    const imagenesRecetaHiddenInputs     = document.getElementById('imagenesRecetaHiddenInputs');
    const inputPortadaImagen             = document.getElementById('inputPortadaImagen');
    const controlesCarruselImagenes      = document.getElementById('controlesCarruselImagenes');

    const mostrarSelectUnidad = (fila) =>
    {
        const selectUnidad = fila.querySelector('.ingrediente-unidad-select');
        if (!selectUnidad) return;
        selectUnidad.classList.remove('d-none');
    };

    const ocultarSelectUnidad = (fila) =>
    {
        const selectUnidad = fila.querySelector('.ingrediente-unidad-select');
        if (!selectUnidad) return;
        selectUnidad.classList.add('d-none');
        selectUnidad.value = '';
    };

    let filaIngredienteActiva  = null;
    const camposNutricionales  = ['calorias', 'proteina', 'carbohidratos', 'grasas'];
    const LIMITE_INGREDIENTE   = 60;
    const LIMITE_CANTIDAD      = 9999;
    const LIMITE_NUTRICION     = 9999;

    let etiquetasSeleccionadasEstado = window.RecetaData.etiquetasSeleccionadas;
    let imagenesExistentesEstado     = window.RecetaData.imagenesExistentes;
    let imagenesNuevasEstado         = [];
    let paginaCarruselImagenes       = 0;

    const obtenerClaveArchivo = (archivo) =>
    {
        if (!archivo) return '';
        return [archivo.name, archivo.size, archivo.lastModified, archivo.type].join('::');
    };

    const renumerarPasos = () =>
    {
        listaPasos.querySelectorAll('.paso-item').forEach((paso, indice) =>
        {
            const etiqueta = paso.querySelector('.input-group-text');
            if (etiqueta) etiqueta.textContent = `Paso ${indice + 1}`;
        });
    };

    const agregarFilaIngrediente = () =>
    {
        const nuevaFila = ingredienteTemplate.content.firstElementChild.cloneNode(true);
        listaIngredientes.appendChild(nuevaFila);
        actualizarResumenNutricionalFila(nuevaFila, false);
        return nuevaFila;
    };

    const contarIngredientesValidos = () =>
    {
        return Array.from(listaIngredientes.querySelectorAll('.ingrediente-item')).filter((fila) =>
        {
            const idIngrediente    = String(fila.querySelector('.ingrediente-id')?.value || '').trim();
            const nombreIngrediente = String(fila.querySelector('.ingrediente-nombre')?.value || '').trim();
            const cantidad          = String(fila.querySelector('input[name="ingrediente_cantidad[]"]')?.value || '').trim();
            return (idIngrediente !== '' || nombreIngrediente !== '') && cantidad !== '';
        }).length;
    };

    const actualizarErrorIngredientes = () =>
    {
        if (!ingredientesError) return;
        ingredientesError.classList.toggle('d-none', contarIngredientesValidos() > 0);
    };

    const asegurarFilaIngrediente = () =>
    {
        if (!listaIngredientes.querySelector('.ingrediente-item')) agregarFilaIngrediente();
    };

    const asegurarPasoInicial = () =>
    {
        if (!listaPasos.querySelector('.paso-item'))
        {
            listaPasos.appendChild(pasoTemplate.content.firstElementChild.cloneNode(true));
        }
        renumerarPasos();
    };

    const normalizarNumero = (valor) =>
    {
        const numero = Number.parseFloat(valor);
        return Number.isFinite(numero) ? Math.max(0, numero) : 0;
    };

    const formatearNumero = (valor) =>
    {
        const numero = normalizarNumero(valor);
        return Number.isInteger(numero) ? `${numero}` : numero.toFixed(2).replace(/\.?0+$/, '');
    };

    const sanearInputNoNegativo = (input) =>
    {
        if (!input) return;
        const valor = normalizarNumero(input.value);
        if (input.value === '') return;
        input.value = input.step === '1' ? `${Math.trunc(valor)}` : formatearNumero(valor);
    };

    const limitarInputEntero = (input, maximo) =>
    {
        if (!input) return;
        const valorLimpio = String(input.value || '').replace(/[^\d]/g, '');
        if (valorLimpio === '')
        {
            input.value = '';
            return;
        }
        input.value = `${Math.min(Number.parseInt(valorLimpio, 10), maximo)}`;
    };

    const limitarInputCantidad = (input) =>
    {
        if (!input) return;

        const bruto = String(input.value || '').replace(',', '.').replace(/[^\d.]/g, '');
        const partes = bruto.split('.');
        const entero = partes.shift() || '';
        const decimal = partes.join('');
        const normalizado = decimal === '' ? entero : `${entero}.${decimal.slice(0, 2)}`;

        if (normalizado === '') {
            input.value = '';
            return;
        }

        const numero = Number.parseFloat(normalizado);

        if (!Number.isFinite(numero) || numero <= 0) {
            input.value = '';
            return;
        }

        input.value = formatearNumero(Math.min(numero, LIMITE_CANTIDAD));
    };

    const limitarInputNutricional = (input) =>
    {
        if (!input) return;

        const bruto = String(input.value || '').replace(',', '.').replace(/[^\d.]/g, '');
        const partes = bruto.split('.');
        const entero = partes.shift() || '';
        const decimal = partes.join('');
        const normalizado = decimal === '' ? entero : `${entero}.${decimal.slice(0, 2)}`;

        if (normalizado === '') {
            input.value = '';
            return 0;
        }

        const numero = Number.parseFloat(normalizado);

        if (!Number.isFinite(numero) || numero < 0) {
            input.value = '0';
            return 0;
        }

        const limitado = Math.min(numero, LIMITE_NUTRICION);
        input.value = formatearNumero(limitado);
        return limitado;
    };

    const obtenerValoresNutricionalesFila = (fila) =>
    ({
        calorias:      normalizarNumero(fila.querySelector('.ingrediente-calorias')?.value),
        proteina:      normalizarNumero(fila.querySelector('.ingrediente-proteina')?.value),
        carbohidratos: normalizarNumero(fila.querySelector('.ingrediente-carbohidratos')?.value),
        grasas:        normalizarNumero(fila.querySelector('.ingrediente-grasas')?.value)
    });

    const actualizarResumenNutricionalFila = (fila, esIngredienteExistente) =>
    {
        const wrapper    = fila.querySelector('.ingrediente-nutricion-wrapper');
        const contenedor = fila.querySelector('.ingrediente-nutricion');
        const resumen    = fila.querySelector('.ingrediente-nutricion-resumen');
        const formulario = fila.querySelector('.ingrediente-nutricion-formulario');
        const inputs     = fila.querySelectorAll('.ingrediente-nutricion-input');
        const valores    = obtenerValoresNutricionalesFila(fila);
        const modoFit    = fitCheckbox.checked;

        if (!wrapper || !contenedor || !resumen || !formulario) return;

        contenedor.dataset.modo = esIngredienteExistente ? 'existente' : 'nuevo';
        wrapper.classList.toggle('d-none', !modoFit);
        inputs.forEach((input) => { input.disabled = !modoFit; });

        if (!modoFit)
        {
            resumen.textContent = '';
        }
        else if (esIngredienteExistente)
        {
            resumen.textContent = `Valores cargados automaticamente: ${formatearNumero(valores.calorias)} kcal, ${formatearNumero(valores.proteina)} g proteina, ${formatearNumero(valores.carbohidratos)} g carbohidratos, ${formatearNumero(valores.grasas)} g grasas. Puedes ajustarlos si lo necesitas.`;
        }
        else
        {
            resumen.textContent = 'Ingrediente nuevo: anade o modifica sus valores nutricionales para incluirlos en el total de la receta.';
        }
    };

    const actualizarEstadoNutricion = () =>
    {
        const modoFit = fitCheckbox.checked;
        abrirNutricion.disabled = !modoFit;
        abrirNutricion.classList.toggle('disabled', !modoFit);
        resumenNutricionItem?.classList.toggle('d-none', !modoFit);
        listaIngredientes.querySelectorAll('.ingrediente-item').forEach((fila) =>
        {
            const esExistente = String(fila.querySelector('.ingrediente-id')?.value || '').trim() !== '';
            actualizarResumenNutricionalFila(fila, esExistente);
        });
    };

    const aplicarNutricionEnFila = (fila, nutricion = {}, esIngredienteExistente = false) =>
    {
        camposNutricionales.forEach((campo) =>
        {
            const valor        = normalizarNumero(nutricion[campo] ?? 0);
            const inputHidden  = fila.querySelector(`.ingrediente-${campo}`);
            const inputVisible = fila.querySelector(`.ingrediente-nutricion-input[data-campo="${campo}"]`);
            if (inputHidden)  inputHidden.value  = valor;
            if (inputVisible) inputVisible.value = valor;
        });
        actualizarResumenNutricionalFila(fila, esIngredienteExistente);
        recalcularNutricionReceta();
    };

    const recalcularNutricionReceta = () =>
    {
        const total = { calorias: 0, proteina: 0, carbohidratos: 0, grasas: 0 };
        listaIngredientes.querySelectorAll('.ingrediente-item').forEach((fila) =>
        {
            total.calorias      += normalizarNumero(fila.querySelector('.ingrediente-calorias')?.value);
            total.proteina      += normalizarNumero(fila.querySelector('.ingrediente-proteina')?.value);
            total.carbohidratos += normalizarNumero(fila.querySelector('.ingrediente-carbohidratos')?.value);
            total.grasas        += normalizarNumero(fila.querySelector('.ingrediente-grasas')?.value);
        });
        modalCalorias.value      = total.calorias.toFixed(2);
        modalProteina.value      = total.proteina.toFixed(2);
        modalCarbohidratos.value = total.carbohidratos.toFixed(2);
        modalGrasas.value        = total.grasas.toFixed(2);
        sincronizarInputsNutricion();
    };

    const abrirModalIngrediente = (fila) =>
    {
        filaIngredienteActiva = fila;
        const inputNombre = fila.querySelector('.ingrediente-nombre');
        buscadorIngredienteModal.value = inputNombre.value.trim();
        renderizarSugerenciasIngredientes(buscadorIngredienteModal.value.trim());
        ingredienteModal.show();
        setTimeout(() => buscadorIngredienteModal.focus(), 200);
    };

    const seleccionarIngredienteEnFila = (ingrediente) =>
    {
        if (!filaIngredienteActiva) return;

        filaIngredienteActiva.querySelector('.ingrediente-id').value     = ingrediente?.ID_Ingrediente ?? '';
        filaIngredienteActiva.querySelector('.ingrediente-nombre').value = ingrediente?.Nombre ?? buscadorIngredienteModal.value.trim();
        aplicarNutricionEnFila(
            filaIngredienteActiva,
            {
                calorias:      ingrediente?.Calorias,
                proteina:      ingrediente?.Proteina,
                carbohidratos: ingrediente?.Carbohidratos,
                grasas:        ingrediente?.Grasas
            },
            true
        );
        ocultarSelectUnidad(filaIngredienteActiva);
        ingredienteModal.hide();
    };

    const usarTextoLibre = () =>
    {
        if (!filaIngredienteActiva) return;
        const nombreLibre = buscadorIngredienteModal.value.trim();
        if (nombreLibre.length > LIMITE_INGREDIENTE)
        {
            window.Alertas.error(`Los ingredientes nuevos no pueden superar los ${LIMITE_INGREDIENTE} caracteres.`);
            buscadorIngredienteModal.focus();
            return;
        }
        filaIngredienteActiva.querySelector('.ingrediente-id').value     = '';
        filaIngredienteActiva.querySelector('.ingrediente-nombre').value = nombreLibre;
        aplicarNutricionEnFila(filaIngredienteActiva, {}, false);
        mostrarSelectUnidad(filaIngredienteActiva);
        filaIngredienteActiva.querySelector('.ingrediente-unidad-select')?.focus();
        ingredienteModal.hide();
    };

    const renderizarSugerenciasIngredientes = (texto) =>
    {
        const termino     = texto.toLowerCase();
        const sugerencias = ingredientesDisponibles.filter((i) => i.Nombre.toLowerCase().includes(termino));
        listaSugerenciasIngredientes.innerHTML = '';
        if (sugerencias.length === 0)
        {
            listaSugerenciasIngredientes.innerHTML = '<div class="list-group-item text-muted">No hay coincidencias. Puedes usar el texto escrito como ingrediente nuevo.</div>';
            return;
        }
        sugerencias.forEach((ingrediente) =>
        {
            const boton = document.createElement('button');
            boton.type      = 'button';
            boton.className = 'list-group-item list-group-item-action';
            boton.textContent = ingrediente.Nombre;
            boton.addEventListener('click', () => seleccionarIngredienteEnFila(ingrediente));
            listaSugerenciasIngredientes.appendChild(boton);
        });
    };

    const renderizarEtiquetasSeleccionadas = () =>
    {
        etiquetasSeleccionadasPreview.innerHTML = '';
        etiquetasHiddenInputs.innerHTML         = '';
        if (!etiquetasSeleccionadasEstado.length)
        {
            const placeholder     = document.createElement('span');
            placeholder.className = 'text-body-secondary small';
            placeholder.textContent = 'No hay etiquetas seleccionadas.';
            etiquetasSeleccionadasPreview.appendChild(placeholder);
            selectorEtiquetas.value = '';
            return;
        }
        const nombres = [];
        etiquetasSeleccionadasEstado.forEach((idEtiqueta) =>
        {
            const etiqueta = etiquetasDisponibles.find((item) => Number(item.ID_Etiqueta) === Number(idEtiqueta));
            if (!etiqueta) return;
            nombres.push(etiqueta.Nombre);

            const badge       = document.createElement('button');
            badge.type        = 'button';
            badge.className   = 'btn btn-sm btn-outline-secondary rounded-pill';
            badge.textContent = `${etiqueta.Nombre} ×`;
            badge.addEventListener('click', () =>
            {
                etiquetasSeleccionadasEstado = etiquetasSeleccionadasEstado.filter((id) => Number(id) !== Number(idEtiqueta));
                renderizarEtiquetasSeleccionadas();
                renderizarSugerenciasEtiquetas(buscadorEtiquetaModal.value.trim());
            });
            etiquetasSeleccionadasPreview.appendChild(badge);

            const hidden  = document.createElement('input');
            hidden.type   = 'hidden';
            hidden.name   = 'etiquetas[]';
            hidden.value  = idEtiqueta;
            etiquetasHiddenInputs.appendChild(hidden);
        });
        selectorEtiquetas.value = nombres.join(', ');
    };

    const alternarEtiquetaSeleccionada = (idEtiqueta) =>
    {
        const idNormalizado = Number(idEtiqueta);
        if (etiquetasSeleccionadasEstado.some((id) => Number(id) === idNormalizado))
        {
            etiquetasSeleccionadasEstado = etiquetasSeleccionadasEstado.filter((id) => Number(id) !== idNormalizado);
        }
        else
        {
            etiquetasSeleccionadasEstado.push(idNormalizado);
        }
        renderizarEtiquetasSeleccionadas();
        renderizarSugerenciasEtiquetas(buscadorEtiquetaModal.value.trim());
    };

    const renderizarSugerenciasEtiquetas = (texto) =>
    {
        const termino     = texto.toLowerCase();
        const sugerencias = etiquetasDisponibles.filter((e) => e.Nombre.toLowerCase().includes(termino));
        listaSugerenciasEtiquetas.innerHTML = '';
        if (sugerencias.length === 0)
        {
            listaSugerenciasEtiquetas.innerHTML = '<div class="list-group-item text-muted">No hay etiquetas con ese nombre.</div>';
            return;
        }
        sugerencias.forEach((etiqueta) =>
        {
            const idEtiqueta  = Number(etiqueta.ID_Etiqueta);
            const seleccionada = etiquetasSeleccionadasEstado.some((id) => Number(id) === idEtiqueta);
            const boton        = document.createElement('button');
            boton.type         = 'button';
            boton.className    = `list-group-item list-group-item-action d-flex justify-content-between align-items-center${seleccionada ? ' active' : ''}`;
            boton.innerHTML    = `<span>${etiqueta.Nombre}</span><span class="badge ${seleccionada ? 'text-bg-light text-dark' : 'text-bg-secondary'} rounded-pill">${seleccionada ? 'Seleccionada' : 'Elegir'}</span>`;
            boton.addEventListener('click', () => alternarEtiquetaSeleccionada(idEtiqueta));
            listaSugerenciasEtiquetas.appendChild(boton);
        });
    };

    const sincronizarInputsNutricion = () =>
    {
        inputCalorias.value      = modalCalorias.value      || 0;
        inputProteina.value      = modalProteina.value      || 0;
        inputCarbohidratos.value = modalCarbohidratos.value || 0;
        inputGrasas.value        = modalGrasas.value        || 0;
    };

    const contarCaracteres = (texto) => String(texto || '').length;

    const actualizarContadorDescripcion = () =>
    {
        if (!inputDescripcion || !descripcionWordCount) return 0;
        const total = contarCaracteres(inputDescripcion.value);
        descripcionWordCount.textContent = `${total}/350 caracteres`;
        descripcionWordCount.classList.toggle('text-danger', total > 350);
        return total;
    };

    const sincronizarArchivosInput = () =>
    {
        if (!inputImagenesReceta) return;
        const dataTransfer = new DataTransfer();
        imagenesNuevasEstado.forEach((img) => { if (img?.file) dataTransfer.items.add(img.file); });
        inputImagenesReceta.files = dataTransfer.files;
    };

    const asegurarPortadaValida = () =>
    {
        const idsDisponibles =
        [
            ...imagenesExistentesEstado,
            ...imagenesNuevasEstado.map((_, i) => `__new__:${i}`)
        ];
        if (!idsDisponibles.length)
        {
            inputPortadaImagen.value = '';
            return;
        }
        if (!idsDisponibles.includes(inputPortadaImagen.value)) inputPortadaImagen.value = idsDisponibles[0];
    };

    const eliminarImagenExistente = (src) =>
    {
        imagenesExistentesEstado = imagenesExistentesEstado.filter((img) => img !== src);
        asegurarPortadaValida();
        renderizarGestorImagenes();
    };

    const eliminarImagenNueva = (indice) =>
    {
        const eliminada = imagenesNuevasEstado[indice];
        if (eliminada?.previewUrl) URL.revokeObjectURL(eliminada.previewUrl);
        imagenesNuevasEstado = imagenesNuevasEstado.filter((_, idx) => idx !== indice);
        sincronizarArchivosInput();
        asegurarPortadaValida();
        renderizarGestorImagenes();
    };

    const renderizarGestorImagenes = () =>
    {
        if (!gestorImagenesReceta || !imagenesRecetaHiddenInputs || !controlesCarruselImagenes) return;
        asegurarPortadaValida();
        gestorImagenesReceta.innerHTML        = '';
        imagenesRecetaHiddenInputs.innerHTML  = '';
        controlesCarruselImagenes.innerHTML   = '';
        controlesCarruselImagenes.classList.add('d-none');

        const items =
        [
            ...imagenesExistentesEstado.map((src) => ({ tipo: 'existing', src })),
            ...imagenesNuevasEstado.map((img, i) => ({ tipo: 'new', src: img.previewUrl, indice: i }))
        ];

        if (!items.length)
        {
            const empty       = document.createElement('div');
            empty.className   = 'col-12';
            empty.innerHTML   = '<div class="recipe-image-empty">Aun no hay imagenes cargadas.</div>';
            gestorImagenesReceta.appendChild(empty);
            return;
        }

        imagenesExistentesEstado.forEach((src) =>
        {
            const hidden  = document.createElement('input');
            hidden.type   = 'hidden';
            hidden.name   = 'imagenes_existentes[]';
            hidden.value  = src;
            imagenesRecetaHiddenInputs.appendChild(hidden);
        });

        const crearTarjetaImagen = (item, visualIndex) =>
        {
            const imageId    = item.tipo === 'existing' ? item.src : `__new__:${item.indice}`;
            const esPortada  = inputPortadaImagen.value === imageId;

            const col        = document.createElement('div');
            col.className    = 'col';

            const card       = document.createElement('div');
            card.className   = `recipe-image-manager-card h-100 d-flex flex-column${esPortada ? ' is-cover' : ''}`;

            const img        = document.createElement('img');
            img.src          = item.src;
            img.alt          = item.tipo === 'existing' ? `Imagen actual ${visualIndex + 1}` : `Nueva imagen ${visualIndex + 1}`;
            img.className    = 'recipe-image-manager-media';
            img.onerror      = () => { img.onerror = null; img.src = '/uploads/NoImg.jpg'; };

            const body       = document.createElement('div');
            body.className   = 'recipe-image-manager-body';

            const header     = document.createElement('div');
            header.className = 'recipe-image-manager-header';

            const title         = document.createElement('p');
            title.className     = 'recipe-image-manager-title';
            title.textContent   = `Foto ${visualIndex + 1}`;

            const botonPortada        = document.createElement('button');
            botonPortada.type         = 'button';
            botonPortada.className    = `btn btn-sm ${esPortada ? 'btn-outline-success recipe-image-manager-primary-btn is-active' : 'btn-outline-secondary recipe-image-manager-primary-btn'}`;
            botonPortada.textContent  = esPortada ? 'Portada actual' : 'Usar como portada';
            botonPortada.addEventListener('click', () => { inputPortadaImagen.value = imageId; renderizarGestorImagenes(); });

            header.appendChild(title);

            const badge         = document.createElement('div');
            badge.className     = 'recipe-image-manager-meta';
            badge.textContent   = item.tipo === 'existing' ? 'Foto ya guardada' : 'Se agregara al guardar';

            if (esPortada)
            {
                const coverBadge        = document.createElement('span');
                coverBadge.className    = 'recipe-image-manager-cover-badge';
                coverBadge.textContent  = 'Portada';
                body.appendChild(coverBadge);
            }

            const acciones        = document.createElement('div');
            acciones.className    = 'recipe-image-manager-actions';

            const botonEliminar       = document.createElement('button');
            botonEliminar.type        = 'button';
            botonEliminar.className   = 'btn btn-outline-danger btn-sm';
            botonEliminar.textContent = 'Eliminar';
            botonEliminar.addEventListener('click', () =>
            {
                if (item.tipo === 'existing') { eliminarImagenExistente(item.src); return; }
                eliminarImagenNueva(item.indice);
            });

            acciones.appendChild(botonPortada);
            acciones.appendChild(botonEliminar);
            body.appendChild(header);
            body.appendChild(badge);
            body.appendChild(acciones);
            card.appendChild(img);
            card.appendChild(body);
            col.appendChild(card);
            return col;
        };

        if (items.length <= 3)
        {
            paginaCarruselImagenes = 0;
            items.forEach((item, i) => gestorImagenesReceta.appendChild(crearTarjetaImagen(item, i)));
            return;
        }

        const tamanoPagina  = 3;
        const totalPaginas  = Math.ceil(items.length / tamanoPagina);
        paginaCarruselImagenes = Math.min(paginaCarruselImagenes, totalPaginas - 1);

        const carouselHeader    = document.createElement('div');
        carouselHeader.className = 'recipe-image-carousel-header';

        const status            = document.createElement('div');
        status.className        = 'recipe-image-carousel-status';
        status.textContent      = `Mostrando grupo ${paginaCarruselImagenes + 1} de ${totalPaginas}. Puedes guardar todas las fotos que necesites.`;

        const nav               = document.createElement('div');
        nav.className           = 'recipe-image-carousel-nav';

        const prevBtn           = document.createElement('button');
        prevBtn.type            = 'button';
        prevBtn.className       = 'btn btn-outline-secondary btn-sm rounded-pill';
        prevBtn.textContent     = '<';
        prevBtn.disabled        = paginaCarruselImagenes === 0;
        prevBtn.addEventListener('click', () =>
        {
            if (paginaCarruselImagenes === 0) return;
            paginaCarruselImagenes -= 1;
            renderizarGestorImagenes();
        });

        const nextBtn           = document.createElement('button');
        nextBtn.type            = 'button';
        nextBtn.className       = 'btn btn-outline-secondary btn-sm rounded-pill';
        nextBtn.textContent     = '>';
        nextBtn.disabled        = paginaCarruselImagenes >= totalPaginas - 1;
        nextBtn.addEventListener('click', () =>
        {
            if (paginaCarruselImagenes >= totalPaginas - 1) return;
            paginaCarruselImagenes += 1;
            renderizarGestorImagenes();
        });

        nav.appendChild(prevBtn);
        nav.appendChild(nextBtn);
        carouselHeader.appendChild(status);
        carouselHeader.appendChild(nav);

        const inicio      = paginaCarruselImagenes * tamanoPagina;
        const paginaItems = items.slice(inicio, inicio + tamanoPagina);
        paginaItems.forEach((item, i) => gestorImagenesReceta.appendChild(crearTarjetaImagen(item, inicio + i)));

        controlesCarruselImagenes.appendChild(carouselHeader);
        controlesCarruselImagenes.classList.remove('d-none');
    };

    // ── Event listeners ──

    document.getElementById('agregarIngrediente').addEventListener('click', () => agregarFilaIngrediente());

    document.getElementById('agregarPaso').addEventListener('click', () =>
    {
        listaPasos.appendChild(pasoTemplate.content.firstElementChild.cloneNode(true));
        renumerarPasos();
    });

    listaIngredientes.addEventListener('click', (event) =>
    {
        const botonEliminar = event.target.closest('.eliminar-ingrediente');
        if (botonEliminar)
        {
            botonEliminar.closest('.ingrediente-item')?.remove();
            asegurarFilaIngrediente();
            recalcularNutricionReceta();
            actualizarErrorIngredientes();
            return;
        }
        const inputNombre = event.target.closest('.ingrediente-nombre');
        if (inputNombre) abrirModalIngrediente(inputNombre.closest('.ingrediente-item'));
    });

    listaIngredientes.addEventListener('input', (event) =>
    {
        const inputNombre = event.target.closest('.ingrediente-nombre');
        if (inputNombre)
        {
            const fila = inputNombre.closest('.ingrediente-item');
            fila.querySelector('.ingrediente-id').value = '';
            aplicarNutricionEnFila(fila, {}, false);
            actualizarErrorIngredientes();
            const nombre = inputNombre.value.trim();
            if (nombre !== '') mostrarSelectUnidad(fila);
            else ocultarSelectUnidad(fila);
            return;
        }
        const inputCantidad = event.target.closest('.ingrediente-cantidad');
        if (inputCantidad)
        {
            limitarInputCantidad(inputCantidad);
            actualizarErrorIngredientes();
            return;
        }
    });

    listaIngredientes.addEventListener('change', (event) =>
    {
        const inputCantidad = event.target.closest('.ingrediente-cantidad');
        if (!inputCantidad) return;
        limitarInputCantidad(inputCantidad);
        actualizarErrorIngredientes();
    });

    listaIngredientes.addEventListener('input', (event) =>
    {
        const inputNutricion = event.target.closest('.ingrediente-nutricion-input');
        if (!inputNutricion) return;
        limitarInputNutricional(inputNutricion);
        const fila       = inputNutricion.closest('.ingrediente-item');
        const campo      = inputNutricion.dataset.campo;
        const inputHidden = fila.querySelector(`.ingrediente-${campo}`);
        if (inputHidden) inputHidden.value = normalizarNumero(inputNutricion.value);
        recalcularNutricionReceta();
    });

    listaIngredientes.addEventListener('change', (event) =>
    {
        const inputNutricion = event.target.closest('.ingrediente-nutricion-input');
        if (!inputNutricion) return;
        limitarInputNutricional(inputNutricion);
        const fila       = inputNutricion.closest('.ingrediente-item');
        const campo      = inputNutricion.dataset.campo;
        const inputHidden = fila.querySelector(`.ingrediente-${campo}`);
        if (inputHidden) inputHidden.value = normalizarNumero(inputNutricion.value);
        recalcularNutricionReceta();
    });

    listaPasos.addEventListener('click', (event) =>
    {
        const botonEliminar = event.target.closest('.eliminar-paso');
        if (!botonEliminar) return;
        botonEliminar.closest('.paso-item')?.remove();
        asegurarPasoInicial();
    });

    listaPasos.addEventListener('input', (event) =>
    {
        const inputPaso = event.target.closest('input[name="pasos[]"]');
        if (!inputPaso) return;
        const pasosError = document.getElementById('pasosError');
        if (!pasosError) return;
        const hayPasoValido = Array.from(listaPasos.querySelectorAll('input[name="pasos[]"]'))
            .some((input) => String(input.value || '').trim() !== '');
        pasosError.classList.toggle('d-none', hayPasoValido);
    });

    buscadorIngredienteModal.addEventListener('input', () => renderizarSugerenciasIngredientes(buscadorIngredienteModal.value.trim()));

    buscadorIngredienteModal.addEventListener('keydown', (event) =>
    {
        if (event.key === 'Enter') { event.preventDefault(); usarTextoLibre(); }
    });

    abrirEtiquetas.addEventListener('click', () =>
    {
        renderizarSugerenciasEtiquetas(buscadorEtiquetaModal.value.trim());
        etiquetaModal.show();
        setTimeout(() => buscadorEtiquetaModal.focus(), 200);
    });

    selectorEtiquetas.addEventListener('click', () => abrirEtiquetas.click());

    buscadorEtiquetaModal.addEventListener('input', () => renderizarSugerenciasEtiquetas(buscadorEtiquetaModal.value.trim()));

    inputImagenesReceta?.addEventListener('change', () =>
    {
        const archivos = Array.from(inputImagenesReceta.files || []);
        const clavesExistentes = new Set(
            imagenesNuevasEstado.map((img) => obtenerClaveArchivo(img?.file)).filter((c) => c !== '')
        );
        const nuevasImagenes = archivos
            .filter((a) => a.type.startsWith('image/'))
            .filter((a) =>
            {
                const clave = obtenerClaveArchivo(a);
                if (clavesExistentes.has(clave)) return false;
                clavesExistentes.add(clave);
                return true;
            })
            .map((a) => ({ file: a, previewUrl: URL.createObjectURL(a) }));

        if (!nuevasImagenes.length) { sincronizarArchivosInput(); return; }

        imagenesNuevasEstado = [...imagenesNuevasEstado, ...nuevasImagenes];
        const totalImagenes  = imagenesExistentesEstado.length + imagenesNuevasEstado.length;
        if (totalImagenes > 3) paginaCarruselImagenes = Math.ceil(totalImagenes / 3) - 1;
        sincronizarArchivosInput();
        asegurarPortadaValida();
        renderizarGestorImagenes();
    });

    usarTextoIngrediente.addEventListener('click', usarTextoLibre);

    abrirNutricion.addEventListener('click', () =>
    {
        if (!fitCheckbox.checked) return;
        nutricionModal.show();
    });

    fitCheckbox.addEventListener('change', () => actualizarEstadoNutricion());

    listaIngredientes.querySelectorAll('.ingrediente-item').forEach((fila) =>
    {
        const esExistente = String(fila.querySelector('.ingrediente-id')?.value || '').trim() !== '';
        actualizarResumenNutricionalFila(fila, esExistente);
        const id     = String(fila.querySelector('.ingrediente-id')?.value || '').trim();
        const nombre = String(fila.querySelector('.ingrediente-nombre')?.value || '').trim();
        if (id !== '' || nombre === '') ocultarSelectUnidad(fila);
        else mostrarSelectUnidad(fila);
    });

    document.querySelectorAll('.no-negative').forEach((input) =>
    {
        input.addEventListener('input', () => limitarInputNutricional(input));
        input.addEventListener('blur',  () => limitarInputNutricional(input));
    });

    inputTiempo?.addEventListener('input',    () => limitarInputEntero(inputTiempo, 1440));
    inputPorciones?.addEventListener('input', () => limitarInputEntero(inputPorciones, 100));
    inputDescripcion?.addEventListener('input', actualizarContadorDescripcion);

    formReceta.addEventListener('submit', (event) =>
    {
        listaIngredientes.querySelectorAll('.ingrediente-cantidad').forEach((input) => limitarInputCantidad(input));
        listaIngredientes.querySelectorAll('.ingrediente-nutricion-input').forEach((input) => limitarInputNutricional(input));

        const ingredientesSinUnidad = [];
        listaIngredientes.querySelectorAll('.ingrediente-item').forEach((fila) =>
        {
            const id     = String(fila.querySelector('.ingrediente-id')?.value || '').trim();
            const nombre = String(fila.querySelector('.ingrediente-nombre')?.value || '').trim();
            const select = fila.querySelector('.ingrediente-unidad-select');
            const unidadVisible = !!select && !select.classList.contains('d-none');
            if (id === '' && nombre !== '' && unidadVisible && select.value === '')
            {
                ingredientesSinUnidad.push(nombre);
            }
        });

        if (ingredientesSinUnidad.length > 0)
        {
            event.preventDefault();
            window.Alertas.error('Debes seleccionar la unidad (g o ml) para los ingredientes nuevos: ' + ingredientesSinUnidad.join(', '));
            const primerSelectVacio = Array.from(listaIngredientes.querySelectorAll('.ingrediente-unidad-select'))
                .find((select) => !select.classList.contains('d-none') && select.value === '');
            if (primerSelectVacio) primerSelectVacio.focus();
            return;
        }

        document.querySelectorAll('.no-negative').forEach((input) => sanearInputNoNegativo(input));
        asegurarPortadaValida();

        const titulo = String(inputTitulo?.value || '').trim();
        if (titulo === '')
        {
            event.preventDefault();
            window.Alertas.error('Debes indicar el titulo de la receta.');
            inputTitulo?.focus();
            return;
        }
        if (titulo.length > 60)
        {
            event.preventDefault();
            window.Alertas.error('El titulo no puede superar los 60 caracteres.');
            inputTitulo?.focus();
            return;
        }

        const caracteresDescripcion = actualizarContadorDescripcion();
        if (caracteresDescripcion > 350)
        {
            event.preventDefault();
            window.Alertas.error('La descripcion no puede superar los 350 caracteres.');
            inputDescripcion?.focus();
            return;
        }

        const ingredienteFueraDeLimite = Array.from(listaIngredientes.querySelectorAll('.ingrediente-nombre')).find((input) =>
        {
            const valor = String(input.value || '').trim();
            const id    = String(input.closest('.ingrediente-item')?.querySelector('.ingrediente-id')?.value || '').trim();
            return id === '' && valor.length > 60;
        });
        if (ingredienteFueraDeLimite)
        {
            event.preventDefault();
            window.Alertas.error('Los ingredientes nuevos no pueden superar los 60 caracteres.');
            ingredienteFueraDeLimite.focus();
            return;
        }

        const pasoFueraDeLimite = Array.from(listaPasos.querySelectorAll('input[name="pasos[]"]'))
            .find((input) => String(input.value || '').trim().length > 100);
        if (pasoFueraDeLimite)
        {
            event.preventDefault();
            window.Alertas.error('Cada paso no puede superar los 100 caracteres.');
            pasoFueraDeLimite.focus();
            return;
        }

        const tiempoValor = String(inputTiempo?.value ?? '').trim();
        if (tiempoValor === '' || Number.parseInt(tiempoValor, 10) < 1)
        {
            event.preventDefault();
            window.Alertas.error('Debes indicar el tiempo de preparacion (minimo 1 minuto).');
            inputTiempo?.focus();
            return;
        }
        const tiempo = Number.parseInt(tiempoValor, 10);
        if (Number.isFinite(tiempo) && tiempo > 1440)
        {
            event.preventDefault();
            window.Alertas.error('El tiempo no puede superar los 1440 minutos.');
            inputTiempo?.focus();
            return;
        }

        const porcionesValor = String(inputPorciones?.value ?? '').trim();
        if (porcionesValor === '' || Number.parseInt(porcionesValor, 10) < 1)
        {
            event.preventDefault();
            window.Alertas.error('Debes indicar las porciones (minimo 1).');
            inputPorciones?.focus();
            return;
        }
        const porciones = Number.parseInt(porcionesValor, 10);
        if (Number.isFinite(porciones) && porciones > 100)
        {
            event.preventDefault();
            window.Alertas.error('Las porciones no pueden superar las 100.');
            inputPorciones?.focus();
            return;
        }

        if (contarIngredientesValidos() < 1)
        {
            event.preventDefault();
            actualizarErrorIngredientes();
            listaIngredientes.querySelector('.ingrediente-nombre')?.focus();
            return;
        }

        const pasosValidos = Array.from(listaPasos.querySelectorAll('input[name="pasos[]"]'))
            .filter((input) => String(input.value || '').trim() !== '').length;
        if (pasosValidos < 1)
        {
            event.preventDefault();
            const pasosError = document.getElementById('pasosError');
            if (pasosError) pasosError.classList.remove('d-none');
            listaPasos.querySelector('input[name="pasos[]"]')?.focus();
            return;
        }

        const pasosError = document.getElementById('pasosError');
        if (pasosError) pasosError.classList.add('d-none');
        actualizarErrorIngredientes();
    });

    // ── Inicialización ──
    asegurarFilaIngrediente();
    asegurarPasoInicial();
    renderizarEtiquetasSeleccionadas();
    renderizarSugerenciasEtiquetas('');
    recalcularNutricionReceta();
    actualizarEstadoNutricion();
    actualizarErrorIngredientes();
    actualizarContadorDescripcion();
    renderizarGestorImagenes();
});
