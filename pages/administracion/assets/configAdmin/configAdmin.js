"use strict";


// escuchar al sistema el tipo de modo
const mediaQueryTemaOscuro = window.matchMedia('(prefers-color-scheme: dark)');

// lee la preferencia de tema del sistema y aplica oscuro o claro
function aplicarTemaDelSistema() {

    const temaSistema = mediaQueryTemaOscuro.matches ? 'dark' : 'light';

    document.documentElement.setAttribute('data-bs-theme', temaSistema);

}

// cambia el tema del sitio y guarda la preferencia del usuario
function escucharCambiosDelSistema(activar) {

    if (activar) {

        mediaQueryTemaOscuro.addEventListener('change', aplicarTemaDelSistema);

    } else {

        mediaQueryTemaOscuro.removeEventListener('change', aplicarTemaDelSistema);

    }
}

// marca el botón del tema activo y desmarca los demás
function marcarBotonActivo(idBoton) {

    const botones = ['btnTemaClaro', 'btnTemaOscuro', 'btnTemaAuto'];

    botones.forEach(id => {

        document.getElementById(id).classList.remove('active');
        
    });

    document.getElementById(idBoton).classList.add('active');

}

// inicializa el tema guardado o el tema del sistema
function inicializarTemaGuardado() {

    const temaGuardado = window.userTheme || 'sistema';

    switch (temaGuardado) {

        case 'oscuro':

            document.documentElement.setAttribute('data-bs-theme', 'dark');
            marcarBotonActivo('btnTemaOscuro');
            break;
        case 'claro':

            document.documentElement.setAttribute('data-bs-theme', 'light');
            marcarBotonActivo('btnTemaClaro');
            break;
        case 'sistema':
        default:

            aplicarTemaDelSistema();
            escucharCambiosDelSistema(true);
            marcarBotonActivo('btnTemaAuto');
            break;
    }
}

document.addEventListener('DOMContentLoaded', () => {

    inicializarTemaGuardado();

    document.getElementById('btnTemaOscuro').addEventListener('click', () => {

        escucharCambiosDelSistema(false);
        document.documentElement.setAttribute('data-bs-theme', 'dark');
        marcarBotonActivo('btnTemaOscuro');
        guardarTemaSeleccionado('oscuro');

    });

    document.getElementById('btnTemaClaro').addEventListener('click', () => {

        escucharCambiosDelSistema(false);
        document.documentElement.setAttribute('data-bs-theme', 'light');
        marcarBotonActivo('btnTemaClaro');
        guardarTemaSeleccionado('claro');

    });

    document.getElementById('btnTemaAuto').addEventListener('click', () => {
        aplicarTemaDelSistema();
        escucharCambiosDelSistema(true);
        marcarBotonActivo('btnTemaAuto');
        guardarTemaSeleccionado('sistema');
    });

    // por si en algun dia se meten 
    // switchNotificaciones.addEventListener('change', () => {
    //     const estado = switchNotificaciones.checked ? 'activadas' : 'desactivadas';
  
    //     guardarNotificaciones(switchNotificaciones.checked);
    // });

});

// function guardarNotificaciones(estado) {

//     fetch('/pages/administracion/configuracion/notificaciones', {
//         method: 'POST',
//         headers: { 'Content-Type': 'application/json' },
//         body: JSON.stringify({ notificaciones: estado })
//     })
//         .then(response => response.json().catch(() => ({})).then(json => ({ ok: response.ok, json })))
//         .then(({ ok, json }) => {
//             if (window.Alertas) {
//                 if (ok) {
//                     window.Alertas.exito(estado ? 'Notificaciones activadas.' : 'Notificaciones desactivadas.');
//                 } else {
//                     window.Alertas.error((json && json.message) || 'No se pudieron guardar las notificaciones.');
//                 }
//             }
//         })
//         .catch(error => {
//             console.error('Error al guardar las notificaciones:', error);
//             if (window.Alertas) window.Alertas.error('Error de conexión al guardar las notificaciones.');
//         });
// }

function guardarTemaSeleccionado(tema) {

    fetch('/pages/administracion/configuracion/tema', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ tema: tema })
    })
        .then(response => response.json()
        .catch(() => ({}))
        .then(json => (
            { ok: response.ok, json }
        )))
        .then(({ ok, json }) => {
            if (window.Alertas && !ok) {
                window.Alertas.error((json && json.message) || 'No se pudo guardar el tema.');
            }
        })
        .catch(error => {
            console.error('Error al guardar el tema:', error);
            if (window.Alertas) window.Alertas.error('Error de conexión al guardar el tema.');
        });
}
