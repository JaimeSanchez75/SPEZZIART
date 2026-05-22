<?php

declare(strict_types=1);
require_once __DIR__ . '/../../../core/session.php';
require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/ComponentesRender.php';

class FeedView
{
    private $componentes;
    public function __construct(){$this->componentes = new ComponentesRender();}
    public function renderRecipeCard($receta){return $this->componentes->renderRecipeCard($receta);} 
    public function render($recetas, $etiquetas, $catActiva = null, $config = null, $orden = 'populares', $fotoPerfilUsuario, $seed = null)
    {
        $userTheme = $config['Tema'] ?? 'sistema';?>
        <script>window.isLoggedIn = <?php echo Auth::check() ? 'true' : 'false'; ?>;</script>      
        <?php if (Auth::check()): ?>
            <script>
                window.currentUserId = <?php echo Auth::id(); ?>;
                window.currentUsername = <?php echo json_encode($_SESSION['user']['Nombre'] ?? $_SESSION['user']['nombre'] ?? ''); ?>;
                window.currentUserFoto = <?php echo json_encode($fotoPerfilUsuario ?? null); ?>;
                window.userConfig = {notificacionesOn: <?php echo $config['NotificacionOn'] ?? 1; ?>};
            </script>
        <?php endif; ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
            <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <title>Spezziart | Feed</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
            <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
            <link rel="stylesheet" href="/global/styles/styles.css">
            <link rel="stylesheet" href="/global/styles/feed.css">
            <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
            <script>window.userTheme = '<?= $_SESSION['user']['tema'] ?? 'sistema' ?>';</script>
            <script src="/global/js/theme.js"></script>
        </head>
        <body>
            <div class="container py-3">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-4 cabecera-page px-2">
                    <div class="d-flex gap-2 align-items-center cabecera-left">
                        <div class="flex-shrink-0"><span><i class="bi bi-house-heart texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande cabecera-icono"></i></span></div>
                        <div class="cabecera-text">
                            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                <p class="titulo letraRomana fw-bold texto-rojo m-0 cabecera-titulo">SPEZZIART</p>
                                <span class="subtitulo letraRomana fw-bold text-secondary cabecera-sep">|</span>
                                <h1 class="fw-bold subtitulo letraRomana m-0 cabecera-titulo">Feed</h1>
                            </div>
                            <p class="texto text-secondary m-0 cabecera-subtitulo">Descubre y explora las recetas de la comunidad</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-center flex-shrink-0">
                        <?php if (Auth::check()): ?>
                            <div class="dropdown">
                                <button id="campana" type="button" class="btn position-relative p-0 border-0 bg-transparent me-2" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false">
                                    <i class="bi bi-bell text-secondary fs-5"></i>
                                    <span id="contadorNotificaciones" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"></span>
                                </button>
                                <div id="dropdownNotificaciones" class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="min-width:300px; max-height:420px;">
                                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                        <span class="fw-bold letraRomana textoMediano">Notificaciones</span>
                                        <button type="button" id="btn-limpiar-notificaciones" class="btn btn-sm btn-link text-danger p-0 text-decoration-none">Limpiar todas</button>
                                    </div>
                                    <div id="notificaciones-lista" style="max-height:340px; overflow-y:auto;">
                                        <div class="text-center text-muted py-3">Cargando...</div>
                                    </div>
                                </div>
                            </div>
                            <span class="text-secondary">|</span>
                            <a href="/pages/configuracion" class="position-relative"><i class="bi bi-gear text-secondary fs-4"></i></a>
                        <?php else: ?>
                            <a href="/pages/login" class="border border-rojo bg-white texto-rojo py-1 px-3 rounded-3 texto fw-medium text-decoration-none">Iniciar sesión</a>
                        <?php endif; ?>
                    </div>
                </div>
                <div id="feed-container" class="lista gap-1">
                    <?php if (empty($recetas)): ?>
                        <div class="text-center py-5">
                            <span class="material-symbols-outlined fs-1 text-muted">no_meals</span>
                            <p class="text-muted mt-2">No se encontraron recetas.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($recetas as $receta): ?>
                            <?php echo $this->componentes->renderRecipeCard($receta); ?>
                        <?php endforeach; ?>
                        <div id="infinite-scroll-trigger" class="py-3 text-center"></div>
                    <?php endif; ?>
                </div>
                <div class="comments-overlay d-none" id="comments-overlay">
                    <div class="comments-sheet modal-content bg-white sombra border-0 rounded-top-4 overflow-hidden">
                        <div class="drag-handle bg-grisClaro rounded-pill mx-auto my-3"></div>
                        <div class="comments-header modal-header d-flex justify-content-between align-items-center border-bottom bg-white px-4 py-3">
                            <div class="d-flex gap-3 align-items-center">
                                <span class="bg-rojoClaro rounded-3 d-flex align-items-center justify-content-center cajaW40">
                                    <i class="bi bi-chat-dots texto-rojo textoMediano"></i>
                                </span>
                                <div>
                                    <h3 class="modal-title fw-bold tituloPequeno letraRomana m-0">Comentarios</h3>
                                    <span class="badge bg-rojoClaro texto-rojo textoPequeno text-uppercase px-3 py-2 rounded-pill">Feed</span>
                                </div>
                            </div>
                            <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" onclick="FeedApp.closeComments()" aria-label="Cerrar"></button>
                        </div>
                        <div class="comments-body bg-body p-3"></div>
                        <div class="comments-input modal-footer border-top bg-white px-4 py-3">
                            <form class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center w-100" onsubmit="FeedApp.sendComment(event)">
                                <div class="input-group rounded-3 overflow-hidden flex-grow-1">
                                    <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0">
                                        <i class="bi bi-chat-dots texto-rojo textoMediano"></i>
                                    </span>
                                    <input type="text" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" placeholder="Añade un comentario...">
                                </div>
                                <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4 flex-shrink-0">Enviar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php $this->componentes->renderModals($etiquetas); ?>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                window.FeedApp = window.FeedApp || {};
                FeedApp.state = 
                {
                    offset: 0,
                    limit: 5,
                    loading: false,
                    isFull: false,
                    orden: '<?php echo $orden; ?>'
                };
                FeedApp.filters = 
                {
                    etiquetas: [],
                    busqueda: ''
                };
                window.feedSeed = <?php echo $seed ? $seed : 1; ?>;
            </script>
            <?php
            $jsFiles = ['FeedCore','FeedScroll','FeedSnap','FeedLikes','FeedComentarios','FeedPopOvers'];
            foreach ($jsFiles as $f):
                $path = __DIR__ . '/../assets/' . $f . '.js';
                $v = file_exists($path) ? filemtime($path) : 1;
            ?>
            <script src="/pages/feed/assets/<?= $f ?>.js?v=<?= $v ?>"></script>
            <?php endforeach; ?>
            <?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
            <?php require_once __DIR__ . '/../../receta/view/verRecetaModal.php'; ?>
        </body>
        </html><?php
    }
}
