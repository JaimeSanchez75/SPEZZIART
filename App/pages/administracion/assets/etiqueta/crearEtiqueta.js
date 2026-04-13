document.addEventListener('DOMContentLoaded',function(){
    const urlParams = new URLSearchParams(window.location.search);

    if(urlParams.get('error')==='existe'){
        const modalCrearEtiqueta = new bootstrap.Modal(document.getElementById('modalCrearEtiqueta'));
        if(modalCrearEtiqueta){
            modalCrearEtiqueta.show();
        }
    }

    setTimeout(() => {
        const alerta = document.getElementById("alertCrearEtiqueta");
        if (alerta) {
            alerta.style.transition = "opacity 0.5s";
            alerta.style.opacity = "0";

            setTimeout(() => {
                alerta.remove();
            }, 500);
        }
    }, 1700);
}
);