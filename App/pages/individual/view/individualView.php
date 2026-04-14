<?php
require_once __DIR__ . '/../../../core/auth.php';
require_once __DIR__ . '/../../../core/csrfcheck.php'; 

class IndividualView 
{
    public function render($misRecetas, $guardadas, $etiquetas, $config = null, $colecciones = [], $query = '') 
    {   $GLOBALS['colecciones'] = $colecciones; // Para usar en el método privado
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
                        <h3 class="fw-bold d-flex align-items-center m-0">
                            <span class="text-danger" style="letter-spacing: 0.3em;">SPEZZIART</span>
                            <span class="ms-2 d-none d-sm-inline text-dark social">| Mis Recetas</span>
                        </h3>
                    </div>
                    <div class="header-item item-search">
                        <form method="GET" action="/App/pages/individual" class="position-relative">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                            <input type="text" name="q" id="busquedaRecetas" class="form-control rounded-pill ps-5 border-0 shadow-sm" placeholder="Buscar recetas, listas o ingredientes..." value="<?= htmlspecialchars($query) ?>">
                        </form>
                    </div>
                </div>
                <!-- BOTÓN CREAR RECETA -->
                <a href="/App/pages/individual/crear" class="btn btn-danger rounded-circle position-fixed bottom-0 end-0 m-4 d-flex justify-content-center align-items-center" style="width:60px; height:60px; font-size:30px;">+</a>
                <!-- RECETAS -->
                <h4 class="fw-bold mb-3"><span class="material-symbols-outlined align-middle">menu_book</span> Recetas</h4>
                <div id="feed-container"><?php $this->renderLista($misRecetas, true); ?></div>
                <!-- GUARDADAS (aún sin implementar) -->
                <h4 class="fw-bold mb-3 mt-5"><span class="material-symbols-outlined align-middle">bookmark</span> Guardadas</h4>
                <div id="feed-container"><?php $this->renderLista($guardadas, false); ?></div>
                <!-- COLECCIONES -->
                <h4 class="fw-bold mt-5"><span class="material-symbols-outlined align-middle">collections_bookmark</span> Mis Colecciones</h4>
                <?php if(!empty($colecciones)): ?>
                    <ul class="list-group mb-3">
                        <?php foreach($colecciones as $col): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div><a href="/App/pages/individual/coleccion?id=<?= $col['ID_Coleccion'] ?>" class="text-decoration-none"><?= htmlspecialchars($col['Nombre']) ?></a></div>
                                <div class="d-flex gap-2">
                                    <!-- Botón eliminar colección -->
                                    <button class="btn btn-sm btn-link text-danger p-0" data-bs-toggle="modal" data-bs-target="#confirmDeleteColeccionModal" data-id="<?= $col['ID_Coleccion'] ?>"><span class="material-symbols-outlined">delete</span></button>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No tienes colecciones aún.</p>
                <?php endif; ?>
                <!-- FORMULARIO CREAR COLECCIÓN -->
                <form method="POST" action="/App/pages/individual/crear-coleccion" class="d-flex gap-2 mb-4">
                    <input type="text" name="nombreColeccion" class="form-control form-control-sm" placeholder="Nombre de la colección" required>
                    <button type="submit" class="btn btn-sm btn-danger">Crear colección</button>
                </form>
            </div>
            <?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
            <!-- MODALES -->
            <!-- Modal confirmar eliminar receta -->
            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-sm modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center">
                            <p>¿Eliminar esta receta?</p>
                            <div class="d-flex justify-content-center gap-2 mt-3">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                <form id="deleteRecetaForm" method="POST" action="/App/pages/individual/eliminar">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="idReceta" id="modalIdReceta">
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal confirmar eliminar colección -->
            <div class="modal fade" id="confirmDeleteColeccionModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <form method="POST" action="/App/pages/individual/eliminar-coleccion">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="idColeccion" id="deleteColeccionId">
                            <div class="modal-body text-center">
                                <p>¿Eliminar esta colección? Se perderán todas las relaciones con recetas.</p>
                                <div class="d-flex justify-content-center gap-2 mt-3">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() 
                {
                    // Buscador
                    const input = document.getElementById('busquedaRecetas');
                    const recetas = document.querySelectorAll('.receta-card');
                    if (input) 
                    {
                        input.addEventListener('input', function() 
                        {
                            const texto = input.value.toLowerCase();
                            recetas.forEach(card => 
                            {
                                const titulo = card.getAttribute('data-titulo');
                                if (titulo && titulo.includes(texto)) {card.style.display = '';} 
                                else {card.style.display = 'none';}
                            });
                        });
                    }
                    // Modal eliminar receta
                    const deleteModal = document.getElementById('confirmDeleteModal');
                    if (deleteModal) 
                    {
                        deleteModal.addEventListener('show.bs.modal', function(event) 
                        {
                            const button = event.relatedTarget;
                            const idReceta = button.getAttribute('data-id');
                            document.getElementById('modalIdReceta').value = idReceta;
                        });
                    }
                    // Modal eliminar colección
                    const deleteColeccionModal = document.getElementById('confirmDeleteColeccionModal');
                    if (deleteColeccionModal) 
                    {
                        deleteColeccionModal.addEventListener('show.bs.modal', function(event) 
                        {
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

    private function renderLista($recetas, $mostrarFormAgregar = true) 
    {
        if (empty($recetas)) {echo "<p class='text-muted ps-2'>No hay recetas aquí todavía.</p>"; return;}
        foreach ($recetas as $receta):
            $id = $receta['ID_Receta'];
            $titulo = htmlspecialchars($receta['Titulo']);
            $descripcion = htmlspecialchars(substr($receta['Descripcion'] ?? '', 0, 150));
            $usuario = htmlspecialchars($receta['Username']);
            $fecha = date('d M', strtotime($receta['FechaCreacion']));
            $imagen = $receta['Imagen'] ?? '';
            ?>
            <div class="card feed-card mb-4 p-3 border-0 shadow-sm rounded-4 receta-card" data-titulo="<?= strtolower($titulo) ?>">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div class="username text-danger fw-bold">👤 @<?= $usuario ?></div>
                    <div class="text-muted small"><?= $fecha ?></div>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <a href="/App/pages/individual/ver?id=<?= $id ?>">
                            <?php if($imagen): ?>
                                <img src="/App/uploads/<?= htmlspecialchars($imagen) ?>" class="feed-img rounded-3" style="cursor:pointer;">
                            <?php else: ?>
                                <div class="feed-img bg-light d-flex align-items-center justify-content-center text-muted border rounded-3" style="cursor:pointer;">Sin imagen</div>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="col-md-8 d-flex flex-column justify-content-between">
                        <div>
                            <h5 class="fw-bold mb-1"><?= $titulo ?></h5>
                            <p class="text-muted small"><?= $descripcion ?>...</p>
                        </div>
                        <div class="d-flex justify-content-end gap-3 align-items-center">
                            <a href="/App/pages/individual/crear?id=<?= $id ?>" class="text-decoration-none">
                                <span class="material-symbols-outlined">edit</span>
                            </a>
                            <button class="btn btn-link p-0 text-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-id="<?= $id ?>">
                                <span class="material-symbols-outlined">delete</span>
                            </button>
                        </div>
                        <?php if($mostrarFormAgregar && !empty($GLOBALS['colecciones'])): ?>
                            <div class="mt-2">
                                <form method="POST" action="/App/pages/individual/coleccion/agregar" class="d-flex gap-2">
                                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                    <input type="hidden" name="idReceta" value="<?= $id ?>">
                                    <select name="idColeccion" class="form-select form-select-sm" required>
                                        <option value="">Agregar a colección...</option>
                                        <?php foreach($GLOBALS['colecciones'] as $col): ?><option value="<?= $col['ID_Coleccion'] ?>"><?= htmlspecialchars($col['Nombre']) ?></option><?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-danger">Agregar</button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach;
    }
}