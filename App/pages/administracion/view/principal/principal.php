<section class="container-fluid py-4">

    <h2 class="subtitulo letraRomana fw-bold  m-0">Panel de Control</h2>
    <p class="texto text-secondary">Bienvenido de nuevo, esto es lo que está pasando hoy.</p>

    <!-- Stats -->
    <div class="row g-3  mt-2 p-0 justify-content-start">
        <div class="col-12 col-sm-4 col-lg-3 p-2">
            <div class="d-flex flex-column p-4 sombra border rounded rounded-4 gap-2 bg-white">
                <div class="mb-1">
                    <span class="bg-azulClaro p-2 pt-3 rounded rounded-3"><i class="bi bi-people iconos texto-azul "></i></span>
                </div>
                <div>
                    <div class="texto text-secondary fw-semibold">Usuarios Totales</div>
                    <div class="d-flex align-items-end justify-content-between gap-2 mt-1">
                        <div class="fw-bold textoMediano"><?php echo $usuariosTotales; ?></div>
                        <div class="textoPequeno texto-verde fw-semibold">+12.5%</div>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-12 col-sm-4 col-lg-3  p-2">
            <div class=" d-flex flex-column p-4 sombra border rounded rounded-4 gap-2 bg-white">

                <div class="mb-1">
                    <span class="bg-verdeClaro p-2 pt-3 rounded rounded-3"><i class="bi bi-egg-fried iconos texto-verde"></i></span>
                </div>
                <div>
                    <div class="texto text-secondary fw-semibold">Recetas Publicadas</div>
                    <div class="d-flex align-items-end justify-content-between gap-2 mt-1">
                        <div class="fw-bold textoMediano"><?php echo $recetasTotalesSem; ?></div>
                        <div class="textoPequeno texto-verde fw-semibold">+8.2%</div>
                    </div>
                </div>

            </div>
        </div>
        <div class="col-12 col-sm-4 col-lg-3 p-2">
            <div class=" d-flex flex-column p-4 sombra border rounded rounded-4 gap-2 bg-white">
                <div class="mb-1">
                    <span class="bg-moradoClaro p-2 pt-3 rounded rounded-3"><i class="bi bi-clock iconos texto-morado"></i></span>
                </div>
                <div>
                    <div class="texto text-secondary fw-semibold">Pendientes Revisión</div>
                    <div class="d-flex align-items-end justify-content-between gap-2 mt-1">
                        <div class="fw-bold textoMediano"><?php echo $pendientesRevision; ?></div>
                        <div class="textoPequeno texto-rojo fw-semibold">-3</div>
                    </div>
                </div>

            </div>
        </div>
    </div>


    <div class="row g-3 mt-3 ">
        <div class="col-lg-7 pe-2">
            <div class=" sombra border rounded rounded-4 gap-2 bg-white p-4">
                <h6 class="fw-bold mb-3 textoMediano">Actividad Semanal</h6>

                <div><canvas id="graficaRecetasSemanal"></canvas></div>

            </div>
        </div>
        <div class="col-lg-5 ps-2">
            <div class="sombra border rounded rounded-4 gap-2 bg-white p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold  textoMediano m-0">Últimas Aprobaciones</h6>
                </div>
                <?php if (empty($ultimasRecetasAprobadas)) { ?>
                    <div class="text-center py-5">
                        <span class="bg-naranjaClaro p-2 pt-4 rounded rounded-3"><i class="bi bi-emoji-frown texto-naranja titulo"></i></span>
                        <h5 class="mt-3">Nada por aquí</h5>
                        <p class="text-muted mb-4">Aún no hay recetas aprobadas recientemente.</p>
                        <a href="/App/pages/administracion/moderacion" class="rounded rounded-3 text-decoration-none fw-medium bg-rojo text-white px-4 py-2">
                            Ir a moderación
                        </a>
                    </div>
                    <?php } else {

                    foreach ($ultimasRecetasAprobadas as $receta) { ?>
                        <div class="d-flex align-items-center justify-content-between py-2 aprobacionesReceta px-2 rounded rounded-4 mb-2">
                            <div class="d-flex align-items-center">

                                <img src="https://picsum.photos/40" class="rounded-circle me-2" alt="Foto de perfil">
                                <div>
                                    <div class="fw-semibold small"><?php echo $receta['Titulo']; ?></div>
                                    <div class="text-muted small">Publicado por @<?php echo $receta['Autor']; ?></div>
                                </div>
                            </div>
                            <span class="badge bg-verdeClaro texto-verde">APROBADA</span>
                        </div>
                <?php }
                } ?>
            </div>
        </div>
    </div>
</section>