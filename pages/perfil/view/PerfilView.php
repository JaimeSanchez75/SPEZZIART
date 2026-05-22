<?php
require_once __DIR__ . '/../../../core/auth.php'; 
class PerfilView 
{
    public function render($u, $numSeg, $vitrina, $recetas, $idLogueado, $isOwnProfile = false, $loSigue = false, $config = null, $bannersDesbloqueados = [], $bannerActual = null, $todosLogros = [], $logrosExpuestosIds = [], $perfilVisible = true, $solicitudPendiente = false) 
    { ?>
        <script>window.isLoggedIn = <?php echo Auth::check() ? 'true' : 'false'; ?>;</script>
        <script>window.profileUserId = <?php echo (int)$u['ID_Usuario']; ?>;</script>
        <script>window.isOwnProfile = <?php echo $isOwnProfile ? 'true' : 'false'; ?>;</script>
        <!DOCTYPE html>
        <html lang="es" data-bs-theme="<?= $_SESSION['user']['tema'] ?? 'sistema' ?>">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
                <title>Perfil de <?php echo $u['Username']; ?> - SpezziArt</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
                <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
                <meta name="csrf-token" content="<?= csrf_token() ?>">
                <link rel="stylesheet" href="/global/styles/global.css">
                <link rel="stylesheet" href="/global/styles/profile.css">
                <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
                <script src="/pages/perfil/assets/EditarPerfil.js"></script>
                <script>window.userTheme = '<?= $_SESSION['user']['tema'] ?? 'sistema' ?>';</script>
                <script src="/global/js/theme.js"> </script>
            </head>
            <body class="perfil-page bg-body">
            <div class="header-perfil position-relative overflow-hidden" 
                style="background-image: url('<?php echo $bannerActual['ImagenURL'] ?? '/uploads/banners/default.jpg'; ?>'); background-size: cover; background-position: center;">
                <div class="position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0,0,0,0.4);"></div>
                <div class="container position-relative z-1 py-5 profile-header-content">
                    <div class="row justify-content-center">
                        <div class="col-auto position-relative">
                            <!-- Foto de perfil -->
                            <div class="avatar-container d-inline-block position-relative">
                                <?php if (!empty($u['FotoPerfil']) && $perfilVisible == true): ?>
                                    <img src="<?php echo $u['FotoPerfil']; ?>" class="rounded-circle border border-3 border-white shadow" 
                                        width="120" height="120" style="object-fit: cover;" id="profileAvatar">
                                <?php else: ?>
                                    <div class="rounded-circle bg-light d-flex align-items-center justify-content-center shadow" 
                                        style="width: 120px; height: 120px;" id="profileAvatarPlaceholder">
                                        <span class="material-symbols-outlined text-secondary" style="font-size: 64px;">account_circle</span>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($idLogueado == $u['ID_Usuario']): ?>
                                    <button class="border-0 bg-white text-secondary rounded-circle position-absolute edit-avatar-btn" 
                                            style="bottom: 5px; right: 5px; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"
                                            data-bs-toggle="modal" data-bs-target="#editAvatarModal">
                                        <i class="bi bi-pencil texto"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center mt-3">
                        <div class="col-auto">
                            <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap profile-name-row">
                                <h2 class="fw-bold mb-0 text-white" id="userDisplayName">@<?php echo htmlspecialchars($u['Nombre']); ?></h2>
                                <?php if ($idLogueado == $u['ID_Usuario']): ?>
                                    <button class="border-0 bg-white text-secondary rounded-circle edit-avatar-btn" 
                                            style=" width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;"
                                            data-bs-toggle="modal" data-bs-target="#editNameModal">
                                        <i class="bi bi-pencil textoPequeno"></i>
                                    </button>
                                <?php endif; ?>
                                <?php if ($idLogueado != $u['ID_Usuario']): ?>
                                    <button class="border-0 bg-white text-secondary rounded-circle edit-avatar-btn" 
                                            style=" width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;"
                                            data-bs-toggle="modal" data-bs-target="#reportModal" 
                                            data-report-type="usuario" data-id="<?= $u['ID_Usuario'] ?>">
                                        <i class="bi bi-flag textoPequeno"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="row justify-content-center mt-4">
                        <div class="col-auto">
                            <div class="d-flex gap-4 flex-wrap justify-content-center profile-stats">
                                <div class="text-center">
                                    <div class="h4 fw-bold text-white mb-0 seguidores-count"><?php echo $numSeg; ?></div>
                                    <div class="small text-white-50">Seguidores</div>
                                </div>
                                <div class="text-center">
                                    <div class="h4 fw-bold text-white mb-0"><?php echo count($recetas); ?></div>
                                    <div class="small text-white-50">Recetas</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php if($idLogueado != $u['ID_Usuario']): ?>
                        <div class="row justify-content-center mt-4">
                            <div class="col-auto">
                                <button class="btn-banner-action p-2 texto rounded-4 px-4"
                                    onclick="gestionarSeguimiento(<?php echo (int)$u['ID_Usuario']; ?>)">
                                <?php
                                    if ($solicitudPendiente) {echo 'Cancelar solicitud';} 
                                    else {echo $loSigue ? 'Siguiendo' : 'Seguir';}
                                ?>
                            </button>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="row justify-content-center mt-4">
                            <div class="col-auto">
                                <button class="btn-banner-action p-2 texto rounded-4 px-4" data-bs-toggle="modal" data-bs-target="#editBannerModal">
                                    <i class="bi bi-image me-1"></i> Cambiar banner
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal fade perfil-modal" id="editNameModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                        <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
                        <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                            <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body ps-4 pe-4 pt-0 pb-4 position-relative z-1">
                            <div class="d-flex gap-3 align-items-center mb-4">
                                <div><span><i class="bi bi-person texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                                <div>
                                    <h3 class="modal-title fw-bold subtitulo letraRomana m-0">Cambiar nombre</h3>
                                    <p class="texto text-secondary m-0">Actualiza tu nombre visible.</p>
                                </div>
                            </div>
                            <form id="editNameForm">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-semibold">Nuevo nombre</label>
                                    <div class="input-group rounded-3 overflow-hidden">
                                        <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-at texto-rojo textoMediano"></i></span>
                                        <input type="text"
                                        name="nombre"
                                        id="editNombreInput"
                                        class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0"
                                        value="<?= htmlspecialchars($u['Nombre']) ?>"
                                        maxlength="30"
                                        required>
                                    </div>
                                    <div class="textoPequeno text-secondary text-end mt-1" id="editNombreCounter">0/30</div>
                                    <div class="invalid-feedback">El nombre ya está en uso</div>
                                </div>
                                <div class="alert alert-danger d-none" id="nameError"></div>
                                <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4 w-100 fw-medium">Guardar cambios</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade perfil-modal" id="editAvatarModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                        <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
                        <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                            <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body ps-4 pe-4 pt-0 pb-4 position-relative z-1">
                            <div class="d-flex gap-3 align-items-center mb-4">
                                <div><span><i class="bi bi-image texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                                <div>
                                    <h3 class="modal-title fw-bold subtitulo letraRomana m-0">Cambiar foto</h3>
                                    <p class="texto text-secondary m-0">Actualiza tu imagen de perfil.</p>
                                </div>
                            </div>
                            <form id="editAvatarForm" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <div class="mb-3 text-center">
                                    <img id="avatarPreview" src="<?= $u['FotoPerfil'] ?? '/uploads/default-avatar.png' ?>" class="rounded-circle border" width="150" height="150" style="object-fit: cover;">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-semibold">Selecciona una imagen</label>
                                    <input type="file" name="foto" class="form-control texto text-secondary rounded-3" accept="image/jpeg,image/png,image/gif,image/webp" required>
                                </div>
                                <div class="alert alert-danger d-none" id="avatarError"></div>
                                <div class="alert alert-success d-none" id="avatarSuccess"></div>
                                <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4 w-100 fw-medium">Subir foto</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade perfil-modal" id="editBannerModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
                    <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                        <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
                        <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                            <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                            <div class="d-flex gap-3 align-items-center mb-4">
                                <div><span><i class="bi bi-card-image texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                                <div>
                                    <h3 class="modal-title fw-bold subtitulo letraRomana m-0">Selecciona un banner</h3>
                                    <p class="texto text-secondary m-0">El banner actual se mantiene hasta que elijas otro.</p>
                                </div>
                            </div>
                            <div class="row g-3" id="bannersGrid">
                                <div class="col-12 text-center">Cargando banners...</div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 position-relative z-1">
                            <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4 fw-medium" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal fade perfil-modal" id="reportModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                        <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
                        <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                            <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                        </div>
                        <div class="modal-body ps-4 pe-4 pt-0 pb-4 position-relative z-1">
                            <div class="d-flex gap-3 align-items-center mb-4">
                                <div><span><i class="bi bi-flag texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                                <div>
                                    <h3 class="modal-title fw-bold subtitulo letraRomana m-0" id="reportModalTitle">Reportar</h3>
                                    <p class="texto text-secondary m-0">Indica el motivo del reporte.</p>
                                </div>
                            </div>
                            <form id="reportForm">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="id" id="reportId">
                                <input type="hidden" name="type" id="reportType">
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-semibold">Motivo del reporte</label>
                                    <div class="input-group rounded-3 overflow-hidden">
                                        <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-shield-exclamation texto-rojo textoMediano"></i></span>
                                        <select class="form-select texto text-secondary border-start-0 rounded-3 rounded-start-0" name="reason" id="reportReason" required>
                                            <option value="">Selecciona un motivo...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-semibold">Detalles adicionales (opcional)</label>
                                    <div class="input-group rounded-3 overflow-hidden">
                                        <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0 align-items-start pt-2"><i class="bi bi-text-paragraph texto-rojo textoMediano"></i></span>
                                        <textarea class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" name="details" rows="2" placeholder="Describe el problema con más detalle..."></textarea>
                                    </div>
                                </div>
                                <div class="alert alert-danger d-none" id="reportError"></div>
                                <div class="alert alert-success d-none" id="reportSuccess"></div>
                                <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4 w-100 fw-medium">Enviar reporte</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <script src="/pages/perfil/assets/GuardaVitrina.js"></script>
            <div class="container py-5 perfil-content">
                <?php if ($perfilVisible): ?>
                    <div class="vitrina-card p-4 mb-5 sombra border rounded rounded-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="fw-bold m-0 d-flex align-items-center tituloPequeno letraRomana">
                                <i class="bi bi-trophy texto-rojo me-2"></i> Vitrina de Logros
                            </h5>
                            <?php if($idLogueado == $u['ID_Usuario']): ?>
                                <button class="text-white px-4 py-2 rounded-pill border-0 bg-rojo texto fw-medium" data-bs-toggle="modal" data-bs-target="#modalVitrina">Personalizar</button>
                            <?php endif; ?>
                        </div>
                        <div class="row g-3" id="vitrina-container">
                            <?php foreach($vitrina as $l): ?>
                                <div class="col-6 col-md-3 logro-card" data-id="<?= $l['ID_Logro'] ?>" style="cursor: pointer;">
                                    <div class="border rounded rounded-4 h-100 sombra efectoEscala p-3 text-center <?= $l['ganado'] ? 'bg-rojoClaro border-rojo texto-rojo' : 'bg-white text-secondary' ?>">
                                        <?php if (!empty($l['ImagenURL'])): ?>
                                            <img src="<?= $l['ImagenURL'] ?>" class="rounded-circle <?= $l['ganado'] ? 'bg-rojoClaro' : 'bg-light' ?> p-1 mb-2" width="60" height="60" style="object-fit: contain;" alt="<?= htmlspecialchars($l['Nombre']) ?>">
                                        <?php else: ?>
                                            <span class="material-symbols-outlined fs-1 mb-2 <?php echo $l['ganado'] ? 'texto-rojo' : 'text-secondary'; ?>">
                                                <?php echo $l['Icono']; ?>
                                            </span>
                                        <?php endif; ?>
                                        <div class="small fw-bold d-block text-truncate"><?php echo $l['Nombre']; ?></div>
                                        <?php if (!$l['ganado'] && isset($l['progreso'])): ?>
                                            <div class="progress mt-2" style="height: 4px;">
                                                <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $l['progreso'] ?>%;"></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal fade perfil-modal" id="logroDetailModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-md">
                            <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                                <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
                                <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                                    <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body text-center p-4 position-relative z-1" id="logroDetailContent">
                                    <div class="spinner-border text-danger" role="status"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade perfil-modal" id="modalVitrina" tabindex="-1" aria-labelledby="modalVitrinaLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl">
                            <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                                <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
                                <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                                    <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                                    <div class="d-flex gap-3 align-items-center mb-4">
                                        <div><span><i class="bi bi-trophy texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                                        <div>
                                            <h3 class="modal-title fw-bold subtitulo letraRomana m-0">Personalizar vitrina</h3>
                                            <p class="texto text-secondary m-0">Selecciona hasta 8 logros desbloqueados.</p>
                                        </div>
                                    </div>
                                    <p class="text-muted small">Selecciona hasta <strong>8 logros desbloqueados</strong> para mostrar en tu vitrina principal.</p>
                                    <form id="formVitrina">
                                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                        
                                        <!-- Logros desbloqueados -->
                                        <h6 class="fw-bold mb-3"><span class="badge bg-success me-2">Desbloqueados</span> (elige hasta 8)</h6>
                                        <div class="row g-3 mb-5" id="desbloqueados-container">
                                            <?php 
                                            $contDesbloq = 0;
                                            foreach($todosLogros as $logro): 
                                                if($logro['desbloqueado']): 
                                                    $contDesbloq++;
                                                    $checked = in_array($logro['ID_Logro'], $logrosExpuestosIds) ? 'checked' : '';
                                            ?>
                                                <div class="col-md-3 col-sm-4">
                                                    <input type="checkbox" class="btn-check" name="logros[]" 
                                                        id="logro_<?= $logro['ID_Logro'] ?>" 
                                                        value="<?= $logro['ID_Logro'] ?>" autocomplete="off" <?= $checked ?>>
                                                    <label class="btn border sombra efectoEscala position-relative p-3 texto rounded-4 w-100 d-flex flex-column align-items-center <?= $checked ? 'bg-rojoClaro texto-rojo border-rojo' : 'bg-white text-secondary' ?>" 
                                                        for="logro_<?= $logro['ID_Logro'] ?>">
                                                        <i class="bi bi-check-circle-fill texto-rojo position-absolute top-0 end-0 m-2 <?= $checked ? '' : 'd-none' ?>" data-selected-icon="true"></i>
                                                        <?php if (!empty($logro['ImagenURL'])): ?>
                                                            <img src="<?= $logro['ImagenURL'] ?>" class="rounded-circle bg-rojoClaro p-1 mb-2" width="48" height="48" style="object-fit: contain;" alt="<?= htmlspecialchars($logro['Nombre']) ?>">
                                                        <?php else: ?>
                                                            <span class="material-symbols-outlined fs-1 mb-2 texto-rojo"><?= htmlspecialchars($logro['Icono']) ?></span>
                                                        <?php endif; ?>
                                                        <span class="small fw-bold text-truncate w-100"><?= htmlspecialchars($logro['Nombre']) ?></span>
                                                    </label>
                                                </div>
                                            <?php endif; endforeach; ?>
                                            <?php if($contDesbloq == 0): ?>
                                                <div class="col-12 text-muted">Aún no tienes logros desbloqueados. ¡Sigue cocinando!</div>
                                            <?php endif; ?>
                                        </div>
                                        <!-- Logros bloqueados (con progreso) -->
                                        <h6 class="fw-bold mb-3 mt-4"><span class="badge bg-secondary me-2">Bloqueados</span> (progreso actual)</h6>
                                        <div class="row g-3">
                                            <?php 
                                            $contBloq = 0;
                                            foreach($todosLogros as $logro): 
                                                if(!$logro['desbloqueado']): 
                                                    $contBloq++;
                                                    $progreso = $logro['progreso'] ?? 0;
                                                    $actual = $logro['actual'] ?? 0;
                                                    $meta = $logro['meta'] ?? 1; ?>
                                           
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="card h-100 border sombra rounded rounded-4 p-3 bg-white text-secondary">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <?php if (!empty($logro['ImagenURL'])): ?>
                                                                <img src="<?= $logro['ImagenURL'] ?>" class="rounded-circle bg-light p-1 opacity-75" width="48" height="48" style="object-fit: contain; filter: grayscale(1);" alt="<?= htmlspecialchars($logro['Nombre']) ?>">
                                                            <?php else: ?>
                                                                <span class="material-symbols-outlined fs-1 text-secondary"><?= htmlspecialchars($logro['Icono']) ?></span>
                                                            <?php endif; ?>
                                                            <div class="flex-grow-1">
                                                                <div class="fw-bold small"><?= htmlspecialchars($logro['Nombre']) ?></div>
                                                                <div class="progress mt-1" style="height: 6px;">
                                                                    <div class="progress-bar bg-danger" style="width: <?= $progreso ?>%;"></div>
                                                                </div>
                                                                <div class="small text-muted"><?= $actual ?> / <?= $meta ?></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; endforeach; ?>
                                            <?php if($contBloq == 0): ?>
                                                <div class="col-12 text-muted">¡Felicidades! Has desbloqueado todos los logros.</div>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer border-0 px-4 pb-4 position-relative z-1">
                                    <span id="selectedCounter" class="text-muted me-auto">0/8 seleccionados</span>
                                    <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4 fw-medium" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4 fw-medium" onclick="guardarVitrina()">Guardar Vitrina</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-4 tituloPequeno letraRomana">Recetas Publicadas</h5>
                    <div class="row g-4">
                        <?php if(empty($recetas)): ?>
                            <div class="col-12 text-center py-5 text-muted mb-5">
                                <span class="material-symbols-outlined fs-1">no_meals</span>
                                <p>Este usuario aún no ha compartido recetas.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($recetas as $r): 
                                $imagenReceta = '';
                                $rutaBase = '/uploads/recetas/';
                                if (!empty($r['Imagen'])) 
                                {
                                    $imagenesReceta = array_values(array_filter(array_map('trim', explode(',', (string)$r['Imagen']))));
                                    $imagenPortada = $imagenesReceta[0] ?? '';
                                    if (str_starts_with($imagenPortada, '/')) 
                                    {
                                        $rutaCompleta = $_SERVER['DOCUMENT_ROOT'] . $imagenPortada;
                                        $imagenReceta = $imagenPortada;
                                    } 
                                    else 
                                    {
                                        $rutaCompleta = $_SERVER['DOCUMENT_ROOT'] . $rutaBase . $imagenPortada;
                                        $imagenReceta = $rutaBase . $imagenPortada;
                                    }
                                    if (!file_exists($rutaCompleta)) {$imagenReceta = ''; }
                                }
                                $descripcionReceta = trim((string)($r['Descripcion'] ?? ''));
                                $descripcionCorta = strlen($descripcionReceta) > 48 ? substr($descripcionReceta, 0, 48) . '...' : $descripcionReceta;
                            ?>
                            <div class="col-xl-4 col-md-6 col-12 d-flex">
                                <div class="card border-0 sombra rounded-4 flex-fill efectoEscala" onclick="abrirRecetaModal(<?= (int)$r['ID_Receta'] ?>)" style="cursor: pointer;">
                                    <?php if (!empty($imagenReceta)) { ?>
                                        <img src="<?= htmlspecialchars($imagenReceta) ?>" class="card-img-top rounded-top-4 object-fit-cover" style="height: 180px;" alt="Imagen de receta">
                                    <?php } else { ?>
                                        <div class="card-img-top rounded-top-4 bg-rojoClaro texto-rojo d-flex align-items-center justify-content-center" style="height: 180px;">
                                            <i class="bi bi-image" style="font-size: 3rem;"></i>
                                        </div>
                                    <?php } ?>
                                    <div class="card-body px-4 d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <div class="d-flex gap-1 flex-wrap align-items-center">
                                                    <div class="badge bg-rojoClaro texto-rojo textoPequeno">RECETA</div>
                                                    <?php if (!empty($r['EsFit'])) { ?>
                                                        <div class="badge bg-verdeClaro texto-verde textoPequeno">FIT</div>
                                                    <?php } ?>
                                                </div>
                                                <span class="text-secondary texto"><i class="bi bi-clock"></i> <?= (int)($r['Tiempo'] ?? 0) ?> min</span>
                                            </div>
                                            <h5 class="card-title tituloPequeno letraRomana fw-bold mt-0 mb-0 text-truncate"><?= htmlspecialchars($r['Titulo']) ?></h5>
                                            <?php if ($descripcionCorta !== '') { ?>
                                                <p class="card-text text-secondary textoPequeno m-0"><?= htmlspecialchars($descripcionCorta) ?></p>
                                            <?php } else { ?>
                                                <p class="card-text text-secondary textoPequeno m-0">Sin descripcion.</p>
                                            <?php } ?>
                                        </div>
                                        <div class="d-flex align-items-center mt-3 border-top pt-2">
                                            <?php if (!empty($u['FotoPerfil'])): ?>
                                                <img src="<?= htmlspecialchars($u['FotoPerfil']) ?>"
                                                    class="me-2 rounded-circle flex-shrink-0"
                                                    style="width:28px;height:28px;object-fit:cover;"
                                                    alt="@<?= htmlspecialchars($u['Nombre']) ?>"
                                                    onerror="this.onerror=null;this.src='/uploads/NoImg.jpg'">
                                            <?php else: ?>
                                                <i class="bi bi-person me-2 rounded-circle p-1 border px-2 bg-light texto text-secondary"></i>
                                            <?php endif; ?>
                                            <span class="text-secondary texto">Por <span><?= htmlspecialchars($u['Nombre']) ?></span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5 my-5">
                        <span class="material-symbols-outlined fs-1 text-muted">lock</span>
                        <h4 class="mt-3">Este perfil es privado</h4>
                        <p class="text-muted">Sigue al usuario y espera su respuesta para ver su contenido.</p>
                    </div>
                <?php endif; ?>
            </div>
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

        <!-- Modal guardar receta -->
        <div class="modal fade" id="saveModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                    <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
                    <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                        <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form id="saveForm">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="id_receta" id="saveRecipeId">
                        <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                            <div class="d-flex gap-3 align-items-center mb-4">
                                <div><span><i class="bi bi-bookmark texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                                <div>
                                    <h3 class="modal-title fw-bold subtitulo letraRomana m-0">Guardar receta</h3>
                                    <p class="texto text-secondary m-0">Selecciona dónde guardar esta receta.</p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">Selecciona las colecciones</label>
                                <div id="cols-list" class="border rounded-4 p-2" style="max-height: 200px; overflow-y: auto;">
                                    <div class="text-muted text-center">Cargando...</div>
                                </div>
                            </div>
                            <div class="alert alert-danger d-none" id="saveError"></div>
                            <div class="alert alert-success d-none" id="saveSuccess"></div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 position-relative z-1">
                            <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="/global/report.js"></script>
        <script src="/global/save.js"></script>
        <script src="/pages/feed/assets/FeedCore.js"></script>
        <script src="/pages/feed/assets/FeedLikes.js"></script>
        <script src="/pages/feed/assets/FeedComentarios.js"></script>
        <script src="/pages/feed/assets/FeedPopOvers.js"></script>
        <script src="/pages/perfil/assets/Logros.js"></script>
        <script src="/pages/perfil/assets/Seguimiento.js"></script>
        <?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
        <?php require_once __DIR__ . '/../../receta/view/verRecetaModal.php'; ?>
    </body>
    </html><?php
    }
}
