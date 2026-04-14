<header class="d-flex justify-content-end align-items-center gap-3 p-3  border-bottom">

    <div class="d-flex align-items-center gap-3">
        <div class="">
            <i class="bi bi-bell text-secondary"></i>
            <span class="notificacion"></span>
        </div>
        <div class="d-flex flex-column align-items-end border-start  px-2">
            <span class="texto fw-semibold"><?php echo $userLogueado['username']; ?></span>
            <span class="textoPequeno texto-grisClaro"><?php echo strtoupper($userLogueado['role']); ?></span>
        </div>
        <div class="bg-rojo text-white rounded-circle d-flex align-items-center justify-content-center circuloPerfil texto fw-bold" >
            <?php echo strtoupper(substr($userLogueado['username'], 0, 2)) ?>
        </div>
    </div>

</header>