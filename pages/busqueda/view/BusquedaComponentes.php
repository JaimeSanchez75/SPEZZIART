<?php
declare(strict_types=1);

class BusquedaComponentes
{
    private function limitarTextoMostrar(?string $texto, int $limite = 40): string
    {
        $texto = trim((string)$texto);
        if (mb_strlen($texto, 'UTF-8') <= $limite) {return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');}
        return htmlspecialchars(mb_substr($texto, 0, $limite, 'UTF-8') . '...', ENT_QUOTES, 'UTF-8');
    }
    public function renderRecipeCardGrid($receta)
    {
        ob_start();

        $imagenes = !empty($receta['Imagen']) ? explode(',', (string)$receta['Imagen']) : [];
        $listaImagenes = array_values(array_filter(array_map('trim', $imagenes)));
        $imagenPortada = $listaImagenes[0] ?? '';

        $titulo = $this->limitarTextoMostrar($receta['Titulo'] ?? '', 45);
        $descripcionCorta = $this->limitarTextoMostrar($receta['Descripcion'] ?? '', 65);
        $usuario = $this->limitarTextoMostrar($receta['NombreUsuario'] ?? $receta['Nombre'] ?? '', 24);
        $tiempo = $receta['Tiempo'] ?? null;
        $likes = (int)($receta['Megustas'] ?? 0);
        $likeClass = !empty($receta['DioLike']) ? 'fill-icon' : 'text-secondary';

        $etiquetasArr = [];
        if (!empty($receta['Etiquetas']) && is_array($receta['Etiquetas'])) {
            $etiquetasArr = $receta['Etiquetas'];
        } elseif (!empty($receta['EtiquetasNombres']) && is_string($receta['EtiquetasNombres'])) {
            $etiquetasArr = explode(',', $receta['EtiquetasNombres']);
        }
        $etiquetasMostrar = array_slice($etiquetasArr, 0, 2);
        $etiquetasOcultas = array_slice($etiquetasArr, 2);
        $totalEtiquetas = count($etiquetasArr);
        $hayMasEtiquetas = $totalEtiquetas > 2;
        $htmlTooltip = '<div class="etiquetas-tooltip-inner">';
        foreach ($etiquetasOcultas as $etq) {
            $htmlTooltip .= '<span class="badge bg-rojoClaro texto-rojo textoPequeno">' . strtoupper(htmlspecialchars(trim((string)$etq))) . '</span>';
        }
        $htmlTooltip .= '</div>';
        ?>
        <div class="card border-0 sombra rounded-4 flex-fill efectoEscala cursor-pointer" data-id="<?= (int)$receta['ID_Receta'] ?>" onclick="abrirRecetaModal(<?= (int)$receta['ID_Receta'] ?>)">
            <?php if (!empty($imagenPortada)) { ?>
                <img src="<?= htmlspecialchars($imagenPortada) ?>" class="card-img-top rounded-top-4 object-fit-cover" style="height: 180px;" alt="Imagen de receta" onerror="this.onerror=null; this.src='/uploads/NoImg.jpg';">
            <?php } else { ?>
                <div class="card-img-top rounded-top-4 bg-rojoClaro texto-rojo d-flex align-items-center justify-content-center" style="height: 180px;">
                    <i class="bi bi-image" style="font-size: 3rem;"></i>
                </div>
            <?php } ?>
            <div class="card-body px-4 d-flex flex-column justify-content-between">
                <div>
                    <div class="d-flex justify-content-between mb-2">
                        <div class="d-flex gap-1 flex-wrap align-items-center">
                            <?php foreach ($etiquetasMostrar as $tag): ?>
                                <div class="badge bg-rojoClaro texto-rojo textoPequeno"><?= strtoupper($this->limitarTextoMostrar((string)$tag, 18)) ?></div>
                            <?php endforeach; ?>
                            <?php if ($hayMasEtiquetas): ?>
                                <span class="etiquetas-mas-badge badge bg-rojoClaro texto-rojo textoPequeno"
                                      tabindex="0" role="button"
                                      data-bs-toggle="tooltip"
                                      data-bs-html="true"
                                      data-bs-placement="top"
                                      data-bs-custom-class="etiquetas-tooltip"
                                      data-bs-container="body"
                                      data-bs-title="<?= htmlspecialchars($htmlTooltip, ENT_QUOTES) ?>"
                                      onclick="event.stopPropagation()">+<?= $totalEtiquetas - 2 ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($tiempo): ?>
                            <span class="text-secondary texto"><i class="bi bi-clock"></i> <?= htmlspecialchars((string)$tiempo) ?> min</span>
                        <?php endif; ?>
                    </div>
                    <h5 class="card-title tituloPequeno letraRomana fw-bold mt-0 mb-0" data-buscado="true"><?= $titulo ?></h5>
                    <p class="card-text text-secondary textoPequeno m-0"><?= $descripcionCorta ?></p>
                </div>
                <div class="d-flex align-items-center mt-3 border-top pt-2">
                    <?php
                    $fotoBusqueda = $receta['FotoPerfil'] ?? '';
                    if (!empty($fotoBusqueda)): ?>
                        <img src="<?= htmlspecialchars($fotoBusqueda) ?>"
                             class="me-2 rounded-circle flex-shrink-0"
                             style="width:28px;height:28px;object-fit:cover;"
                             alt="@<?= $usuario ?>"
                             onerror="this.onerror=null;this.src='/uploads/NoImg.jpg'">
                    <?php else: ?>
                        <i class="bi bi-person me-2 rounded-circle p-1 border px-2 bg-light texto text-secondary"></i>
                    <?php endif; ?>
                    <span class="text-secondary texto">Por <span data-buscado="true"><?= $usuario ?></span></span>
                    <button type="button" class="ms-auto border-0 bg-transparent text-secondary p-0 texto d-flex align-items-center gap-1" onclick="event.stopPropagation(); FeedApp.toggleLike(<?= (int)$receta['ID_Receta'] ?>, this)">
                        <span class="material-symbols-outlined like-icon <?= $likeClass ?>">favorite</span>
                        <span class="like-count"><?= $likes ?></span>
                    </button>
                </div>
                <div class="text-end card-img-overlay p-3" style="pointer-events: none;">
                    <button type="button" class="border-0 bg-transparent p-0" style="pointer-events: auto;" onclick="event.stopPropagation(); if(!window.isLoggedIn) { window.location.href='/pages/login'; return; }" data-bs-toggle="modal" data-bs-target="#saveModal" data-id="<?= (int)$receta['ID_Receta'] ?>">
                        <i class="bi bi-bookmark bg-white sombra rounded-circle p-1 px-2 pt-2 text-muted"></i>
                    </button>
                    <button type="button" class="border-0 bg-transparent p-0 ms-1" style="pointer-events: auto;" onclick="event.stopPropagation(); if(!window.isLoggedIn) { window.location.href='/pages/login'; return; }" data-bs-toggle="modal" data-bs-target="#reportModal" data-report-type="receta" data-id="<?= (int)$receta['ID_Receta'] ?>">
                        <i class="bi bi-flag bg-white sombra rounded-circle p-1 px-2 pt-2 text-muted"></i>
                    </button>
                    <?php if (isset($_GET['fit']) && isset($_GET['meal'])): ?>
                        <a href="/fit/addRecipe?meal=<?= urlencode($_GET['meal']) ?>&recipe=<?= (int)$receta['ID_Receta'] ?>"
                           class="bg-rojo text-white border-0 p-2 texto rounded-4 px-3 text-decoration-none"
                           style="pointer-events:auto;">
                            Añadir a <?= htmlspecialchars(ucfirst($_GET['meal'])) ?>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function renderUserSearchCard($user)
    {
        ob_start();
        $fotoPerfil = $user['FotoPerfil'] ?? '';
        if (!empty($fotoPerfil) && file_exists($_SERVER['DOCUMENT_ROOT'] . $fotoPerfil)) {
            $foto = htmlspecialchars($fotoPerfil);
        } else {
            $foto = '/uploads/NoImg.jpg';
        }
        $nombre = $this->limitarTextoMostrar($user['Nombre'] ?? '', 28);
        $recetasCount = $user['TotalRecetas'] ?? 0;
        $seguidoresCount = $user['Seguidores'] ?? 0;
        ?>
        <div class="user-search-card d-flex align-items-center p-3 bg-white rounded-4 sombra border-0 mb-3 efectoEscala" style="grid-column: 1 / -1;">
            <img src="<?= $foto ?>" class="rounded-circle me-3 flex-shrink-0" style="width: 60px; height: 60px; object-fit: cover;" alt="@<?= $nombre ?>">
            <div class="flex-grow-1 min-width-0">
                <h5 class="mb-1 fw-bold texto-rojo">@<?= $nombre ?></h5>
                <div class="d-flex gap-3 mt-1">
                    <small class="text-muted"><?= $recetasCount ?> receta(s)</small>
                    <small class="text-muted"><?= $seguidoresCount ?> seguidor(es)</small>
                </div>
            </div>
            <a href="/pages/perfil/<?= (int)$user['ID_Usuario'] ?>" class="border text-secondary p-2 bg-white texto rounded-4 px-3 text-decoration-none ms-3 flex-shrink-0">
                Ver perfil
            </a>
        </div>
        <?php
        return ob_get_clean();
    }

    public function renderUserBlock($receta, $compact = false)
    {
        ob_start();
        ?>
        <a href="/pages/perfil/<?php echo (int)$receta['ID_Creador']; ?>" class="text-decoration-none d-flex align-items-center gap-2 overflow-hidden texto-rojo">
            <div class="rounded-circle bg-rojo d-flex align-items-center justify-content-center text-white flex-shrink-0"
                 style="width: <?php echo $compact ? '28px' : '35px'; ?>; height: <?php echo $compact ? '28px' : '35px'; ?>;">
                <i class="bi bi-person text-white" style="font-size: <?php echo $compact ? '14px' : '18px'; ?>;"></i>
            </div>
            <div class="d-flex flex-column text-truncate texto-rojo">
                <span class="fw-bold small text-truncate" style="line-height:1;">@<?php echo htmlspecialchars((string)$receta['Nombre']); ?></span>
                <?php if (!$compact): ?>
                    <small class="texto-rojo text-truncate"><?php echo htmlspecialchars((string)($receta['Apodo'] ?? '')); ?></small>
                <?php endif; ?>
            </div>
        </a>
        <?php
        return ob_get_clean();
    }

    public function renderModals($etiquetas)
    {
        ?>
        <div class="modal fade" id="reportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                    <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
                    <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative">
                        <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form id="reportForm">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="id" id="reportId">
                        <input type="hidden" name="type" id="reportType">
                        <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                            <div class="d-flex gap-3 align-items-center mb-4">
                                <div><span><i class="bi bi-flag texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                                <div>
                                    <h3 class="modal-title fw-bold subtitulo letraRomana m-0" id="reportModalTitle">Reportar</h3>
                                    <p class="texto text-secondary m-0">Indica el motivo del reporte.</p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">Motivo del reporte</label>
                                <div class="input-group rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-shield-exclamation texto-rojo textoMediano"></i></span>
                                    <select class="form-select texto text-secondary border-start-0 rounded-3 rounded-start-0" name="reason" id="reportReason" required><option value="">Selecciona un motivo...</option></select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">Detalles adicionales (opcional)</label>
                                <div class="input-group rounded-3 overflow-hidden">
                                    <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0 align-items-start pt-2"><i class="bi bi-text-paragraph texto-rojo textoMediano"></i></span>
                                    <textarea class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" name="details" rows="2" placeholder="Describe el problema con más detalle..."></textarea>
                                </div>
                            </div>
                            <div class="alert alert-danger d-none" id="reportError"></div>
                            <div class="alert alert-success d-none" id="reportSuccess"></div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 position-relative z-1">
                            <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4">Enviar reporte</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal fade" id="saveModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                    <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
                    <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative">
                        <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <form id="saveForm">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        <input type="hidden" name="id_receta" id="saveRecipeId">
                        <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                            <div class="d-flex gap-3 align-items-center mb-4">
                                <div><span><i class="bi bi-bookmark texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                                <div>
                                    <h3 class="modal-title fw-bold subtitulo letraRomana m-0">Guardar receta</h3>
                                    <p class="texto text-secondary m-0">Selecciona dónde guardar esta receta.</p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-dark fw-semibold">Selecciona las colecciones</label>
                                <div id="cols-list" class="border rounded-4 p-2" style="max-height: 200px; overflow-y: auto;">
                                    <div class="text-muted text-center">Cargando...</div>
                                </div>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="guardarDefecto" checked>
                                <label class="form-check-label texto text-secondary" for="guardarDefecto">Guardar también en mi colección por defecto</label>
                            </div>
                            <div class="alert alert-danger d-none" id="saveError"></div>
                            <div class="alert alert-success d-none" id="saveSuccess"></div>
                        </div>
                        <div class="modal-footer border-0 px-4 pb-4 position-relative z-1">
                            <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <script src="/global/save.js"></script>
        <?php
    }
}
