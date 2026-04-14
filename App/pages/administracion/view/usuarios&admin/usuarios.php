<section class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h2 class="subtitulo letraRomana fw-bold  m-0">Usuarios & Administradores</h2>
            <p class="texto text-secondary">Gestiona los accesos y roles de la plataforma.</p>
        </div>
        <button class="text-white px-4 py-2 rounded-pill border-0 bg-rojo texto fw-medium"
             data-bs-toggle="modal" data-bs-target="#modalCrearAdmin">
            <i class="bi bi-person-plus me-2"></i>Nuevo Admin
        </button>
    </div>

    <!-- Card Principal -->
    <div class="border-0 rounded-4">

        <!-- Filtros -->
        <div class="d-flex justify-content-between align-items-center mb-4">

            <!-- Buscador -->
            <div class="input-group w-50 border rounded rounded-4">
                <span class="input-group-text bg-light border-0 rounded-start-4">
                    <i class="bi bi-search text-secondary"></i>
                </span>
                <input type="text" class="form-control input border-0 bg-light texto rounded-end-4 text-secondary"
                    placeholder="Buscar por nombre ..." id="buscador">
            </div>

            <!-- Select -->
            <select class="form-select w-auto rounded-4 py-2 px-4 border text-secondary texto pe-5 bg-light " id="filtroTabla" data-filtro="rol">

                <option value="todos">Todos los roles</option>
                <?php foreach ($roles as $rol) { ?>
                    <option value="<?= $rol['EsAdmin'] ?>">
                        <?php if ($rol['EsAdmin']) { ?>
                            Administrador
                        <?php } else { ?>
                            Usuario
                        <?php } ?>
                    </option>
                <?php } ?>

            </select>

        </div>

        <div id="no-resultados"></div>

        <!-- Tabla -->
        <div class="">
            <table class="w-100 rounded-4 overflow-hidden" id="tablaPaginada">
                <thead class="bg-light text-secondary text-uppercase texto">
                    <tr >
                        <th class="py-3 ps-3 " data-ordenacion="string" data-orden="desc">USUARIO <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                        <th class="py-3">ROL</th>
                        <th class="py-3 text-end pe-3">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>

                    <?php foreach ($usuarios as $usuario) { ?>

                        <tr data-nombre="<?= $usuario['EsAdmin'] ?>" class="registroTabla border-bottom align-middle">
                            <td class="py-2">
                                <div class="d-flex align-items-center ps-3">
                                    <div class="circuloPerfil rounded-circle bg-rojo d-flex align-items-center justify-content-center text-white fw-semibold me-3 text-uppercase texto"><?= substr($usuario['Nombre'], 0, 2) ?></div>
                                    <div>
                                        <div class="fw-semibold texto" data-buscado="true"><?= $usuario['Nombre'] ?></div>
                                        <small class="text-secondary textoPequeno" data-buscado="true">@<?= $usuario['Username'] ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="texto">
                                <?php if ($usuario['EsAdmin']) { ?>
                                    <i class="bi bi-shield me-2 texto-rojo"></i>
                                    Administrador
                                <?php } else { ?>
                                    <i class="bi bi-person me-2 text-secondary"></i>
                                    Usuario
                                <?php } ?>
                            </td>
                            <td class="text-end pe-3 ocultaAcciones">
                                <i class="bi bi-trash text-secondary me-3 cursor-pointer" data-bs-toggle="modal" data-bs-target="#eliminarUsuario" data-id="<?= $usuario['ID_Usuario'] ?>" data-nombre="<?= $usuario['Nombre'] ?>"></i>
                                <i class="bi bi-three-dots-vertical text-secondary cursor-pointer" data-bs-toggle="dropdown"></i>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" data-bs-display="static">

                                    <li>
                                        <a class="dropdown-item" href="/App/pages/perfil/<?= $usuario['ID_Usuario'] ?>">
                                            👤 Ver perfil
                                        </a>
                                    </li>

                                    <li>
                                        <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#verDatos" data-nombre="<?php echo $usuario['Nombre'] ?>" data-username="<?php echo $usuario['Username'] ?>" data-email="<?php echo $usuario['Email'] ?>" data-rol="<?php echo ($usuario['EsAdmin']) ? 'Administrador' : 'Usuario'; ?>">
                                            👁️ Ver datos
                                        </a>
                                    </li>

                                    <li>

                                        <a class="dropdown-item botonResetearContraseña" data-id-usuario="<?= $usuario['ID_Usuario'] ?>">
                                            🔑 Resetear contraseña
                                        </a>
                                    </li>

                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>

                                    <li>
                                        <a class="dropdown-item text-danger" href="deshabilitar_usuario.php?id=1">
                                            🚫 Deshabilitar usuario
                                        </a>
                                    </li>

                                </ul>
                            </td>
                        </tr>


                    <?php } ?>

                </tbody>
            </table>

        </div>
    </div>
</section>

<script src="assets/componentePaginacion.js"></script>
<script src="assets/resetearContrasena.js"></script>