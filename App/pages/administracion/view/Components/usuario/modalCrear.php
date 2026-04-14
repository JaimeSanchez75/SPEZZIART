<?php require_once __DIR__ . '/../../../../../core/csrfcheck.php'; ?>

<div class="modal fade" id="modalCrearAdmin" tabindex="-1" aria-labelledby="modalCrearAdminLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content ">
            
            <div class="modal-header border-0 d-flex justify-content-end p-3">
                <div class=""><button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-light" data-bs-dismiss="modal" aria-label="Cerrar" ></button></div>
            </div>

            
            <form id="formCrearAdmin" action="/App/pages/administracion/usuarios/crear" method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="modal-body ps-4 pe-4 pt-0 pb-2">
                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div>
                            <span><i class="bi bi-person-plus texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span>
                        </div>
                       <div>
                            <h3 class="fw-bold subtitulo letraRomana m-0">Crear Administrador</h3>
                            <p class="texto text-secondary m-0">Completa los campos para crear un nuevo administrador.</p>
                       </div>
                    </div>


                    <div class="mb-3">
                        <label for="apodo" class="form-label fw-semibold">Apodo</label>
                        <div class="input-group rounded-3 overflow-hidden ">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-person texto-rojo textoMediano"></i></span>
                            <input type="text" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="apodo" name="datosUsuario[apodo]" placeholder="Escribe el apodo" required>
                            <div class="invalid-feedback">Por favor ingresa un apodo.</div>
                        </div>
                        
                        
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label text-dark fw-semibold">Nombre de usuario</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-at texto-rojo textoMediano"></i></span>
                            <input type="text" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="username" name="datosUsuario[username]" placeholder="Ejemplo: juan123" required>
                            <div class="invalid-feedback">Por favor ingresa un nombre de usuario.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label text-dark fw-semibold">Correo electrónico</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-envelope textoMediano texto-rojo "></i></span>
                            <input type="email" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="email" name="datosUsuario[email]" placeholder="usuario@ejemplo.com" required>
                            <div class="invalid-feedback">Por favor ingresa un email válido.</div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password " class="form-label text-dark fw-semibold">Contraseña</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-lock texto-rojo textoMediano"></i></span>
                            <input type="password" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="password" name="datosUsuario[password]" placeholder="********" required minlength="6">
                            <div class="invalid-feedback">La contraseña debe tener al menos 6 caracteres.</div>
                        </div>
                    </div>
                </div>


                <div class="modal-footer border-0">
                    <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="bg-rojo text-white border-0 p-2  texto rounded-4 px-4" >Crear Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>