<?php
class PerfilView {
public function render($u, $vitrina, $recetas, $idLogueado, $loSigue = false, $config = null) { ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de <?php echo $u['Username']; ?> - SpezziArt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    <link rel="stylesheet" href="/App/global/styles/global.css">
    <link rel="stylesheet" href="/App/global/styles/profile.css">
</head>
 <body data-bs-theme="<?php echo ($config && $config['ModoOscuro']) ? 'dark' : 'light'; ?>">
<div class="header-perfil text-center">
    <div class="container">
        <div class="mb-3">
            <span class="material-symbols-outlined" style="font-size: 80px;">account_circle</span>
        </div>
        <h2 class="fw-bold mb-0 opacity-75">@<?php echo $u['Nombre']; ?></h2>
        <div class="d-flex justify-content-center gap-4 mt-3">
            <div><b class="d-block fs-5"><?php echo $u['Seguidores']; ?></b> <small>Seguidores</small></div>
            <div><b class="d-block fs-5"><?php echo count($recetas); ?></b> <small>Recetas</small></div>
        </div>
        <div class="mt-3">
            <?php if($idLogueado == $u['ID_Usuario']): ?>
                <button class="btn btn-outline-light btn-sm rounded-pill px-4">Editar Perfil</button>
            <?php else: ?>
                <button class="btn <?php echo $loSigue ? 'btn-outline-light' : 'btn-light'; ?> btn-sm rounded-pill px-4 fw-bold" 
                        onclick="gestionarSeguimiento(<?php echo $u['ID_Usuario']; ?>)">
                    <?php echo $loSigue ? 'Siguiendo' : 'Seguir'; ?>
                </button>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="/App/pages/perfil/view/GuardaVitrina.js"></script>



<div class="container my-5">
    <div class="vitrina-card p-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold m-0 d-flex align-items-center">
                <span class="material-symbols-outlined me-2 text-danger">workspace_premium</span> Vitrina de Logros
            </h5>
            <?php if($idLogueado == $u['ID_Usuario']): ?>
                <button class="btn btn-sm btn-outline-danger rounded-pill px-3" 
        data-bs-toggle="modal" 
        data-bs-target="#modalVitrina">
    Personalizar
</button>
            <?php endif; ?>
        </div>
        <div class="row g-3">
            <?php foreach($vitrina as $l): ?>
                <div class="col-6 col-md-3">
                    <div class="logro-item border">
                        <span class="material-symbols-outlined logro-icon <?php echo $l['ganado'] ? 'logro-ganado' : 'logro-bloqueado'; ?>">
                            <?php echo $l['Icono']; ?>
                        </span>
                        <div class="small fw-bold d-block text-truncate"><?php echo $l['Nombre']; ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <h5 class="fw-bold mb-4">Recetas Publicadas</h5>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-4">
        <?php if(empty($recetas)): ?>
            <div class="col-12 text-center py-5 text-muted">
                <span class="material-symbols-outlined fs-1">no_meals</span>
                <p>Este usuario aún no ha compartido recetas.</p>
            </div>
        <?php else: ?>
            <?php foreach($recetas as $r): ?>
                <div class="col">
                    <div class="card receta-card shadow-sm">
                        <img src="uploads/<?php echo $r['Imagen'] ?? 'default.jpg'; ?>" class="receta-img" alt="Receta">
                        <div class="card-body p-3">
                            <h6 class="fw-bold text-truncate mb-1"><?php echo $r['Titulo']; ?></h6>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <span class="badge bg-light text-danger border"><?php echo $r['Tiempo']; ?> min</span>
                                <span class="material-symbols-outlined fs-6 text-muted">favorite</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="modal fade" id="modalVitrina" tabindex="-1" aria-labelledby="modalVitrinaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 25px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold px-3 pt-3">Mis Logros Destacados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small px-3">Selecciona exactamente 8 logros para mostrar en tu vitrina principal.</p>
                <form id="formVitrina">
                    <div class="row g-3 px-2">
                        <?php 
                        foreach($vitrina as $logro): 
                            if($logro['ganado']): ?>
                            <div class="col-md-4">
                                <input type="checkbox" class="btn-check" name="logros[]" 
                                       id="logro_<?php echo $logro['ID_Logro']; ?>" 
                                       value="<?php echo $logro['ID_Logro']; ?>" autocomplete="off">
                                <label class="btn btn-outline-warning w-100 p-3 rounded-4 d-flex flex-column align-items-center" 
                                       for="logro_<?php echo $logro['ID_Logro']; ?>">
                                    <span class="material-symbols-outlined fs-2"><?php echo $logro['Icono']; ?></span>
                                    <span class="small fw-bold mt-1"><?php echo $logro['Nombre']; ?></span>
                                </label>
                            </div>
                        <?php endif; endforeach; ?>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger rounded-pill px-4" onclick="guardarVitrina()">Guardar Vitrina</button>
                
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php }
}