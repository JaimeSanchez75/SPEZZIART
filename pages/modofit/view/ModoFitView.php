<?php
// ==========================
// PROGRESOS
// ==========================
$porcentajeCal = ($objetivoCalorias > 0)
    ? min(100, ($total['calorias'] / $objetivoCalorias) * 100)
    : 0;

$claseCal = $porcentajeCal < 70 ? "bg-verde" : ($porcentajeCal < 100 ? "bg-naranja" : "bg-rojo");

$protPerc = ($proteinasObjetivo > 0) ? min(200, ($total['proteinas'] / $proteinasObjetivo) * 100) : 0;
$carbPerc = ($carbsObjetivo > 0)     ? min(200, ($total['carbohidratos']     / $carbsObjetivo)     * 100) : 0;
$fatPerc  = ($grasasObjetivo > 0)    ? min(200, ($total['grasas']    / $grasasObjetivo)    * 100) : 0;

// comidas dinámicas
$tiposBase = ['desayuno','comida','cena','snack','extra1','extra2'];
$tipos = array_slice($tiposBase, 0, $plan['NumComidas']);
?>
<!DOCTYPE html>
<html lang="es" data-bs-theme="<?= $_SESSION['user']['tema'] ?? 'sistema' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modo Fit | SPEZZIART</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/global/styles/navbar.css">
    <link rel="stylesheet" href="/global/styles/modofit.css">
    <script>window.userTheme = '<?= $_SESSION['user']['tema'] ?? 'sistema' ?>';</script>
    <script src="/global/js/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="w-100">

<!-- padding-bottom para que la navbar inferior no tape el contenido -->
<div class="container-fluid px-3 px-md-4 pt-5" style="padding-bottom: 90px;">

    <!-- =============================== -->
    <!-- CABECERA DE PÁGINA              -->
    <!-- =============================== -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-3">
                <p class="titulo letraRomana fw-bold texto-rojo m-0">SPEZZIART</p>
                <span class="subtitulo letraRomana fw-bold text-secondary">|</span>
                <h2 class="subtitulo letraRomana fw-bold m-0">Modo Fit</h2>
            </div>
            <p class="texto text-secondary m-0">Planifica tus objetivos, comidas y macros diarios.</p>
        </div>
        <button class="btn btn-primary texto fw-medium px-4" onclick="savePlan()">
            <i class="bi bi-bookmark-plus me-2"></i>Guardar plan
        </button>
    </div>

    <!-- =============================== -->
    <!-- TARJETAS DE DATOS CORPORALES    -->
    <!-- =============================== -->
    <div class="row g-3 mb-4">

        <!-- Peso -->
        <div class="col-12 col-sm-6 col-lg">
            <div class="p-4 sombra border rounded-4 bg-white efectoEscala h-100 d-flex flex-column gap-2">
                <div><span class="bg-rojoClaro p-3 rounded-3 d-inline-flex"><i class="bi bi-speedometer2 iconos texto-rojo"></i></span></div>
                <label class="texto text-secondary fw-semibold mb-0">Peso</label>
                <div class="input-group">
                    <input type="number" class="form-control texto bg-light border-0"
                           id="fit-peso" value="<?= $user['Peso'] ?? 0 ?>"
                           min="55" max="300" oninput="debounceFitUpdate()">
                    <span class="input-group-text bg-rojoClaro border border-rojo texto-rojo fw-semibold">kg</span>
                </div>
            </div>
        </div>

        <!-- Altura -->
        <div class="col-12 col-sm-6 col-lg">
            <div class="p-4 sombra border rounded-4 bg-white efectoEscala h-100 d-flex flex-column gap-2">
                <div><span class="bg-azulClaro p-3 rounded-3 d-inline-flex"><i class="bi bi-rulers iconos texto-azul"></i></span></div>
                <label class="texto text-secondary fw-semibold mb-0">Altura</label>
                <div class="input-group">
                    <input type="number" class="form-control texto bg-light border-0"
                           id="fit-altura" value="<?= $user['Altura'] ?? 0 ?>"
                           min="100" max="272" oninput="debounceFitUpdate()">
                    <span class="input-group-text bg-rojoClaro border border-rojo texto-rojo fw-semibold">cm</span>
                </div>
            </div>
        </div>

        <!-- Edad -->
        <div class="col-12 col-sm-6 col-lg">
            <div class="p-4 sombra border rounded-4 bg-white efectoEscala h-100 d-flex flex-column gap-2">
                <div><span class="bg-verdeClaro p-3 rounded-3 d-inline-flex"><i class="bi bi-calendar2-heart iconos texto-verde"></i></span></div>
                <label class="texto text-secondary fw-semibold mb-0">Edad</label>
                <div class="input-group">
                    <input type="number" class="form-control texto bg-light border-0"
                           id="fit-edad" value="<?= $user['Edad'] ?? 0 ?>"
                           min="14" max="120" oninput="debounceFitUpdate()">
                    <span class="input-group-text bg-rojoClaro border border-rojo texto-rojo fw-semibold">años</span>
                </div>
            </div>
        </div>

        <!-- Sexo -->
        <div class="col-12 col-sm-6 col-lg">
            <div class="p-4 sombra border rounded-4 bg-white efectoEscala h-100 d-flex flex-column gap-2">
                <div><span class="bg-moradoClaro p-3 rounded-3 d-inline-flex"><i class="bi bi-person iconos texto-morado"></i></span></div>
                <label class="texto text-secondary fw-semibold mb-0">Sexo</label>
                <select id="fit-sexo" class="form-select texto bg-light border-0" onchange="debounceFitUpdate()">
                    <option value="hombre" <?= ($user['Sexo'] ?? '') === 'hombre' ? 'selected' : '' ?>>Hombre</option>
                    <option value="mujer"  <?= ($user['Sexo'] ?? '') === 'mujer'  ? 'selected' : '' ?>>Mujer</option>
                </select>
            </div>
        </div>

        <!-- Actividad -->
        <div class="col-12 col-sm-6 col-lg">
            <div class="p-4 sombra border rounded-4 bg-white efectoEscala h-100 d-flex flex-column gap-2">
                <div><span class="bg-naranjaClaro p-3 rounded-3 d-inline-flex"><i class="bi bi-activity iconos texto-naranja"></i></span></div>
                <label class="texto text-secondary fw-semibold mb-0">Actividad</label>
                <select id="fit-actividad" class="form-select texto bg-light border-0" onchange="debounceFitUpdate()">
                    <?php foreach(['sedentario','ligero','moderado','activo','muy_activo'] as $act): ?>
                        <option value="<?= $act ?>" <?= ($user['NivelActividad'] ?? '') === $act ? 'selected' : '' ?>>
                            <?= ucfirst(str_replace('_', ' ', $act)) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

    </div><!-- /datos corporales -->

    <!-- =============================== -->
    <!-- LAYOUT PRINCIPAL (2 columnas)   -->
    <!-- =============================== -->
    <div class="row g-4">

        <!-- ========== COLUMNA IZQUIERDA ========== -->
        <div class="col-12 col-xl-8 d-flex flex-column gap-3">

            <!-- OBJETIVOS -->
            <div class="p-4 sombra border rounded-4 bg-white">
                <h5 class="fw-semibold mb-1"><i class="bi bi-bullseye texto-rojo me-2"></i>Objetivo</h5>
                <p class="texto text-secondary mb-3">Elige qué quieres conseguir con tu plan</p>
                <div class="row g-3">
                    <?php foreach([
                        'definicion'    => ['Definición',    'Perder grasa y mantener músculo'],
                        'volumen'       => ['Volumen',        'Ganar masa muscular'],
                        'mantenimiento' => ['Mantenimiento', 'Equilibrio y estabilidad'],
                    ] as $key => [$title, $desc]):
                        $isActive = ($plan['Objetivo'] == $key);
                    ?>
                        <div class="col-12 col-md">
                            <div class="fit-goal-card p-4 sombra border rounded-4 efectoEscala h-100 d-flex align-items-start gap-3 <?= $isActive ? 'active bg-rojo text-white border-rojo' : 'bg-white' ?>"
                                 data-obj="<?= $key ?>"
                                 onclick="changeObjetivo('<?= $key ?>')"
                                 style="cursor:pointer;">
                                <span class="bg-rojoClaro rounded-3 d-inline-flex align-items-center justify-content-center flex-shrink-0 cajaW40">
                                    <i class="bi bi-bullseye texto-rojo iconos"></i>
                                </span>
                                <div class="min-w-0">
                                    <strong class="fw-bold textoMediano d-block"><?= $title ?></strong>
                                    <p class="texto text-secondary mb-0"><?= $desc ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- INTENSIDAD -->
            <div class="p-4 sombra border rounded-4 bg-white">
                <h5 class="fw-semibold mb-1"><i class="bi bi-sliders texto-rojo me-2"></i>Nivel de Intensidad</h5>
                <p class="texto text-secondary mb-3">Ajusta qué tan agresivo será tu objetivo nutricional</p>
                <div class="row g-3">
                    <?php foreach([
                        'muy_leve'     => ['Muy leve',  'Cambio progresivo y suave'],
                        'leve'         => ['Leve',      'Pequeño déficit o superávit'],
                        'moderado'     => ['Moderado',  'Equilibrio ideal'],
                        'agresivo'     => ['Agresivo',  'Resultados más rápidos'],
                        'muy_agresivo' => ['Extremo',   'Máxima intensidad'],
                    ] as $val => [$label, $desc]):
                        $isActive = (($plan['Intensidad'] ?? 'moderado') == $val);
                    ?>
                        <div class="col-12 col-md">
                            <div class="fit-intensity-card p-3 sombra border rounded-4 efectoEscala h-100 <?= $isActive ? 'active bg-rojo text-white border-rojo' : 'bg-white' ?>"
                                 data-int="<?= $val ?>"
                                 onclick="selectIntensidad('<?= $val ?>')"
                                 style="cursor:pointer;">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="bg-rojoClaro rounded-3 d-inline-flex align-items-center justify-content-center flex-shrink-0 cajaW40">
                                        <i class="bi bi-sliders texto-rojo iconos"></i>
                                    </span>
                                    <strong class="fw-bold texto d-block"><?= $label ?></strong>
                                </div>
                                <p class="textoPequeno text-secondary mb-0"><?= $desc ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Nº COMIDAS -->
            <div class="p-4 sombra border rounded-4 bg-white">
                <h5 class="fw-semibold mb-1"><i class="bi bi-calendar3 texto-rojo me-2"></i>Número de comidas</h5>
                <p class="texto text-secondary mb-3">Distribuye tu ingesta diaria según tu rutina</p>
                <div class="row g-3">
                    <?php for($i = 3; $i <= 6; $i++): ?>
                        <div class="col-6 col-md-3">
                            <div class="fit-meal-card text-center p-4 sombra border rounded-4 efectoEscala <?= ($plan['NumComidas'] == $i) ? 'active bg-rojo text-white border-rojo' : 'bg-white' ?>"
                                 onclick="setMeals(<?= $i ?>)"
                                 style="cursor:pointer;">
                                <strong class="fw-bold textoMediano d-block"><?= $i ?></strong>
                                <span class="texto">comidas</span>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- GRID DE COMIDAS DEL DÍA -->
            <div>
                <h5 class="fw-semibold mb-3"><i class="bi bi-grid texto-rojo me-2"></i>Mis comidas del día</h5>
                <div class="row g-3">
                    <?php foreach ($tipos as $tipo):
                        $meal = $meals[$tipo] ?? null;
                    ?>
                        <div class="col-12 col-md-6 col-xxl-4" id="meal-<?= $tipo ?>">
                            <div class="sombra border rounded-4 h-100 bg-white efectoEscala overflow-hidden">

                                <!-- Cabecera tarjeta comida -->
                                <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
                                    <span class="badge bg-rojoClaro texto-rojo textoPequeno"><?= ucfirst($tipo) ?></span>
                                    <?php if($meal): ?>
                                        <span class="fit-meal-kcal texto text-secondary fw-semibold"><?= round($meal['Calorias']) ?> kcal</span>
                                    <?php endif; ?>
                                </div>

                                <?php if($meal): ?>
                                    <!-- Imagen -->
                                    <?php if(!empty($meal['Imagen'])): ?>
                                        <?php $primeraImagen = trim(explode(',', $meal['Imagen'])[0]); ?>
                                        <div class="position-relative">
                                            <div class="ratio ratio-16x9 bg-light">
                                                <img class="w-100 h-100 object-fit-cover"
                                                     src="<?= $primeraImagen ?>"
                                                     alt="<?= htmlspecialchars($meal['Titulo']) ?>">
                                            </div>
                                            <!-- Botón eliminar -->
                                            <button
                                                    class="btn btn-danger rounded-circle position-absolute top-0 end-0 m-2 d-flex align-items-center justify-content-center"
                                                    style="width:42px;height:42px;z-index:10;"
                                                    onclick="removeMeal('<?= $tipo ?>')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>

                                    <div class="p-3">
                                        <h6 class="tituloPequeno letraRomana fw-bold mb-2"><?= htmlspecialchars($meal['Titulo']) ?></h6>

                                        <!-- Badges macros -->
                                        <div class="d-flex gap-2 flex-wrap mb-3">
                                            <span class="fit-macro protein badge bg-verdeClaro texto-verde textoPequeno">P <?= round($meal['Proteinas']) ?>g</span>
                                            <span class="fit-macro carbs badge bg-azulClaro texto-azul textoPequeno">C <?= round($meal['Carbohidratos']) ?>g</span>
                                            <span class="fit-macro fats badge bg-naranjaClaro texto-naranja textoPequeno">G <?= round($meal['Grasas']) ?>g</span>
                                        </div>

                                        <!-- Cantidad -->
                                        <label class="textoPequeno text-secondary fw-semibold mb-1">Cantidad</label>
                                        <input type="number"
                                               class="fit-qty-input form-control texto bg-light border-0 mb-3"
                                               value="<?= $meal['Cantidad'] ?>"
                                               min="1" max="3000"
                                               data-meal="<?= $tipo ?>">

                                        <button class="btn btn-primary w-100 texto" onclick="goToFeed('<?= $tipo ?>')">
                                            Cambiar receta
                                        </button>
                                    </div>

                                <?php else: ?>
                                    <div class="d-flex flex-column align-items-center justify-content-center text-center p-4 py-5">
                                        <span class="bg-rojoClaro p-3 rounded-3 d-inline-flex mb-3">
                                            <i class="bi bi-journal-plus iconos texto-rojo"></i>
                                        </span>
                                        <p class="texto text-secondary mb-3">Sin receta asignada</p>
                                        <button class="btn btn-outline-danger texto px-4" onclick="goToFeed('<?= $tipo ?>')">
                                            + Añadir receta
                                        </button>
                                    </div>
                                <?php endif; ?>

                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

        </div><!-- /col-xl-8 izquierda -->

        <!-- ========== COLUMNA DERECHA ========== -->
        <div class="col-12 col-xl-4">
            <div class="p-4 sombra border rounded-4 bg-white">

                <h5 class="fw-semibold mb-1"><i class="bi bi-bar-chart texto-rojo me-2"></i>Resumen nutricional</h5>
                <p class="texto text-secondary mb-4">Seguimiento diario de objetivos</p>

                <!-- Calorías -->
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong class="texto fw-semibold">Calorías</strong>
                    <span class="textoPequeno text-secondary" id="calories-text">
                        <?= round($total['calorias']) ?> / <?= round($objetivoCalorias) ?> kcal
                    </span>
                </div>
                <div class="progress rounded-pill bg-grisClaro mb-4" style="height:8px;">
                    <div class="progreso progress-bar <?= $claseCal ?>"
                         style="width:<?= $porcentajeCal ?>%; height:8px;"></div>
                </div>

                <!-- Macros donuts -->
                <div class="d-flex flex-column gap-3">

                    <!-- Proteínas -->
                    <div class="p-3 rounded-4 bg-white">
                        <div class="texto text-secondary fw-semibold mb-2">Proteínas</div>
                        <div class="grafica position-relative">
                        <img
                            src="/global/fit/protein2.png"
                            class="fit-character protein-char"
                            alt="Protein">

                        <canvas id="proteinChart"></canvas>
                        <div class="fit-chart-center">
                            <h5 class="fw-bold m-0" id="protein-percent"><?= round($protPerc) ?>%</h5>
                            <span class="textoPequeno text-secondary" id="protein-text">
                                <?= round($total['proteinas']) ?>g / <?= round($proteinasObjetivo) ?>g
                            </span>
                        </div>
                        </div>
                    </div>

                    <!-- Carbohidratos -->
                    <div class="p-3 rounded-4 bg-white">
                        <div class="texto text-secondary fw-semibold mb-2">Carbohidratos</div>
                        <div class="grafica position-relative">
                        <img
                            src="/global/fit/carbs2.png"
                            class="fit-character carbs-char"
                            alt="Carbs">
                        <canvas id="carbsChart"></canvas>
                        <div class="fit-chart-center">
                            <h5 class="fw-bold m-0" id="carbs-percent"><?= round($carbPerc) ?>%</h5>
                            <span class="textoPequeno text-secondary" id="carbs-text">
                                <?= round($total['carbohidratos']) ?>g / <?= round($carbsObjetivo) ?>g
                            </span>
                        </div>
                        </div>
                    </div>

                    <!-- Grasas -->
                    <div class="p-3 rounded-4 bg-white">
                        <div class="texto text-secondary fw-semibold mb-2">Grasas</div>
                        <div class="grafica position-relative">
                        <img
                            src="/global/fit/fats.png"
                            class="fit-character fat-char"
                            alt="Fat">
                        <canvas id="fatChart"></canvas>
                        <div class="fit-chart-center">
                            <h5 class="fw-bold m-0" id="fat-percent"><?= round($fatPerc) ?>%</h5>
                            <span class="textoPequeno text-secondary" id="fat-text">
                                <?= round($total['grasas']) ?>g / <?= round($grasasObjetivo) ?>g
                            </span>
                        </div>
                    </div>
                    </div>

                </div><!-- /macros donuts -->
            </div>
        </div><!-- /col-xl-4 derecha -->

    </div><!-- /row layout principal -->

    <!-- =============================== -->
    <!-- PLANES GUARDADOS                -->
    <!-- =============================== -->
    <div class="p-4 sombra border rounded-4 bg-white mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h5 class="fw-semibold mb-0"><i class="bi bi-bookmark-star texto-rojo me-2"></i>Mis planes</h5>
                <p class="texto text-secondary m-0">Planes guardados anteriormente</p>
            </div>
            <button class="btn btn-primary texto" onclick="savePlan()">
                <i class="bi bi-bookmark-plus me-2"></i>Guardar plan
            </button>
        </div>

        <div class="row g-3">
            <?php foreach($planes as $p): ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="p-4 sombra border rounded-4 bg-white efectoEscala h-100">
                        <strong class="fw-bold textoMediano d-block"><?= htmlspecialchars($p['Nombre'] ?? 'Mi Plan') ?></strong>
                        <p class="texto text-secondary mb-0"><?= ucfirst($p['Objetivo'] ?? 'mantenimiento') ?></p>
                        <p class="textoPequeno text-secondary mb-3"><?= $p['Fecha'] ?? '' ?></p>
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn bg-white border text-secondary texto flex-fill"
                                    onclick="previewPlan(<?= $p['ID_Guardado'] ?>)">
                                Previsualizar
                            </button>
                            <button class="btn btn-primary texto flex-fill"
                                    onclick="loadPlan(<?= $p['ID_Guardado'] ?>)">
                                Cargar
                            </button>
                            <button class=" danger texto flex-fill rounded-4"
                                    onclick="deletePlan(<?= $p['ID_Guardado'] ?>)">
                                Borrar
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if(empty($planes)): ?>
                <div class="col-12 text-center py-4">
                    <span class="bg-grisClaro p-3 rounded-3 d-inline-flex mb-3">
                        <i class="bi bi-bookmark iconos texto-gris"></i>
                    </span>
                    <p class="texto text-secondary mb-0">No tienes planes guardados aún.</p>
                </div>
            <?php endif; ?>
        </div>

    </div><!-- /planes guardados -->

</div><!-- /container-fluid -->

<!-- ===== MODAL GUARDAR PLAN ===== -->
<div class="modal fade" id="saveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-sm">
            <div class="modal-header border-0 d-flex justify-content-end p-3">
                <button type="button" class="btn-close rounded-circle sombra border p-2 bg-white"
                        data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body px-4 pt-0 pb-2">
                <div class="d-flex gap-3 align-items-center mb-4">
                    <span class="bg-rojoClaro p-3 rounded-3 d-inline-flex">
                        <i class="bi bi-bookmark-plus texto-rojo iconos perfilUsuarioGrande"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold letraRomana m-0">Guardar Plan</h4>
                        <p class="texto text-secondary m-0">Ponle un nombre al plan actual.</p>
                    </div>
                </div>
                <label for="planName" class="form-label fw-semibold">Nombre del plan</label>
                <div class="input-group">
                    <span class="input-group-text bg-rojoClaro border border-rojo">
                        <i class="bi bi-pencil texto-rojo"></i>
                    </span>
                    <input type="text" class="form-control texto" id="planName" placeholder="Nombre del plan">
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4">
                <button type="button" class="btn bg-white border text-secondary texto px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary texto px-4"
                        onclick="confirmSavePlan()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- ===== MODAL PREVIEW PLAN ===== -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 rounded-4 shadow-sm">
            <div class="modal-header border-0 d-flex justify-content-end p-3">
                <button type="button" class="btn-close rounded-circle sombra border p-2 bg-white"
                        data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body px-4 pt-0 pb-4" id="previewBody"></div>
        </div>
    </div>
</div>

<!-- ===== MODAL ELIMINAR PLAN ===== -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-sm">
            <div class="modal-header border-0 d-flex justify-content-end p-3">
                <button type="button" class="btn-close rounded-circle sombra border p-2 bg-white"
                        data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center px-4 pt-0 pb-2">
                <div class="mb-4">
                    <span class="bg-rojoClaro p-3 rounded-3 d-inline-flex">
                        <i class="bi bi-trash texto-rojo iconos perfilUsuarioGrande"></i>
                    </span>
                </div>
                <h4 class="fw-bold letraRomana mb-2">Eliminar Plan</h4>
                <p class="texto mb-1">¿Seguro que quieres eliminar este plan?</p>
                <p class="textoPequeno text-secondary mb-0">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2 px-4 pb-4">
                <button type="button" class="btn bg-white border text-secondary texto px-4"
                        data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary texto px-4"
                        onclick="confirmDeletePlan()">Eliminar</button>
            </div>
        </div>
    </div>
</div>
<!-- ===== MODAL ELIMINAR COMIDA ===== -->
<div class="modal fade" id="removeMealModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-sm">

            <div class="modal-header border-0 d-flex justify-content-end p-3">
                <button type="button"
                        class="btn-close rounded-circle sombra border p-2 bg-white"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body text-center px-4 pt-0 pb-2">

                <div class="mb-4">
                    <span class="bg-rojoClaro p-3 rounded-3 d-inline-flex">
                        <i class="bi bi-trash texto-rojo iconos perfilUsuarioGrande"></i>
                    </span>
                </div>

                <h4 class="fw-bold letraRomana mb-2">
                    Eliminar receta
                </h4>

                <p class="texto mb-1">
                    ¿Quitar esta receta del plan?
                </p>

                <p class="textoPequeno text-secondary mb-0">
                    La comida quedará vacía y podrás añadir otra después.
                </p>

            </div>

            <div class="modal-footer border-0 justify-content-center gap-2 px-4 pb-4">

                <button type="button"
                        class="btn bg-white border text-secondary texto px-4"
                        data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="button"
                        class="btn btn-primary texto px-4"
                        onclick="confirmRemoveMeal()">
                    Eliminar
                </button>

            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- ================= JS ================= -->
<script>
let objetivoActual = "<?= $plan['Objetivo'] ?>";
let intensidadActual = "<?= $plan['Intensidad'] ?? 'moderado' ?>";
let timeout;
let qtyTimeout;
let fitTimeout;
let proteinChart;
let carbsChart;
let fatChart;
let activeInput = null;

function setAdminSelectableActive(el, isActive){
    if(!el){ return; }
    el.classList.toggle("active", isActive);
    el.classList.toggle("bg-rojo", isActive);
    el.classList.toggle("text-white", isActive);
    el.classList.toggle("border-rojo", isActive);
    el.classList.toggle("bg-white", !isActive);
    el.querySelectorAll("p, strong, span").forEach(child => {
        child.classList.toggle("text-white", isActive);
        if(child.matches("p")){
            child.classList.toggle("text-secondary", !isActive);
        }
    });
}

function createMacroChart(id, color, value){
    const ctx = document.getElementById(id);
    const adminStyles = getComputedStyle(document.documentElement);
    const emptyColor = adminStyles.getPropertyValue('--gris-claro').trim() || '#e9ecef';
    const visualValue = Math.min(100, Number(value) || 0);
    const isFull = visualValue >= 100;
    return new Chart(ctx, {
        type:'doughnut',
        data:{
            datasets:[{
                data: isFull ? [100] : [visualValue, 100 - visualValue],
                backgroundColor: isFull ? [color] : [color, emptyColor],
                borderWidth:0,
                borderRadius: isFull ? 0 : 20
            }]
        },
        options:{
            responsive:true,
            maintainAspectRatio:false,
            cutout:'82%',
            plugins:{
                legend:{ display:false },
                tooltip:{ enabled:false }
            },
            animation:{ duration:800 }
        }
    });
}

function updateResumenUI(data){
    const adminStyles = getComputedStyle(document.documentElement);
    const emptyColor = adminStyles.getPropertyValue('--gris-claro').trim() || '#e9ecef';
    const verde = adminStyles.getPropertyValue('--verde').trim() || '#198754';
    const azul = adminStyles.getPropertyValue('--azul').trim() || '#0d6efd';
    const naranja = adminStyles.getPropertyValue('--naranja').trim() || '#fd7e14';

    console.log("REFRESH UI:", data);

    const caloriesText = document.getElementById("calories-text");
    if(caloriesText){
        caloriesText.textContent = `${Math.round(data.totalCal)} / ${Math.round(data.objetivoCalorias)} kcal`;
    }

    const progressBar = document.querySelector(".progreso");
    if(progressBar){
        const totalCal = parseFloat(data.totalCal) || 0;
        const objetivoCalorias = parseFloat(data.objetivoCalorias) || 1;
        let calPerc = (totalCal / objetivoCalorias) * 100;
        if(!isFinite(calPerc)){ calPerc = 0; }
        calPerc = Math.max(0, Math.min(100, calPerc));
        calPerc = Math.round(calPerc);
        progressBar.style.width = `${calPerc}%`;
        progressBar.classList.remove("bg-verde","bg-naranja","bg-rojo");
        if(calPerc < 70){ progressBar.classList.add("bg-verde"); }
        else if(calPerc < 100){ progressBar.classList.add("bg-naranja"); }
        else{ progressBar.classList.add("bg-rojo"); }
    }

    const protPerc = Math.max(0, Math.min(200, Number(data.protPerc || 0)));
    document.getElementById("protein-percent").textContent = Math.round(protPerc) + "%";
    document.getElementById("protein-text").textContent = `${Math.round(data.totalProt)}g / ${Math.round(data.protObj)}g`;
    const protVisual = Math.min(100, protPerc);
    if(protVisual >= 100){
        proteinChart.data.datasets[0].data = [100];
        proteinChart.data.datasets[0].backgroundColor = [verde];
        proteinChart.data.datasets[0].borderRadius = 0;
    } else {
        proteinChart.data.datasets[0].data = [protVisual, 100 - protVisual];
        proteinChart.data.datasets[0].backgroundColor = [verde, emptyColor];
        proteinChart.data.datasets[0].borderRadius = 20;
    }
    proteinChart.update();

    const carbPerc = Math.max(0, Math.min(200, Number(data.carbPerc || 0)));
    document.getElementById("carbs-percent").textContent = Math.round(carbPerc) + "%";
    document.getElementById("carbs-text").textContent = `${Math.round(data.totalCarb)}g / ${Math.round(data.carbObj)}g`;
    const carbVisual = Math.min(100, carbPerc);
    if(carbVisual >= 100){
        carbsChart.data.datasets[0].data = [100];
        carbsChart.data.datasets[0].backgroundColor = [azul];
        carbsChart.data.datasets[0].borderRadius = 0;
    } else {
        carbsChart.data.datasets[0].data = [carbVisual, 100 - carbVisual];
        carbsChart.data.datasets[0].backgroundColor = [azul, emptyColor];
        carbsChart.data.datasets[0].borderRadius = 20;
    }
    carbsChart.update();

    const fatPerc = Math.max(0, Math.min(200, Number(data.fatPerc || 0)));
    document.getElementById("fat-percent").textContent = Math.round(fatPerc) + "%";
    document.getElementById("fat-text").textContent = `${Math.round(data.totalFat)}g / ${Math.round(data.fatObj)}g`;
    const fatVisual = Math.min(100, fatPerc);
    if(fatVisual >= 100){
        fatChart.data.datasets[0].data = [100];
        fatChart.data.datasets[0].backgroundColor = [naranja];
        fatChart.data.datasets[0].borderRadius = 0;
    } else {
        fatChart.data.datasets[0].data = [fatVisual, 100 - fatVisual];
        fatChart.data.datasets[0].backgroundColor = [naranja, emptyColor];
        fatChart.data.datasets[0].borderRadius = 20;
    }
    fatChart.update();
}

function goToFeed(tipo){
    const url = new URL("/pages/buscar", window.location.origin);
    url.searchParams.set("meal", tipo);
    url.searchParams.set("fit", "1");
    url.searchParams.set("esfit", "1");
    window.location.href = url.toString();
}

function setMeals(num){
   window.location.href = "/fit/setMeals?num=" + num;
}

function loadPlan(id){
    window.location.href = "../../fit/loadPlan?id=" + id;
}

let deletePlanId = null;

function deletePlan(id){
    deletePlanId = id;
    bootstrap.Modal.getOrCreateInstance(document.getElementById("deleteModal")).show();
}

function closeDeleteModal(){
    deletePlanId = null;
    bootstrap.Modal.getOrCreateInstance(document.getElementById("deleteModal")).hide();
}

function confirmDeletePlan(){
    if(!deletePlanId){ return; }
    window.location.href = "../../fit/deletePlan?id=" + deletePlanId;
}

function savePlan(){
    bootstrap.Modal.getOrCreateInstance(document.getElementById("saveModal")).show();
}

function closeSaveModal(){
    bootstrap.Modal.getOrCreateInstance(document.getElementById("saveModal")).hide();
}

function confirmSavePlan(){
    const nombre = document.getElementById("planName").value.trim();
    if(!nombre){ window.Alertas.aviso("Pon un nombre"); return; }
    const form = document.createElement("form");
    form.method = "POST";
    form.action = "../../fit/savePlan";
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "nombre";
    input.value = nombre;
    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

async function previewPlan(id)
{
    const modal =
        document.getElementById('previewModal');
    const body =
        document.getElementById('previewBody');
    bootstrap.Modal
        .getOrCreateInstance(modal)
        .show();
    body.innerHTML = 'Cargando plan...';
    try {
        const response = await fetch(
            '/fit/previewPlan?id=' + id
        );
        const html =
            await response.text();

        body.innerHTML = html;
    }
    catch(err){

        console.error(err);

        body.innerHTML =
            'Error cargando preview';
    }
}
function closePreviewModal(){
    bootstrap.Modal.getOrCreateInstance(document.getElementById('previewModal')).hide();
}

function updateCantidad(meal, cantidad){
    cantidad = parseInt(cantidad);
    if(isNaN(cantidad)){ return; }
    cantidad = Math.max(1, Math.min(3000, cantidad));
    if(!meal){ return; }
    const formData = new FormData();
    formData.append("action", "updateCantidad");
    formData.append("meal", meal);
    formData.append("cantidad", cantidad);
    fetch('../../fit/updateCantidad', { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {
        console.log("DATA RECIBIDA:", data);
        if(data.error){ console.error(data); return; }
        const card = document.getElementById("meal-" + meal);
        if(card){
            const kcalEl = card.querySelector(".fit-meal-kcal");
            if(kcalEl){ kcalEl.innerText = Math.round(data.mealCal) + " kcal"; }
            const proteinMacro = card.querySelector(".fit-macro.protein");
            const carbsMacro   = card.querySelector(".fit-macro.carbs");
            const fatsMacro    = card.querySelector(".fit-macro.fats");
            if(proteinMacro){ proteinMacro.innerText = "P " + Math.round(data.mealProt) + "g"; }
            if(carbsMacro){ carbsMacro.innerText = "C " + Math.round(data.mealCarb) + "g"; }
            if(fatsMacro){ fatsMacro.innerText = "G " + Math.round(data.mealFat) + "g"; }
            const qtyInput = card.querySelector(".fit-qty-input");
            if(qtyInput && data.cantidad !== undefined && activeInput !== qtyInput){
                qtyInput.value = data.cantidad;
            }
        }
        updateResumenUI(data);
    });
}
//BORRAR COMIDA
let mealToRemove = null;

function removeMeal(meal)
{
    mealToRemove = meal;

    bootstrap.Modal
        .getOrCreateInstance(
            document.getElementById(
                "removeMealModal"
            )
        )
        .show();
}

function confirmRemoveMeal()
{
    if(!mealToRemove){
        return;
    }

    const formData = new FormData();

    formData.append(
        "meal",
        mealToRemove
    );

    fetch(
        "/fit/removeMeal",
        {
            method:"POST",
            body:formData
        }
    )
        .then(
            res => res.json()
        )
        .then(data => {

            if(data.error){
                console.error(data);
                return;
            }

            bootstrap.Modal
                .getOrCreateInstance(
                    document.getElementById(
                        "removeMealModal"
                    )
                )
                .hide();

            location.reload();

        })
        .catch(err => {
            console.error(err);
        });
}
function debounceFitUpdate(){
    clearTimeout(fitTimeout);
    fitTimeout = setTimeout(() => { updateFitData(); }, 400);
}

function updateFitData(){
    const formData = new FormData();
    formData.append("action", "updateFitRealtime");
    const peso = Math.min(300, Math.max(25, parseFloat(document.getElementById("fit-peso").value) || 25));
    const altura = Math.min(272, Math.max(100, parseFloat(document.getElementById("fit-altura").value) || 170));
    const edad = Math.min(120, Math.max(10, parseInt(document.getElementById("fit-edad").value) || 18));
    formData.append("peso", peso);
    formData.append("altura", altura);
    formData.append("edad", edad);
    formData.append("sexo", document.getElementById("fit-sexo").value);
    formData.append("actividad", document.getElementById("fit-actividad").value);
    fetch('../../fit/updateFitRealtime', { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {
        console.log("FIT REALTIME:", data);
        updateResumenUI(data);
    });
}

function validatePhysicalInput(inputId, min, max, fallback){
    const input = document.getElementById(inputId);
    if(!input){ return; }
    input.addEventListener("blur", () => {
        let value = parseFloat(input.value);
        if(isNaN(value)){ input.value = fallback; debounceFitUpdate(); return; }
        if(value > max){ input.value = max; }
        if(value < min){ input.value = min; }
        debounceFitUpdate();
    });
}

function validateMealInputs(){
    document.querySelectorAll(".fit-qty-input").forEach(input => {
        let debounce;
        input.addEventListener("focus", () => { activeInput = input; });
        input.addEventListener("blur", () => {
            activeInput = null;
            let value = parseInt(input.value);
            if(isNaN(value)){ value = 100; }
            value = Math.max(1, Math.min(3000, value));
            input.value = value;
        });
        input.addEventListener("input", () => {
            clearTimeout(debounce);
            debounce = setTimeout(() => {
                let value = parseInt(input.value);
                if(isNaN(value)){ return; }
                value = Math.max(1, Math.min(3000, value));
                const meal = input.dataset.meal;
                updateCantidad(meal, value);
            }, 250);
        });
    });
}

function selectIntensidad(val){
    intensidadActual = val;
    document.querySelectorAll(".fit-intensity-card").forEach(el => setAdminSelectableActive(el, false));
    const selected = document.querySelector(`.fit-intensity-card[data-int='${val}']`);
    if(selected){ setAdminSelectableActive(selected, true); }
    changeObjetivo(objetivoActual, intensidadActual);
}

function changeObjetivo(objetivo, intensidad = null){
    objetivoActual = objetivo;
    if(!intensidad){ intensidad = intensidadActual; }
    intensidadActual = intensidad;
    const formData = new FormData();
    formData.append("objetivo", objetivo);
    formData.append("intensidad", intensidad);
    fetch('../../fit/changeObjetivo', { method: "POST", body: formData })
    .then(res => res.json())
    .then(data => {
        updateResumenUI(data);
        document.querySelectorAll(".fit-goal-card").forEach(el => setAdminSelectableActive(el, false));
        const activeObj = document.querySelector(`.fit-goal-card[data-obj='${objetivo}']`);
        if(activeObj){ setAdminSelectableActive(activeObj, true); }
        document.querySelectorAll(".fit-intensity-card").forEach(el => setAdminSelectableActive(el, false));
        const activeInt = document.querySelector(`.fit-intensity-card[data-int='${intensidad}']`);
        if(activeInt){ setAdminSelectableActive(activeInt, true); }
    })
    .catch(err => console.error(err));
}
function preventNegativeInput(selector){

    // Detecta si es id (#) o clase (.)
    const inputs = selector.startsWith(".")
        ? document.querySelectorAll(selector)
        : [document.getElementById(selector)];
    inputs.forEach(input => {

        if(!input){ return; }
        // Bloquear negativos y notación científica
        input.addEventListener("keydown", (e) => {
            const blocked = ['-', '+', 'e', 'E'];
            if(blocked.includes(e.key)){
                e.preventDefault();
            }

        });
        // Limpiar caracteres no válidos
        input.addEventListener("input", () => {
            let value = input.value;
            // Solo números
            value = value.replace(/[^0-9]/g, '');
            input.value = value;
        });
        // Evitar pegar negativos
        input.addEventListener("paste", (e) => {

            const text =
                (e.clipboardData || window.clipboardData)
                    .getData('text');
            if(
                text.includes('-') ||
                text.includes('+') ||
                text.includes('e') ||
                text.includes('E')
            ){
                e.preventDefault();
            }
        });
    });

}

// Highlight al volver del feed
const params = new URLSearchParams(window.location.search);
const meal = params.get("meal");
if(meal){
    const el = document.getElementById("meal-"+meal);
    if(el){
        el.style.boxShadow = "0 0 0 3px var(--vino-light)";
        el.scrollIntoView({behavior:"smooth", block:"center"});
    }
}

window.addEventListener('load', () => {
    const adminStyles = getComputedStyle(document.documentElement);
    const verde = adminStyles.getPropertyValue('--verde').trim() || '#198754';
    const azul = adminStyles.getPropertyValue('--azul').trim() || '#0d6efd';
    const naranja = adminStyles.getPropertyValue('--naranja').trim() || '#fd7e14';

    document.querySelectorAll(".fit-goal-card, .fit-intensity-card, .fit-meal-card")
        .forEach(el => setAdminSelectableActive(el, el.classList.contains("active")));

    proteinChart = createMacroChart('proteinChart', verde, <?= round($protPerc) ?>);
    carbsChart   = createMacroChart('carbsChart',   azul,   <?= round($carbPerc) ?>);
    fatChart     = createMacroChart('fatChart',     naranja, <?= round($fatPerc) ?>);

    validatePhysicalInput("fit-peso",   45,  300, 70);
    validatePhysicalInput("fit-altura", 120, 272, 170);
    validatePhysicalInput("fit-edad",   15,  120, 18);

    preventNegativeInput("fit-peso");
    preventNegativeInput("fit-altura");
    preventNegativeInput("fit-edad");
    preventNegativeInput(".fit-qty-input");


    validateMealInputs();
});
</script>

<?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
