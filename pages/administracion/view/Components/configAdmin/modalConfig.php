<div class="modal fade" id="modalConfigAdmin" tabindex="-1" aria-labelledby="modalConfigLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
            <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
            <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body ps-4 pe-4 pt-0 pb-4 position-relative z-1">
                
                <div class="d-flex gap-3 align-items-center mb-4">
                    <div><span><i class="bi bi-gear texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                    <div>
                        <h3 class="fw-bold subtitulo letraRomana m-0" id="modalConfigLabel">Configuración</h3>
                        <p class="texto text-secondary m-0">Personaliza tu experiencia en la plataforma.</p>
                    </div>
                </div>

                
                <p class="texto fw-bold text-dark mb-2 textoMediano">Perfil</p>
                <div class="config-item d-flex align-items-center justify-content-between border rounded-4 p-3 sombra mb-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="position-relative d-inline-block">
                            <?php if (!empty($userLogueado['avatar'])) { ?>
                                <img src="<?= htmlspecialchars($userLogueado['avatar']) ?>" alt="perfil"
                                    class="rounded-circle object-fit-cover circuloPerfil cajaW40 cursor-pointer"
                                    data-bs-toggle="modal" data-bs-target="#modalEditarAvatar" id="imgPerfilConfig"
                                    title="Click para cambiar la foto">
                            <?php } else { ?>
                                <div class="bg-rojo text-white rounded-circle d-flex align-items-center justify-content-center circuloPerfil texto fw-bold cursor-pointer"
                                    data-bs-toggle="modal" data-bs-target="#modalEditarAvatar"
                                    title="Click para añadir una foto">
                                    <?= strtoupper(substr(htmlspecialchars($userLogueado['username']), 0, 2)); ?>
                                </div>
                            <?php } ?>
                            <span class="position-absolute bottom-0 end-0 bg-white texto-rojo rounded-circle d-flex align-items-center justify-content-center cursor-pointer border border-gris sombra iconoEditarFoto iconoMuyPequeno"
                                data-bs-toggle="modal" data-bs-target="#modalEditarAvatar"
                                title="Cambiar foto">
                                <i class="bi bi-pencil-fill"></i>
                            </span>
                        </div>
                        <div class="d-flex flex-column">
                            <span class="texto fw-semibold text-dark"><?= htmlspecialchars($userLogueado['nombre']); ?></span>
                            <span class="badge bg-rojoClaro fw-semibold texto-rojo textoPequeno align-self-start"><?= strtoupper(htmlspecialchars($userLogueado['role'])); ?></span>
                        </div>
                    </div>
                    <button type="button" class="config-action border-0 bg-rojo text-white py-1 px-3 rounded-3 texto fw-medium" data-bs-toggle="modal" data-bs-target="#modalEditarperfil">
                        <i class="bi bi-pencil me-1"></i> Editar perfil
                    </button>
                </div>

                
                <p class="texto fw-bold text-dark mb-2 textoMediano">Apariencia</p>
                <div class="config-item d-flex justify-content-between align-items-center border rounded-4 p-3 sombra mb-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="config-icon bg-rojoClaro rounded-3 d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-palette texto-rojo"></i>
                        </div>
                        <div>
                            <p class="texto fw-semibold text-dark mb-0">Tema</p>
                            <p class="textoPequeno text-secondary mb-0">Elige el modo de visualización</p>
                        </div>
                    </div>
                    <div class="d-flex gap-2 flex-wrap justify-content-center">
                        <button type="button" id="btnTemaClaro" class="border border-gris bg-white py-1 px-2 text-secondary rounded-3 d-flex align-items-center gap-2 texto active"><i class="bi bi-sun"></i> Claro</button>
                        <button type="button" id="btnTemaOscuro" class="border border-gris bg-white py-1 px-2 text-secondary rounded-3 d-flex align-items-center gap-2 texto"><i class="bi bi-moon"></i> Oscuro</button>
                        <button type="button" id="btnTemaAuto" class="border border-gris bg-white py-1 px-2 text-secondary rounded-3 d-flex align-items-center gap-2 texto"><i class="bi bi-circle-half"></i> Auto</button>
                    </div>
                </div>

                
                <!-- <p class="texto fw-bold text-dark mb-2 textoMediano">Notificaciones</p>
                <div class="config-item d-flex justify-content-between align-items-center border rounded-4 p-3 sombra mb-3 bg-white">
                    <div class="d-flex align-items-center gap-3">
                        <div class="config-icon bg-rojoClaro rounded-3 d-inline-flex align-items-center justify-content-center">
                            <i class="bi bi-bell texto-rojo"></i>
                        </div>
                        <div>
                            <p class="texto fw-semibold text-dark mb-0">Notificaciones</p>
                            <p class="textoPequeno text-secondary mb-0">Recibe alertas y actualizaciones</p>
                        </div>
                    </div>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="switchNotificaciones" <?= ($userLogueado['notificaciones'] == 1) ? 'checked' : '' ?>>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>
<script src="/pages/administracion/assets/configAdmin/configAdmin.js"></script>
<?php
require 'modalEditarAdmin.php';
require 'modalEditarAvatar.php';
?>
