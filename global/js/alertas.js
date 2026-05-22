"use strict";
(function () // Módulo para mostrar alertas utilizando Bootstrap. Permite mostrar alertas de éxito, error, aviso e información con un mensaje personalizado y una duración configurable. Las alertas se muestran en la esquina superior derecha de la pantalla y se ocultan automáticamente después de un tiempo o al hacer clic en el botón de cerrar.
{
    const TIPOS = 
    {
        success: { icono: 'bi-check-circle-fill', clase: 'text-bg-success' },
        danger:  { icono: 'bi-exclamation-octagon-fill', clase: 'text-bg-danger' },
        warning: { icono: 'bi-exclamation-triangle-fill', clase: 'text-bg-warning' },
        info:    { icono: 'bi-info-circle-fill', clase: 'text-bg-info' }
    };
    function getContenedor() // Obtiene el contenedor de las alertas. Si no existe, lo crea y lo añade al body.
    {
        // Vemos si el contenedor ya existe
        let cont = document.getElementById('contenedorAlertas');
        if (!cont) 
        {
            // sino, lo creamos y lo añadimos al body
            cont = document.createElement('div');
            cont.id = 'contenedorAlertas';
            cont.className = 'toast-container position-fixed top-0 end-0 p-3';
            cont.style.zIndex = '1080';
            document.body.appendChild(cont);
        }
        return cont;
    }
    function escaparHTML(texto) // Escapa caracteres especiales en el texto para evitar que se interprete como HTML. Esto es importante para prevenir ataques de inyección de código si el mensaje proviene de una fuente no confiable.
    {
        const div = document.createElement('div');
        div.textContent = texto ?? '';
        return div.innerHTML;
    }
    function mostrar(tipo, mensaje, opciones = {}) // Muestra una alerta del tipo especificado con el mensaje dado y las opciones configurables. El tipo puede ser 'success', 'danger', 'warning' o 'info'. Las opciones pueden incluir 'delay' para configurar el tiempo que se muestra la alerta en milisegundos (por defecto 4000 ms).
    {
        const configuracion = TIPOS[tipo] || TIPOS.info;
        const contenedor = getContenedor();
        // Tiempo que se muestra la alerta
        const delay = Number.isFinite(opciones.delay) ? opciones.delay : 4000; // por defecto 4 segundos
        // Creamos el elemento de la aleta
        const toast = document.createElement('div'); 
        toast.className = `toast align-items-center border-0 ${configuracion.clase}`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        // Contenido
        toast.innerHTML = 
            `<div class="d-flex">
                <div class="toast-body d-flex align-items-center gap-2">
                    <i class="bi ${configuracion.icono}"></i>
                    <span>${escaparHTML(mensaje)}</span>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>`;
        // Añadimos al contenedor
        contenedor.append(toast);
        // inicializamos el toast de Bootstrap
        const bsToast = new bootstrap.Toast(toast, { delay, autohide: true });
        toast.addEventListener('hidden.bs.toast', () => toast.remove());
        bsToast.show();
    }
    // creamos un objeto global para poder manejar las alertas desde cualquier lugar
    window.Alertas = 
    {
        exito:  (msg, opt) => mostrar('success', msg, opt),
        error:  (msg, opt) => mostrar('danger',  msg, opt),
        aviso:  (msg, opt) => mostrar('warning', msg, opt),
        info:   (msg, opt) => mostrar('info',    msg, opt),
        mostrar
    };
    function procesarFlashes() // Procesa las alertas pendientes que se hayan almacenado en la variable global window.__alertasPendientes. Esta variable puede ser utilizada por el backend para pasar mensajes de alerta que deben mostrarse al cargar la página. Cada alerta pendiente debe ser un objeto con las propiedades 'tipo' (success, danger, warning, info) y 'mensaje' (el texto a mostrar).
    {
        const pendientes = Array.isArray(window.__alertasPendientes) ? window.__alertasPendientes : [];
        pendientes.forEach(f => 
        {
            const tipo = f && typeof f.tipo === 'string' ? f.tipo : 'info';
            const msg  = f && typeof f.mensaje === 'string' ? f.mensaje : '';
            if (msg) mostrar(tipo, msg);
        });
        window.__alertasPendientes = [];
    }
    // mostramos las alertas pendientes al cargar la página
    document.addEventListener('DOMContentLoaded', procesarFlashes);
})();
