<?php declare(strict_types=1); ?>
<section class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap align-items-center mb-2">
        <div>
            <h2 class="subtitulo letraRomana fw-bold  m-0">Recetas</h2>
            <p class="texto text-secondary">Crea, edita o elimina el recetas base de la aplicación.</p>
        </div>
        <button class="text-white px-4 py-2 rounded-pill border-0 bg-rojo texto fw-medium" data-bs-toggle="modal" data-bs-target="#modalCrearReceta">
            <i class="bi bi-person-plus me-2"></i>Nueva receta
        </button>
    </div>
    <?php if (empty($recetas)) 
          { ?>
            <div class="text-center py-5">
                <i class="bi bi-exclamation-triangle display-4 text-muted"></i>
                <p class="text-muted mt-3">No hay recetas registradas.</p>
            </div>
    <?php } 
          else 
          { ?>
            
            <div class="row justify-content-between align-items-center gap-2 mb-4">

                
                <div class="col-12 col-sm-6 col-md-5">
                    <div class="input-group border rounded rounded-4">
                        <span class="input-group-text bg-light border-0 rounded-start-4">
                            <i class="bi bi-search text-secondary"></i>
                        </span>
                        <input type="text" class="form-control input border-0 bg-light texto rounded-end-4 text-secondary"
                            placeholder="Buscar por nombre ..." id="buscador">
                    </div>
                </div>

                
                <div class="col-12 col-sm-4 col-md-3">
                    <select class="form-select rounded-4 py-2 px-4 border text-secondary texto pe-5 bg-light " id="filtroTabla" data-filtro="rol">

                        <option value="todos" selected>Todas</option>
                        <?php foreach ($etiquetas as $etiqueta) { ?>
                            <option value="<?php echo htmlspecialchars($etiqueta['Nombre'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($etiqueta['Nombre'], ENT_QUOTES, 'UTF-8'); ?></option>
                        <?php } ?>

                    </select>
                </div>

            </div>

            <div id="no-resultados" class="text-center py-4"></div>

            <div class="row g-4 " id="tablaPaginada" data-paginacion-pendiente="true">

                <?php foreach ($recetas as $receta) { ?>

                    <div class="col-xl-4 col-md-6 col-12 d-flex"
                        data-nombre="<?= htmlspecialchars(implode(' ', $receta['Etiquetas'] ?? [])) ?>">
                        <div class="card border-0 sombra rounded-4 flex-fill efectoEscala" >
                            <?php
                                $imagenPortada = '';
                                if (!empty($receta['Imagen'])) 
                                {
                                    $listaImagenes = array_values(array_filter(array_map('trim', explode(',', $receta['Imagen']))));
                                    $imagenPortada = $listaImagenes[0] ?? '';
                                }
                            ?>
                         <?php if (!empty($imagenPortada)) 
                               { ?>
                                    <img src="<?= htmlspecialchars($imagenPortada) ?>" class="card-img-top rounded-top-4 object-fit-cover" style="height: 180px;" alt="Imagen de receta">
                         <?php } 
                               else 
                               { ?>
                                    <div class="card-img-top rounded-top-4 bg-rojoClaro texto-rojo d-flex align-items-center justify-content-center"
                                        style="height: 180px;">
                                        <i class="bi bi-image" style="font-size: 3rem;"></i>
                                    </div>
                         <?php } ?>
                            <div class="card-body px-4 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between mb-2">
                                        
                                        <div class="d-flex gap-1 flex-wrap align-items-center">
                                            <?php
                                            if (!empty($receta['Etiquetas']))
                                            {
                                                $etiquetasReceta = $receta['Etiquetas'];
                                                $maxVisibles = 2;
                                                $visibles    = array_slice($etiquetasReceta, 0, $maxVisibles);
                                                $restantes   = array_slice($etiquetasReceta, $maxVisibles);

                                                foreach ($visibles as $etq) {
                                                    echo '<div class="badge bg-rojoClaro texto-rojo textoPequeno">' . strtoupper(htmlspecialchars($etq)) . '</div>';
                                                }

                                                if (!empty($restantes)) {
                                                    $htmlTooltip = '<div class="etiquetas-tooltip-inner">';
                                                    foreach ($restantes as $etq) {
                                                        $htmlTooltip .= '<span class="badge bg-rojoClaro texto-rojo textoPequeno">' . strtoupper(htmlspecialchars($etq)) . '</span>';
                                                    }
                                                    $htmlTooltip .= '</div>';
                                            ?>
                                                    <span class="etiquetas-mas-badge badge bg-rojoClaro texto-rojo textoPequeno"
                                                          tabindex="0" role="button"
                                                          data-bs-toggle="tooltip"
                                                          data-bs-html="true"
                                                          data-bs-placement="top"
                                                          data-bs-custom-class="etiquetas-tooltip"
                                                          data-bs-title="<?= htmlspecialchars($htmlTooltip, ENT_QUOTES) ?>">
                                                        +<?= count($restantes) ?>
                                                    </span>
                                            <?php }
                                            }
                                            ?>
                                        </div>
                                        
                                        <span class="text-secondary texto"><i class="bi bi-clock"></i> <?php echo (int)$receta['Tiempo']; ?> min</span>
                                    </div>
                                    
                                    <h5 class=" card-title tituloPequeno letraRomana fw-bold mt-0 mb-0" data-buscado="true"><?php echo htmlspecialchars($receta['Titulo'], ENT_QUOTES, 'UTF-8'); ?></h5>
                                    <p class="card-text text-secondary textoPequeno m-0"><?php echo htmlspecialchars(strlen($receta['Descripcion']) > 30 ? substr($receta['Descripcion'], 0, 30) . '...' : $receta['Descripcion'], ENT_QUOTES, 'UTF-8'); ?></p>
                                </div>
                                
                                <div class="d-flex align-items-center mt-3 border-top pt-2">
                                    <i class="bi bi-person me-2 rounded-circle p-1 border px-2 bg-light texto text-secondary"></i>
                                    <span class="text-secondary texto">Por <span data-buscado="true"><?php echo htmlspecialchars($receta['NombreUsuario'], ENT_QUOTES, 'UTF-8'); ?></span></span>
                                    
                                    <a href="/pages/administracion/recetas/ver/<?php echo (int)$receta['ID_Receta']; ?>" class="ms-auto text-decoration-none textoPequeno texto-rojo"><i class="bi bi-eye me-1"></i>Ver detalles</a>
                                </div>
                                <div class="text-end card-img-overlay p-3" style="pointer-events: none;">
                                    <i class="bi bi-pencil-square bg-white sombra rounded-circle p-1 px-2 pt-2 text-muted" style="pointer-events: auto; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#modalCrearReceta" data-mode="editar" data-id="<?php echo (int)$receta['ID_Receta'] ?>"></i>
                                    <i class="bi bi-trash  bg-white sombra rounded-circle p-1 px-2 pt-2 text-muted" style="pointer-events: auto; cursor: pointer;" data-bs-toggle="modal" data-bs-target="#eliminarRecetaModal" data-id="<?php echo (int)$receta['ID_Receta'] ?>" data-nombre="<?php echo htmlspecialchars($receta['Titulo'], ENT_QUOTES, 'UTF-8') ?>"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
    <?php } ?>
</section>
<?php
require __DIR__ . "/../Components/receta/modalCrear.php";
require __DIR__ . "/../Components/receta/modalConfirmacionEliminacion.php";
?>
<script src="assets/componentePaginacion.js"></script>
<script src="assets/receta/receta.js"></script>

