<?php
require_once __DIR__ . '/../../../core/csrfcheck.php'; 
class ComponentesRender
{
    public function renderRecipeCard($receta)
    {
        ob_start(); /* Renderiza una tarjeta de receta para el feed, con toda su información y acciones */

        // Variables para controlar el estado del like, las imágenes y las etiquetas de la receta
        $likeClass = (isset($receta['DioLike']) && $receta['DioLike']) ? 'text-danger fill-icon' : '';
        $imagenes = !empty($receta['Imagen']) ? explode(',', $receta['Imagen']) : [];
        $idCarousel = "carousel-" . $receta['ID_Receta'];
        $etiquetasArr = !empty($receta['EtiquetasNombres']) ? explode(',', $receta['EtiquetasNombres']) : [];
        $limiteVisibles = 3;
        $visibles = array_slice($etiquetasArr, 0, $limiteVisibles);
        $ocultas = array_slice($etiquetasArr, $limiteVisibles);
        $totalOcultas = count($ocultas);
        $htmlOcultas = "";
        // Generar el HTML para las etiquetas ocultas (si las hay) que se mostrarán en el popover al hacer clic en "+X"
        foreach ($ocultas as $tag) {$htmlOcultas .= '<span class="badge rounded-pill bg-danger bg-opacity-10 text-danger m-1">#' . htmlspecialchars(trim($tag)) . '</span>';}?>
        <div class="card feed-card border-0 shadow-sm rounded-4 overflow-hidden bg-white mx-auto recipe-container mb-3" data-id="<?php echo $receta['ID_Receta']; ?>">
            <div class="d-flex flex-column h-100 mb-1">
                <div class="recipe-header d-none d-md-flex align-items-center px-4 border-bottom bg-white py-3">
                    <?php echo $this->renderUserBlock($receta, false); ?>
                </div>
                <div class="row g-0 flex-grow-1 recipe-content">
                    <!-- Carrusel de imágenes -->
                    <div class="col-12 col-md-7 bg-light carousel-column"> 
                        <?php if (!empty($imagenes)): ?>
                            <div id="<?php echo $idCarousel; ?>" class="carousel slide h-100" data-bs-ride="false">
                                <div class="carousel-inner h-100">
                                    <?php foreach ($imagenes as $index => $img): ?>
                                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?> h-100">
                                            <img src="/App/uploads/<?php echo htmlspecialchars(trim($img)); ?>" 
                                                class="d-block w-100 h-100 object-fit-cover"
                                                onerror="this.onerror=null; this.src='/App/uploads/NoImg.jpg';"
                                                alt="Imagen de receta">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($imagenes) > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $idCarousel; ?>" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $idCarousel; ?>" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <img src="/App/uploads/NoImg.jpg" class="d-block w-100 h-100 object-fit-cover" alt="Sin imagen">
                        <?php endif; ?>
                    </div>
                    <!-- Columna de información -->
                    <div class="col-12 col-md-5 d-flex flex-column bg-white border-start-md">
                        <!-- Versión móvil del usuario -->
                        <div class="d-md-none p-3 border-bottom d-flex justify-content-between align-items-center gap-2">
                            <h5 class="fw-bold mb-0 text-danger text-truncate" style="max-width: 60%; font-size: 1.1rem;"><?php echo htmlspecialchars($receta['Titulo']); ?></h5>
                            <?php echo $this->renderUserBlock($receta, true); ?>
                        </div>
                        <div class="p-4 flex-grow-1 d-flex flex-column">
                            <!-- Etiquetas visibles -->
                            <div class="mb-3 d-none d-md-block">
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <?php foreach ($visibles as $tag): ?><span class="badge rounded-pill bg-danger bg-opacity-10 text-danger fw-normal">#<?php echo trim($tag); ?></span><?php endforeach; ?>
                                    <?php if ($totalOcultas > 0): ?>
                                        <button type="button" class="btn btn-sm rounded-pill bg-secondary bg-opacity-10 text-secondary fw-bold border-0 py-0 px-2" 
                                                data-bs-toggle="popover" data-bs-placement="top" data-bs-html="true" title="Más etiquetas"
                                                data-bs-content='<?php echo htmlspecialchars($htmlOcultas, ENT_QUOTES); ?>'>
                                            +<?php echo $totalOcultas; ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <h5 class="fw-bold mb-0 text-danger d-none d-md-block recipe-title-desktop"><?php echo htmlspecialchars($receta['Titulo']); ?></h5>
                            <p class="text-secondary recipe-desc mt-md-4"><?php echo htmlspecialchars($receta['Descripcion'] ?? 'Sin descripción.'); ?></p>
                        </div>
                    </div>
                </div>
                <!-- Sección de comentarios inline (oculta por defecto) -->
                <div class="comments-section-inline d-none border-top" id="inline-comments-<?php echo $receta['ID_Receta']; ?>">
                    <div class="comments-container p-3" style="max-height: 300px; overflow-y: auto;"></div>
                    <div class="comment-input-wrapper p-2 border-top bg-light">
                        <form onsubmit="FeedApp.sendInlineComment(event, <?php echo $receta['ID_Receta']; ?>)" class="d-flex align-items-center">
                            <input type="text" class="form-control form-control-sm rounded-pill border-0 bg-white ps-3" placeholder="Añade un comentario..." required>
                            <button type="submit" class="btn btn-link text-danger fw-bold text-decoration-none">Publicar</button>
                        </form>
                    </div>
                </div>
                <!-- Pie con acciones -->
                <div class="recipe-footer d-flex align-items-center px-4 border-top bg-white">
                    <div class="d-flex justify-content-between w-100">
                        <div class="d-flex gap-4">
                            <div class="d-flex align-items-center gap-2 cursor-pointer text-danger" onclick="FeedApp.toggleLike(<?php echo $receta['ID_Receta']; ?>, this)">
                                <button class="btn btn-link"><span class="material-symbols-outlined like-icon <?php echo $likeClass; ?>">favorite</span></button>
                                <span class="fw-bold small like-count"><?php echo $receta['Megustas'] ?? 0; ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-2 cursor-pointer text-danger" onclick="FeedApp.openComments(<?php echo $receta['ID_Receta']; ?>)">
                                <button class="btn btn-link"><span class="material-symbols-outlined">chat_bubble</span></button>
                                <span class="fw-bold small comment-count-<?php echo $receta['ID_Receta']; ?>"><?php echo $receta['TotalComentarios'] ?? 0; ?></span>
                            </div>
                        </div>
                        <div>
                            <button class="btn btn-link text-danger" onclick="if(!window.isLoggedIn) { event.preventDefault(); window.location.href='/App/pages/login'; }" data-bs-toggle="modal" data-bs-target="#saveModal"  data-id="<?php echo $receta['ID_Receta']; ?>"><span class="material-symbols-outlined cursor-pointer">bookmark</span></button>
                            <button class="btn btn-link text-danger" data-bs-toggle="modal" data-bs-target="#reportModal" data-report-type="receta" data-id="<?= $receta['ID_Receta'] ?>"onclick="if(!window.isLoggedIn) { event.preventDefault(); window.location.href='/App/pages/login'; }"><span class="material-symbols-outlined">flag</span></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    public function renderUserBlock($receta, $compact = false)
    {
        ob_start();
        ?>
        <a href="/App/pages/perfil/<?php echo $receta['ID_Creador']; ?>" class="text-decoration-none d-flex align-items-center gap-2 overflow-hidden">
            <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center text-white flex-shrink-0" 
                 style="width: <?php echo $compact ? '28px' : '35px'; ?>; height: <?php echo $compact ? '28px' : '35px'; ?>;">
                <span class="material-symbols-outlined" style="font-size: <?php echo $compact ? '14px' : '18px'; ?>;">person</span>
            </div>
            <div class="d-flex flex-column text-truncate">
                <span class="fw-bold text-dark small text-truncate" style="line-height:1;">@<?php echo htmlspecialchars($receta['Nombre']); ?></span>
                <?php if (!$compact): ?><small class="text-muted text-truncate"><?php echo htmlspecialchars($receta['Apodo'] ?? ''); ?></small><?php endif; ?>
            </div>
        </a>
        <?php
        return ob_get_clean();
    }
    public function renderModals($etiquetas)
    {
        ?>
        <!-- Modal para seleccionar etiquetas -->
        <div class="modal fade" id="modalEtiquetas" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-4">
                    <div class="modal-header border-0">
                        <h5 class="modal-title fw-bold">Añadir Etiquetas</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="d-flex flex-wrap gap-2" id="modal-tags-list">
                            <?php foreach ($etiquetas as $et): ?><button class="btn btn-sm rounded-pill chip-selectable" data-name="<?php echo htmlspecialchars($et['Nombre']); ?>"><?php echo htmlspecialchars($et['Nombre']); ?></button><?php endforeach; ?>
                        </div>
                        <button id="clear-filters" class="btn btn-link btn-sm text-muted text-decoration-none d-none mt-3">Limpiar filtros</button>
                    </div>
                    <div class="modal-footer border-0"><button type="button" class="btn btn-danger w-100 rounded-pill" data-bs-dismiss="modal">Aplicar Filtros</button></div>
                </div>
            </div>
        </div>
        <!-- Modal de reporte -->
        <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reportModalTitle">Reportar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="reportForm">
                            <input type="hidden" name="id" id="reportId">
                            <input type="hidden" name="type" id="reportType">
                            <div class="mb-3">
                                <label class="form-label">Motivo del reporte</label>
                                <select class="form-select" name="reason" id="reportReason" required><option value="">Selecciona un motivo...</option></select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Detalles adicionales (opcional)</label>
                                <textarea class="form-control" name="details" rows="2" placeholder="Describe el problema con más detalle..."></textarea>
                            </div>
                            <div class="alert alert-danger d-none" id="reportError"></div>
                            <div class="alert alert-success d-none" id="reportSuccess"></div>
                            <button type="submit" class="btn btn-danger w-100">Enviar reporte</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal de guardado -->
        <div class="modal fade" id="saveModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Guardar receta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="saveForm">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="id_receta" id="saveRecipeId">
                            <div class="mb-3">
                                <label class="form-label">Selecciona las colecciones</label>
                                <div id="cols-list" class="border rounded p-2" style="max-height: 200px; overflow-y: auto;">
                                    <div class="text-muted text-center">Cargando...</div>
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="guardarDefecto" checked>
                                <label class="form-check-label" for="guardarDefecto">
                                    Guardar también en mi colección por defecto (si no seleccionas ninguna)
                                </label>
                            </div>
                            <div class="alert alert-danger d-none" id="saveError"></div>
                            <div class="alert alert-success d-none" id="saveSuccess"></div>
                            <button type="submit" class="btn btn-danger w-100">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script src="/App/global/save.js"></script>
        <script src="/App/global/report.js"></script>
        <?php
    }
    public function renderRecipeCardGrid($receta)
    {
        ob_start();
        // Variables para controlar el estado del like, las imágenes y las etiquetas de la receta en la vista de grid
        $likeClass = (isset($receta['DioLike']) && $receta['DioLike']) ? 'text-danger fill-icon' : '';
        $imagenes = !empty($receta['Imagen']) ? explode(',', $receta['Imagen']) : [];
        $idCarousel = "carousel-grid-" . $receta['ID_Receta'];
        $titulo = htmlspecialchars($receta['Titulo']);
        $usuario = htmlspecialchars($receta['Nombre']);
        $likes = $receta['Megustas'] ?? 0;
        $comentarios = $receta['TotalComentarios'] ?? 0;
        ?>
        <div class="card feed-card-grid border-0 shadow-sm rounded-4 overflow-hidden bg-white" data-id="<?php echo $receta['ID_Receta']; ?>">
            <!-- Carrusel / Imagen (cuadrada) -->
            <div class="grid-img-container">
                <?php if (!empty($imagenes)): ?>
                    <div id="<?php echo $idCarousel; ?>" class="carousel slide h-100" data-bs-ride="false">
                        <div class="carousel-inner h-100">
                            <?php foreach ($imagenes as $index => $img): ?>
                                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?> h-100">
                                    <img src="/App/uploads/<?php echo htmlspecialchars(trim($img)); ?>" 
                                        class="d-block w-100 h-100 object-fit-cover"
                                        onerror="this.onerror=null; this.src='/App/uploads/NoImg.jpg';"
                                        alt="Imagen de receta">
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($imagenes) > 1): ?>
                            <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $idCarousel; ?>" data-bs-slide="prev">
                                <span class="carousel-control-prev-icon"></span>
                            </button>
                            <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $idCarousel; ?>" data-bs-slide="next">
                                <span class="carousel-control-next-icon"></span>
                            </button>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <img src="/App/uploads/NoImg.jpg" class="d-block w-100 h-100 object-fit-cover" alt="Sin imagen">
                <?php endif; ?>
            </div>
            <!-- Información debajo de la imagen -->
            <div class="grid-info p-2">
                <div class="d-flex justify-content-between align-items-center">
                    <a href="/App/pages/perfil/<?php echo $receta['ID_Creador']; ?>" class="text-decoration-none">
                        <span class="fw-bold text-dark small">@<?php echo $usuario; ?></span>
                    </a>
                    <div class="d-flex gap-2">
                        <div class="d-flex align-items-center gap-1" onclick="FeedApp.toggleLike(<?php echo $receta['ID_Receta']; ?>, this)">
                            <span class="material-symbols-outlined like-icon <?php echo $likeClass; ?>" style="font-size: 1.2rem;">favorite</span>
                            <span class="small like-count"><?php echo $likes; ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-1" onclick="FeedApp.openComments(<?php echo $receta['ID_Receta']; ?>)">
                            <span class="material-symbols-outlined" style="font-size: 1.2rem;">chat_bubble</span>
                            <span class="small comment-count-<?php echo $receta['ID_Receta']; ?>"><?php echo $comentarios; ?></span>
                        </div>
                    </div>
                </div>
                <h6 class="fw-bold mt-1 mb-0 text-truncate" title="<?php echo $titulo; ?>"><?php echo $titulo; ?></h6>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}