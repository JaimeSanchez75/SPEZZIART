<?php

declare(strict_types=1); ?>
<?php
if (!function_exists('moderacionTiempoRelativo')) {
    function moderacionTiempoRelativo(?string $fecha): string
    {
        if (!$fecha) return '';
        try {
            $ts    = strtotime($fecha);
            $ahora = time();
            $diff  = $ahora - $ts;

            if ($diff < 60)     return 'ahora';
            if ($diff < 3600)   return 'hace ' . floor($diff / 60) . ' min';
            if ($diff < 86400)  return 'hace ' . floor($diff / 3600) . ' h';
            if ($diff < 172800) return 'ayer';
            if ($diff < 604800) return 'hace ' . floor($diff / 86400) . ' días';

            $meses = ['ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
            return date('j', $ts) . ' ' . $meses[(int)date('n', $ts) - 1];
        } catch (\Throwable $e) {
            return '';
        }
    }
}

$reportesRecetas     = $reportesRecetas     ?? [];
$reportesComentarios = $reportesComentarios ?? [];
$reportesPerfiles    = $reportesPerfiles    ?? [];
$pendientes          = $pendientes          ?? 0;

$totalRecetas     = count($reportesRecetas);
$totalComentarios = count($reportesComentarios);
$totalPerfiles    = count($reportesPerfiles);
?>
<section class="container-fluid py-4">
    <span class="badge bg-rojoClaro texto-rojo texto fw-medium px-3 py-2 rounded-pill mb-2">
        <i class="bi bi-flag me-1"></i><?= (int)$pendientes ?> pendientes
    </span>
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div>
            
            <h2 class="subtitulo letraRomana fw-bold m-0">Moderación Social</h2>
            <p class="texto text-secondary m-0">Revisa los reportes pendientes enviados por la comunidad.</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            
            <a href="/pages/administracion/moderacion/historial"
                class="border bg-white text-secondary px-4 py-2 rounded-pill texto fw-medium text-decoration-none">
                <i class="bi bi-clock-history me-2"></i>Historial
            </a>
        </div>
    </div>


    <div class="row justify-content-between align-items-center my-3">
        <div class="col-12 col-sm-6 col-md-5">
            <div class="input-group  border rounded rounded-4">
                <span class="input-group-text bg-light border-0 rounded-start-4">
                    <i class="bi bi-search text-secondary"></i>
                </span>
                <input type="text"
                    class="form-control input border-0 bg-light texto rounded-end-4 text-secondary"
                    placeholder="Buscar por usuario, motivo, receta o comentario..."
                    id="buscador">
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs adminTabs mt-3" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabReportesRecetas" type="button" role="tab">
                <i class="bi bi-egg-fried me-2"></i>Recetas
                <span class="badge bg-rojoClaro texto-rojo ms-2"><?= $totalRecetas ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabReportesComentarios" type="button" role="tab">
                <i class="bi bi-chat-dots me-2"></i>Comentarios
                <span class="badge bg-grisClaro text-secondary ms-2"><?= $totalComentarios ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabReportesPerfiles" type="button" role="tab">
                <i class="bi bi-person-circle me-2"></i>Perfiles
                <span class="badge bg-grisClaro text-secondary ms-2"><?= $totalPerfiles ?></span>
            </button>
        </li>
    </ul>

    <div class="tab-content mt-4">


        <div class="tab-pane fade show active" id="tabReportesRecetas" role="tabpanel">
            <?php if (empty($reportesRecetas)) { ?>
                <div class="text-center py-5 empty-tab-state">
                    <i class="bi bi-shield-check display-4 text-muted"></i>
                    <p class="text-muted mt-3 texto">No hay reportes de recetas pendientes.</p>
                </div>
            <?php } else { ?>
                <div class="row g-4 mt-2" data-paginacion-pendiente="true">
                    <?php foreach ($reportesRecetas as $r) {
                        $reporteId    = (int)($r['ID_Reporte'] ?? 0);
                        $recetaId     = (int)($r['ID_Receta']  ?? 0);
                        $titulo       = (string)($r['Titulo']  ?? 'Receta sin título');
                        $imagen       = (string)($r['Imagen']  ?? '');
                        $reportador   = (string)($r['Reportador']  ?? 'Anónimo');
                        $reportado    = (string)($r['UsuarioReportado'] ?? '');
                        $motivo       = (string)($r['Motivo']  ?? '');
                        $fecha        = moderacionTiempoRelativo($r['Fecha'] ?? null);

                        $listaImagenes = array_values(array_filter(array_map('trim', explode(',', $imagen))));
                        $portada       = $listaImagenes[0] ?? '';
                    ?>
                        <div class="col-md-4 col-sm-6 col-12 d-flex">
                            <div class="card border-0 sombra rounded-4 flex-fill efectoEscala">
                                <?php if (!empty($portada)) { ?>
                                    <img src="<?= htmlspecialchars($portada) ?>" class="card-img-top rounded-top-4 object-fit-cover" style="height: 180px;" alt="Imagen de receta">
                                <?php } else { ?>
                                    <div class="card-img-top rounded-top-4 bg-rojoClaro texto-rojo d-flex align-items-center justify-content-center" style="height: 180px;">
                                        <i class="bi bi-egg-fried" style="font-size: 3rem;"></i>
                                    </div>
                                <?php } ?>
                                <div class="card-body px-4 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <div class="badge bg-verdeClaro texto-verde textoPequeno">RECETA</div>
                                            <?php if ($fecha) { ?>
                                                <span class="text-secondary textoPequeno"><i class="bi bi-clock me-1"></i><?= htmlspecialchars($fecha) ?></span>
                                            <?php } ?>
                                        </div>
                                        <h5 class="card-title tituloPequeno letraRomana fw-bold mt-0 mb-2" data-buscado="true"><?= htmlspecialchars($titulo) ?></h5>
                                        <p class="card-text text-secondary textoPequeno mb-1"><i class="bi bi-person me-1"></i>Autor: <strong data-buscado="true">@<?= htmlspecialchars($reportado) ?></strong></p>
                                        <p class="card-text text-secondary textoPequeno mb-1"><i class="bi bi-flag me-1"></i>Reportado por: <strong data-buscado="true">@<?= htmlspecialchars($reportador) ?></strong></p>
                                        <p class="card-text text-secondary textoPequeno fst-italic mb-0" data-buscado="true"><i class="bi bi-chat-quote me-1"></i><?= htmlspecialchars($motivo) ?></p>
                                        <a href='/pages/administracion/recetas/ver/<?php echo $recetaId;  ?>' class="texto-rojo textoPequeno text-decoration-none"><i class="bi bi-eye"></i> Ver detalles</a>
                                    </div>

                                    <div class="d-flex gap-2 mt-3 border-top pt-3">
                                        <button type="button"
                                            class="flex-fill bg-rojo text-white border-0 rounded-3 texto fw-medium py-2 abrirModal"
                                            data-tipo="receta"
                                            data-id="<?= $reporteId ?>"
                                            data-receta="<?= $recetaId ?>"
                                            data-usuario="<?= htmlspecialchars($reportado) ?>">
                                            <i class="bi bi-check-lg me-1"></i>Tomar acción
                                        </button>
                                        <button type="button"
                                            class="flex-fill border bg-white text-secondary rounded-3 texto fw-medium py-2 text-center btnMarcarRevisado"
                                            data-id="<?= $reporteId ?>">
                                            <i class="bi bi-x-lg me-1"></i>Marcar como revisado
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>


        <div class="tab-pane fade" id="tabReportesComentarios" role="tabpanel">
            <?php if (empty($reportesComentarios)) { ?>
                <div class="text-center py-5 empty-tab-state">
                    <i class="bi bi-shield-check display-4 text-muted"></i>
                    <p class="text-muted mt-3 texto">No hay reportes de comentarios pendientes.</p>
                </div>
            <?php } else { ?>
                <div class="row g-4 mt-2" data-paginacion-pendiente="true">
                    <?php foreach ($reportesComentarios as $r) {
                        $reporteId    = (int)($r['ID_Reporte']    ?? 0);
                        $comentarioId = (int)($r['ID_Comentario'] ?? 0);
                        $recetaId     = (int)($r['ID_Receta']     ?? 0);
                        $comentario   = (string)($r['Comentario'] ?? '');
                        $tituloReceta = (string)($r['Titulo']     ?? '');
                        $reportador   = (string)($r['Reportador'] ?? 'Anónimo');
                        $reportado    = (string)($r['UsuarioReportado'] ?? '');
                        $fotoReportado = (string)($r['FotoReportado']  ?? '');
                        $motivo       = (string)($r['Motivo']     ?? '');
                        $fecha        = moderacionTiempoRelativo($r['Fecha'] ?? null);
                        $iniciales    = strtoupper(substr($reportado !== '' ? $reportado : '?', 0, 2));
                    ?>
                        <div class="col-md-4 col-sm-6 col-12 d-flex">
                            <div class="card border-0 sombra rounded-4 flex-fill efectoEscala">
                                <div class="card-body px-4 d-flex flex-column justify-content-between">
                                    <div>

                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div class="badge bg-azulClaro texto-azul textoPequeno">COMENTARIO</div>
                                            <?php if ($fecha) { ?>
                                                <span class="text-secondary texto"><i class="bi bi-clock"></i> <?= htmlspecialchars($fecha) ?></span>
                                            <?php } ?>
                                        </div>


                                        <h5 class="card-title tituloPequeno letraRomana fw-bold mt-0 mb-1 fst-italic" data-buscado="true">
                                            <i class="bi bi-quote texto-rojo me-1"></i><?= htmlspecialchars($comentario) ?>
                                        </h5>

                                        <?php if (!empty($tituloReceta)) { ?>
                                            <p class="card-text text-secondary textoPequeno m-0" data-buscado="true">En la receta «<?= htmlspecialchars($tituloReceta) ?>»</p>
                                        <?php } ?>


                                        <div class="d-flex align-items-center mt-3 border-top pt-2">
                                            <?php if (!empty($fotoReportado)) { ?>
                                                <img src="<?= htmlspecialchars($fotoReportado) ?>" alt="@<?= htmlspecialchars($reportado) ?>" class="rounded-circle object-fit-cover me-2" style="width:28px; height:28px;">
                                            <?php } else { ?>
                                                <i class="bi bi-person me-2 rounded-circle p-1 border px-2 bg-light texto text-secondary"></i>
                                            <?php } ?>
                                            <span class="text-secondary texto">Por <span class="fw-semibold text-dark" data-buscado="true">@<?= htmlspecialchars($reportado) ?></span></span>
                                        </div>


                                        <div class="text-secondary textoPequeno mt-2">
                                            <i class="bi bi-flag-fill texto-rojo me-1"></i>
                                            <span class="fw-semibold text-dark" data-buscado="true">@<?= htmlspecialchars($reportador) ?></span>
                                            · <span class="fst-italic" data-buscado="true">«<?= htmlspecialchars($motivo) ?>»</span>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 mt-3 border-top pt-3">
                                        <button type="button"
                                            class="flex-fill bg-rojo text-white border-0 rounded-3 texto fw-medium py-2 abrirModal"
                                            data-tipo="comentario"
                                            data-id="<?= $reporteId ?>"
                                            data-comentario="<?= $comentarioId ?>"
                                            data-receta="<?= $recetaId ?>"
                                            data-usuario="<?= htmlspecialchars($reportado) ?>">
                                            <i class="bi bi-check-lg me-1"></i>Tomar acción
                                        </button>
                                        <button type="button"
                                            class="flex-fill border bg-white text-secondary rounded-3 texto fw-medium py-2 text-center btnMarcarRevisado"
                                            data-id="<?= $reporteId ?>">
                                            <i class="bi bi-x-lg me-1"></i>Marcar como revisado
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>


        <div class="tab-pane fade" id="tabReportesPerfiles" role="tabpanel">
            <?php if (empty($reportesPerfiles)) { ?>
                <div class="text-center py-5 empty-tab-state">
                    <i class="bi bi-shield-check display-4 text-muted"></i>
                    <p class="text-muted mt-3 texto">No hay reportes de perfiles pendientes.</p>
                </div>
            <?php } else { ?>
                <div class="row g-4 mt-2" data-paginacion-pendiente="true">
                    <?php foreach ($reportesPerfiles as $r) {
                        $reporteId    = (int)($r['ID_Reporte'] ?? 0);
                        $reportador   = (string)($r['Reportador']       ?? 'Anónimo');
                        $reportado    = (string)($r['UsuarioReportado'] ?? '');
                        $nombre       = (string)($r['NombreReportado']  ?? '');
                        $fotoReportado = (string)($r['FotoReportado']    ?? '');
                        $motivo       = (string)($r['Motivo']           ?? '');
                        $fecha        = moderacionTiempoRelativo($r['Fecha'] ?? null);
                        $iniciales    = strtoupper(substr($reportado !== '' ? $reportado : '?', 0, 2));
                    ?>
                        <div class="col-md-4 col-sm-6 col-12 d-flex">
                            <div class="card border-0 sombra rounded-4 flex-fill efectoEscala">
                                <div class="card-body px-4 d-flex flex-column justify-content-between">
                                    <div>

                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="badge bg-moradoClaro texto-morado textoPequeno">PERFIL</div>
                                            <?php if ($fecha) { ?>
                                                <span class="text-secondary texto"><i class="bi bi-clock"></i> <?= htmlspecialchars($fecha) ?></span>
                                            <?php } ?>
                                        </div>


                                        <div class="d-flex align-items-center mb-3">
                                            <?php if (!empty($fotoReportado)) { ?>
                                                <img src="<?= htmlspecialchars($fotoReportado) ?>" alt="@<?= htmlspecialchars($reportado) ?>" class="rounded-circle object-fit-cover me-3 cajaW65 sombra">
                                            <?php } else { ?>
                                                <div class="rounded-circle bg-rojo text-white d-inline-flex align-items-center justify-content-center fw-bold me-3 cajaW65 sombra text-uppercase" style="font-size:1.3rem;"><?= htmlspecialchars($iniciales) ?></div>
                                            <?php } ?>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <?php if ($nombre !== '') { ?>
                                                    <h5 class="card-title tituloPequeno letraRomana fw-bold mt-0 mb-0 text-truncate" data-buscado="true"><?= htmlspecialchars($nombre) ?></h5>
                                                <?php } ?>
                                                <p class="card-text text-secondary texto m-0 text-truncate" data-buscado="true">@<?= htmlspecialchars($reportado) ?></p>
                                            </div>
                                        </div>


                                        <div class="border-top pt-2 text-secondary textoPequeno">
                                            <i class="bi bi-flag-fill texto-rojo me-1"></i>
                                            Reportado por <span class="fw-semibold text-dark" data-buscado="true">@<?= htmlspecialchars($reportador) ?></span>
                                            <div class="fst-italic mt-1" data-buscado="true">«<?= htmlspecialchars($motivo) ?>»</div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 mt-3 border-top pt-3">
                                        <button type="button"
                                            class="flex-fill bg-rojo text-white border-0 rounded-3 texto fw-medium py-2 abrirModal"
                                            data-tipo="perfil"
                                            data-id="<?= $reporteId ?>"
                                            data-usuario="<?= htmlspecialchars($reportado) ?>">
                                            <i class="bi bi-check-lg me-1"></i>Tomar acción
                                        </button>
                                        <button type="button"
                                            class="flex-fill border bg-white text-secondary rounded-3 texto fw-medium py-2 text-center btnMarcarRevisado"
                                            data-id="<?= $reporteId ?>">
                                            <i class="bi bi-x-lg me-1"></i>Marcar como revisado
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <div id="no-resultados" class="text-center mt-3"></div>
</section>

<?php require __DIR__ . '/../Components/moderacion/mensajeEmail.php'; ?>
<script src="assets/moderacion/moderacion.js"></script>
<script src="assets/componentePaginacion.js"></script>

<script>
    (function() {
        const buscadorEl = document.getElementById('buscador');
        if (!buscadorEl) return;

        function refrescarMensajesVacios() {
            const hayBusqueda = buscadorEl.value.trim() !== '';
            document.querySelectorAll('.empty-tab-state').forEach(el => {
                el.style.display = hayBusqueda ? 'none' : '';
            });
        }

        buscadorEl.addEventListener('input', refrescarMensajesVacios);
        refrescarMensajesVacios();
    })();
</script>