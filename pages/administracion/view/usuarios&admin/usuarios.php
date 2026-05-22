<?php declare(strict_types=1); ?>
<section class="container-fluid py-4">
    <div class="d-flex justify-content-between flex-wrap align-items-center mb-2">
        <div>
            <h2 class="subtitulo letraRomana fw-bold  m-0">Usuarios & Administradores</h2>
            <p class="texto text-secondary">Gestiona los accesos y roles de la plataforma.</p>
        </div>
        <button class="text-white px-4 py-2 rounded-pill border-0 bg-rojo texto fw-medium" data-bs-toggle="modal" data-bs-target="#modalCrearAdmin">
            <i class="bi bi-person-plus me-2"></i>Nuevo Admin
        </button>
    </div>
    
    <div class="border-0 rounded-4">
        
        <div class="row justify-content-between align-items-stretch align-items-md-center gap-2 mb-4">
            
            <div class="col-12 col-sm-6 col-md-5">
                <div class="input-group  border rounded rounded-4 ">
                    <span class="input-group-text bg-light border-0 rounded-start-4">
                        <i class="bi bi-search text-secondary"></i>
                    </span>
                    <input type="text" class="form-control input border-0 bg-light texto rounded-end-4 text-secondary" placeholder="Buscar por nombre ..." id="buscador">
                </div>
            </div>
            
            <div class="col-12 col-sm-4 col-md-3">
                <select class="form-select rounded-4 py-2 px-4 border text-secondary texto pe-5 bg-light" id="filtroTabla" data-filtro="rol">
                    <option value="todos">Todos los roles</option>
                    <?php foreach ($roles as $rol)
                        { ?>
                        <option value="<?= $rol['EsAdmin'] ?>">
                            <?php if ($rol['EsAdmin']) { ?>Administrador<?php }
                            else { ?>Usuario<?php } ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <div id="no-resultados"></div>
        
        <div class="table-responsive rounded-4 ">
            <table class="w-100 tablaAdmin tablaAdmin--responsive " id="tablaPaginada" data-paginacion-pendiente="true">
                <thead class="bg-light text-secondary text-uppercase texto cabeceraTabla">
                    <tr>
                        <th class="py-3 ps-3 " data-ordenacion="string" data-orden="desc">USUARIO <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                        <th class="py-3">ROL</th>
                        <th class="py-3">ESTADO</th>
                        <th class="py-3 text-end pe-3">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario) {
                            $activa = isset($usuario['Activa']) ? (int)$usuario['Activa'] : 1;
                          ?>
                            <tr data-nombre="<?= $usuario['EsAdmin'] ?>" class="registroTabla border-bottom align-middle filaUsuario" data-id-usuario="<?= (int)$usuario['ID_Usuario'] ?>" data-activa="<?= $activa ?>">
                                <td class="py-2" data-label="Usuario" data-ordenar="<?= htmlspecialchars($usuario['Nombre'], ENT_QUOTES) ?>">
                                    <div class="d-flex align-items-center ps-3 celda-contenido">
                                        <?php if (!empty($usuario['FotoPerfil'])) { ?>
                                            <img src="<?= htmlspecialchars($usuario['FotoPerfil'], ENT_QUOTES, 'UTF-8') ?>" alt="perfil" id="imgPerfilUsuarios<?php echo (int)$usuario['ID_Usuario'] ?>" class="circuloPerfil rounded-circle object-fit-cover me-3 cajaW40 ">
                                        <?php }
                                        else { ?>
                                            <div class="circuloPerfil rounded-circle bg-rojo d-flex align-items-center justify-content-center text-white fw-semibold me-3 text-uppercase texto"><?= htmlspecialchars(substr($usuario['Nombre'], 0, 2), ENT_QUOTES, 'UTF-8') ?></div>
                                        <?php } ?>
                                        <div>
                                            <div class="fw-semibold texto text-break" data-buscado="true"><?= htmlspecialchars($usuario['Nombre'], ENT_QUOTES, 'UTF-8') ?></div>
                                            <small class="text-secondary textoPequeno text-break" data-buscado="true">@<?= htmlspecialchars($usuario['Username'], ENT_QUOTES, 'UTF-8') ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td class="texto" data-label="Rol">
                                    <?php if ($usuario['EsAdmin']) { ?><span class="celda-contenido d-inline-flex align-items-center"><i class="bi bi-shield me-2 texto-rojo"></i><span>Administrador</span></span><?php } else { ?><span class="celda-contenido d-inline-flex align-items-center"><i class="bi bi-person me-2 text-secondary"></i><span>Usuario</span></span><?php } ?>
                                </td>
                                <td class="texto" data-label="Estado">
                                    <span class="celda-contenido badge badgeEstadoUsuario textoPequeno <?= $activa === 1 ? 'bg-verdeClaro texto-verde' : 'bg-grisClaro texto-gris' ?>">
                                        <i class="bi <?= $activa === 1 ? 'bi-check-circle-fill' : 'bi-slash-circle-fill' ?> me-1 iconoBadgeEstado"></i>
                                        <span class="textoEstadoUsuario"><?= $activa === 1 ? 'Activo' : 'Deshabilitado' ?></span>
                                    </span>
                                </td>
                                <td class="text-end pe-3" data-label="Acciones">
                                    <div class="celda-contenido d-inline-flex align-items-center gap-3"><i class="bi bi-trash text-secondary cursor-pointer" data-bs-toggle="modal" data-bs-target="#eliminarUsuario" data-id="<?= (int)$usuario['ID_Usuario'] ?>" data-nombre="<?= htmlspecialchars($usuario['Nombre'], ENT_QUOTES, 'UTF-8') ?>"></i><div class="dropdown d-inline-block">
                                        <button type="button" class="btn btn-link p-0 border-0 text-secondary" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Acciones del usuario">
                                            <i class="bi bi-three-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end menuAcciones  sombra ">
                                            <li class="menuAcciones-titulo letraRomana"><i class="bi bi-sliders2 me-1"></i> Opciones</li>
                                            <li>
                                                <a class="menuAcciones-item btnAccionAdmin" href="/pages/perfil/<?= (int)$usuario['ID_Usuario'] ?>">
                                                    <i class="bi bi-person-circle"></i>
                                                    <span>Ver perfil</span>
                                                </a>
                                            </li>
                                            <li>
                                                <button type="button" class="menuAcciones-item btnAccionAdmin" data-bs-toggle="modal" data-bs-target="#verDatos" data-nombre="<?php echo htmlspecialchars($usuario['Nombre'], ENT_QUOTES, 'UTF-8') ?>" data-username="<?php echo htmlspecialchars($usuario['Username'], ENT_QUOTES, 'UTF-8') ?>" data-email="<?php echo htmlspecialchars($usuario['Email'], ENT_QUOTES, 'UTF-8') ?>" data-rol="<?php echo ($usuario['EsAdmin']) ? 'Administrador' : 'Usuario'; ?>" data-foto="<?php echo htmlspecialchars($usuario['FotoPerfil'] ?? '', ENT_QUOTES, 'UTF-8') ?>" data-fecha="<?php echo htmlspecialchars($usuario['fechaRegistro'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="bi bi-eye"></i>
                                                    <span>Ver datos</span>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="menuAcciones-item  btnAlternarEstadoUsuario btnAccionAdmin" data-id-usuario="<?= (int)$usuario['ID_Usuario'] ?>" data-nombre="<?= htmlspecialchars($usuario['Nombre']) ?>">
                                                    <i class="bi <?= $activa === 1 ? 'bi-slash-circle' : 'bi-check-circle' ?> iconoEstadoUsuario"></i>
                                                    <span class="etiquetaEstadoUsuario"><?= $activa === 1 ? 'Deshabilitar usuario' : 'Activar usuario' ?></span>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="menuAcciones-item btnCambiarRol btnAccionAdmin" data-id-usuario="<?= (int)$usuario['ID_Usuario'] ?>" data-nombre="<?= htmlspecialchars($usuario['Nombre']) ?>" data-es-admin="<?= (int)$usuario['EsAdmin'] ?>">
                                                    <i class="bi <?= $usuario['EsAdmin'] ? 'bi-person' : 'bi-shield' ?> iconoRolUsuario"></i>
                                                    <span class="etiquetaRolUsuario"><?= $usuario['EsAdmin'] ? 'Hacer usuario' : 'Hacer admin' ?></span>
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="menuAcciones-item menuAcciones-item--destacado botonResetearContraseña rounded-3 border-0" data-id-usuario="<?= (int)$usuario['ID_Usuario'] ?>">
                                                    <i class="bi bi-key-fill"></i>
                                                    <span>Resetear contraseña</span>
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                    </div>
                                </td>
                            </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<?php
require __DIR__ . "/../Components/usuario/modalCrear.php";
require __DIR__ . "/../Components/usuario/modalConfirmacion.php";
require __DIR__ . "/../Components/usuario/modalDatos.php";
?>
<script src="assets/componentePaginacion.js"></script>
<script src="/global/js/alertas.js"></script>
<script src="/global/js/confirmacion.js?v=<?= filemtime(ROOT_PATH . '/global/js/confirmacion.js') ?>"></script>
<script src="assets/peticionModalConfirmacion.js"></script>
<script src="assets/cambiarEstadoUsuario.js"></script>
<script src="assets/resetearContrasena.js"></script>
<script src="assets/cambiarRolUsuario.js"></script>