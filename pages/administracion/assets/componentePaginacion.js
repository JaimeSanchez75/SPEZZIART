"use strict";

function quitarTildes(texto) {
    return (texto ?? "").normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}

function normalizarPagina(pagina, totalPaginas) {
    const paginaNumero = Number.isFinite(+pagina) ? +pagina : 1;
    return Math.min(Math.max(paginaNumero, 1), totalPaginas);
}

function crearInstanciaPaginacion(miTabla) {

    const esTabla = miTabla?.tagName === 'TABLE';
    const itemsPorPagina = 6;

    function obtenerItems() {
        if (!miTabla) return [];
        if (esTabla) return Array.from(miTabla.tBodies[0]?.rows ?? []);
        return Array.from(miTabla.children)
            .filter(el => !el.hasAttribute('data-skip-paginacion'));
    }

    function obtenerHost() {
        return esTabla ? miTabla.tBodies[0] : miTabla;
    }

    function valorDeCelda(td) {
        if (td.dataset.ordenar !== undefined) return quitarTildes(td.dataset.ordenar.toLowerCase());
        const input = td.querySelector('input');
        if (input) return quitarTildes(String(input.value ?? "").toLowerCase());
        return quitarTildes(td.textContent.trim().replace(/\s+/g, ' ').toLowerCase());
    }

    function obtenerValoresOrdenar(nodo) {
        if (nodo.cells) {
            return Array.from(nodo.cells).map(valorDeCelda);
        }
        return [quitarTildes(nodo.textContent.toLowerCase())];
    }

    const datosTabla = obtenerItems().map(nodo => ({
        nodo,
        valoresOrdenar: obtenerValoresOrdenar(nodo),
        texto: Array.from(nodo.querySelectorAll('[data-buscado="true"]'))
            .map(el => quitarTildes(el.textContent.toLowerCase()))
            .join(" ").replace(/\s+/g, " "),
        filtro: quitarTildes(nodo.dataset?.nombre?.toLowerCase() ?? "")
    }));

    function refrescarValoresOrdenar() {
        datosTabla.forEach(d => {
            d.valoresOrdenar = obtenerValoresOrdenar(d.nodo);
        });
    }

    let datosBuscados = datosTabla.slice();
    let ordenActual    = "asc";
    let tipoOrdenacion = "string";
    let indiceColumna  = 0;
    let paginacionEl   = null;

    function pintarPagina(pagina) {
        const totalPaginas = Math.max(1, Math.ceil(datosBuscados.length / itemsPorPagina));
        const paginaActual = normalizarPagina(pagina, totalPaginas);

        if (datosBuscados.length === 0) {
            miTabla.classList.add("d-none");
            if (paginacionEl) paginacionEl.innerHTML = "";
            miTabla.removeAttribute('data-paginacion-pendiente');
            return;
        }

        miTabla.classList.remove("d-none");
        filtrarDatos(paginaActual);
        controlesPaginacion(paginaActual, totalPaginas);
        miTabla.removeAttribute('data-paginacion-pendiente');
    }

    function filtrarDatos(pagina) {
        const primerElemento = (pagina - 1) * itemsPorPagina;
        const ultimoElemento = primerElemento + itemsPorPagina;
        const host = obtenerHost();

        datosTabla.forEach((dato) => {
            if (dato.nodo.parentNode) {
                dato.nodo.parentNode.removeChild(dato.nodo);
            }
            dato.nodo.classList.remove('d-none');
        });

        const visibles = datosBuscados.slice(primerElemento, ultimoElemento);
        const fragmento = document.createDocumentFragment();
        visibles.forEach((dato) => fragmento.append(dato.nodo));
        host.append(fragmento);
    }

    function controlesPaginacion(pagina, totalPaginas) {

        if (!paginacionEl) {
            paginacionEl = document.createElement('div');
            paginacionEl.className = 'paginacion-componente';
            miTabla.after(paginacionEl);

            paginacionEl.addEventListener('click', (event) => {
                if (event.target.tagName === 'BUTTON' && event.target.dataset.salto) {
                    if (isFinite(event.target.dataset.salto)) {
                        pintarPagina(+event.target.dataset.salto);
                    }
                }
            });
            paginacionEl.addEventListener('change', (event) => {
                if (event.target.classList.contains('paginas-selector')) {
                    pintarPagina(+event.target.value);
                }
            });
        }

        let opciones = "";
        for (let i = 1; i <= totalPaginas; i++) {
            opciones += `<option value="${i}" ${i == pagina ? 'selected' : ""}>${i}</option>`;
        }

        paginacionEl.innerHTML = `
            <div class="d-flex align-items-center justify-content-between">
                <span class="texto text-secondary">Página ${pagina} de ${totalPaginas}</span>
                <nav aria-label="Page navigation" class="d-flex justify-content-end mt-4">
                    <ul class="pagination pagination-sm rounded-pill shadow-sm">

                        <li class="page-item text-secondary">
                            <button ${pagina == 1 ? 'disabled' : ''} data-salto=${+pagina - 1} class="page-link">Anterior</button>
                        </li>

                        <li class="page-item active" aria-current="page">
                            <select class="paginas-selector">${opciones}</select>
                        </li>

                        <li class="page-item text-secondary">
                            <button class="page-link" ${pagina == totalPaginas ? 'disabled' : ''} data-salto=${+pagina + 1}>Siguiente</button>
                        </li>

                    </ul>
                </nav>
            </div>`;
    }

    

    function filtrarPorBuscador(pagina) {
        const buscador    = document.getElementById('buscador');
        const filtroTabla = document.getElementById('filtroTabla');

        const valorFiltro      = filtroTabla ? quitarTildes(filtroTabla.value.toLowerCase()) : "todos";
        const contenidoBuscado = buscador    ? quitarTildes(buscador.value.toLowerCase())    : "";

        datosBuscados = datosTabla.filter((fila) => {
            const resultBuscado = !contenidoBuscado || fila.texto.includes(contenidoBuscado);
            const resultFiltro  = valorFiltro === "todos" || fila.filtro.includes(valorFiltro);
            return resultBuscado && resultFiltro;
        });

        ordenarDatos();
        pintarPagina(pagina);
    }

    function ordenarPorColumna(event) {
        const th = event.target.closest('th');
        if (!th || !th.dataset.ordenacion) return;

        th.querySelector('.orden')?.classList.toggle('invertido');

        indiceColumna  = th.cellIndex;
        tipoOrdenacion = th.dataset.ordenacion;

        ordenActual = th.dataset.orden = ordenActual === "asc" ? "desc" : "asc";
        refrescarValoresOrdenar();
        ordenarDatos();
        pintarPagina(1);
    }

    function ordenarDatos() {
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

            if (valorA < valorB) return ordenActual === "asc" ? -1 :  1;
            if (valorA > valorB) return ordenActual === "asc" ?  1 : -1;
            return 0;
        });
    }

    if (esTabla) {
        miTabla.querySelectorAll('thead th').forEach(th => th.addEventListener('click', ordenarPorColumna));
    }

    return {
        filtrarPorBuscador,
        estaVacia: () => datosBuscados.length === 0
    };
}

(function inicializar() {

    const tablas = Array.from(document.querySelectorAll('[data-paginacion-pendiente]'));
    if (tablas.length === 0) return;

    const instancias = tablas.map(t => crearInstanciaPaginacion(t));

    function actualizarNoResultados() {
        const contenedor = document.getElementById("no-resultados");
        if (!contenedor) return;

        const todasVacias = instancias.every(i => i.estaVacia());

        if (todasVacias) {
            contenedor.innerHTML = `
                <i class="bi bi-search display-5 text-muted mb-3"></i>
                <h6 class="fw-semibold">No se encontraron resultados</h6>
                <p class="text-muted mb-3">Prueba con otro término de búsqueda</p>`;
        } else {
            contenedor.innerHTML = "";
        }
    }

    function refrescar(pagina = 1) {
        instancias.forEach(i => i.filtrarPorBuscador(pagina));
        actualizarNoResultados();
    }

    refrescar(1);

    let timeout;
    const buscador = document.getElementById('buscador');
    if (buscador) {
        buscador.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => refrescar(1), 300);
        });
    }

    const filtroTabla = document.getElementById('filtroTabla');
    if (filtroTabla) {
        filtroTabla.addEventListener('change', () => refrescar(1));
    }
})();
