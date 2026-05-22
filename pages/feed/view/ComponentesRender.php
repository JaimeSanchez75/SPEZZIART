<?php

declare(strict_types=1);
class ComponentesRender
{
    public function renderRecipeCard($receta)
    {
        ob_start();
        $formatearNutricion = static function ($valor): string 
        {
            $valor = is_numeric($valor) ? (float)$valor : 0.0;
            return fmod($valor, 1.0) === 0.0 ? (string)(int)$valor : rtrim(rtrim(number_format($valor, 2, '.', ''), '0'), '.');
        };
        $likeClass = (isset($receta['DioLike']) && $receta['DioLike']) ? 'fill-icon texto-rojo' : 'text-secondary';
        $imagenes = !empty($receta['Imagen']) ? array_values(array_filter(array_map('trim', explode(',', $receta['Imagen'])))) : [];
        $idCarousel = "carousel-" . (int)$receta['ID_Receta'];
        $portada = !empty($imagenes) ? $imagenes[0] : '/uploads/NoImg.jpg';
        $etiquetasArr = [];
        if (!empty($receta['EtiquetasNombres']) && is_string($receta['EtiquetasNombres'])) {$etiquetasArr = array_values(array_filter(array_map('trim', explode(',', $receta['EtiquetasNombres']))));}
        $visibles = array_slice($etiquetasArr, 0, 3);
        $ocultas  = array_slice($etiquetasArr, 3);
        $htmlOcultas = '<div class="etiquetas-tooltip-inner">';
        foreach ($ocultas as $tag) 
        {
            $htmlOcultas .= '<span class="badge bg-rojoClaro texto-rojo textoPequeno text-uppercase px-3 py-2 rounded-pill">'. htmlspecialchars($tag). '</span>';
        }
        $htmlOcultas .= '</div>';
        $esFit     = (bool)($receta['EsFit'] ?? false);
        $tiempo    = $receta['Tiempo']    ?? null;
        $porciones = $receta['Porciones'] ?? null;?>
        <div class="card feed-card tarjeta-receta  rounded-4 overflow-hidden"
            data-id="<?= (int)$receta['ID_Receta'] ?>"
            data-score="<?= isset($receta['final_score']) ? floatval($receta['final_score']) : '0' ?>"
            onclick="abrirRecetaModal(<?= (int)$receta['ID_Receta'] ?>)">
            <div class="row g-0 align-items-stretch">
                <div class="col-lg-5">
                    <?php if (!empty($imagenes)): ?>
                        <div id="<?= $idCarousel ?>" class="carousel slide" data-bs-ride="false">
                            <div class="carousel-inner  bg-light h-100">
                                <?php foreach ($imagenes as $i => $img): ?>
                                    <div class="carousel-item <?= $i === 0 ? 'active' : '' ?> h-100">
                                        <img src="<?= htmlspecialchars($img) ?>"
                                            class="img-fluid w-100 h-100 object-fit-cover"
                                            onerror="this.onerror=null; this.parentNode.innerHTML='<div class=\'d-flex flex-column align-items-center justify-content-center text-secondary text-center w-100\' style=\'height:360px;\'><i class=\'bi bi-image-alt texto-rojo bg-rojoClaro rounded-3 p-3 mb-2\' style=\'font-size:1.4rem;\'></i><span class=\'textoPequeno text-uppercase\'>Imagen no disponible</span></div>';" ;"
                                            alt="Receta">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php if (count($imagenes)>1) { ?>
                                <button class="carousel-control-prev" type="button"
                                    data-bs-target="#<?= $idCarousel ?>" data-bs-slide="prev"
                                    onclick="event.stopPropagation();">
                                    <span class="bg-rojo rounded-circle d-flex align-items-center justify-content-center cajaW40 sombra"><i class="bi bi-chevron-left text-white"></i></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button"
                                    data-bs-target="#<?= $idCarousel ?>" data-bs-slide="next"
                                    onclick="event.stopPropagation();">
                                    <span class="bg-rojo rounded-circle d-flex align-items-center justify-content-center cajaW40 sombra"><i class="bi bi-chevron-right text-white"></i></span>
                                    <span class="visually-hidden">Siguiente</span>
                                </button>
                            <?php } ?>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column align-items-center justify-content-center text-secondary text-center bg-light py-5 h-100" >
                            <i class="bi bi-image texto-rojo bg-rojoClaro rounded-3 p-3 mb-2" style="font-size: 1.4rem;"></i>
                            <span class="textoPequeno text-uppercase">Sin imágenes</span>
                        </div>
                    <?php endif; ?>
                </div>
                <!-- Contenido (derecha en md+, debajo en móvil) -->
                <div class="col-lg-7">
                    <div class="p-4 d-flex flex-column h-100">
                        <div class="d-flex align-items-center gap-2 border-bottom pb-2 mb-4">
                            <?= $this->renderUserBlock($receta, false); ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                            <div class="d-flex flex-wrap gap-2 align-items-center">
                                <?php if ($etiquetasArr): ?>
                                    <?php foreach ($visibles as $etq) { ?>
                                        <span class="badge bg-rojoClaro texto-rojo textoPequeno text-uppercase px-3 py-2 rounded-pill">
                                            <?= htmlspecialchars($etq) ?>
                                        </span>
                                    <?php } ?>
                                    <?php if (count($ocultas) > 0): ?>
                                        <span class="etiquetas-mas-badge badge bg-rojoClaro texto-rojo textoPequeno text-uppercase px-3 py-2 rounded-pill"
                                            tabindex="0" role="button"
                                            data-bs-toggle="popover" data-bs-placement="top" data-bs-html="true" data-bs-custom-class="etiquetas-tooltip"
                                            data-bs-content='<?= htmlspecialchars($htmlOcultas, ENT_QUOTES) ?>'
                                            onclick="event.stopPropagation();">+<?= count($ocultas) ?></span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        <?php if (!empty($esFit)) 
                              { ?>
                            <span class="badge bg-verdeClaro texto-verde textoPequeno text-uppercase px-3 py-2 rounded-pill">
                                <i class="bi bi-heart-pulse me-1"></i>FIT
                            </span>
                        <?php } ?>
                        </div>
                        <!-- Título -->
                        <h3 class="titulo letraRomana fw-bold m-0"><?= htmlspecialchars($receta['Titulo']) ?></h3>
                        <!-- Descripción -->
                        <p class="texto text-secondary mt-2 mb-3">
                            <?= nl2br(htmlspecialchars($receta['Descripcion'] ?? 'Sin descripción.')) ?>
                        </p>
                        <!-- Meta pills -->
                        <?php if ($tiempo || $porciones): ?>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="border bg-light text-secondary texto rounded-pill px-3 py-2">
                                    <i class="bi bi-clock me-1 texto-rojo"></i><?= (int)($tiempo ?? 0) ?> min
                                </span>
                                <span class="border bg-light text-secondary texto rounded-pill px-3 py-2">
                                    <i class="bi bi-people me-1 texto-rojo"></i><?= (int)($porciones ?? 0) ?> porciones
                                </span>
                            </div>
                        <?php endif; ?>
                        <!-- Nutrición (solo recetas FIT con datos) -->
                        <div class="row g-2 mb-3">
                            <div class="col-6 col-md-3">
                                <div class="bg-light border rounded-3 px-3 py-2">
                                    <div class="textoPequeno text-secondary text-uppercase">Calorías</div>
                                    <div class="texto fw-bold texto-rojo m-0">
                                        <?= htmlspecialchars($formatearNutricion($receta['Calorias']) ?? 0) ?>
                                        <span class="textoPequeno text-secondary fw-normal">kcal</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light border rounded-3 px-3 py-2">
                                    <div class="textoPequeno text-secondary text-uppercase">Proteína</div>
                                    <div class="texto fw-bold texto-rojo m-0">
                                        <?= htmlspecialchars($formatearNutricion($receta['Proteina']) ?? 0) ?>
                                        <span class="textoPequeno text-secondary fw-normal">g</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light border rounded-3 px-3 py-2">
                                    <div class="textoPequeno text-secondary text-uppercase">Carbohidr.</div>
                                    <div class="texto fw-bold texto-rojo m-0">
                                        <?= htmlspecialchars($formatearNutricion($receta['Carbohidratos']) ?? 0) ?>
                                        <span class="textoPequeno text-secondary fw-normal">g</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="bg-light border rounded-3 px-3 py-2">
                                    <div class="textoPequeno text-secondary text-uppercase">Grasas</div>
                                    <div class="texto fw-bold texto-rojo m-0">
                                        <?= htmlspecialchars($formatearNutricion($receta['Grasas']) ?? 0) ?>
                                        <span class="textoPequeno text-secondary fw-normal">g</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center gap-3 px-5 py-3">
                        <button type="button"
                            class="btn p-0 border-0 bg-transparent d-flex align-items-center gap-2 fs-4"
                            data-requiere-login="true"
                            onclick="event.stopPropagation(); FeedApp.toggleLike(<?= (int)$receta['ID_Receta'] ?>, this)">
                            <i class="bi bi-heart-fill like-icon <?= $likeClass ?>"></i>
                            <span class="textoPequeno text-secondary  texto like-count"><?= (int)($receta['Megustas'] ?? 0) ?></span>
                        </button>
                        <div class="d-flex align-items-center">
                            <span class="texto fw-light texto-gris">|</span>
                        </div>
                        <button type="button"
                            class="btn p-0 border-0 bg-transparent d-flex align-items-center gap-2 fs-4"
                            data-requiere-login="true"
                            onclick="event.stopPropagation(); FeedApp.openComments(<?= (int)$receta['ID_Receta'] ?>)">
                            <i class="bi bi-chat text-secondary"></i>
                            <span class="textoPequeno text-secondary texto comment-count-<?= (int)$receta['ID_Receta'] ?>">
                                <?= (int)($receta['TotalComentarios'] ?? 0) ?>
                            </span>
                        </button>
                        <div class="d-flex align-items-center">
                            <span class="texto fw-light texto-gris">|</span>
                        </div>
                        <button type="button"
                            class="btn p-0 border-0 bg-transparent texto-rojo fs-4"
                            data-bs-toggle="modal" data-bs-target="#saveModal"
                            data-id="<?= (int)$receta['ID_Receta'] ?>"
                            data-requiere-login="true">
                            <i class="bi bi-bookmark text-secondary"></i>
                        </button>
                        <div class="d-flex align-items-center">
                            <span class="texto fw-light texto-gris">|</span>
                        </div>
                        <button type="button"
                            class="btn p-0 border-0 bg-transparent texto-rojo fs-4 "
                            data-bs-toggle="modal" data-bs-target="#reportModal"
                            data-report-type="receta" data-id="<?= (int)$receta['ID_Receta'] ?>"
                            data-requiere-login="true">
                            <i class="bi bi-flag text-secondary"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div><?php
        return ob_get_clean();
    }
    public function renderUserBlock($receta, $compact = false)
    {
        ob_start();
        $tieneFoto = !empty($receta['FotoPerfil']);?>
        <a href="/pages/perfil/<?= (int)$receta['ID_Creador'] ?>"
            class="text-decoration-none d-flex align-items-center gap-2 mt-auto text-dark mt-2 mb-2"
            onclick="event.stopPropagation();">
            <?php if ($tieneFoto): ?>
                <img src="<?= htmlspecialchars($receta['FotoPerfil']) ?>"
                    class="circuloPerfil flex-shrink-0"
                    alt="Foto de perfil">
            <?php else: ?>
                <div class="circuloPerfil rounded-circle bg-rojo d-flex align-items-center justify-content-center text-white fw-semibold text-uppercase texto">
                    <?= strtoupper(htmlspecialchars(substr($receta['Nombre'] ?? '?', 0, 2))) ?>
                </div>
            <?php endif; ?>
            <div class="d-flex flex-column text-truncate">
                <div class="texto fw-semibold m-0">
                    @<?= htmlspecialchars($receta['Nombre'] ?? 'Desconocido') ?>
                </div>
                <?php if (!empty($receta['FechaCreacion'])) { ?>
                    <div class="textoPequeno text-secondary">
                        Creada el <?= htmlspecialchars(date('d/m/Y', strtotime((string)$receta['FechaCreacion']))) ?>
                    </div>
                <?php } ?>
            </div>
        </a><?php
        return ob_get_clean();
    }
    public function renderModals($etiquetas)
    {?>
        <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-md">
                <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                    <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                        <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                        <!-- titulo -->
                        <div class="d-flex gap-3 align-items-center mb-4">
                            <div><span><i class="bi bi-flag texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                            <div>
                                <h3 class="modal-title fw-bold subtitulo letraRomana m-0" id="reportModalTitle">Reportar</h3>
                                <p class="texto text-secondary m-0">Indica el motivo del reporte para ayudarnos a revisar este contenido.</p>
                            </div>
                        </div>
                        <form id="reportForm">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="id" id="reportId">
                            <input type="hidden" name="type" id="reportType">
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">Motivo del reporte</label>
                                <div class="input-group rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-chat-left-text texto-rojo textoMediano"></i></span>
                                    <select class="form-select texto text-secondary border-start-0 rounded-3 rounded-start-0" name="reason" id="reportReason" required>
                                        <option value="">Selecciona un motivo...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">Detalles adicionales (opcional)</label>
                                <div class="input-group rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-text-paragraph texto-rojo textoMediano"></i></span>
                                    <textarea class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" name="details" rows="2" placeholder="Describe el problema con más detalle..."></textarea> 
                                </div>
                            </div>
                            <div class="alert alert-danger d-none" id="reportError"></div>
                            <div class="alert alert-success d-none" id="reportSuccess"></div>
                            <div class="modal-footer border-0 p-0 pb-4">
                                <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4" id="btnSubmitReceta">Enviar reporte</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="saveModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered ">
                <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                    <!-- boton -->
                    <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                        
                        <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                        <!-- titulo -->
                        <div class="d-flex gap-3 align-items-center mb-4">
                            <div><span><i class="bi bi-egg-fried texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                            <div>
                                <h3 class="modal-title fw-bold subtitulo letraRomana m-0">Guardar receta</h3>
                                <p class="texto text-secondary m-0" >Guarda esta receta en una colección para encontrarla fácilmente más tarde.</p>
                            </div>
                        </div>
                        <form id="saveForm">
                            <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                            <input type="hidden" name="id_receta" id="saveRecipeId">
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">Selecciona las colecciones</label>
                                <div id="cols-list" class="mb-3" style="max-height: 200px; overflow-y: auto;">
                                    <div class="text-secondary texto text-center">Cargando...</div>
                                </div>
                            </div>
                            <div class="alert alert-danger d-none" id="saveError"></div>
                            <div class="alert alert-success d-none" id="saveSuccess"></div>
                            <div class="modal-footer border-0 p-0 pb-4">
                                <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="/global/save.js"></script>
        <script src="/global/report.js"></script>
        
        <?php require ROOT_PATH . '/global/modalConfirmacionGenerico.php'; ?>
        <?php
    }
}
