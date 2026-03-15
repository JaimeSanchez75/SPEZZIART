<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1">Usuarios & Administradores</h2>
        <p class="text-muted mb-0">Gestiona los accesos y roles de la plataforma.</p>
    </div>
    <button class="btn text-white px-4 py-2 rounded-pill"
        style="background: var(--brand-wine);">
        <i class="bi bi-person-plus me-2"></i>Nuevo Usuario
    </button>
</div>

<!-- Card Principal -->
<div class="card border-0 rounded-4">

    <!-- Filtros -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <!-- Buscador -->
        <div class="input-group w-50">
            <span class="input-group-text bg-light border-0 rounded-start-pill">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" class="form-control input border-0 bg-light rounded-end-pill"
                placeholder="Buscar por nombre o email...">
        </div>

        <!-- Select -->
        <select class="form-select w-auto rounded-pill px-4">
            <option>Todos los roles</option>
            <option>Administrador</option>
            <option>Moderador</option>
            <option>Usuario</option>
        </select>

    </div>

    <!-- Tabla -->
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="text-muted small">
                <tr>
                    <th>USUARIO</th>
                    <th>ROL</th>
                    <th class="text-end">ACCIONES</th>
                </tr>
            </thead>
            <tbody>

                <!-- Usuario 1 -->

                <?php foreach ($usuarios as $usuario) { ?>

                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3"><?= substr($usuario['Nombre'], 0, 2) ?></div>
                                <div>
                                    <div class="fw-semibold"><?= $usuario['Nombre'] ?></div>
                                    <small class="text-muted">@<?= $usuario['Username'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($usuario['EsAdmin']) { ?>
                                <i class="bi bi-shield me-2 text-danger"></i>
                                Administrador
                            <?php } else { ?>
                                <i class="bi bi-person me-2 text-primary"></i>
                                Usuario
                            <?php } ?>
                        </td>
                        <td class="text-end">
                            <i class="bi bi-trash text-muted me-3"></i>
                            <i class="bi bi-three-dots-vertical text-muted" data-bs-toggle="dropdown"></i>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" data-bs-display="static">

                                <li>
                                    <a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#perfilModal">
                                        👤 Ver perfil
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="editar_usuario.php?id=1">
                                        👁️ Ver datos
                                    </a>
                                </li>

                                <li>
                                    <a class="dropdown-item" href="reset_password.php?id=1">
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

        <div>
            <div>

            </div>
            <nav aria-label="Page navigation example" class="d-flex justify-content-end mt-4">

                <ul class="pagination pagination-sm rounded-pill shadow-sm">

                    <li class="page-item">
                        <button class="page-link">Anterior</button>
                    </li>

                    <li class="page-item active" aria-current="page">
                        <button class="page-link">1</button>
                    </li>

                    <li class="page-item">
                        <button class="page-link">2</button>
                    </li>

                    <li class="page-item">
                        <button class="page-link">3</button>
                    </li>

                    <li class="page-item">
                        <button class="page-link">Siguiente</button>
                    </li>

                </ul>

            </nav>
        </div>
        
    </div>