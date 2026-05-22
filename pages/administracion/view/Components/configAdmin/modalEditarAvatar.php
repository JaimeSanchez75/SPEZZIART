<?php require_once __DIR__ . '/../../../../../core/auth.php'; ?>
<div class="modal fade" id="modalEditarAvatar" tabindex="-1" aria-labelledby="modalEditarAvatarLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
            <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
            <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formEditarAvatar" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                    
                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div><span><i class="bi bi-camera texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                        <div>
                            <h3 class="fw-bold subtitulo letraRomana m-0" id="modalEditarAvatarLabel">Cambiar foto de perfil</h3>
                            <p class="texto text-secondary m-0">Actualiza tu foto de perfil aquí.</p>
                        </div>
                    </div>
                    
                    <div class="mb-4 d-flex justify-content-center">
                        <div id="contenedorVistaPrevia" class="rounded-circle border d-flex align-items-center justify-content-center overflow-hidden shadow-sm bg-light cajaW120">
                            <?php if (!empty($userLogueado['avatar'])) { ?>
                                <img id="imagenVistaPrevia" src="<?= htmlspecialchars($userLogueado['avatar']) ?>" alt="Foto de perfil" class="w-100 h-100 object-fit-cover">
                            <?php } else { ?>
                                <div id="avatarVistaPrevia" class="bg-rojo text-white rounded-circle d-flex align-items-center justify-content-center texto fw-bold w-100 h-100"><?= strtoupper(substr(htmlspecialchars($userLogueado['username'] ?? ''), 0, 2)) ?></div>
                            <?php } ?>
                        </div>
                    </div>
                    <input type="file" name="foto" id="inputImagen" accept="image/*" class="d-none">
                    <div class="text-center">
                        <button type="button" id="btnSeleccionarImagen" class="border border-rojo bg-rojoClaro texto-rojo p-2 fw-medium px-5 rounded-3 mb-2"><i class="bi bi-image me-2"></i>Seleccionar imagen</button>
                        <p class="textoPequeno text-secondary mb-0">JPG, PNG, GIF o WEBP. Tamaño máximo: 2MB</p>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="/pages/administracion/assets/configAdmin/editarAvatar.js"></script>
