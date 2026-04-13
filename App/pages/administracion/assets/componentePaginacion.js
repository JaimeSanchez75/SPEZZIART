"use strict";

const miTabla = document.getElementById('tablaPaginada');
const datosTabla = Array.from(miTabla.rows).slice(1).map(fila=>({
    nodo: fila,
    valoresOrdenar: Array.from(fila.cells).map(td=> quitarTildes(td.textContent.toLowerCase())),
    texto: Array.from(fila.querySelectorAll('[data-buscado="true"]')).map(td=> quitarTildes(td.textContent.toLowerCase())).join(" ").replace(/\s+/g, " "),
    filtro: quitarTildes(fila.dataset.nombre?.toLowerCase())  || ""
}));

let datosBuscados;

let ordenActual = "asc";
let tipoOrdenacion = "string";
let indiceColumna=0;

let timeout;

let itemsPorPagina = 5;

function pintarPagina(pagina) {
    const paginacion = document.getElementById('paginacion');

    let contenedorNoEncontrado= document.getElementById("no-resultados");

    if ( datosBuscados.length === 0) {

        miTabla.classList.add("d-none");

    
        contenedorNoEncontrado.innerHTML=`
            <i class="bi bi-search display-5 text-muted mb-3"></i>
            <h6 class="fw-semibold">No se encontraron resultados</h6>
            <p class="text-muted mb-3">
                Prueba con otro término de búsqueda
            </p>`
        ;
        
        if (paginacion) paginacion.innerHTML="";
        return;
    }
    else {

        contenedorNoEncontrado.innerHTML="";
        miTabla.classList.remove("d-none");
        filtrarDatos(pagina);
        controlesPaginacion(pagina);

    }

}

function filtrarDatos(pagina) {

    let primerElemento = (pagina - 1) * itemsPorPagina;
    let ultimoElemento = primerElemento + itemsPorPagina;

    const datosAmostrar = datosBuscados.slice(primerElemento, ultimoElemento);

    const fragmento = document.createDocumentFragment();
    datosAmostrar.forEach(dato => {
        const clone = dato.nodo.cloneNode(true);
        fragmento.append(clone);
    });

    miTabla.tBodies[0].replaceChildren(fragmento);

}

function controlesPaginacion(pagina) {

    let paginacion = document.getElementById('paginacion');

    if (!paginacion) {

        paginacion = document.createElement('DIV');
        paginacion.id = "paginacion";

        miTabla.after(paginacion);

        paginacion.addEventListener('click', (event) => {
            if (event.target.tagName == 'BUTTON' && event.target.dataset.salto) {
                if (isFinite(event.target.dataset.salto)) {
                    pintarPagina(event.target.dataset.salto);
                }
            }
        });
        paginacion.addEventListener('change', (event) => {
            if (event.target.id === 'paginas') {
                pintarPagina(+event.target.value);
            }
        });

    }

    // opciones
    let opciones = [];
    for (let i = 1; i <= Math.ceil(datosBuscados.length / itemsPorPagina); i++) {

        opciones.push(`<option value="${i}" ${i == pagina ? 'selected' : ""}>${i}</option>`);

    }


    paginacion.innerHTML = `
        <div class="d-flex align-items-center justify-content-between">
            <span class="texto text-secondary">Página ${pagina} de ${Math.ceil(datosBuscados.length / itemsPorPagina)}</span>
            <nav aria-label="Page navigation" class="d-flex justify-content-end mt-4" >

                <ul class="pagination pagination-sm rounded-pill shadow-sm" id="paginacionTabla">

                    <li class="page-item text-secondary">
                        <button ${pagina == 1 ? 'disabled' : ''} data-salto=${+pagina - 1} class="page-link">Anterior</button>
                    </li>

                    <li class="page-item active" aria-current="page">
                        <select id="paginas" >
                            ${opciones}
                        </select>
                    </li>

                    <li class="page-item text-secondary">
                        <button class="page-link" ${pagina == Math.ceil(datosBuscados.length / itemsPorPagina) ? 'disabled' : ''} data-salto=${+pagina + 1}>Siguiente</button>
                    </li>

                </ul>

            </nav>
        </div>`
        ;

}

function filtrarPorBuscador(pagina) {

    let buscador = document.getElementById('buscador');

    let valorFiltro=document.getElementById('filtroTabla')? quitarTildes(document.getElementById('filtroTabla').value.toLowerCase()):"todos";

    let contenidoBuscado = quitarTildes(buscador.value.toLowerCase());
  
    datosBuscados = datosTabla.filter((fila)=>{
        
        
        let resultBuscado=true;
        if(contenidoBuscado){
            resultBuscado = fila.texto.includes(contenidoBuscado);
        }
    
        let resultFiltro=true;
        if(valorFiltro!="todos"){
            resultFiltro = fila.filtro.includes(valorFiltro);
        }

        return resultBuscado && resultFiltro;

    });

    ordenarDatos();
   

    pintarPagina(pagina);

}

function ordenarPorColumna(event) {

    const th = event.target.closest('th');
    if (!th || !th.dataset.ordenacion) return;

    th.querySelector('.orden').classList.toggle('invertido');

    
    indiceColumna = th.cellIndex;
    tipoOrdenacion = th.dataset.ordenacion;

    ordenActual = th.dataset.orden = ordenActual === "asc" ? "desc" : "asc";
    ordenarDatos();
   
    pintarPagina(1);
}
    

function ordenarDatos(){
    

    datosBuscados.sort((a, b) => {

        let valorA = a.valoresOrdenar[indiceColumna];
        let valorB = b.valoresOrdenar[indiceColumna];

       
        if (tipoOrdenacion === "number") {
            valorA = parseFloat(valorA) || 0;
            valorB = parseFloat(valorB) || 0;
        } else if (tipoOrdenacion === "date") {
            valorA = new Date(valorA);
            valorB = new Date(valorB);
        }

        if (valorA < valorB) return ordenActual === "asc" ? -1 : 1;
        if (valorA > valorB) return ordenActual === "asc" ? 1 : -1;
        return 0;

    });
}

function quitarTildes(texto) {

    return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "");

}


filtrarPorBuscador(1);

let buscador = document.getElementById('buscador');

buscador.addEventListener('input', () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => {
        filtrarPorBuscador(1);
    }, 300);
});

let filtroTabla = document.getElementById('filtroTabla');

if (filtroTabla) {
    filtroTabla.addEventListener('change', () => {
        filtrarPorBuscador(1);
    });
}

let encabezados = document.querySelectorAll('#tablaPaginada thead th');

encabezados.forEach(encabezado => {
    encabezado.addEventListener('click', ordenarPorColumna);
});



