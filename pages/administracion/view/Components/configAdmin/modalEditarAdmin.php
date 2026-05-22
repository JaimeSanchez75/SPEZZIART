<?php require_once __DIR__ . '/../../../../../core/auth.php'; ?>
<div class="modal fade" id="modalEditarperfil" tabindex="-1" aria-labelledby="modalEditarperfilLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
            <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
            <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formEditarperfil" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                    
                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div><span><i class="bi bi-person-gear texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                        <div>
                            <h3 class="fw-bold subtitulo letraRomana m-0" id="modalEditarperfilLabel">Editar perfil</h3>
                            <p class="texto text-secondary m-0">Actualiza tu información personal.</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="apodo" class="form-label text-dark fw-semibold">Apodo</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-person texto-rojo textoMediano"></i></span>
                            <input type="text" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="apodo" name="datosUsuario[apodo]" value="<?= htmlspecialchars($userLogueado['nombre']); ?>" placeholder="Escribe el apodo" required minlength="2" maxlength="60">
                            <div class="invalid-feedback">El apodo debe tener entre 2 y 60 caracteres.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="username" class="form-label text-dark fw-semibold">Nombre de usuario</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-at texto-rojo textoMediano"></i></span>
                            <input type="text" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="username" name="datosUsuario[username]" value="<?= htmlspecialchars($userLogueado['username']); ?>" placeholder="Ejemplo: juan123" required minlength="3" maxlength="30" pattern="^[a-zA-Z0-9_\.]+$" title="Solo letras, números, guion bajo y punto.">
                            <div class="invalid-feedback">Usa entre 3 y 30 caracteres (letras, números, _ o .).</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label text-dark fw-semibold">Correo electrónico</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-envelope texto-rojo textoMediano"></i></span>
                            <input type="email" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="email" name="datosUsuario[email]" value="<?= htmlspecialchars($userLogueado['email']); ?>" placeholder="usuario@ejemplo.com" required maxlength="120">
                            <div class="invalid-feedback">Por favor ingresa un email válido.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label text-dark fw-semibold">Contraseña <span class="textoPequeno fw-semibold text-secondary">(Opcional)</span></label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-lock texto-rojo textoMediano"></i></span>
                            <input type="password" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="password" name="datosUsuario[password]" value="" placeholder="********" minlength="6" maxlength="100">
                            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password2" class="form-label text-dark fw-semibold">Repite la contraseña <span class="textoPequeno fw-semibold text-secondary">(Opcional)</span></label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-lock texto-rojo textoMediano"></i></span>
                            <input type="password" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="password2" name="datosUsuario[password2]" value="" placeholder="********" minlength="6" maxlength="100">
                            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="/pages/administracion/assets/configAdmin/editarDatosConf.js"></script>
