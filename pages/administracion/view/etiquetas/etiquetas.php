<section class="container-fluid py-4">
    <?php if (isset($_GET['mensaje'])) 
          { ?>
            <div class="alert alert-success  texto alert-dismissible fade show" id="alertEtiqueta">
                Etiqueta <?php echo htmlspecialchars($_GET['mensaje'], ENT_QUOTES, 'UTF-8'); ?> correctamente.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
    <?php } ?>
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
        <div>
            <h2 class="subtitulo letraRomana fw-bold  m-0">Etiquetas</h2>
            <p class="texto text-secondary">Crea, edita o elimina el contenido de la aplicación.</p>
        </div>
        <button class="text-white px-4 py-2 rounded-pill border-0 bg-rojo texto fw-medium"
            data-bs-toggle="modal" data-bs-target="#modalCrearEtiqueta">
            <i class="bi bi-bookmark-star me-2"></i>Nueva etiqueta
        </button>
    </div>
    <?php if (empty($etiquetas)) 
          { ?>
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle display-4 text-muted"></i>
                <p class="text-muted mt-3">No hay etiquetas registradas.</p>
            </div>
    <?php } 
          else 
          { ?>
            
            <div class="rowjustify-content-between align-items-center mb-4">
                
                <div class="col-12 col-sm-6 col-md-5">
                    <div class="input-group border rounded rounded-4">
                        <span class="input-group-text bg-light border-0 rounded-start-4"><i class="bi bi-search text-secondary"></i></span>
                        <input type="text" class="form-control input border-0 bg-light texto rounded-end-4 text-secondary" placeholder="Buscar por nombre " id="buscador">
                    </div>
                </div>
            </div>

            <div id="no-resultados"></div>

            <div class="table-responsive">

                <table class="w-100 rounded-4  tablaAdmin tablaAdmin--responsive" id="tablaPaginada" data-paginacion-pendiente="true">

                    <thead class="bg-light text-secondary text-uppercase texto">

                        <tr class="">

                            <th data-ordenacion="string" data-orden="desc" class="py-3 ps-3">Etiqueta <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                            <th  class="py-3 text-end pe-3">ACCIONES</th>
                            
                        </tr>

                    </thead>
                    <tbody>
                        <?php foreach ($etiquetas as $etiqueta) 
                            { ?>
                                <tr data-nombre="<?= htmlspecialchars($etiqueta['Nombre'], ENT_QUOTES) ?>" class="registroTabla border-bottom align-middle">
                                    <td class="py-2 ps-3" data-label="Etiqueta">
                                        <div class="celda-contenido d-flex align-items-center">
                                            <div class="circuloPerfil rounded-circle bg-rojo d-flex align-items-center justify-content-center text-white fw-semibold me-3 text-uppercase texto">
                                                <?php echo htmlspecialchars(strtoupper(substr($etiqueta['Nombre'], 0, 2)), ENT_QUOTES, 'UTF-8'); ?>
                                            </div>
                                            <div><div class="fw-semibold texto text-break" data-buscado="true"><?php echo htmlspecialchars($etiqueta['Nombre'], ENT_QUOTES, 'UTF-8'); ?></div></div>
                                        </div>
                                    </td>
                                    <td class="text-end pe-3 ocultaAcciones" data-label="Acciones">
                                        <span class="celda-contenido">
                                            <i class="bi bi-pencil me-3 text-secondary me-3 cursor-pointer editarEtiqueta" data-bs-toggle="modal" data-bs-target="#modalEditarEtiqueta" data-id="<?php echo (int)$etiqueta['ID_Etiqueta'] ?>" data-nombre="<?php echo htmlspecialchars($etiqueta['Nombre'], ENT_QUOTES, 'UTF-8') ?>"></i>
                                            <i class="bi bi-trash text-secondary cursor-pointer" data-bs-toggle="modal" data-bs-target="#modalEliminarEtiqueta" data-id="<?php echo (int)$etiqueta['ID_Etiqueta'] ?>" data-nombre="<?php echo htmlspecialchars($etiqueta['Nombre'], ENT_QUOTES, 'UTF-8') ?>"></i>
                                        </span>
                                    </td>
                                </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
    <?php } ?>
    <script src="assets/etiqueta/EditarEtiqueta.js"></script>
    <script src="assets/componentePaginacion.js"></script>
    <script src="assets/etiqueta/eliminarEtiqueta.js"></script>
    <?php
        require __DIR__ . "/../Components/etiqueta/modalCrear.php";
        require __DIR__ . "/../Components/etiqueta/modalEditar.php";
        require __DIR__ . "/../Components/etiqueta/modalConfirmacionEliminacion.php";
    ?>
</section>
