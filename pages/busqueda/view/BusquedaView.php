<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../core/session.php';
require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/BusquedaComponentes.php';
class BusquedaView
{
    private $componentes;
    public function __construct(){$this->componentes = new BusquedaComponentes();}
    public function renderRecipeCardGrid($receta){return $this->componentes->renderRecipeCardGrid($receta);}
    public function renderUserCard($user){return $this->componentes->renderUserSearchCard($user);}
    public function render($recetas, $etiquetas, $recomendadas = [])
    {
        $mostrarRecomendadas = empty($recetas) && !empty($recomendadas);
        $hayResultados = !empty($recetas);
        ?>
        <script>window.isLoggedIn = <?php echo Auth::check() ? 'true' : 'false'; ?>;</script>
        <!DOCTYPE html>
        <html lang="es" data-bs-theme="<?= $_SESSION['user']['tema'] ?? 'sistema' ?>">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="csrf-token" content="<?= csrf_token() ?>">
            <title>Spezziart | Explorar</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="/global/styles/global.css">
            <link rel="stylesheet" href="/global/styles/busqueda.css">
            <link rel="stylesheet" href="/global/styles/comments-overlay.css">
            <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
            <script>window.userTheme = '<?= $_SESSION['user']['tema'] ?? 'sistema' ?>';</script>
            <script src="/global/js/theme.js"></script>
        </head>
        <body>
            <main class="container py-4 py-lg-5 mb-5 mx-auto busqueda-shell">
                <section class="d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-4 cabecera-page">
                        <div class="d-flex gap-2 align-items-center cabecera-left">
                            <div class="flex-shrink-0"><span><i class="bi bi-search texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande cabecera-icono"></i></span></div>
                            <div class="cabecera-text">
                                <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                    <p class="titulo letraRomana fw-bold texto-rojo m-0 cabecera-titulo">SPEZZIART</p>
                                    <span class="subtitulo letraRomana fw-bold text-secondary cabecera-sep">|</span>
                                    <h3 class="modal-title fw-bold subtitulo letraRomana m-0 cabecera-titulo">Explorar</h3>
                                </div>
                                <p class="texto text-secondary m-0 cabecera-subtitulo">Busca recetas, ingredientes y usuarios en Spezziart</p>
                            </div>
                        </div>
                        <?php if (!Auth::check()): ?>
                            <a href="/pages/login" class="border border-rojo bg-white texto-rojo py-1 px-3 rounded-3 texto fw-medium text-decoration-none">Iniciar sesión</a>
                        <?php endif; ?>
                    </div>
                    <div class="search-controls-wrapper rounded-4 mb-4">
                        <div class="search-bar-row">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-3 rounded-end-0">
                                    <i class="bi bi-search texto-rojo"></i>
                                </span>
                                <input id="search-input" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" placeholder="Buscar recetas, ingredientes o usuarios... (ej: @usuario)">
                            </div>
                        </div>
                        <div class="filters-row">
                            <div class="filters-group">
                                <button class="border border-rojo bg-rojoClaro texto-rojo py-2 px-3 rounded-3 texto fw-medium d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#modalEtiquetas">
                                    <i class="bi bi-sliders2"></i> Filtrar
                                </button>
                                <div id="chips-wrapper" class="d-flex gap-2 flex-wrap"></div>
                                <span id="extra-chips-badge" class="badge rounded-pill bg-grisClaro texto-gris d-none"></span>
                                <button id="clear-filters" class="btn btn-link btn-sm text-muted d-none">Limpiar filtros</button>
                            </div>
                            <div class="cols-selector-group">
                                <div class="d-flex gap-2 flex-wrap justify-content-center" role="group">
                                    <button type="button" class="border border-gris bg-white py-1 px-2 text-secondary rounded-3 d-flex align-items-center gap-2 texto" data-cols="2" title="2 columnas">
                                        <span class="material-symbols-outlined">grid_view</span>
                                    </button>
                                    <button type="button" class="border border-gris bg-white py-1 px-2 text-secondary rounded-3 d-flex align-items-center gap-2 texto" data-cols="3" title="3 columnas">
                                        <span class="material-symbols-outlined">apps</span>
                                    </button>
                                    <button type="button" class="border border-gris bg-white py-1 px-2 text-secondary rounded-3 d-none d-md-flex align-items-center gap-2 texto" data-cols="4" title="4 columnas">
                                        <span class="material-symbols-outlined">view_quilt</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if ($mostrarRecomendadas): ?>
                        <div class="welcome-section rounded-4 p-3 sombra mb-4 bg-white">
                            <h4 class="textoMediano fw-bold text-dark mb-2"><i class="bi bi-stars texto-rojo me-1"></i> ¡Buen provecho!</h4>
                            <p class="text-secondary mb-0">Hoy te recomendamos estas delicias. ¿Te animas a probar algo nuevo?</p>
                        </div>
                    <?php endif; ?>
                    <div id="resultados-header" class="mb-4"></div>
                    <div id="resultados-container" class="grid-container grid-3">
                        <?php if (!$hayResultados && !$mostrarRecomendadas): ?>
                            <div class="full-width-message py-5">
                                <span class="material-symbols-outlined fs-1 text-muted">search_off</span>
                                <p class="mt-3 text-muted">No se encontraron recetas con esos filtros.</p>
                                <button class="border border-rojo bg-rojoClaro texto-rojo py-2 px-3 rounded-3 texto fw-medium mt-2" onclick="window.BuscarApp.clearFilters()">
                                    <i class="bi bi-filter-circle me-1"></i> Limpiar filtros
                                </button>
                            </div>
                        <?php else: ?>
                            <?php
                            $itemsAMostrar = $hayResultados ? $recetas : $recomendadas;
                            foreach ($itemsAMostrar as $receta):
                                echo $this->renderRecipeCardGrid($receta);
                            endforeach;
                            ?>
                        <?php endif; ?>
                    </div>
                    <div id="infinite-scroll-trigger" style="height: 10px;"></div>
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
                                        <span class="badge bg-rojoClaro texto-rojo textoPequeno text-uppercase px-3 py-2 rounded-pill">Explorar</span>
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
                </section>
            </main>
            <div class="modal fade" id="modalEtiquetas" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                        <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
                        <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                            <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                            <div class="d-flex gap-3 align-items-center mb-4">
                                <div><span><i class="bi bi-tags texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                                <div>
                                    <h3 class="modal-title fw-bold subtitulo letraRomana m-0">Filtrar por etiquetas</h3>
                                    <p class="texto text-secondary m-0">Selecciona las etiquetas para filtrar recetas.</p>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2" id="modal-tags-list">
                                <?php foreach ($etiquetas as $et): ?>
                                    <button class="border border-rojo bg-white texto-rojo p-2 texto rounded-4 px-3 chip-selectable" data-name="<?= htmlspecialchars($et['Nombre']) ?>"><?= htmlspecialchars($et['Nombre']) ?></button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-5 position-relative z-1  ">
                            <button type="button" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4" data-bs-dismiss="modal">Aplicar</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php $this->componentes->renderModals($etiquetas); ?>
            <?php require_once ROOT_PATH . '/global/modalConfirmacionGenerico.php'; ?>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <script src="/global/report.js"></script>
            <script src="/global/save.js"></script>
            <script>
                window.isLoggedIn = <?= Auth::check() ? 'true' : 'false'; ?>;
                window.BuscarApp = 
                {
                    state: 
                    {
                        offset: <?= $hayResultados ? count($recetas) : count($recomendadas) ?>,
                        limit: 12,
                        loading: false,
                        isFull: false,
                        showingRecommendations: <?= $mostrarRecomendadas ? 'true' : 'false' ?>
                    },
                    filters: 
                    {
                        busqueda: '',
                        etiquetas: [],
                        esfit: <?= isset($_GET['esfit']) ? 'true' : 'false' ?>
                    },
                    clearFilters: function() 
                    {
                        this.filters.busqueda = '';
                        this.filters.etiquetas = [];
                        document.getElementById('search-input').value = '';
                        const headerContainer = document.getElementById('resultados-header');
                        if (headerContainer) headerContainer.innerHTML = '';
                        const clearBtn = document.getElementById('clear-filters');
                        if (clearBtn) clearBtn.click();
                    }
                };
            </script>
            <script src="/../pages/feed/assets/FeedCore.js"></script>
            <script src="/../pages/feed/assets/FeedLikes.js"></script>
            <script src="/../pages/feed/assets/FeedComentarios.js"></script>
            <script src="/../pages/feed/assets/FeedPopOvers.js"></script>
            <script src="/../pages/busqueda/assets/Buscar.js"></script>
            <?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
            <?php require_once __DIR__ . '/../../receta/view/verRecetaModal.php'; ?>
        </body>
        </html>
        <?php
    }
}
