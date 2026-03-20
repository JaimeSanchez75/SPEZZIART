<section class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h2 class="fw-bold mb-1">Recetas</h2>
            <p class="text-muted mb-0">Crea, edita o elimina el contenido base de la aplicación.</p>
        </div>
        <button class="btn text-white px-4 py-2 rounded-pill"
            style="background: var(--brand-wine);">
            <i class="bi bi-person-plus me-2"></i>Nueva receta
        </button>
    </div>

    <?php if (empty($recetas)) { ?>

    <div class="text-center py-5">
        <i class="bi bi-exclamation-triangle display-4 text-muted"></i>
        <p class="text-muted mt-3">No hay recetas registradas.</p>
    </div>

    <?php } else { ?>
    

    <div class="d-flex justify-content-between align-items-center mt-3 gap-2">
        <div class="input-group search-box">
            <span class="input-group-text border-0">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" class="form-control border-0" placeholder="Buscar recetas...">
        </div>

        <!-- Botón filtros -->
        <button class="btn btn-light filter-btn">
            <i class="bi bi-funnel me-1"></i> Filtros
        </button>
    </div>

    <div class="row g-5 mt-4">

        <?php foreach ($recetas as $receta) { ?>


            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <img src="https://media.istockphoto.com/id/1297400965/es/foto/un-primer-plano-de-una-tortilla-espa%C3%B1ola-fresca-y-sabrosa-un-plato-tradicional-de-espa%C3%B1a.jpg?s=612x612&w=0&k=20&c=BIz2CCaqwhCR4Yngx-hV9H9kaK3tiyARz7wqqvA3Ges=" class="card-img-top" alt="Imagen de receta">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <?php
                            foreach ($receta['Etiquetas'] as $etiqueta) {
                                echo '<span class="badge bg-primary">' . strtoupper($etiqueta) . '</span>';
                            }
                            ?>
                            <small class="text-muted"><i class="bi bi-clock"></i> <?php echo $receta['Tiempo']; ?> min</small>
                        </div>
                        <h5 class="card-title"><?php echo $receta['Titulo']; ?></h5>
                        <div class="d-flex align-items-center mt-3">
                            <i class="bi bi-person-circle me-2"></i>
                            <small class="text-muted">Por <?php echo $receta['NombreUsuario']; ?></small>
                            <a href="#" class="ms-auto text-decoration-none"><i class="bi bi-eye me-1"></i>Ver detalles</a>
                        </div>
                        <td class="text-end">
                            <i class="bi bi-pencil me-3 text-muted editarEtiqueta" data-bs-toggle="modal" data-bs-target="#modalEditarReceta" data-id=<?php echo $receta['ID_Receta']?>></i>
                            <i class="bi bi-trash text-muted" data-bs-toggle="modal" data-bs-target="#eliminarReceta" data-id=<?php echo $receta['ID_Receta']?>></i>
                            
                        </td>
                    </div>
                </div>
            </div>

        <?php } ?>

    </div>

    <?php } ?>

    



</section>