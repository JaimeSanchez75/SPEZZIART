<?php require_once __DIR__ . '/../../../../../core/csrfcheck.php'; ?>

<div class="modal fade" id="modalCrearAdmin" tabindex="-1" aria-labelledby="modalCrearAdminLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-sm" style="border:none;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-dark" id="modalCrearAdminLabel">Crear Usuario Administrador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formCrearAdmin" action="/App/pages/administracion/usuarios/crear" method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="modal-body">


                    <div class="mb-3">
                        <label for="apodo" class="form-label text-dark fw-semibold">Apodo</label>
                        <input type="text" class="form-control" id="apodo" name="datosUsuario[apodo]" placeholder="Escribe el apodo" required>
                        <div class="invalid-feedback">Por favor ingresa un apodo.</div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label text-dark fw-semibold">Nombre de usuario</label>
                        <input type="text" class="form-control" id="username" name="datosUsuario[username]" placeholder="Ejemplo: juan123" required>
                        <div class="invalid-feedback">Por favor ingresa un nombre de usuario.</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label text-dark fw-semibold">Correo electrónico</label>
                        <input type="email" class="form-control" id="email" name="datosUsuario[email]" placeholder="usuario@ejemplo.com" required>
                        <div class="invalid-feedback">Por favor ingresa un email válido.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label text-dark fw-semibold">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="datosUsuario[password]" placeholder="********" required minlength="6">
                        <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                    </div>
                </div>


                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4" style="background: var(--brand-wine);">Crear Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>