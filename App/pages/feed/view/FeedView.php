<?php
require_once __DIR__ . '/../../../core/auth.php';

class FeedView
{
    /**
     * Renderiza la página completa del feed
     */
    public function render($recetas, $etiquetas, $catActiva = null, $config = null)
    {
        $modoOscuro = $config && $config['ModoOscuro'] ? 'dark' : 'light';
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Spezziart | Social</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
            <link rel="stylesheet" href="/App/global/styles/global.css">
            <link rel="stylesheet" href="/App/global/styles/feed.css">
        </head>
        <body data-bs-theme="<?php echo $modoOscuro; ?>">
            <div class="container mt-4">
                <!-- Cabecera -->
                <div class="header-grid mb-4">
                    <div class="header-item item-logo">
                        <h3 class="fw-bold d-flex align-items-center m-0 text-nowrap">
                            <span class="text-danger" style="letter-spacing: 0.3em;">SPEZZIART</span>
                            <span class="ms-2 d-none d-sm-inline text-dark social">| Social</span>
                        </h3>
                    </div>

                    <div class="header-item item-search">
                        <div class="position-relative">
                            <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                            <input id="search-input" class="form-control rounded-pill ps-5 border-0 shadow-sm" 
                                placeholder="Buscar recetas, chefs...">
                        </div>
                    </div>

                    <div class="header-item item-actions">
                        <?php if (Auth::check()): ?>
                            <div class="notification-icon position-relative">
                                <span class="material-symbols-outlined cursor-pointer">notifications</span>
                                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                            </div>
                        <?php else: ?>
                            <a href="/App/pages/login" class="btn btn-danger rounded-pill px-4 shadow-sm">Login</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Filtro de etiquetas -->
                <div class="rounded-pill d-flex align-items-center gap-2 mb-5 flex-wrap etiquetas-filtro">
                    <button class="btn btn-outline-danger rounded-pill light d-flex align-items-center justify-content-center btn-add-tag" 
                            data-bs-toggle="modal" data-bs-target="#modalEtiquetas">
                        <span class="material-symbols-outlined">add</span>
                    </button>
                    <div id="chips-wrapper" class="d-flex gap-2 flex-wrap align-items-center"></div>
                    <span id="extra-chips-badge" class="badge rounded-pill bg-secondary d-none" style="cursor: default;"></span>
                </div>

                <!-- Contenedor del feed -->
                <div id="feed-container">
                    <?php if (empty($recetas)): ?>
                        <p class="text-center text-muted">No se encontraron recetas.</p>
                    <?php else: ?>
                        <?php foreach ($recetas as $receta): ?>
                            <?php echo $this->renderRecipeCard($receta); ?>
                        <?php endforeach; ?>
                        <div id="infinite-scroll-trigger" style="height: 10px;"></div>
                    <?php endif; ?>
                </div>

                <!-- Overlays de comentarios (uno por receta) -->
                <div id="comments-overlays-container">
                    <?php foreach ($recetas as $receta): ?>
                        <div class="comments-overlay d-none" id="comments-<?php echo $receta['ID_Receta']; ?>">
                            <div class="comments-sheet">
                                <div class="drag-handle"></div>
                                <div class="comments-header">
                                    <span>Comentarios</span>
                                    <span class="close-btn" onclick="FeedApp.closeComments(<?php echo $receta['ID_Receta']; ?>)">✕</span>
                                </div>
                                <div class="comments-body"></div>
                                <div class="comments-input">
                                    <form onsubmit="FeedApp.sendInlineComment(event, <?php echo $receta['ID_Receta']; ?>)">
                                        <input type="text" placeholder="Añade un comentario...">
                                        <button type="submit">Enviar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php $this->renderModals($etiquetas); ?>

            <!-- Scripts -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            
            <!-- Estado global del feed -->
            <script>
                window.FeedApp = window.FeedApp || {};
                FeedApp.state = {
                    offset: <?php echo count($recetas); ?>,
                    limit: 5,
                    loading: false,
                    isFull: false,
                    overlayAbierto: null
                };
            </script>

            <script src="/App/pages/feed/view/feed.js"></script>
            <script src="/App/pages/feed/view/FiltradoEtiquetas.js"></script>
            <?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
            <script src="/App/pages/feed/view/PopUps.js"></script>
        </body>
        </html>
        <?php
    }

    /**
     * Devuelve el HTML de una tarjeta de receta
     */
    public function renderRecipeCard($receta)
    {
        ob_start();
        $likeClass = (isset($receta['DioLike']) && $receta['DioLike']) ? 'text-danger fill-icon' : '';
        $imagenes = !empty($receta['Imagen']) ? explode(',', $receta['Imagen']) : [];
        $idCarousel = "carousel-" . $receta['ID_Receta'];
        
        // Etiquetas
        $etiquetasArr = !empty($receta['EtiquetasNombres']) ? explode(',', $receta['EtiquetasNombres']) : [];
        $limiteVisibles = 3;
        $visibles = array_slice($etiquetasArr, 0, $limiteVisibles);
        $ocultas = array_slice($etiquetasArr, $limiteVisibles);
        $totalOcultas = count($ocultas);
        $htmlOcultas = "";
        foreach ($ocultas as $tag) {
            $htmlOcultas .= '<span class="badge rounded-pill bg-danger bg-opacity-10 text-danger m-1">#' . htmlspecialchars(trim($tag)) . '</span>';
        }
        ?>
        <div class="card feed-card mb-5 border-0 shadow-sm rounded-4 overflow-hidden bg-white mx-auto recipe-container" data-id="<?php echo $receta['ID_Receta']; ?>">
            <div class="d-flex flex-column h-100">
                
                <!-- Cabecera del usuario (solo escritorio) -->
                <div class="recipe-header d-none d-md-flex align-items-center px-4 border-bottom bg-white">
                    <?php echo $this->renderUserBlock($receta); ?>
                </div>

                <div class="row g-0 flex-grow-1 recipe-content">
                    <!-- Carrusel de imágenes -->
                    <div class="col-12 col-md-7 bg-light carousel-column"> 
                        <?php if (!empty($imagenes)): ?>
                            <div id="<?php echo $idCarousel; ?>" class="carousel slide h-100" data-bs-ride="false">
                                <div class="carousel-inner h-100">
                                    <?php foreach ($imagenes as $index => $img): ?>
                                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?> h-100">
                                            <img src="/App/uploads/<?php echo htmlspecialchars(trim($img)); ?>" class="d-block w-100 h-100 object-fit-cover">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php if (count($imagenes) > 1): ?>
                                    <button class="carousel-control-prev" type="button" data-bs-target="#<?php echo $idCarousel; ?>" data-bs-slide="prev"><span class="carousel-control-prev-icon"></span></button>
                                    <button class="carousel-control-next" type="button" data-bs-target="#<?php echo $idCarousel; ?>" data-bs-slide="next"><span class="carousel-control-next-icon"></span></button>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Columna de información -->
                    <div class="col-12 col-md-5 d-flex flex-column bg-white border-start-md">
                        <!-- Versión móvil del usuario -->
                        <div class="d-md-none p-3 border-bottom d-flex justify-content-between align-items-center gap-2">
                            <h5 class="fw-bold mb-0 text-danger text-truncate" style="max-width: 60%; font-size: 1.1rem;">
                                <?php echo htmlspecialchars($receta['Titulo']); ?>
                            </h5>
                            <?php echo $this->renderUserBlock($receta, true); ?>
                        </div>

                        <div class="p-4 flex-grow-1 d-flex flex-column">
                            <!-- Etiquetas visibles -->
                            <div class="mb-3 d-none d-md-block">
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <?php foreach ($visibles as $tag): ?>
                                        <span class="badge rounded-pill bg-danger bg-opacity-10 text-danger fw-normal">#<?php echo trim($tag); ?></span>
                                    <?php endforeach; ?>
                                    <?php if ($totalOcultas > 0): ?>
                                        <button type="button" class="btn btn-sm rounded-pill bg-secondary bg-opacity-10 text-secondary fw-bold border-0 py-0 px-2" 
                                                data-bs-toggle="popover" data-bs-placement="top" data-bs-html="true" title="Más etiquetas"
                                                data-bs-content='<?php echo htmlspecialchars($htmlOcultas, ENT_QUOTES); ?>'>
                                            +<?php echo $totalOcultas; ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <h5 class="fw-bold mb-0 text-danger d-none d-md-block recipe-title-desktop">
                                <?php echo htmlspecialchars($receta['Titulo']); ?>
                            </h5>
                            <p class="text-secondary recipe-desc mt-md-4">
                                <?php echo htmlspecialchars($receta['Descripcion'] ?? 'Sin descripción.'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Sección de comentarios inline (oculta por defecto) -->
                <div class="comments-section-inline d-none border-top" id="inline-comments-<?php echo $receta['ID_Receta']; ?>">
                    <div class="comments-container p-3" style="max-height: 300px; overflow-y: auto;"></div>
                    <div class="comment-input-wrapper p-2 border-top bg-light">
                        <form onsubmit="FeedApp.sendInlineComment(event, <?php echo $receta['ID_Receta']; ?>)" class="d-flex align-items-center">
                            <input type="text" class="form-control form-control-sm rounded-pill border-0 bg-white ps-3" 
                                placeholder="Añade un comentario..." required>
                            <button type="submit" class="btn btn-link text-danger fw-bold text-decoration-none">Publicar</button>
                        </form>
                    </div>
                </div>

                <!-- Pie con acciones -->
                <div class="recipe-footer d-flex align-items-center px-4 border-top bg-white">
                    <div class="d-flex justify-content-between w-100">
                        <div class="d-flex gap-4">
                            <div class="d-flex align-items-center gap-2 cursor-pointer" onclick="FeedApp.toggleLike(<?php echo $receta['ID_Receta']; ?>, this)">
                                <span class="material-symbols-outlined <?php echo $likeClass; ?>">favorite</span>
                                <span class="fw-bold small"><?php echo $receta['Megustas'] ?? 0; ?></span>
                            </div>
                            <div class="d-flex align-items-center gap-2 cursor-pointer" onclick="FeedApp.openComments(<?php echo $receta['ID_Receta']; ?>)">
                                <span class="material-symbols-outlined">chat_bubble</span>
                                <span class="fw-bold small comment-count-<?php echo $receta['ID_Receta']; ?>">
                                    <?php echo $receta['TotalComentarios'] ?? 0; ?>
                                </span>
                            </div>
                        </div>
                        <span class="material-symbols-outlined cursor-pointer text-muted">bookmark</span>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Devuelve el HTML del bloque de usuario (avatar y nombre)
     */
    private function renderUserBlock($receta, $compact = false)
    {
        ob_start();
        ?>
        <a href="/App/pages/perfil/<?php echo $receta['ID_Creador']; ?>" class="text-decoration-none d-flex align-items-center gap-2 overflow-hidden">
            <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center text-white flex-shrink-0" 
                 style="width: <?php echo $compact ? '28px' : '35px'; ?>; height: <?php echo $compact ? '28px' : '35px'; ?>;">
                <span class="material-symbols-outlined" style="font-size: <?php echo $compact ? '14px' : '18px'; ?>;">person</span>
            </div>
            <div class="d-flex flex-column text-truncate">
                <span class="fw-bold text-dark small text-truncate" style="line-height:1;">@<?php echo htmlspecialchars($receta['Username']); ?></span>
                <?php if (!$compact): ?>
                    <small class="text-muted text-truncate"><?php echo htmlspecialchars($receta['Apodo'] ?? ''); ?></small>
                <?php endif; ?>
            </div>
        </a>
        <?php
        return ob_get_clean();
    }

    /**
     * Renderiza los modales (solo el de etiquetas, el de comentarios ya no se usa)
     */
    private function renderModals($etiquetas)
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
                            <?php foreach ($etiquetas as $et): ?>
                                <button class="btn btn-sm rounded-pill chip-selectable" data-name="<?php echo htmlspecialchars($et['Nombre']); ?>">
                                    <?php echo htmlspecialchars($et['Nombre']); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <button id="clear-filters" class="btn btn-link btn-sm text-muted text-decoration-none d-none mt-3">Limpiar filtros</button>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-danger w-100 rounded-pill" data-bs-dismiss="modal">Aplicar Filtros</button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
}