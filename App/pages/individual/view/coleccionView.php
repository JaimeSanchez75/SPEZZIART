<?php
require_once __DIR__ . '/../../../core/auth.php';

class ColeccionView 
{

    public function render($coleccion, $recetas, $config = null) 
    {

        if (!$coleccion) {die("Error: colección no válida");}
        if (!is_array($recetas)) {$recetas = [];}
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Spezziart | <?= htmlspecialchars($coleccion['Nombre'] ?? 'Colección') ?></title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
            <link rel="stylesheet" href="/App/global/styles/global.css">
            <link rel="stylesheet" href="/App/global/styles/feed.css">
            <link rel="stylesheet" href="/App/global/styles/colecciones.css">
        </head>

        <body data-bs-theme="<?= ($config && ($config['ModoOscuro'] ?? false)) ? 'dark' : 'light'; ?>">
            <div class="container mt-4">
                <!-- HEADER -->
                <div class="header-grid mb-4">
                    <div class="header-item item-logo">
                        <h3 class="fw-bold m-0">
                            <span class="text-danger">SPEZZIART</span>
                            <span class="ms-2 text-dark">Colección: <?= htmlspecialchars($coleccion['Nombre'] ?? '') ?></span>
                        </h3>
                    </div>
                    <div class="header-item item-search"><a href="/App/pages/individual" class="btn btn-secondary">← Volver</a></div>
                </div>
                <!-- RECETAS -->
                <h4 class="fw-bold mb-3">Recetas en esta colección</h4>
                <div><?php $this->renderLista($recetas, $coleccion['ID_Coleccion']); ?></div>
            </div>
            <?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
            <!-- MODAL CONFIRMACIÓN -->
            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center">
                            <p>¿Quitar esta receta de la colección?</p>
                            <div class="d-flex justify-content-center gap-2 mt-3">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <form id="deleteForm" method="POST">
                                    <input type="hidden" name="idReceta" id="modalIdReceta">
                                    <input type="hidden" name="idColeccion" id="modalIdColeccion">
                                    <button type="submit" class="btn btn-danger btn-sm">Quitar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                // Asignar datos al modal cuando se abre
                const confirmModal = document.getElementById('confirmDeleteModal');
                confirmModal.addEventListener('show.bs.modal', event => 
                {
                    const button = event.relatedTarget;
                    const idReceta = button.getAttribute('data-idreceta');
                    const idColeccion = button.getAttribute('data-idcoleccion');
                    document.getElementById('modalIdReceta').value = idReceta;
                    document.getElementById('modalIdColeccion').value = idColeccion;
                    document.getElementById('deleteForm').action = "/App/pages/individual/coleccion/eliminar-receta";
                });
            </script>
        </body>
        </html>
        <?php
    }
    private function renderLista($recetas, $idColeccion) 
    {

        if (empty($recetas)) {echo "<p class='text-muted ps-2'>No hay recetas en esta colección todavía.</p>"; return;}
        foreach ($recetas as $receta):
            $idReceta = $receta['ID_Receta'];
            $titulo = htmlspecialchars($receta['Titulo'] ?? '');
            $descripcionBase = preg_split('/\n\nPASOS:\n/', (string)($receta['Descripcion'] ?? ''), 2)[0];
            $descripcion = htmlspecialchars(substr($descripcionBase, 0, 150));
            $usuario = htmlspecialchars($receta['Username'] ?? 'Usuario');
            $fecha = !empty($receta['FechaCreacion']) ? date('d M', strtotime($receta['FechaCreacion'])) : '';
            $imagen = $receta['Imagen'] ?? '';
            ?>
            <div id="receta-<?= $idReceta ?>"class="card feed-card mb-4 p-3 border-0 shadow-sm rounded-4 receta-card"data-titulo="<?= strtolower($titulo) ?>">
                <!-- HEADER -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="text-danger fw-bold">👤 @<?= $usuario ?></div>
                    <div class="text-muted small"><?= $fecha ?></div>
                </div>
                <div class="row g-3">
                    <!-- IMAGEN -->
                    <div class="col-md-4">
                        <a href="/App/pages/individual/ver?id=<?= $idReceta ?>">
                            <?php if(!empty($imagen)): ?>
                                <img src="/App/uploads/<?= htmlspecialchars($imagen) ?>"class="feed-img rounded-3"style="cursor:pointer;">
                            <?php else: ?>
                                <div class="feed-img bg-light d-flex align-items-center justify-content-center text-muted border rounded-3">Sin imagen</div>
                            <?php endif; ?>
                        </a>
                    </div>
                    <!-- INFO -->
                    <div class="col-md-8 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold"><?= $titulo ?></h5>
                            <p class="text-muted small"><?= $descripcion ?>...</p>
                        </div>
                        <!-- ACCIONES -->
                        <div class="d-flex justify-content-end gap-3 align-items-center">
                            <!-- EDITAR -->
                            <a href="/App/pages/individual/crear?id=<?= $idReceta ?>"><span class="material-symbols-outlined">edit</span></a>
                            <!-- ELIMINAR DE COLECCIÓN -->
                            <button type="button" class="btn-icon" data-bs-toggle="modal"data-bs-target="#confirmDeleteModal"data-idreceta="<?= $idReceta ?>"data-idcoleccion="<?= $idColeccion ?>">span class="material-symbols-outlined">delete</span></button>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;
    }
}
