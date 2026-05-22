"use strict";

document.addEventListener('DOMContentLoaded', () => {

    const tablaIngredientes = document.getElementById('tabIngredientesBase');
    if (!tablaIngredientes) return;

    let timeoutFeedback = null;

    tablaIngredientes.addEventListener('change', (event) => {
        if (event.target.tagName !== 'INPUT') return;

        const inputAcciona = event.target;
        const fila = event.target.closest('tr');

        if (!fila) return;

        const datos = {
            id: fila.dataset.id,
            nomb: null,
            cal: null,
            prot: null,
            ch: null,
            gr: null
        };

        fila.querySelectorAll('input').forEach(element => {

            const tipoContenido = element.dataset.contenido;

            if (!tipoContenido) return;

            if (element.type === 'number') {

                let valor = element.value === '' ? 0 : Number(element.value);
                if (Number.isNaN(valor) || valor < 0) valor = 0;

                const maxAttr = element.getAttribute('max');
                if (maxAttr !== null && maxAttr !== '' && valor > Number(maxAttr)) {
                    valor = Number(maxAttr);
                }

                element.value = valor;
                datos[tipoContenido] = valor;
                return;
            }

            datos[tipoContenido] = element.value;
        });

        const nombreLimpio = String(datos.nomb ?? '').trim();
        if (nombreLimpio === '') {

            inputAcciona.classList.add("is-invalid");
            setTimeout(() => inputAcciona.classList.remove("is-invalid"), 2000);

            if (window.Alertas) {
                window.Alertas.error('El nombre del ingrediente es obligatorio.');
            }
            return;
        }
        if (nombreLimpio.length < 2 || nombreLimpio.length > 100) {

            inputAcciona.classList.add("is-invalid");
            setTimeout(() => inputAcciona.classList.remove("is-invalid"), 2000);

            if (window.Alertas) {
                window.Alertas.error('El nombre del ingrediente debe tener entre 2 y 100 caracteres.');
            }
            return;
        }
        datos.nomb = nombreLimpio;

        fetch("/pages/administracion/Ingredientes/editar", {

            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(datos)

        })
        .then(async res => {

            const json = await res.json().catch(() => ({}));
            if (!res.ok || json.success === false) {

                throw new Error(json.message || 'No se pudo editar el ingrediente.');
                window.Alertas.error('No se pudo editar el ingrediente.');

            }
            return json;
        })
        .then(json => {

            inputAcciona.classList.add("correcto");
            
            clearTimeout(timeoutFeedback);
            timeoutFeedback = setTimeout(() => inputAcciona.classList.remove("correcto"), 1500);

            if (window.Alertas) {

                window.Alertas.exito(json.message || 'Ingrediente actualizado.');
            }
        })
        .catch(err => {

            inputAcciona.classList.add("is-invalid");

            setTimeout(() => inputAcciona.classList.remove("is-invalid"), 2000);

            if (window.Alertas) {
                window.Alertas.error(err.message || 'Error al guardar.');
            }
        });
    });
});
