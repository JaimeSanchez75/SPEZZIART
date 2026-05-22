document.addEventListener("DOMContentLoaded", function () {

    const modalVerificacion = document.getElementById('verificaringrediente');
    if (!modalVerificacion) return;

    const btnVerificar = document.getElementById('siverificarIngrediente');
    if (!btnVerificar) return;

    let nutricionActual = null;

    modalVerificacion.addEventListener("show.bs.modal", function (event) {

        const botonAcciona = event.relatedTarget;
        if (!botonAcciona) return;

        const fila   = botonAcciona.closest('tr');
        const celdas = fila.querySelectorAll('td');

        nutricionActual = {
            calorias:      +celdas[1].textContent.trim(),
            proteina:      +celdas[2].textContent.trim(),
            carbohidratos: +celdas[3].textContent.trim(),
            grasas:        +celdas[4].textContent.trim()
        };

        document.getElementById('nombreIngredienteVerificar').textContent = botonAcciona.dataset.nombre;

        btnVerificar.dataset.id     = botonAcciona.dataset.id;
        btnVerificar.dataset.nombre = botonAcciona.dataset.nombre;
    });

    async function llamarVerificar(confirmarSobrescritura) {

        const response = await fetch('/pages/administracion/Ingredientes/verificar', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id: btnVerificar.dataset.id,
                nombre: btnVerificar.dataset.nombre,
                confirmarSobrescritura,
                ...nutricionActual
            })
        });

        return response.json();
    }

    btnVerificar.addEventListener('click', async function () {

        if (!nutricionActual) return;

        btnVerificar.disabled = true;

        try {

            let data = await llamarVerificar(false);

            if (data && data.repetido) {

                const modal = bootstrap.Modal.getInstance(modalVerificacion);
                if (modal) modal.hide();

                const ok = await window.Confirmacion.preguntar({
                    titulo:          'Verificar ingrediente repetido',
                    mensaje:         '¿Deseas sobreescribir los datos del ingrediente base existente con estos nuevos?',
                    subtexto:        'Se reemplazarán los datos actuales del ingrediente base.',
                    textoConfirmar:  'Sobreescribir',
                    textoCancelar:   'Cancelar',
                    icono:           'bi-check2-circle',
                });

                if (!ok) return;

                data = await llamarVerificar(true);
            }

            const modal = bootstrap.Modal.getInstance(modalVerificacion);
            if (modal) modal.hide();

            if (data && data.success) {
                location.reload();
            } else {
                console.error('[verificarIngrediente] respuesta no OK', data);
            }

        } catch (err) {

            console.error('[verificarIngrediente]', err);

        } finally {

            btnVerificar.disabled = false;
            nutricionActual = null;
        }
    });

});
