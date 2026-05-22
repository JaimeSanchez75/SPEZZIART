<?php
require_once __DIR__ . '/../../../core/auth.php';

class IndividualView
{
    private function normalizarSrcImagen(?string $src): string
    {
        $src = trim((string)$src);
        if ($src === '') {
            return '';
        }

        if (preg_match('#^(https?:)?//#i', $src)) {
            return $src;
        }

        return str_starts_with($src, '/') ? $src : '/' . ltrim($src, '/');
    }

    public function render($misRecetas, $guardadas, $etiquetas, $config = null, $colecciones = [], $query = '', $pagination = [])
    {
        $GLOBALS['colecciones'] = $colecciones;
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Spezziart | Mis Recetas</title>
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
            <link rel="stylesheet" href="/global/styles/global.css">
            <link rel="stylesheet" href="/global/styles/styles.css">
            <link rel="stylesheet" href="/global/styles/comments-overlay.css">
            <link rel="stylesheet" href="/global/styles/individual.css">
            <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
            <script>window.userTheme = '<?= $_SESSION['user']['tema'] ?? 'sistema' ?>';</script>
            <script src="/global/js/theme.js"></script>
            <meta name="csrf-token" content="<?= csrf_token() ?>">
        </head>
        <body class="d-flex flex-column min-vh-100 individual-page">
        <script>
            window.isLoggedIn = <?php echo Auth::check() ? 'true' : 'false'; ?>;
            window.isIndividualView = true;
            <?php if (Auth::check()): ?>
            window.currentUserId   = <?php echo Auth::id(); ?>;
            window.currentUsername = <?php echo json_encode($_SESSION['user']['Nombre'] ?? $_SESSION['user']['nombre'] ?? ''); ?>;
            window.currentUserFoto = <?php echo json_encode($fotoPerfilUsuario ?? null); ?>;
            window.userConfig      = { notificacionesOn: <?php echo $config['NotificacionOn'] ?? 1; ?> };
            <?php endif ?>
        </script>
        <div class="main-content">
            <div class="container py-3 individual-shell">

                <div class="d-flex justify-content-between align-items-center gap-2 mb-3 cabecera-page px-2">
                    <div class="d-flex gap-2 align-items-center cabecera-left">
                        <div class="flex-shrink-0"><span><i class="bi bi-journal-bookmark-fill texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande cabecera-icono"></i></span></div>
                        <div class="cabecera-text">
                            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                <p class="titulo letraRomana fw-bold texto-rojo m-0 cabecera-titulo">SPEZZIART</p>
                                <span class="subtitulo letraRomana fw-bold text-secondary cabecera-sep">|</span>
                                <h1 class="fw-bold subtitulo letraRomana m-0 cabecera-titulo">Mis Recetas</h1>
                            </div>
                            <p class="texto text-secondary m-0 cabecera-subtitulo">Organiza y gestiona tus recetas</p>
                        </div>
                    </div>
                    <div class="d-flex gap-3 align-items-center flex-shrink-0">
                        <form method="GET" action="/pages/individual" class="position-relative d-none d-md-block receta-search-form js-receta-search-form">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                            <input type="text" name="q"
                                   class="form-control rounded-pill ps-5 js-receta-search-input"
                                   placeholder="Buscar recetas y colecciones..."
                                   value="<?= htmlspecialchars($query) ?>">
                        </form>
                    </div>
                </div>

                <div class="d-flex d-md-none gap-2 mb-4 px-2 individual-search-mobile">
                    <form method="GET" action="/pages/individual" class="position-relative flex-grow-1 js-receta-search-form">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                        <input type="text" name="q"
                               class="form-control rounded-pill ps-5 js-receta-search-input"
                               placeholder="Buscar recetas..."
                               value="<?= htmlspecialchars($query) ?>">
                    </form>
                    <a href="/pages/individual/crear" class="btn btn-danger rounded-circle shadow btn-floating-mobile flex-shrink-0" aria-label="Crear receta">+</a>
                </div>

                <a href="/pages/individual/crear" class="btn btn-danger rounded-circle position-fixed shadow d-none d-md-flex btn-floating" aria-label="Crear receta">
                    +
                </a>

                <section class="collections-panel mb-5">
                    <div class="collections-panel__head">
                        <div class="collections-panel__meta">
                            <div class="collections-panel__title-row">
                                <span class="material-symbols-outlined collections-panel__icon">collections_bookmark</span>
                                <h4 class="fw-bold mb-0">Mis Colecciones</h4>
                                <?php if (!empty($colecciones)): ?>
                                    <span class="collections-panel__count"><?= count($colecciones) ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="collections-panel__desc">Organiza tus recetas favoritas para acceder facilmente.</p>
                        </div>
                        <form method="POST" action="/pages/individual/crear-coleccion" class="collections-panel__form">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <div class="collections-panel__form-row">
                                <input id="nombreColeccionTop" type="text" name="nombreColeccion" class="form-control" placeholder="Nombre de la coleccion..." required maxlength="30" autocomplete="off">
                                <button type="submit" class="btn btn-danger collections-panel__add-btn">
                                    <span class="material-symbols-outlined" aria-hidden="true">add</span>
                                    Crear
                                </button>
                            </div>
                        </form>
                    </div>

                    <?php if (!empty($colecciones)): ?>
                        <button
                            class="btn collections-panel__mobile-toggle d-md-none mb-2"
                            type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collections-list-collapse"
                            aria-expanded="false"
                            aria-controls="collections-list-collapse">
                            <span class="material-symbols-outlined collections-panel__mobile-toggle-icon">expand_more</span>
                            Ver colecciones
                        </button>
                        <div id="collections-list-collapse" class="collapse d-md-block">
                            <div class="collections-list">
                                <?php
                                $chipIcons = ['ramen_dining', 'restaurant', 'bakery_dining', 'lunch_dining', 'local_cafe', 'icecream'];
                                foreach ($colecciones as $index => $col):
                                    $icono = $chipIcons[$index % count($chipIcons)];
                                    $colorIdx = ($index % 6) + 1;
                                ?>
                                    <article class="collection-chip">
                                        <a href="/pages/individual/coleccion?id=<?= $col['ID_Coleccion'] ?>" class="collection-chip__link text-decoration-none">
                                            <span class="collection-chip__icon collection-chip__icon--<?= $colorIdx ?>">
                                                <span class="material-symbols-outlined"><?= $icono ?></span>
                                            </span>
                                            <span class="collection-chip__name"><?= htmlspecialchars($col['Nombre']) ?></span>
                                        </a>
                                        <button
                                            class="collection-chip__remove"
                                            type="button"
                                            aria-label="Eliminar coleccion <?= htmlspecialchars($col['Nombre']) ?>"
                                            data-bs-toggle="modal"
                                            data-bs-target="#confirmDeleteColeccionModal"
                                            data-id="<?= $col['ID_Coleccion'] ?>">
                                            <span class="material-symbols-outlined">close</span>
                                        </button>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="collections-empty">
                            <span class="material-symbols-outlined">folder_open</span>
                            <p class="mb-0">Todavia no tienes colecciones. Crea una para organizar tus recetas.</p>
                        </div>
                    <?php endif; ?>
                </section>

                <div class="mb-2 recipe-section">
                    <h4 class="fw-bold mb-3 recipe-section__title">
                        <span class="material-symbols-outlined">menu_book</span> Recetas
                    </h4>
                    <?php $this->renderLista($misRecetas, true); ?>
                    <?php $this->renderPaginacion($query, $pagination); ?>
                </div>
            </div>
        </div>

        <?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>

        <div class="modal fade" id="confirmDeleteModal" tabindex="-1">
            <div class="modal-dialog modal-sm modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body text-center">
                        <p>Eliminar esta receta?</p>
                        <form method="POST" action="/pages/individual/eliminar" class="d-flex justify-content-center gap-2 mt-3">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="idReceta" id="modalIdReceta">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="confirmDeleteColeccionModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="/pages/individual/eliminar-coleccion">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="idColeccion" id="deleteColeccionId">
                        <div class="modal-body text-center">
                            <p>Eliminar esta coleccion?</p>
                            <div class="d-flex justify-content-center gap-2 mt-3">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
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
                                <div id="cols-list" class="border rounded p-2 individual-save-collections-list">
                                    <div class="text-muted text-center">Cargando...</div>
                                </div>
                            </div>
                            <div class="form-check mb-3">
                            </div>
                            <div class="alert alert-danger d-none" id="saveError"></div>
                            <div class="alert alert-success d-none" id="saveSuccess"></div>
                            <button type="submit" class="btn btn-danger w-100">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reportModalTitle">Reportar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="reportForm">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="id" id="reportId">
                            <input type="hidden" name="type" id="reportType">
                            <div class="mb-3">
                                <label class="form-label">Motivo del reporte</label>
                                <select class="form-select" name="reason" id="reportReason" required><option value="">Selecciona un motivo...</option></select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Detalles adicionales (opcional)</label>
                                <textarea class="form-control" name="details" rows="2" placeholder="Describe el problema con mas detalle..."></textarea>
                            </div>
                            <div class="alert alert-danger d-none" id="reportError"></div>
                            <div class="alert alert-success d-none" id="reportSuccess"></div>
                            <button type="submit" class="btn btn-danger w-100">Enviar reporte</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="comments-overlay d-none" id="comments-overlay" data-bs-theme="light">
            <div class="comments-sheet">
                <div class="drag-handle"></div>
                <div class="comments-header">
                    <span>Comentarios</span>
                    <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" onclick="FeedApp.closeComments()" aria-label="Cerrar"></button>
                </div>
                <div class="comments-body"></div>
                <div class="comments-input modal-footer border-top bg-white px-4 py-3">
                    <form class="d-flex flex-column flex-sm-row gap-2 align-items-stretch align-items-sm-center w-100" onsubmit="FeedApp.sendComment(event)">
                        <div class="input-group rounded-3 overflow-hidden flex-grow-1">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0">
                                <i class="bi bi-chat-dots texto-rojo textoMediano"></i>
                            </span>
                            <input type="text"
                                class="form-control bg-white texto text-secondary border-start-0 rounded-3 rounded-start-0"
                                placeholder="Añade un comentario..."
                                maxlength="200">
                        </div>
                        <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4 flex-shrink-0">Enviar</button>
                    </form>
                </div>
            </div>
        </div>
        <?php require_once __DIR__ . '/../../../global/modalConfirmacionGenerico.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="/../pages/feed/assets/FeedCore.js"></script>
        <script src="/../pages/feed/assets/FeedLikes.js"></script>
        <script src="/../pages/feed/assets/FeedComentarios.js"></script>
        <script src="/../pages/feed/assets/FeedPopOvers.js"></script>
        <script src="/global/js/alertas.js"></script>
        <script src="/pages/individual/assets/IndividualView.js"></script>
        <script src="/global/save.js"></script>
        <script src="/global/report.js"></script>
        <?php require_once __DIR__ . '/../../receta/view/verRecetaModal.php'; ?>
        </body>
        </html>
        <?php
    }

    private function renderLista($recetas, $mostrarFormAgregar = true)
    {
        if (empty($recetas)) {
            echo "<p class='text-muted ps-2'>No hay recetas aqui todavia.</p>";
            return;
        }

        echo '<div class="row g-4">';

        foreach ($recetas as $receta):
            $id = $receta['ID_Receta'];
            $titulo = htmlspecialchars($receta['Titulo']);
            $descripcionBase = preg_split('/\n\nPASOS:\n/', (string)($receta['Descripcion'] ?? ''), 2)[0];
            $descripcionTexto = trim((string)$descripcionBase);
            $descripcion = htmlspecialchars(mb_strimwidth($descripcionTexto, 0, 90, '...'));
            $usuario = htmlspecialchars($receta['Username']);
            $fecha = date('d M', strtotime($receta['FechaCreacion']));
            $imagenes = !empty($receta['Imagen']) ? array_values(array_filter(array_map('trim', explode(',', (string)$receta['Imagen'])))) : [];
            $imagen = $imagenes[0] ?? '';
            $esFit = !empty($receta['EsFit']);
            $fotoPerfil = $receta['FotoPerfil'] ?? null;
            $tiempo = $receta['Tiempo'] ?? null;
            $porciones = $receta['Porciones'] ?? null;
            $etiquetasReceta = is_array($receta['etiquetas'] ?? null) ? $receta['etiquetas'] : [];
        ?>
        <div class="col-12 col-sm-6 col-lg-4 d-flex receta-card-wrap">
            <article class="card border-0 shadow-sm rounded-4 flex-fill efectoEscala receta-card overflow-hidden h-100" data-titulo="<?= strtolower($titulo) ?>">
                <div class="receta-card__media-wrap">
                    <a href="javascript:void(0)" onclick="abrirRecetaModal(<?= $id ?>)" class="text-decoration-none receta-card__media-link">
                        <?php if ($imagen): ?>
                            <img src="<?= htmlspecialchars($imagen) ?>" class="card-img-top rounded-top-4 object-fit-cover receta-card__image" onerror="this.onerror=null; this.src='/uploads/NoImg.jpg';" alt="Imagen de <?= $titulo ?>">
                        <?php else: ?>
                            <div class="card-img-top rounded-top-4 bg-light text-muted d-flex align-items-center justify-content-center receta-card__image receta-card__image--placeholder">
                                <span class="material-symbols-outlined receta-card__placeholder-icon">image</span>
                            </div>
                        <?php endif; ?>
                    </a>

                    <div class="receta-card__overlay">
                        <a href="/pages/individual/crear?id=<?= $id ?>" class="receta-card__icon-btn" aria-label="Editar receta">
                            <span class="material-symbols-outlined">edit</span>
                        </a>
                        <button class="receta-card__icon-btn" type="button" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="<?= $id ?>" aria-label="Borrar receta">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </div>
                </div>

                <div class="card-body px-3 px-xl-4 py-3 d-flex flex-column justify-content-between receta-card__body">
                    <div>
                        <div class="d-flex justify-content-between mb-2 align-items-start gap-2">
                            <div class="d-flex gap-1 flex-wrap align-items-center receta-card__tags-row">
                                <?php
                                $maxVisibles = 2;
                                $etiquetasVisibles = array_slice($etiquetasReceta, 0, $maxVisibles);
                                $etiquetasRestantes = array_slice($etiquetasReceta, $maxVisibles);
                                foreach ($etiquetasVisibles as $etiqueta):
                                ?>
                                    <span class="badge receta-card__badge"><?= strtoupper(htmlspecialchars($etiqueta['Nombre'])) ?></span>
                                <?php endforeach; ?>
                                <?php if (!empty($etiquetasRestantes)): ?>
                                    <span class="badge receta-card__badge">+<?= count($etiquetasRestantes) ?></span>
                                <?php endif; ?>
                                <?php if ($esFit): ?>
                                    <span class="badge rounded-pill bg-success text-white recipe-fit-badge recipe-fit-badge--fit">Fit</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($tiempo || $porciones): ?>
                                <span class="text-secondary textoPequeno receta-card__time">
                                    <?php if ($tiempo): ?>
                                        <span class="receta-card__time-item">
                                            <span class="material-symbols-outlined align-middle">schedule</span>
                                            <?= htmlspecialchars((string)$tiempo) ?> min
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($porciones): ?>
                                        <span class="receta-card__time-item">
                                            <span class="material-symbols-outlined align-middle">groups</span>
                                            <?= htmlspecialchars((string)$porciones) ?> por
                                        </span>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <h5 class="card-title tituloPequeno fw-bold mt-0 mb-1 recipe-card-title"><?= $titulo ?></h5>
                        <p class="card-text text-secondary textoPequeno mb-0 receta-card__description"><?= $descripcion ?></p>
                    </div>

                    <div class="mt-3">
                        <div class="d-flex align-items-center border-top pt-3 receta-card__footer">
                            <span class="rounded-circle bg-danger d-flex align-items-center justify-content-center text-white flex-shrink-0 overflow-hidden receta-card__avatar">
                                <?php if (!empty($fotoPerfil)): ?>
                                    <img src="<?= htmlspecialchars($fotoPerfil) ?>" class="receta-card__avatar-image" alt="Foto de perfil de @<?= $usuario ?>">
                                <?php else: ?>
                                    <span class="material-symbols-outlined text-white">person</span>
                                <?php endif; ?>
                            </span>
                            <div class="text-secondary textoPequeno ms-2 me-auto receta-card__author">
                                <div>Por <span class="fw-semibold">@<?= $usuario ?></span></div>
                                <div class="receta-card__meta-row">
                                    <span><?= $fecha ?></span>
                                </div>
                            </div>
                            <div class="receta-card__footer-actions">
                                <button type="button"
                                        class="receta-card__save-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#saveModal"
                                        data-id="<?= $id ?>"
                                        title="Guardar en coleccion"
                                        aria-label="Guardar receta en coleccion">
                                    <span class="material-symbols-outlined">bookmark_add</span>
                                </button>
                                <a href="javascript:void(0)" onclick="abrirRecetaModal(<?= $id ?>)" class="text-decoration-none textoPequeno receta-card__details-link">
                                    <span class="material-symbols-outlined align-middle">visibility</span>
                                    Ver
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </div>
        <?php endforeach;

        echo '</div>';
    }

    private function renderPaginacion(string $query, array $pagination): void
    {
        $totalPages = (int)($pagination['totalPages'] ?? 1);
        $currentPage = (int)($pagination['currentPage'] ?? 1);

        if ($totalPages <= 1) {
            // Sin paginación: emitir un espaciador para que el navbar fijo
            // no tape las últimas tarjetas de recetas.
            echo '<div class="recipe-no-pagination-spacer"></div>';
            return;
        }

        $startPage = max(1, $currentPage - 2);
        $endPage = min($totalPages, $startPage + 4);
        $startPage = max(1, $endPage - 4);
        ?>
        <nav class="mt-4 recipe-pagination" aria-label="Paginacion de recetas">
            <ul class="pagination justify-content-center flex-wrap gap-2">
                <?php if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link rounded-pill px-3" href="<?= htmlspecialchars($this->buildPaginationUrl($query, $currentPage - 1)) ?>">Anterior</a>
                    </li>
                <?php endif; ?>

                <?php for ($page = $startPage; $page <= $endPage; $page++): ?>
                    <li class="page-item<?= $page === $currentPage ? ' active' : '' ?>">
                        <a
                            class="page-link rounded-pill px-3<?= $page === $currentPage ? ' bg-danger border-danger' : '' ?>"
                            href="<?= htmlspecialchars($this->buildPaginationUrl($query, $page)) ?>">
                            <?= $page ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link rounded-pill px-3" href="<?= htmlspecialchars($this->buildPaginationUrl($query, $currentPage + 1)) ?>">Siguiente</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php
    }

    private function buildPaginationUrl(string $query, int $page): string
    {
        $params = [];
        if ($query !== '') {
            $params['q'] = $query;
        }
        $params['page'] = $page;

        return '/pages/individual' . (!empty($params) ? '?' . http_build_query($params) : '');
    }
}
