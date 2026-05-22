<header class="d-flex justify-content-lg-end  justify-content-between align-items-center gap-3 p-2 px-3  border-bottom">
    <!-- boton del navegador responsive -->
    <button class="border-0 d-lg-none bg-white text-black rounded-2" data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile">
        <i class="bi bi-list fs-2"></i>
    </button>

    <div class="d-flex align-items-center gap-3">
        <div class="dropdown">
            <!-- boton de notificaciones -->
            <button type="button" id="btnNotificaciones" class="btn position-relative p-0 border-0 bg-transparent me-2" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                <i class="bi bi-bell text-secondary fs-5"></i>
                <span id="contadorNotificacionesAdmin" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">0</span>
            </button>
            <!-- submenu notificaciones -->
            <div id="dropdownNotificacionesAdmin" class="dropdown-menu dropdown-menu-end shadow-sm p-2 menuNotificaciones">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                    <span class="fw-bold letraRomana textoMediano">Notificaciones</span>
                    <button type="button" id="btnLimpiarNotifAdmin" class="btn btn-sm btn-link text-danger p-0 text-decoration-none">Limpiar todas</button>
                </div>
                <div id="notifAdminLista" class="listaNotificaciones"><div class="text-center text-muted py-3">Cargando...</div></div>
            </div>
        </div>
        <!-- configuracion -->
        <button data-bs-toggle="modal" class="border-0 bg-transparent border-start" data-bs-target="#modalConfigAdmin">
            <div class="d-flex gap-2 rounded-4 px-2 py-2 hover">
                <div class="d-flex flex-column align-items-end  px-2">
                    <span class="texto fw-semibold"><?php echo htmlspecialchars($userLogueado['nombre']); ?></span>
                    <span class="textoPequeno texto-gris"><?php echo strtoupper(htmlspecialchars($userLogueado['role'])); ?></span>
                </div>
                <!-- logo -->
                <?php if (!empty($userLogueado['avatar'])) { ?>
                    <img src="<?= htmlspecialchars($userLogueado['avatar']) ?>" alt="perfil" id="imgPerfil" class="rounded-circle object-fit-cover circuloPerfil">
                <?php } 
                else { ?>
                    <div class="bg-rojo text-white rounded-circle d-flex align-items-center justify-content-center circuloPerfil texto fw-bold">
                         <?php echo strtoupper(substr(htmlspecialchars($userLogueado['username']), 0, 2)) ?>
                    </div>
                <?php } ?>
            </div>
        </button>
    </div>
</header>