<?php declare(strict_types=1); ?>
<?php
    
    $formatearNutricion = static function ($valor): string 
    {
        $valor = is_numeric($valor) ? (float)$valor : 0.0;
        return fmod($valor, 1.0) === 0.0
            ? (string)(int)$valor
            : rtrim(rtrim(number_format($valor, 2, '.', ''), '0'), '.');
    };
    $imagenes        = $receta['Imagenes'] ?? [];
    $etiquetasReceta = $receta['etiquetas'] ?? [];
    $ingredientes    = $receta['ingredientes'] ?? [];
    $pasos           = $receta['Pasos'] ?? [];

    
    $calcularMacrosIngrediente = static function (array $ing): array
    {
        $cantidad = is_numeric($ing['Cantidad'] ?? null) ? (float)$ing['Cantidad'] : null;

        if ($cantidad === null || $cantidad <= 0) {
            return ['calorias' => null, 'proteina' => null, 'carbohidratos' => null, 'grasas' => null, 'valido' => false];
        }

        $factor = $cantidad / 100.0;

        return [
            'calorias'      => (float)($ing['Calorias']      ?? 0) * $factor,
            'proteina'      => (float)($ing['Proteina']      ?? 0) * $factor,
            'carbohidratos' => (float)($ing['Carbohidratos'] ?? 0) * $factor,
            'grasas'        => (float)($ing['Grasas']        ?? 0) * $factor,
            'valido'        => true,
        ];
    };

    $macros = ['calorias' => 0.0, 'proteina' => 0.0, 'carbohidratos' => 0.0, 'grasas' => 0.0];
    $macrosValidos = false;

    foreach ($ingredientes as $ing)
    {
        $m = $calcularMacrosIngrediente($ing);
        if ($m['valido']) {
            $macros['calorias']      += $m['calorias'];
            $macros['proteina']      += $m['proteina'];
            $macros['carbohidratos'] += $m['carbohidratos'];
            $macros['grasas']        += $m['grasas'];
            $macrosValidos = true;
        }
    }

    $hayMacros = $macrosValidos;
?>
<section class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
            <button id="btn-volver"
               class="border text-secondary p-2 bg-white texto rounded-4 px-3 text-decoration-none cursor-pointer">
                <i class="bi bi-arrow-left me-1"></i>Volver
            </button>
            <div>
                <h2 class="subtitulo letraRomana fw-bold m-0">Detalle de receta</h2>
                <p class="texto text-secondary m-0">Vista completa con ingredientes, pasos y nutrición.</p>
            </div>
        </div>
        <?php if((int)$receta['EsBase']){ ?>
        <div class="d-flex gap-2">
            <button type="button"
                    class="border text-secondary p-2 bg-white texto rounded-4 px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#modalCrearReceta"
                    data-mode="editar"
                    data-id="<?= (int)$receta['ID_Receta'] ?>">
                <i class="bi bi-pencil-square me-1"></i>Editar
            </button>
            <button type="button"
                    class="bg-rojo text-white border-0 p-2 texto rounded-4 px-3"
                    data-bs-toggle="modal"
                    data-bs-target="#eliminarRecetaModal"
                    data-id="<?= (int)$receta['ID_Receta'] ?>"
                    data-nombre="<?php echo htmlspecialchars($receta['Titulo'], ENT_QUOTES, 'UTF-8') ?>">
                <i class="bi bi-trash me-1"></i>Eliminar
            </button>
        </div>
        <?php } ?>
    </div>
    
    <div class="card border-0 sombra rounded-4 mb-4 overflow-hidden feed-card">
        <div class="row g-0 align-items-stretch">
          
            <div class="col-lg-5">
                <?php if (!empty($imagenes)) 
                      { ?>
                        <div id="carouselReceta" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4500" style="height: 360px;">
                            <div class="carousel-inner bg-light" style="height: 360px;">
                                <?php foreach ($imagenes as $idx => $img) { ?>
                                    <div class="carousel-item <?= $idx === 0 ? 'active' : '' ?>" style="height: 360px;">
                                        <img src="<?= htmlspecialchars($img) ?>"
                                            class="d-block w-100"
                                            style="height: 360px; object-fit: cover;"
                                            alt="Imagen <?= $idx + 1 ?> de <?= htmlspecialchars($receta['Titulo']) ?>"
                                            onerror="this.onerror=null;this.parentNode.innerHTML='<div class=\'d-flex flex-column align-items-center justify-content-center text-secondary text-center w-100\' style=\'height:360px;\'><i class=\'bi bi-image-alt texto-rojo bg-rojoClaro rounded-3 p-3 mb-2\' style=\'font-size:1.4rem;\'></i><span class=\'textoPequeno text-uppercase\'>Imagen no disponible</span></div>';">
                                    </div>
                                <?php } ?>
                            </div>
                            <?php if (count($imagenes) > 1) { ?>
                                <button class="carousel-control-prev" type="button" data-bs-target="#carouselReceta" data-bs-slide="prev" style="width: 12%;">
                                    <span class="bg-rojo rounded-circle d-flex align-items-center justify-content-center cajaW40 sombra">
                                        <i class="bi bi-chevron-left text-white"></i>
                                    </span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#carouselReceta" data-bs-slide="next" style="width: 12%;">
                                    <span class="bg-rojo rounded-circle d-flex align-items-center justify-content-center cajaW40 sombra">
                                        <i class="bi bi-chevron-right text-white"></i>
                                    </span>
                                    <span class="visually-hidden">Siguiente</span>
                                </button>
                                <div class="carousel-indicators m-0 mb-2">
                                    <?php foreach ($imagenes as $idx => $_) { ?>
                                        <button type="button" data-bs-target="#carouselReceta" data-bs-slide-to="<?= $idx ?>"
                                                class="<?= $idx === 0 ? 'active' : '' ?>"
                                                aria-current="<?= $idx === 0 ? 'true' : 'false' ?>"
                                                aria-label="Imagen <?= $idx + 1 ?>"></button>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                <?php } 
                      else 
                      { ?>
                        <div class="d-flex flex-column align-items-center justify-content-center text-secondary text-center bg-light py-5" style="height: 360px;">
                            <i class="bi bi-image texto-rojo bg-rojoClaro rounded-3 p-3 mb-2" style="font-size: 1.4rem;"></i>
                            <span class="textoPequeno text-uppercase">Sin imágenes</span>
                        </div>
                <?php } ?>
            </div>
            
            <div class="col-lg-7 d-flex flex-column min-vh-0">
                <div class="p-4 d-flex flex-column h-100 flex-grow-1 overflow-auto">
                    
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-2">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <?php if (!empty($etiquetasReceta))
                            {
                                $maxVisiblesDetalle = 3;
                                $visiblesDetalle   = array_slice($etiquetasReceta, 0, $maxVisiblesDetalle);
                                $restantesDetalle  = array_slice($etiquetasReceta, $maxVisiblesDetalle);
                            ?>
                                <?php foreach ($visiblesDetalle as $etq) { ?>
                                    <span class="badge bg-rojoClaro texto-rojo textoPequeno text-uppercase px-3 py-2 rounded-pill">
                                        <?= htmlspecialchars($etq['Nombre']) ?>
                                    </span>
                                <?php } ?>
                                <?php if (!empty($restantesDetalle)) {
                                    $htmlTooltipDetalle = '<div class="etiquetas-tooltip-inner">';
                                    foreach ($restantesDetalle as $etq) {
                                        $htmlTooltipDetalle .= '<span class="badge bg-rojoClaro texto-rojo textoPequeno text-uppercase px-3 py-2 rounded-pill">'
                                                            . htmlspecialchars($etq['Nombre'])
                                                            . '</span>';
                                    }
                                    $htmlTooltipDetalle .= '</div>';
                                ?>
                                    <span class="etiquetas-mas-badge badge bg-rojoClaro texto-rojo textoPequeno text-uppercase px-3 py-2 rounded-pill"
                                          tabindex="0" role="button"
                                          data-bs-toggle="tooltip"
                                          data-bs-html="true"
                                          data-bs-placement="top"
                                          data-bs-custom-class="etiquetas-tooltip"
                                          data-bs-title="<?= htmlspecialchars($htmlTooltipDetalle, ENT_QUOTES) ?>">
                                        +<?= count($restantesDetalle) ?>
                                    </span>
                                <?php } ?>
                      <?php }
                            else
                            { ?>
                                <span class="text-secondary textoPequeno fst-italic">Sin etiquetas</span>
                      <?php } ?>
                        </div>
                      <?php if (!empty($receta['EsFit'])) 
                            { ?>
                            <span class="badge bg-verdeClaro texto-verde textoPequeno text-uppercase px-3 py-2 rounded-pill">
                                <i class="bi bi-heart-pulse me-1"></i>FIT
                            </span>
                      <?php } ?>
                    </div>
                    
                    <h3 class="titulo letraRomana fw-bold m-0"><?= htmlspecialchars($receta['Titulo']) ?></h3>
                    <p class="texto text-secondary mt-2 mb-3">
                        <?= nl2br(htmlspecialchars($receta['Descripcion'] ?? '')) ?>
                    </p>
                    
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="border bg-light text-secondary texto rounded-pill px-3 py-2">
                            <i class="bi bi-clock me-1 texto-rojo"></i><?= (int)($receta['Tiempo'] ?? 0) ?> min
                        </span>
                        <span class="border bg-light text-secondary texto rounded-pill px-3 py-2">
                            <i class="bi bi-people me-1 texto-rojo"></i><?= (int)($receta['Porciones'] ?? 0) ?> porciones
                        </span>
                        <span class="border bg-light text-secondary texto rounded-pill px-3 py-2">
                            <i class="bi bi-egg-fried me-1 texto-rojo"></i><?= count($ingredientes) ?> ingredientes
                        </span>
                        <span class="border bg-light text-secondary texto rounded-pill px-3 py-2">
                            <i class="bi bi-list-ol me-1 texto-rojo"></i><?= count($pasos) ?> pasos
                        </span>
                    </div>
                    
                    <?php if ($hayMacros) 
                          { ?>
                            <div class="row g-2 mb-3">
                                <div class="col-6 col-md-3">
                                    <div class="bg-light border rounded-3 px-3 py-2">
                                        <div class="textoPequeno text-secondary text-uppercase">Calorías</div>
                                        <div class="texto fw-bold texto-rojo m-0">
                                            <?= htmlspecialchars($formatearNutricion($macros['calorias'])) ?>
                                            <span class="textoPequeno text-secondary fw-normal">kcal</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="bg-light border rounded-3 px-3 py-2">
                                        <div class="textoPequeno text-secondary text-uppercase">Proteína</div>
                                        <div class="texto fw-bold texto-rojo m-0">
                                            <?= htmlspecialchars($formatearNutricion($macros['proteina'])) ?>
                                            <span class="textoPequeno text-secondary fw-normal">g</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="bg-light border rounded-3 px-3 py-2">
                                        <div class="textoPequeno text-secondary text-uppercase">Carbohidr.</div>
                                        <div class="texto fw-bold texto-rojo m-0">
                                            <?= htmlspecialchars($formatearNutricion($macros['carbohidratos'])) ?>
                                            <span class="textoPequeno text-secondary fw-normal">g</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="bg-light border rounded-3 px-3 py-2">
                                        <div class="textoPequeno text-secondary text-uppercase">Grasas</div>
                                        <div class="texto fw-bold texto-rojo m-0">
                                            <?= htmlspecialchars($formatearNutricion($macros['grasas'])) ?>
                                            <span class="textoPequeno text-secondary fw-normal">g</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    <?php } ?>
                    
                    <div class="d-flex align-items-center gap-2 mt-auto border-top pt-3">
                        <?php if (!empty($receta['FotoCreador'])): ?>
                            <img src="<?= htmlspecialchars($receta['FotoCreador']) ?>"
                                 class="circuloPerfil rounded-circle object-fit-cover flex-shrink-0"
                                 alt="Foto de <?= htmlspecialchars($receta['NombreCreador'] ?? '') ?>"
                                 onerror="this.onerror=null; this.replaceWith(Object.assign(document.createElement('span'), {className:'circuloPerfil rounded-circle bg-rojo d-flex align-items-center justify-content-center text-white fw-semibold text-uppercase texto flex-shrink-0', textContent:'<?= strtoupper(substr((string)($receta['NombreCreador'] ?? '?'), 0, 2)) ?>'}))">
                        <?php else: ?>
                            <span class="circuloPerfil rounded-circle bg-rojo d-flex align-items-center justify-content-center text-white fw-semibold text-uppercase texto flex-shrink-0">
                                <?= strtoupper(substr((string)($receta['NombreCreador'] ?? '?'), 0, 2)) ?>
                            </span>
                        <?php endif; ?>
                        <div>
                            <div class="texto fw-semibold m-0">
                                <?= htmlspecialchars($receta['NombreCreador'] ?? 'Desconocido') ?>
                            </div>
                            <?php if (!empty($receta['FechaCreacion'])) { ?>
                                <div class="textoPequeno text-secondary">
                                    Creada el <?= htmlspecialchars(date('d/m/Y', strtotime((string)$receta['FechaCreacion']))) ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        
        <div class="col-lg-5">
            <div class="card border-0 sombra rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span><i class="bi bi-egg-fried texto-rojo iconos bg-rojoClaro p-2 rounded-3"></i></span>
                        <h5 class="tituloPequeno letraRomana fw-bold m-0">Ingredientes</h5>
                    </div>
                <?php if (empty($ingredientes)) 
                      { ?>
                        <div class="text-center py-4">
                            <i class="bi bi-basket text-muted display-6"></i>
                            <p class="text-muted texto m-0 mt-2">Esta receta no tiene ingredientes registrados.</p>
                        </div>
                <?php } 
                      else 
                      { ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($ingredientes as $ing):
                                  $macrosIng = $calcularMacrosIngrediente($ing);
                            ?>
                                    <div class="border rounded-4 p-3">
                                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                            <div class="texto fw-semibold text-dark">
                                                <?= htmlspecialchars($ing['Nombre'] ?? '') ?>
                                            </div>
                                            <?php if (is_numeric($ing['Cantidad'] ?? null) && (float)$ing['Cantidad'] > 0): ?>
                                                <span class="badge bg-rojoClaro texto-rojo textoPequeno rounded-pill px-3 py-2">
                                                    <?= htmlspecialchars((string)(float)$ing['Cantidad']) ?>
                                                    <?= htmlspecialchars(strtolower($ing['Unidad_Base'] ?? '')) ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($macrosIng['valido']): ?>
                                            <div class="row g-2">
                                                <div class="col-6 col-xl-3">
                                                    <div class="bg-light rounded-3 p-2 text-center">
                                                        <div class="textoPequeno text-secondary">Cal</div>
                                                        <div class="texto fw-semibold"><?= htmlspecialchars($formatearNutricion($macrosIng['calorias'])) ?> <span class="textoPequeno text-secondary fw-normal">kcal</span></div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-xl-3">
                                                    <div class="bg-light rounded-3 p-2 text-center">
                                                        <div class="textoPequeno text-secondary">Prot</div>
                                                        <div class="texto fw-semibold"><?= htmlspecialchars($formatearNutricion($macrosIng['proteina'])) ?> <span class="textoPequeno text-secondary fw-normal">g</span></div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-xl-3">
                                                    <div class="bg-light rounded-3 p-2 text-center">
                                                        <div class="textoPequeno text-secondary">HC</div>
                                                        <div class="texto fw-semibold"><?= htmlspecialchars($formatearNutricion($macrosIng['carbohidratos'])) ?> <span class="textoPequeno text-secondary fw-normal">g</span></div>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-xl-3">
                                                    <div class="bg-light rounded-3 p-2 text-center">
                                                        <div class="textoPequeno text-secondary">Gr</div>
                                                        <div class="texto fw-semibold"><?= htmlspecialchars($formatearNutricion($macrosIng['grasas'])) ?> <span class="textoPequeno text-secondary fw-normal">g</span></div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="textoPequeno text-secondary fst-italic">
                                                <i class="bi bi-exclamation-circle me-1"></i>Sin cantidad registrada — no se puede calcular la nutrición.
                                            </div>
                                        <?php endif; ?>
                                    </div>
                            <?php endforeach; ?>
                        </div>
                <?php } ?>
                </div>
            </div>
        </div>
        
        <div class="col-lg-7">
            <div class="card border-0 sombra rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <span><i class="bi bi-list-ol texto-rojo iconos bg-rojoClaro p-2 rounded-3"></i></span>
                        <h5 class="tituloPequeno letraRomana fw-bold m-0">Pasos de preparación</h5>
                    </div>
                    <?php if (empty($pasos)) 
                          { ?>
                            <div class="text-center py-4">
                                <i class="bi bi-journal-text text-muted display-6"></i>
                                <p class="text-muted texto m-0 mt-2">Esta receta no tiene pasos registrados.</p>
                            </div>
                    <?php } 
                          else 
                          { ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($pasos as $indice => $paso) 
                                      { ?>
                                        <div class="d-flex gap-3 border rounded-4 p-3">
                                            <span class="bg-rojo text-white rounded-circle d-flex align-items-center justify-content-center cajaW40 fw-bold flex-shrink-0">
                                                <?= $indice + 1 ?>
                                            </span>
                                            <p class="texto text-dark m-0 align-self-center text-break">
                                                <?= nl2br(htmlspecialchars((string)$paso)) ?>
                                            </p>
                                        </div>
                                <?php } ?>
                            </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
   
    require __DIR__ . "/../Components/receta/modalCrear.php";
    require __DIR__ . "/../Components/receta/modalConfirmacionEliminacion.php";
?>  
<script src="../../assets/receta/receta.js"></script>

