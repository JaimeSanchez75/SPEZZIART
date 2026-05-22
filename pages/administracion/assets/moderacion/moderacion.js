"use strict";

const ACCIONES_POR_TIPO = {
    receta: {
        titulo: "Aceptar reporte de receta",
        opciones: [
            { value: "eliminar_receta",      label: "Eliminar publicación" },
            { value: "deshabilitar_usuario", label: "Deshabilitar usuario" }
        ]
    },
    comentario: {
        titulo: "Aceptar reporte de comentario",
        opciones: [
            { value: "eliminar_comentario",  label: "Eliminar comentario" },
            { value: "deshabilitar_usuario", label: "Deshabilitar usuario" }
        ]
    },
    perfil: {
        titulo: "Aceptar reporte de perfil",
        opciones: [
            { value: "deshabilitar_usuario", label: "Deshabilitar usuario" }
        ]
    }
};

function cargarAcciones(tipo) {
    const select = document.getElementById("accionReporte");
    const titulo = document.getElementById("modalModeracionTitulo");
    if (!select) return;

    const config = ACCIONES_POR_TIPO[tipo];

    select.innerHTML = '<option value="">Seleccionar acción</option>';
    if (titulo) titulo.textContent = config?.titulo ?? "Aceptar reporte";
    if (!config) return;

    config.opciones.forEach(op => {
        const opt = document.createElement("option");
        opt.value = op.value;
        opt.textContent = op.label;
        select.appendChild(opt);
    });
}

document.addEventListener("click", function (e) {

    const btn = e.target.closest(".abrirModal");
    if (!btn) return;

    const tipo         = btn.dataset.tipo       ?? "";
    const reporteId    = btn.dataset.id         ?? "";
    const recetaId     = btn.dataset.receta     ?? "";
    const comentarioId = btn.dataset.comentario ?? "";
    const usuario      = btn.dataset.usuario    ?? "";

    const reporteInput    = document.getElementById("reporte_id");
    const recetaInput     = document.getElementById("receta_id");
    const comentarioInput = document.getElementById("comentario_id");
    const usuarioInput    = document.getElementById("usuario_reportado");
    const tipoInput       = document.getElementById("tipo_reporte");

    if (reporteInput)    reporteInput.value    = reporteId;
    if (recetaInput)     recetaInput.value     = recetaId;
    if (comentarioInput) comentarioInput.value = comentarioId;
    if (usuarioInput)    usuarioInput.value    = usuario;
    if (tipoInput)       tipoInput.value       = tipo;

    cargarAcciones(tipo);

    const modalEl = document.getElementById("modalModeracion");
    if (!modalEl) return;
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
});

document.addEventListener("click", async function (e) {

    const btn = e.target.closest('.btnMarcarRevisado');
    if (!btn) return;

    const id = btn.dataset.id;
    if (!id) return;

    const ok = window.Confirmacion
        ? await window.Confirmacion.preguntar({
            titulo: 'Marcar como revisado',
            mensaje: '¿Marcar este reporte como revisado sin tomar acción?',
            subtexto: 'El reporte se cerrará y no se aplicará ninguna sanción.',
            textoConfirmar: 'Marcar revisado',
            icono: 'bi-check2-circle',
        })
        : window.confirm('¿Marcar este reporte como revisado sin tomar acción?');

    if (!ok) return;

    const csrf  = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const form  = document.createElement('form');
    form.method = 'POST';
    form.action = '/moderacion/marcarRevisado';

    const csrfInput  = document.createElement('input');
    csrfInput.type   = 'hidden';
    csrfInput.name   = 'csrf_token';
    csrfInput.value  = csrf;
    form.appendChild(csrfInput);

    const idInput  = document.createElement('input');
    idInput.type   = 'hidden';
    idInput.name   = 'id';
    idInput.value  = id;
    form.appendChild(idInput);

    document.body.appendChild(form);
    form.submit();
});

document.addEventListener("submit", function (e) {

    const form = e.target;
    if (!form || form.getAttribute('action') !== '/moderacion/aceptarReporte') return;

    const select = document.getElementById('accionReporte');
    if (!select || select.value === '') {
        e.preventDefault();
        if (window.Alertas) {
            window.Alertas.aviso('Selecciona una acción antes de aplicar el reporte.');
        }
    }
});
