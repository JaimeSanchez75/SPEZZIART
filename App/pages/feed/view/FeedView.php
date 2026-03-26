<?php
require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/ComponentesRender.php';

class FeedView
{
    private $componentes;
    public function __construct(){$this->componentes = new ComponentesRender();}
    public function render($recetas, $etiquetas, $catActiva = null, $config = null)
    {   $modoOscuro = $config && $config['ModoOscuro'] ? 'dark' : 'light';
        ?>
        <script>window.isLoggedIn = <?php echo Auth::check() ? 'true' : 'false'; ?>;</script>
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
                            <div class="dropdown">
                                <button id="campana" class="btn position-relative" data-bs-toggle="dropdown">
                                    <span class="material-symbols-outlined cursor-pointer">notifications</span>
                                    <span id="contadorNotificaciones" 
                                        class="position-absolute top-0 start-100 translate-middle badge bg-danger">
                                    </span>
                                </button>
                                <div id="dropdownNotificaciones" class="dropdown-menu dropdown-menu-end p-2" style="width:300px;">
                                    <div class="text-muted text-center">Sin notificaciones</div>
                                </div>
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
                        <?php foreach ($recetas as $receta): ?><?php echo $this->componentes->renderRecipeCard($receta); ?><?php endforeach; ?>
                        <div id="infinite-scroll-trigger" style="height: 10px;"></div>
                    <?php endif; ?>
                </div>
                <!-- Overlays de comentarios (uno por receta) -->
                <div class="comments-overlay d-none" id="comments-overlay">
                    <div class="comments-sheet">
                        <div class="drag-handle"></div>
                        <div class="comments-header">
                            <span>Comentarios</span>
                            <span class="close-btn" onclick="FeedApp.closeComments()">✕</span>
                        </div>
                        <div class="comments-body"></div>
                        <div class="comments-input">
                            <form onsubmit="FeedApp.sendComment(event)">
                                <input type="text" placeholder="Añade un comentario...">
                                <button type="submit">Enviar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php $this->componentes->renderModals($etiquetas); ?>
            
            <!-- Scripts -->
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <!-- Estado global del feed -->
            <script>
                window.FeedApp = window.FeedApp || {};
                FeedApp.state = 
                {
                    offset: <?php echo count($recetas); ?>,
                    limit: 5,
                    loading: false,
                    isFull: false,
                    overlayAbierto: null
                };
            </script>
            <script src="/App/pages/feed/view/feed.js"></script>
            <script src="/App/pages/feed/view/FiltradoEtiquetas.js"></script>
            <script src="/App/pages/feed/view/PopUps.js"></script>
            <?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
        </body>
        </html>
        <?php
    }
}