<?php
require_once __DIR__ . '/../../../core/auth.php';

class IndividualView {
    public function render($misRecetas, $guardadas, $etiquetas, $config = null, $colecciones = [], $query = '') {
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

        <body data-bs-theme="<?php echo ($config && $config['ModoOscuro']) ? 'dark' : 'light'; ?>">
            <div class="container mt-4">

                <!-- HEADER -->
                <div class="header-grid mb-4">
                    <div class="header-item item-logo">
                        <h3 class="fw-bold d-flex align-items-center m-0">
                            <span class="text-danger" style="letter-spacing: 0.3em;">SPEZZIART</span>
                            <span class="ms-2 d-none d-sm-inline text-dark social">| Mis Recetas</span>
                        </h3>
                    </div>

                    <!-- 🔧 BUSCADOR (FIX) -->
                    <div class="header-item item-search">
                        <form method="GET" action="/App/pages/individual" class="position-relative">
                            <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">search</span>
                            <input type="text" name="buscar" id="busquedaRecetas" class="form-control rounded-pill ps-5 border-0 shadow-sm" 
                                   placeholder="Buscar recetas, listas o ingredientes..." 
                                   value="<?= htmlspecialchars($query) ?>">
                        </form>
                    </div>
                </div>

                <!-- 🔧 BOTÓN CREAR (FIX) -->
                <a href="/App/pages/individual/crear" 
                   class="btn btn-danger rounded-circle position-fixed bottom-0 end-0 m-4 d-flex justify-content-center align-items-center"
                   style="width:60px; height:60px; font-size:30px;">
                    +
                </a>

                <!-- RECETAS -->
                <h4 class="fw-bold mb-3"><span class="material-symbols-outlined align-middle">menu_book</span> Recetas</h4>
                <div id="feed-container">
                    <?php $this->renderLista($misRecetas); ?>
                </div>

                <!-- GUARDADAS -->
                <h4 class="fw-bold mb-3 mt-5"><span class="material-symbols-outlined align-middle">bookmark</span> Guardadas</h4>
                <div id="feed-container">
                    <?php $this->renderLista($guardadas); ?>
                </div>

                <!-- COLECCIONES -->
                <h4 class="fw-bold mt-5"><span class="material-symbols-outlined align-middle">collections_bookmark</span> Mis Colecciones</h4>

                <?php if(!empty($colecciones)): ?>
                    <ul class="list-group mb-3">
                        <?php foreach($colecciones as $col): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($col['Nombre']) ?>

                                <!-- 🔧 AGREGAR RECETA (FIX) -->
                                <form method="POST" action="/App/pages/individual" class="d-flex gap-2 m-0">
                                    <input type="hidden" name="idColeccion" value="<?= $col['ID_Coleccion'] ?>">
                                    <select name="idReceta" class="form-select form-select-sm">
                                        <?php foreach($misRecetas as $r): ?>
                                            <option value="<?= $r['ID_Receta'] ?>"><?= htmlspecialchars($r['Titulo']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Agregar</button>
                                </form>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted">No tienes colecciones aún.</p>
                <?php endif; ?>

                <!-- 🔧 CREAR COLECCIÓN (FIX) -->
                <form method="POST" action="/App/pages/individual" class="d-flex gap-2 mb-4">
                    <input type="text" name="nombreColeccion" class="form-control form-control-sm" placeholder="Nombre de la colección" required>
                    <button type="submit" class="btn btn-sm btn-danger">Crear colección</button>
                </form>

            </div>

            <?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const busqueda = document.getElementById('busquedaRecetas');
                    const recetas = document.querySelectorAll('.receta-card');

                    busqueda.addEventListener('input', function() {
                        const texto = busqueda.value.toLowerCase();
                        recetas.forEach(card => {
                            const titulo = card.getAttribute('data-titulo');
                            if (titulo.startsWith(texto)) {
                                card.style.display = '';
                            } else {
                                card.style.display = 'none';
                            }
                        });
                    });
                });
            </script>
        </body>
        </html>
        <?php
    }

    private function renderLista($recetas) {
        if (empty($recetas)): ?>
            <p class="text-muted ps-2">No hay recetas aquí todavía.</p>
        <?php else: 
            foreach ($recetas as $receta): ?>
                <div class="card feed-card mb-4 p-3 border-0 shadow-sm rounded-4 receta-card" data-titulo="<?= strtolower(htmlspecialchars($receta['Titulo'])) ?>">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="username text-danger fw-bold">👤 @<?php echo htmlspecialchars($receta['Username']); ?></div>
                        <div class="text-muted small"><?php echo date('d M', strtotime($receta['FechaCreacion'])); ?></div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <?php if($receta['Imagen']): ?>
                                <img src="/App/uploads/<?php echo htmlspecialchars($receta['Imagen']); ?>" class="feed-img rounded-3">
                            <?php else: ?>
                                <div class="feed-img bg-light d-flex align-items-center justify-content-center text-muted border rounded-3">Sin imagen</div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8 d-flex flex-column justify-content-between">
                            <div>
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($receta['Titulo']); ?></h5>
                                <p class="text-muted small"><?php echo htmlspecialchars(substr($receta['Descripcion'], 0, 150)) . '...'; ?></p>
                            </div>
                            <div class="d-flex justify-content-end gap-3">
                                <span class="material-symbols-outlined cursor-pointer">edit</span>
                                <span class="material-symbols-outlined cursor-pointer text-danger">delete</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach;
        endif;
    }
}  