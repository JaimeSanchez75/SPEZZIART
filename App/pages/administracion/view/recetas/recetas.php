<section class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h2 class="subtitulo letraRomana fw-bold  m-0">Recetas</h2>
            <p class="texto text-secondary">Crea, edita o elimina el recetas base de la aplicación.</p>
        </div>
        <button class="text-white px-4 py-2 rounded-pill border-0 bg-rojo texto fw-medium" data-bs-toggle="modal" data-bs-target="#modalCrearReceta">
            <i class="bi bi-person-plus me-2"></i>Nueva receta
        </button>
    </div>

    <?php if (empty($recetas)) { ?>

        <div class="text-center py-5">
            <i class="bi bi-exclamation-triangle display-4 text-muted"></i>
            <p class="text-muted mt-3">No hay recetas registradas.</p>
        </div>

    <?php } else { ?>

        <!-- Filtros -->
        <div class="d-flex justify-content-between align-items-center mb-4">

            <!-- Buscador -->
            <div class="input-group w-50 border rounded rounded-4">
                <span class="input-group-text bg-light border-0 rounded-start-4">
                    <i class="bi bi-search text-secondary"></i>
                </span>
                <input type="text" class="form-control input border-0 bg-light texto rounded-end-4 text-secondary"
                    placeholder="Buscar por nombre ..." id="buscador">
            </div>

            <!-- Select -->
            <select class="form-select w-auto rounded-4 py-2 px-4 border text-secondary texto pe-5 bg-light " id="filtroTabla" data-filtro="rol">

                <option value="todas" selected>Todas</option>
                <?php foreach ($etiquetas as $etiqueta) { ?>
                    <option value="<?php echo $etiqueta['Nombre']; ?>"><?php echo $etiqueta['Nombre']; ?></option>
                <?php } ?>


            </select>

        </div>

        <div class="row g-4 mt-2 " id="tablaPaginada">

            <?php foreach ($recetas as $receta) { ?>


                <div class="col-md-4 col-sm-6 col-12 d-flex">
                    <div class="card border-0 sombra rounded-4 flex-fill">
                        <img src="https://media.istockphoto.com/id/1297400965/es/foto/un-primer-plano-de-una-tortilla-espa%C3%B1ola-fresca-y-sabrosa-un-plato-tradicional-de-espa%C3%B1a.jpg?s=612x612&w=0&k=20&c=BIz2CCaqwhCR4Yngx-hV9H9kaK3tiyARz7wqqvA3Ges=" class="card-img-top rounded-top-4" alt="Imagen de receta">
                        <div class="card-body px-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between mb-2">
                                    <!-- etiquetas -->

                                    <div class="d-flex gap-1">


                                        <?php

                                        if (!empty($receta['Etiquetas'])) {
                                            echo '<div class="badge bg-rojoClaro texto-rojo textoPequeno ">' . strtoupper($receta['Etiquetas'][0]) . '</div>';
                                            if (count($receta['Etiquetas']) > 1) {
                                            }
                                        }

                                        ?>
                                    </div>

                                    <!-- tiempo -->
                                    <span class="text-secondary texto"><i class="bi bi-clock"></i> <?php echo $receta['Tiempo']; ?> min</span>
                                </div>
                                <!-- titulo -->
                                <h5 class=" card-title tituloPequeno letraRomana fw-bold mt-0 mb-0" data-buscado="true"><?php echo $receta['Titulo']; ?></h5>
                                <p class="card-text text-secondary textoPequeno m-0"><?php echo strlen($receta['Descripcion']) > 30 ? substr($receta['Descripcion'], 0, 30) . '...' : $receta['Descripcion']; ?></p>

                            </div>
                            <!-- autor -->
                            <div class="d-flex align-items-center mt-3 border-top pt-2">

                                <i class="bi bi-person me-2 rounded-circle p-1 border px-2 bg-light texto text-secondary"></i>


                                <span class="text-secondary texto">Por <span data-buscado="true"><?php echo $receta['NombreUsuario']; ?></span></span>

                                <!-- boton de ver detalles -->
                                <a href="#" class="ms-auto text-decoration-none textoPequeno texto-rojo"><i class="bi bi-eye me-1"></i>Ver detalles</a>
                            </div>
                            <div class="text-end card-img-overlay p-3">

                                <i class="bi bi-pencil-square bg-white sombra rounded-circle p-1 px-2 pt-2 text-muted" data-bs-toggle="modal" data-bs-target="#modalCrearReceta" data-mode="editar" data-id="<?php echo $receta['ID_Receta'] ?>"></i>
                                <i class="bi bi-trash  bg-white sombra rounded-circle p-1 px-2 pt-2 text-muted" data-bs-toggle="modal" data-bs-target="#eliminarReceta" data-id=<?php echo $receta['ID_Receta'] ?>></i>

                            </div>
                        </div>
                    </div>
                </div>

            <?php } ?>

        </div>

    <?php
    } ?>


</section>

<?php
require __DIR__ . "/../Components/receta/modalCrear.php";
require __DIR__ . "/../Components/receta/modalNutricion.php";
require __DIR__ . "/../Components/receta/modalConfirmacionEliminacion.php";
?>
