document.addEventListener('DOMContentLoaded',function(){
    setTimeout(() => {
        const alerta = document.getElementById("alertEtiqueta");
        if (alerta) {
            alerta.style.transition = "opacity 0.5s";
            alerta.style.opacity = "0";

            setTimeout(() => {
                alerta.remove();
                // limpiar la url para evitar que el mensaje vuelva a aparecer al recargar la pagina
                const url = new URL(window.location.href);
                url.search = ""; 

                window.history.replaceState(null, "", url);
            }, 500);
        }
    }, 1700);
});