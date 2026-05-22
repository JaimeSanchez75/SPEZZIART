<?php
require_once __DIR__ . '/../../../core/auth.php';

class ColeccionView
{
    public function render($coleccion, $recetas, $config = null, $pagination = [])
    {
        if (!$coleccion) {
            die('Error: coleccion no valida');
        }

        if (!is_array($recetas)) {
            $recetas = [];
        }
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title>Spezziart | <?= htmlspecialchars($coleccion['Nombre'] ?? 'Coleccion') ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
            <link rel="stylesheet" href="/global/styles/global.css">
            <link rel="stylesheet" href="/global/styles/individual.css">
            <link rel="stylesheet" href="/global/styles/comments-overlay.css">
            <script>window.userTheme = '<?= $_SESSION['user']['tema'] ?? 'sistema' ?>';</script>
            <script src="/global/js/theme.js"></script>
            <meta name="csrf-token" content="<?= csrf_token() ?>">
            <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
        </head>
        <body class="coleccion-page">
            <script>
                window.isLoggedIn = <?php echo Auth::check() ? 'true' : 'false'; ?>;
            </script>
            <main class="container py-4 py-lg-5">
                <div class="row justify-content-center">
                    <div class="col-12 col-xl-10">

                        <div class="coleccion-hero mb-4">
                            <div class="coleccion-hero__inner">
                                <div class="coleccion-hero__left">
                                    <div class="coleccion-hero__icon-wrap">
                                        <span class="material-symbols-outlined">collections_bookmark</span>
                                    </div>
                                    <div>
                                        <div class="coleccion-hero__meta">
                                            <span class="coleccion-hero-badge">Coleccion</span>
                                            <span class="coleccion-hero-counter"><?= count($recetas) ?> receta<?= count($recetas) === 1 ? '' : 's' ?></span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2 coleccion-hero__title-wrap">
                                            <h1 class="coleccion-hero-title mb-0"><?= htmlspecialchars($coleccion['Nombre'] ?? '') ?></h1>
                                            <button type="button"
                                                    class="btn btn-link p-0 coleccion-hero-edit-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#renameColeccionModal"
                                                    aria-label="Editar nombre de la coleccion"
                                                    title="Editar nombre">
                                                <span class="material-symbols-outlined">edit</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <a href="/pages/individual" class="btn btn-outline-secondary rounded-pill coleccion-hero__back-btn">
                                    <span class="material-symbols-outlined coleccion-hero__back-icon">arrow_back</span>
                                    Volver
                                </a>
                            </div>
                        </div>

                        <div class="card border-0 shadow-sm rounded-4 coleccion-list-card">
                            <div class="card-body coleccion-shell-card-body">
                                <?php if (!empty($recetas)): ?>
                                    <div class="coleccion-list-header">
                                        <div>
                                            <h2 class="coleccion-list-heading">Recetas guardadas</h2>
                                            <p class="text-body-secondary coleccion-list-copy">Explora o elimina elementos de esta seleccion.</p>
                                        </div>
                                        <span class="coleccion-list-pill">
                                            <span class="material-symbols-outlined coleccion-list-pill__icon">menu_book</span>
                                            <?= (int)($pagination['totalItems'] ?? count($recetas)) ?> elemento<?= (int)($pagination['totalItems'] ?? count($recetas)) === 1 ? '' : 's' ?>
                                        </span>
                                    </div>
                                <?php endif; ?>
                                <?php $this->renderLista($recetas, (int)$coleccion['ID_Coleccion']); ?>
                            </div>
                        </div>
                        <?php $this->renderPaginacion((int)$coleccion['ID_Coleccion'], $pagination); ?>

                    </div>
                </div>
            </main>

            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-body text-center p-4">
                            <p class="mb-3">Quitar esta receta de la coleccion?</p>
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <form id="deleteForm" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="idReceta" id="modalIdReceta">
                                    <input type="hidden" name="idColeccion" id="modalIdColeccion">
                                    <button type="submit" class="btn btn-danger btn-sm">Quitar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal fade" id="renameColeccionModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="/pages/individual/renombrar-coleccion">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="idColeccion" value="<?= (int)$coleccion['ID_Coleccion'] ?>">
                            <div class="modal-header">
                                <h5 class="modal-title">Renombrar coleccion</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <label for="renameColeccionInput" class="form-label">Nuevo nombre</label>
                                <input type="text" id="renameColeccionInput" name="nuevoNombre" class="form-control" maxlength="30" required autocomplete="off" value="<?= htmlspecialchars($coleccion['Nombre'] ?? '') ?>">
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-danger btn-sm">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal guardar en coleccion -->
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
                                <div class="alert alert-danger d-none" id="saveError"></div>
                                <div class="alert alert-success d-none" id="saveSuccess"></div>
                                <button type="submit" class="btn btn-danger w-100">Guardar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal reportar -->
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
                                    <select class="form-select" name="reason" id="reportReason" required>
                                        <option value="">Selecciona un motivo...</option>
                                    </select>
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

            <!-- Overlay de comentarios -->
            <div class="comments-overlay d-none" id="comments-overlay">
                <div class="comments-sheet">
                    <div class="drag-handle"></div>
                    <div class="comments-header">
                        <span>Comentarios</span>
                        <span class="close-btn js-close-comments">×</span>
                    </div>
                    <div class="comments-body"></div>
                    <div class="comments-input">
                        <form class="comments-input__form" method="post">
                            <span class="comments-input__icon">
                                <span class="material-symbols-outlined">chat_bubble</span>
                            </span>
                            <input type="text"
                                   class="comments-input__field"
                                   placeholder="Añade un comentario..."
                                   maxlength="200">
                            <span class="comments-input__counter">0/200</span>
                            <button type="submit" class="comments-input__btn">Enviar</button>
                        </form>
                    </div>
                </div>
            </div>

            <?php require_once __DIR__ . '/../../receta/view/verRecetaModal.php'; ?>
            <?php require_once __DIR__ . '/../../../global/modalConfirmacionGenerico.php'; ?>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <script src="/../pages/feed/assets/FeedCore.js"></script>
            <script src="/../pages/feed/assets/FeedLikes.js"></script>
            <script src="/../pages/feed/assets/FeedComentarios.js"></script>
            <script src="/../pages/feed/assets/FeedPopOvers.js"></script>
            <script src="/global/save.js"></script>
            <script src="/global/report.js"></script>
            <script src="/pages/individual/assets/ColeccionView.js"></script>
            <?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
        </body>
        </html>
        <?php
    }

    private function renderPaginacion(int $idColeccion, array $pagination): void
    {
        $totalPages  = (int)($pagination['totalPages'] ?? 1);
        $currentPage = (int)($pagination['currentPage'] ?? 1);

        if ($totalPages <= 1) {
            echo '<div class="coleccion-pagination-spacer"></div>';
            return;
        }

        $startPage = max(1, $currentPage - 2);
        $endPage   = min($totalPages, $startPage + 4);
        $startPage = max(1, $endPage - 4);
        ?>
        <nav class="mt-4 mb-2 recipe-pagination" aria-label="Paginacion de recetas">
            <ul class="pagination justify-content-center flex-wrap gap-2">
                <?php if ($currentPage > 1): ?>
                    <li class="page-item">
                        <a class="page-link rounded-pill px-3"
                           href="/pages/individual/coleccion?id=<?= $idColeccion ?>&page=<?= $currentPage - 1 ?>">Anterior</a>
                    </li>
                <?php endif; ?>

                <?php for ($p = $startPage; $p <= $endPage; $p++): ?>
                    <li class="page-item<?= $p === $currentPage ? ' active' : '' ?>">
                        <a class="page-link rounded-pill px-3<?= $p === $currentPage ? ' bg-danger border-danger' : '' ?>"
                           href="/pages/individual/coleccion?id=<?= $idColeccion ?>&page=<?= $p ?>">
                            <?= $p ?>
                        </a>
                    </li>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link rounded-pill px-3"
                           href="/pages/individual/coleccion?id=<?= $idColeccion ?>&page=<?= $currentPage + 1 ?>">Siguiente</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="coleccion-pagination-spacer"></div>
        <?php
    }

    private function renderLista($recetas, $idColeccion)
    {
        if (empty($recetas)) {
            echo "<div class='coleccion-empty-state text-center py-5 px-3'>
                    <span class='material-symbols-outlined coleccion-empty-icon d-block mb-3'>bookmark_border</span>
                    <p class='fw-semibold mb-1'>Esta coleccion esta vacia</p>
                    <p class='text-body-secondary small mb-0'>Guarda recetas desde el feed o desde tus propias recetas.</p>
                  </div>";
            return;
        }
        ?>
        <div class="coleccion-grid">
        <?php foreach ($recetas as $receta):
            $idReceta   = (int)$receta['ID_Receta'];
            $titulo     = htmlspecialchars($receta['Titulo'] ?? '');
            $descripcionBase = preg_split('/\n\nPASOS:\n/', (string)($receta['Descripcion'] ?? ''), 2)[0];
            $descripcion = htmlspecialchars(mb_strimwidth(trim($descripcionBase), 0, 110, '…'));
            $usuario    = htmlspecialchars($receta['Username'] ?? 'Usuario');
            $fecha      = !empty($receta['FechaCreacion']) ? date('d M Y', strtotime($receta['FechaCreacion'])) : '';
            $imagenes   = !empty($receta['Imagen']) ? array_values(array_filter(array_map('trim', explode(',', (string)$receta['Imagen'])))) : [];
            $imagen     = $imagenes[0] ?? '';
            $esFit      = !empty($receta['EsFit']);
            $tiempo     = $receta['Tiempo'] ?? null;
            $porciones  = $receta['Porciones'] ?? null;
            $etiquetas  = is_array($receta['etiquetas'] ?? null) ? $receta['etiquetas'] : [];
            $fotoPerfil = $receta['FotoPerfil'] ?? null;
        ?>
        <article class="coleccion-receta-card card border-0 rounded-4 overflow-hidden shadow-sm">

            <a href="javascript:void(0)" onclick="abrirRecetaModal(<?= $idReceta ?>)" class="coleccion-receta-media d-block position-relative text-decoration-none" tabindex="-1" aria-hidden="true">
                <?php if ($imagen !== ''): ?>
                    <img src="<?= htmlspecialchars($imagen) ?>"
                         alt="<?= $titulo ?>"
                         class="w-100 h-100 object-fit-cover"
                         onerror="this.onerror=null;this.src='/uploads/NoImg.jpg'">
                <?php else: ?>
                    <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-body-secondary text-body-tertiary coleccion-receta-media-placeholder">
                        <span class="material-symbols-outlined">image</span>
                    </div>
                <?php endif; ?>

                <div class="coleccion-receta-overlay"></div>

                <div class="coleccion-receta-media-badges">
                    <?php if ($esFit): ?>
                        <span class="badge rounded-pill text-bg-success coleccion-fit-badge">
                            <span class="material-symbols-outlined">fitness_center</span>Fit
                        </span>
                    <?php endif; ?>
                    <?php if ($tiempo): ?>
                        <span class="badge rounded-pill coleccion-time-badge">
                            <span class="material-symbols-outlined">schedule</span><?= (int)$tiempo ?> min
                        </span>
                    <?php endif; ?>
                </div>
            </a>

            <button type="button"
                    class="coleccion-receta-remove"
                    title="Quitar de coleccion"
                    data-bs-toggle="modal"
                    data-bs-target="#confirmDeleteModal"
                    data-idreceta="<?= $idReceta ?>"
                    data-idcoleccion="<?= (int)$idColeccion ?>"
                    aria-label="Quitar <?= $titulo ?> de la coleccion">
                <span class="material-symbols-outlined">close</span>
            </button>

            <div class="card-body coleccion-receta-body">
                <?php if (!empty($etiquetas)): ?>
                    <div class="coleccion-receta-tags">
                        <?php foreach (array_slice($etiquetas, 0, 2) as $et): ?>
                            <span class="coleccion-tag"><?= strtoupper(htmlspecialchars($et['Nombre'])) ?></span>
                        <?php endforeach; ?>
                        <?php if (count($etiquetas) > 2): ?>
                            <span class="coleccion-tag">+<?= count($etiquetas) - 2 ?></span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <h3 class="coleccion-receta-title">
                    <a href="javascript:void(0)" onclick="abrirRecetaModal(<?= $idReceta ?>)" class="text-decoration-none coleccion-receta-title-link">
                        <?= $titulo ?>
                    </a>
                </h3>

                <?php if ($descripcion): ?>
                    <p class="coleccion-receta-desc"><?= $descripcion ?></p>
                <?php endif; ?>

                <div class="coleccion-receta-footer">
                    <div class="coleccion-receta-author">
                        <span class="coleccion-receta-author-avatar">
                            <?php if (!empty($fotoPerfil)): ?>
                                <img src="<?= htmlspecialchars($fotoPerfil) ?>"
                                     class="coleccion-receta-author-avatar-img"
                                     alt="Foto de @<?= $usuario ?>"
                                     onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
                                <span class="coleccion-receta-author-avatar-fallback d-none"><?= mb_strtoupper(mb_substr($usuario, 0, 1)) ?></span>
                            <?php else: ?>
                                <?= mb_strtoupper(mb_substr($usuario, 0, 1)) ?>
                            <?php endif; ?>
                        </span>
                        <span class="coleccion-receta-author-name">@<?= $usuario ?></span>
                    </div>
                    <?php if ($fecha): ?>
                        <span class="coleccion-receta-date"><?= $fecha ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
        </div>
        <?php
    }
}
