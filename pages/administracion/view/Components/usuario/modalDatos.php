<!-- MODAL VER USUARIO -->
<div class="modal fade" id="verDatos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
            <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
            <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                <!-- Hero -->
                <div class="d-flex mb-4 gap-3 align-items-center">
                    <div><span class="perfilUsuarioGrande bg-rojo d-flex align-items-center justify-content-center text-white fw-semibold text-uppercase texto rounded-circle titulo sombra" id="avatarUsuario"></span></div>
                    <div>
                        <p class="text-secondary textoPequeno m-0">DATOS DEL USUARIO</p>
                        <h3 id="nombreUsuarioModal" class="subtitulo fw-bold letraRomana m-0 text-break"></h3>
                        <div id="usernameUsuarioModal" class="text-secondary texto text-break"></div>
                    </div>
                </div>
                <div class="d-flex flex-column gap-2 mb-2">
                    <!-- Email -->
                    <div class="border sombra p-3 rounded-3 d-flex gap-2 align-items-center justify-content-between bg-white">
                        <div class="d-flex gap-3 align-items-center">
                            <div><span class="bg-rojoClaro p-2 rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-envelope iconos texto-rojo"></i></span></div>
                            <div class="d-flex flex-column">
                                <p class="text-secondary textoPequeno m-0">EMAIL</p>
                                <p id="emailUsuarioModal" class="texto m-0 text-break"></p>
                            </div>
                        </div>
                        <button class="bg-rojoClaro texto-rojo rounded-circle border-0 d-flex align-items-center justify-content-center cajaW40" id="btnCopiar"><i class="bi bi-clipboard"></i></button>
                    </div>
                    <!-- Rol -->
                    <div class="border sombra p-3 rounded-3 d-flex gap-3 align-items-center bg-white">
                        <div><span class="bg-rojoClaro p-2 rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-shield iconos texto-rojo" id="iconoRol"></i></span></div>
                        <div class="d-flex flex-column">
                            <p class="text-secondary textoPequeno m-0">ROL</p>
                            <p id="rolUsuarioModal" class="texto m-0"></p>
                        </div>
                    </div>
                    <!-- Fecha de registro -->
                    <div class="border sombra p-3 rounded-3 d-flex gap-3 align-items-center bg-white">
                        <div><span class="bg-rojoClaro p-2 rounded-3 d-inline-flex align-items-center justify-content-center"><i class="bi bi-calendar-check iconos texto-rojo"></i></span></div>
                        <div class="d-flex flex-column">
                            <p class="text-secondary textoPequeno m-0">MIEMBRO DESDE</p>
                            <p id="fechaUsuarioModal" class="texto m-0"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 position-relative z-1">
                <button type="button" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<script src="assets/modalDatosUsuario.js"></script>
