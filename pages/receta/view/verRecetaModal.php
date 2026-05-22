<?php
$recetaCssPath = __DIR__ . '/../style/receta.css';
$recetaJsPath = __DIR__ . '/../assets/receta.js';
$recetaCssVersion = file_exists($recetaCssPath) ? filemtime($recetaCssPath) : time();
$recetaJsVersion = file_exists($recetaJsPath) ? filemtime($recetaJsPath) : time();
?>
<?php if (Auth::check()): ?>
    <script>
        window.currentUserId = <?php echo Auth::id(); ?>;
        window.currentUsername = <?php echo json_encode($_SESSION['user']['Nombre'] ?? $_SESSION['user']['nombre'] ?? ''); ?>;
        window.currentUserFoto = <?php echo json_encode($fotoPerfilUsuario ?? null); ?>;
        window.userConfig = {notificacionesOn: <?php echo $config['NotificacionOn'] ?? 1; ?>};
    </script>
<?php endif ?>
<link rel="stylesheet" href="/pages/receta/style/receta.css?v=<?= $recetaCssVersion ?>">
<!-- Modal de receta -->
<div class="modal fade" id="recipeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable recipe-modal-dialog">
        <div class="modal-content border-0 rounded-4 overflow-hidden shadow recipe-modal-content">
            <div class="modal-header border-0 recipe-modal-header px-4 py-3">
                <p class="mb-0 fw-semibold small text-body-secondary recipe-modal-header__label">Ver receta</p>
                <button type="button"
                        class="btn-close recipe-modal-close"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar">
                </button>
            </div>
            <div class="modal-body p-0">
                <div id="recipeModalContent">
                    <div class="text-center py-5">
                        <div class="spinner-border text-danger" role="status"></div>
                        <p class="mt-2 text-muted">Cargando receta...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="/pages/receta/assets/receta.js?v=<?= $recetaJsVersion ?>"></script>
