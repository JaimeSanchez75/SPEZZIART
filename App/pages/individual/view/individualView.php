<?php
require_once __DIR__ . '/../../../core/auth.php';

class IndividualView {

    public function render($misRecetas, $guardadas, $etiquetas, $config = null, $colecciones = [], $query = '') {
        $GLOBALS['colecciones'] = $colecciones;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Spezziart | Mis Recetas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="/App/global/styles/global.css">
    <link rel="stylesheet" href="/App/global/styles/feed.css">
</head>

<body data-bs-theme="<?= ($config && $config['ModoOscuro']) ? 'dark' : 'light'; ?>">

<div class="container mt-4">

    <!-- HEADER -->
    <div class="header-grid mb-4">
        <div class="header-item item-logo">
            <h3 class="fw-bold m-0">
                <span class="text-danger">SPEZZIART</span>
                <span class="ms-2 text-dark">| Mis Recetas</span>
            </h3>
        </div>

        <!-- BUSCADOR -->
        <div class="header-item item-search">
            <input type="text" id="busquedaRecetas"
                   class="form-control rounded-pill shadow-sm"
                   placeholder="Buscar recetas..."
                   value="<?= htmlspecialchars($query) ?>">
        </div>
    </div>

    <!-- BOTÓN CREAR -->
    <a href="/App/pages/individual/crear"
       class="btn btn-danger rounded-circle position-fixed bottom-0 end-0 m-4 d-flex justify-content-center align-items-center"
       style="width:60px;height:60px;font-size:30px;">+</a>

    <!-- RECETAS -->
    <h4 class="fw-bold mb-3">Recetas</h4>
    <div>
        <?php $this->renderLista($misRecetas); ?>
    </div>

    <!-- GUARDADAS -->
    <h4 class="fw-bold mt-5">Guardadas</h4>
    <div>
        <?php $this->renderLista($guardadas); ?>
    </div>

    <!-- COLECCIONES -->
    <div class="d-flex align-items-center justify-content-between mt-5 mb-3">
        <h4 class="fw-bold m-0">Mis Colecciones</h4>
        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#crearColeccionModal">
            <span class="material-symbols-outlined">add</span>
        </button>
    </div>

    <?php if(!empty($colecciones)): ?>
        <div class="list-group mb-4">
            <?php foreach($colecciones as $col): ?>
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <a href="/App/pages/individual/coleccion?id=<?= $col['ID_Coleccion'] ?>"
                       class="list-group-item list-group-item-action flex-grow-1">
                       <?= htmlspecialchars($col['Nombre']) ?>
                    </a>
                    <button class="btn btn-link text-danger p-0 ms-2"
                            data-bs-toggle="modal"
                            data-bs-target="#confirmDeleteColeccionModal"
                            data-id="<?= $col['ID_Coleccion'] ?>">
                        <span class="material-symbols-outlined">delete</span>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">No tienes colecciones aún.</p>
    <?php endif; ?>

</div>

<?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>

<!-- MODAL CREAR COLECCIÓN -->
<div class="modal fade" id="crearColeccionModal" tabindex="-1" aria-labelledby="crearColeccionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="/App/pages/individual/crear-coleccion">
        <div class="modal-header">
          <h5 class="modal-title" id="crearColeccionModalLabel">Crear nueva colección</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <input type="text" name="nombreColeccion" class="form-control" placeholder="Nombre de la colección" required>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Crear</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- MODAL BORRAR COLECCIÓN -->
<div class="modal fade" id="confirmDeleteColeccionModal" tabindex="-1" aria-labelledby="confirmDeleteColeccionModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" action="/App/pages/individual/eliminar-coleccion">
        <input type="hidden" name="idColeccion" id="deleteColeccionId" value="">
        <div class="modal-header">
          <h5 class="modal-title" id="confirmDeleteColeccionModalLabel">Confirmar eliminación</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          ¿Estás seguro de que deseas eliminar esta colección? Se eliminarán todas las relaciones con recetas.
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Eliminar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('busquedaRecetas');
    const recetas = document.querySelectorAll('.receta-card');
    const colecciones = document.querySelectorAll('.list-group-item');

    input.addEventListener('input', () => {
        const texto = input.value.toLowerCase();
        recetas.forEach(card => card.style.display = card.dataset.titulo.startsWith(texto) ? '' : 'none');
        colecciones.forEach(col => col.style.display = col.textContent.toLowerCase().startsWith(texto) ? '' : 'none');
    });

    // BORRAR COLECCIÓN
    const deleteColeccionModal = document.getElementById('confirmDeleteColeccionModal');
    if(deleteColeccionModal) {
        deleteColeccionModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            document.getElementById('deleteColeccionId').value = id;
        });
    }
});
</script>

</body>
</html>
<?php
    }

    private function renderLista($recetas) {
        if (empty($recetas)) {
            echo "<p class='text-muted ps-2'>No hay recetas aquí todavía.</p>";
            return;
        }

        foreach ($recetas as $receta): ?>
            <div class="card feed-card mb-4 p-3 border-0 shadow-sm rounded-4 receta-card"
                 data-titulo="<?= strtolower($receta['Titulo']) ?>">

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="text-danger fw-bold">👤 @<?= htmlspecialchars($receta['Username']); ?></div>
                    <div class="text-muted small"><?= date('d M', strtotime($receta['FechaCreacion'])); ?></div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="/App/pages/individual/ver?id=<?= $receta['ID_Receta'] ?>">
                            <?php if(!empty($receta['Imagen'])): ?>
                                <img src="/App/uploads/<?= htmlspecialchars($receta['Imagen']); ?>"
                                     class="feed-img rounded-3" style="cursor:pointer;">
                            <?php else: ?>
                                <div class="feed-img bg-light d-flex align-items-center justify-content-center text-muted border rounded-3" style="cursor:pointer;">
                                    Sin imagen
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>

                    <div class="col-md-8 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold"><?= htmlspecialchars($receta['Titulo']); ?></h5>
                            <p class="text-muted small"><?= htmlspecialchars(substr($receta['Descripcion'], 0, 150)) ?>...</p>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex justify-content-end gap-3">
                                <a href="/App/pages/individual/crear?id=<?= $receta['ID_Receta'] ?>">
                                    <span class="material-symbols-outlined">edit</span>
                                </a>
                                <button class="btn btn-link p-0 text-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="<?= $receta['ID_Receta'] ?>">
                                    <span class="material-symbols-outlined">delete</span>
                                </button>
                            </div>

                            <?php if(!empty($GLOBALS['colecciones'])): ?>
                                <form method="POST" action="/App/pages/individual/coleccion/agregar" class="d-flex gap-2 mt-2">
                                    <input type="hidden" name="idReceta" value="<?= $receta['ID_Receta'] ?>">
                                    <select name="idColeccion" class="form-select form-select-sm" required>
                                        <option value="">Agregar a colección...</option>
                                        <?php foreach($GLOBALS['colecciones'] as $col): ?>
                                            <option value="<?= $col['ID_Coleccion'] ?>"><?= htmlspecialchars($col['Nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-danger">Agregar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;
    }
}