<?php
require_once __DIR__ . '/../../../core/auth.php';

class FeedView 
{
    public function render($recetas, $etiquetas, $catActiva = null) 
    {
        ?>
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
        <body>
            <div class="container mt-4">
                <div class="header-grid mb-4">
                    <div class="header-item item-logo">
                        <h3 class="fw-bold d-flex align-items-center m-0 text-nowrap">
                            <span class="text-danger" style="letter-spacing: 0.3em;">SPEZZIART</span>
                            <span class="ms-2 d-none d-sm-inline text-dark">| Social</span>
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
                        <?php if(Auth::check()): ?>
                            <div class="notification-icon position-relative">
                                <span class="material-symbols-outlined cursor-pointer">notifications</span>
                                <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                            </div>
                        <?php else: ?>
                            <a href="/App/pages/login" class="btn btn-danger rounded-pill px-4 shadow-sm">Login</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="rounded-pill d-flex align-items-center gap-2 mb-4 flex-wrap etiquetas-filtro">
                    <button class="btn btn-outline-danger rounded-pill light d-flex align-items-center justify-content-center btn-add-tag" 
                            
                            data-bs-toggle="modal" data-bs-target="#modalEtiquetas">
                        <span class="material-symbols-outlined">add</span>
                    </button>

                    <div id="chips-wrapper" class="d-flex gap-2 flex-wrap align-items-center">
                        </div>
                    
                    <span id="extra-chips-badge" class="badge rounded-pill bg-secondary d-none" style="cursor: default;"></span>
                    
                    
                </div>

                <div id="feed-container">
                    <?php if (empty($recetas)): ?>
                        <p class="text-center text-muted">No se encontraron recetas.</p>
                    <?php else: ?>
                        <?php foreach ($recetas as $receta): ?>
                            <div class="card feed-card mb-4 p-3 border-0 shadow-sm rounded-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <a href="/App/pages/perfil/<?php echo $receta['ID_Creador']; ?>" class="text-decoration-none">
                                        <div class="username text-danger fw-bold">👤 @<?php echo htmlspecialchars($receta['Username']); ?></div>
                                    </a>
                                    <div class="text-muted small"><?php echo date('d M', strtotime($receta['FechaCreacion'])); ?></div>
                                </div>

                                <div class="mb-2">
                                    <?php if(!empty($receta['EtiquetasNombres'])): 
                                        foreach(explode(',', $receta['EtiquetasNombres']) as $tag): ?>
                                            <span class="badge badge-tag"><?php echo htmlspecialchars($tag); ?></span>
                                        <?php endforeach; 
                                    endif; ?>
                                </div>

                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <?php if($receta['Imagen']): ?>
                                            <img src="/App/uploads/<?php echo htmlspecialchars($receta['Imagen']); ?>" class="feed-img">
                                        <?php else: ?>
                                            <div class="feed-img bg-light d-flex align-items-center justify-content-center text-muted border">Sin imagen</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-8 d-flex flex-column justify-content-between">
                                        <div>
                                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($receta['Titulo']); ?></h5>
                                            <p class="text-muted small"><?php echo htmlspecialchars(substr($receta['Descripcion'], 0, 150)) . '...'; ?></p>
                                        </div>
                                        <div class="d-flex justify-content-end gap-3">
                                            <span class="material-symbols-outlined cursor-pointer">favorite</span>
                                            <span class="material-symbols-outlined cursor-pointer">chat</span>
                                            <span class="material-symbols-outlined cursor-pointer">bookmark</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="modal fade" id="modalEtiquetas" tabindex="-1" aria-labelledby="modalEtiquetasLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg rounded-4">
                        <div class="modal-header border-0">
                            <h5 class="modal-title fw-bold" id="modalEtiquetasLabel">Añadir Etiquetas</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted small mb-3">Selecciona etiquetas para filtrar tu feed:</p>
                            <div class="d-flex flex-wrap gap-2" id="modal-tags-list">
                                <?php foreach ($etiquetas as $et): ?>
                                    <button class="btn btn-sm rounded-pill chip-selectable" 
                                            data-name="<?php echo htmlspecialchars($et['Nombre']); ?>">
                                        <?php echo htmlspecialchars($et['Nombre']); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <button id="clear-filters" class="btn btn-link btn-sm text-muted text-decoration-none d-none">Limpiar filtros</button>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-danger w-100 rounded-pill" data-bs-dismiss="modal">Aplicar Filtros</button>
                        </div>
                    </div>
                </div>
            </div>
            <script  src="/App/pages/feed/view/FiltradoEtiquetas.js"></script> 
        <?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        
<?php
    }
}