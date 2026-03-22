document.addEventListener("DOMContentLoaded", () => {

    const buscador = document.getElementById("buscadorUsuarios");
    const filtro = document.getElementById("filtroRoles");
    const tabla = document.getElementById("tablaUsuarios");
    const filas = Array.from(tabla.querySelectorAll("tbody tr"));
    const paginacion = document.getElementById("paginacionUsuarios");

    const filasPorPagina = 5; // filas por página
    let paginaActual = 1;

    function filtrarYMostrar() {
        const texto = buscador.value.toLowerCase();
        const rolSeleccionado = filtro.value;

        // Filtrar filas
        const filasFiltradas = filas.filter(fila => {
            const nombre = fila.querySelector(".fw-semibold")?.textContent.toLowerCase() || "";
            const usuario = fila.querySelector("small")?.textContent.toLowerCase() || "";
            const esAdmin = fila.querySelector("td:nth-child(2) i")?.classList.contains("bi-shield") ? "1" : "0";

            const cumpleBusqueda = nombre.includes(texto) || usuario.includes(texto);
            const cumpleRol = rolSeleccionado === "todos" || rolSeleccionado === esAdmin;

            return cumpleBusqueda && cumpleRol;
        });

        const totalPaginas = Math.ceil(filasFiltradas.length / filasPorPagina);
        if (paginaActual > totalPaginas) paginaActual = 1;

        // Ocultar todas
        filas.forEach(f => f.style.display = "none");

        // Mostrar solo filas de la página actual
        const inicio = (paginaActual - 1) * filasPorPagina;
        const fin = inicio + filasPorPagina;
        filasFiltradas.slice(inicio, fin).forEach(f => f.style.display = "");

        // Generar botones de paginación
        renderizarPaginacion(totalPaginas);
    }

    function renderizarPaginacion(totalPaginas) {
        paginacion.innerHTML = "";

        // Botón "Anterior"
        const prevLi = document.createElement("li");
        prevLi.className = "page-item";
        const prevBtn = document.createElement("button");
        prevBtn.className = "page-link";
        prevBtn.textContent = "Anterior";
        prevBtn.disabled = paginaActual === 1;
        prevBtn.addEventListener("click", () => {
            if (paginaActual > 1) { paginaActual--; filtrarYMostrar(); }
        });
        prevLi.appendChild(prevBtn);
        paginacion.appendChild(prevLi);

        // Botones de página
        for (let i = 1; i <= totalPaginas; i++) {
            const li = document.createElement("li");
            li.className = "page-item" + (i === paginaActual ? " active" : "");
            const btn = document.createElement("button");
            btn.className = "page-link";
            btn.textContent = i;
            btn.addEventListener("click", () => { paginaActual = i; filtrarYMostrar(); });
            li.appendChild(btn);
            paginacion.appendChild(li);
        }

        // Botón "Siguiente"
        const nextLi = document.createElement("li");
        nextLi.className = "page-item";
        const nextBtn = document.createElement("button");
        nextBtn.className = "page-link";
        nextBtn.textContent = "Siguiente";
        nextBtn.disabled = paginaActual === totalPaginas || totalPaginas === 0;
        nextBtn.addEventListener("click", () => {
            if (paginaActual < totalPaginas) { paginaActual++; filtrarYMostrar(); }
        });
        nextLi.appendChild(nextBtn);
        paginacion.appendChild(nextLi);
    }

    // Escuchar cambios
    buscador.addEventListener("keyup", () => { paginaActual = 1; filtrarYMostrar(); });
    filtro.addEventListener("change", () => { paginaActual = 1; filtrarYMostrar(); });

    // Inicializar
    filtrarYMostrar();

});