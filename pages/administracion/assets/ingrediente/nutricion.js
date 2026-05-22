"use strict";
(() => {

    let formularioActivo = null;
    let botonOrigen      = null;
    let modalAnterior    = null;

    
    function obtenerInputsNutricion(form) {
        return {
            calorias:      form?.querySelector('[name="datos[calorias]"]')      || document.getElementById('inputCalorias'),
            proteina:      form?.querySelector('[name="datos[proteina]"]')      || document.getElementById('inputProteina'),
            carbohidratos: form?.querySelector('[name="datos[carbohidratos]"]') || document.getElementById('inputCarbohidratos'),
            grasas:        form?.querySelector('[name="datos[grasas]"]')        || document.getElementById('inputGrasas'),
        };
    }

    
    function nutricionRellena(form) {
        const inputs = obtenerInputsNutricion(form);
        return ['calorias', 'proteina', 'carbohidratos', 'grasas'].every(k => {
            const el = inputs[k];
            return el && el.value !== '' && !Number.isNaN(Number(el.value));
        });
    }

    document.addEventListener('DOMContentLoaded', function () {

        const modalNutricion = document.getElementById('modalNutricion');
        if (!modalNutricion) return;

        modalNutricion.addEventListener('show.bs.modal', function (event) {

            botonOrigen      = event.relatedTarget;
            modalAnterior    = botonOrigen?.closest('.modal');
            formularioActivo = botonOrigen?.closest('form');

            const boton = botonOrigen;
            const form  = formularioActivo;

            const valorPara = (clave) => {
                const desdeBoton = boton?.dataset?.[clave];
                const desdeForm  = form?.querySelector(`[name="datos[${clave}]"]`)?.value;
                if (desdeBoton !== undefined && desdeBoton !== '') return desdeBoton;
                if (desdeForm  !== undefined && desdeForm  !== '') return desdeForm;
                return '';
            };

            document.getElementById('calorias').value      = valorPara('calorias');
            document.getElementById('proteina').value      = valorPara('proteina');
            document.getElementById('carbohidratos').value = valorPara('carbohidratos');
            document.getElementById('grasas').value        = valorPara('grasas');
        });

        modalNutricion.addEventListener('hidden.bs.modal', function () {
            if (modalAnterior) {
                modalAnterior.dataset.restoreState = 'true';
                const previousModal = bootstrap.Modal.getOrCreateInstance(modalAnterior);
                previousModal.show();
                modalAnterior = null;
                botonOrigen   = null;
            }
        });
    });

    const btnNutricion = document.getElementById('btnGuardarNutricion') || document.getElementById('btnNutricion');
    if (btnNutricion) {

        btnNutricion.addEventListener('click', function () {

            const campos = {
                calorias:      document.getElementById('calorias'),
                proteina:      document.getElementById('proteina'),
                carbohidratos: document.getElementById('carbohidratos'),
                grasas:        document.getElementById('grasas'),
            };

            const vacios = Object.entries(campos).filter(([, el]) => !el || el.value === '');
            if (vacios.length > 0) {
                vacios.forEach(([, el]) => {
                    if (!el) return;
                    el.classList.add('is-invalid');
                    setTimeout(() => el.classList.remove('is-invalid'), 2500);
                });
                if (window.Alertas) {
                    window.Alertas.error('Completa todos los valores nutricionales (escribe 0 si no aporta).');
                }
                return;
            }

            for (const [clave, el] of Object.entries(campos)) {
                const num = Number(el.value);
                if (Number.isNaN(num) || num < 0) {
                    el.classList.add('is-invalid');
                    setTimeout(() => el.classList.remove('is-invalid'), 2500);
                    if (window.Alertas) {
                        window.Alertas.error(`El valor de ${clave} debe ser un número mayor o igual a 0.`);
                    }
                    return;
                }
            }

            const inputs        = obtenerInputsNutricion(formularioActivo);
            const calorias      = campos.calorias.value;
            const proteina      = campos.proteina.value;
            const carbohidratos = campos.carbohidratos.value;
            const grasas        = campos.grasas.value;

            if (inputs.calorias)      inputs.calorias.value      = calorias;
            if (inputs.proteina)      inputs.proteina.value      = proteina;
            if (inputs.carbohidratos) inputs.carbohidratos.value = carbohidratos;
            if (inputs.grasas)        inputs.grasas.value        = grasas;

            if (botonOrigen) {
                botonOrigen.dataset.calorias      = calorias;
                botonOrigen.dataset.proteina      = proteina;
                botonOrigen.dataset.carbohidratos = carbohidratos;
                botonOrigen.dataset.grasas        = grasas;
                botonOrigen.classList.add('nutricion-completa');
                const span = botonOrigen.querySelector('span');
                if (span) span.innerHTML = '<i class="bi bi-check-circle me-2 texto-verde"></i>Información nutricional añadida';
            }

            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNutricion'));
            if (modal) modal.hide();
        });
    }

    document.addEventListener('DOMContentLoaded', function () {

        const formCrear = document.getElementById('formCrearIngrediente');
        if (!formCrear) return;

        formCrear.addEventListener('submit', function (e) {

            if (nutricionRellena(formCrear)) return;

            e.preventDefault();

            if (window.Alertas) {
                window.Alertas.error('Debes rellenar la información nutricional antes de crear el ingrediente.');
            }

            const trigger = formCrear.querySelector('[data-bs-target="#modalNutricion"]');
            if (trigger) trigger.click();
        });
    });

})();
