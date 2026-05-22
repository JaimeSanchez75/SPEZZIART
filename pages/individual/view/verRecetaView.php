<!DOCTYPE html>
<html lang="es" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($receta['Titulo']) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="/global/styles/individual.css">
    <script>window.userTheme = '<?= $_SESSION['user']['tema'] ?? 'sistema' ?>';</script>
    <script src="/global/js/theme.js"></script>
</head>

<body class="bg-body recipe-detail-page">
<?php
    $formatearNutricion = static function (float $valor): string {
        return fmod($valor, 1.0) === 0.0
            ? (string)(int)$valor
            : rtrim(rtrim(number_format($valor, 2, '.', ''), '0'), '.');
    };
    $imagenesReceta = !empty($receta['Imagenes']) && is_array($receta['Imagenes'])
        ? $receta['Imagenes']
        : (!empty($receta['Imagen']) ? array_values(array_filter(array_map('trim', explode(',', (string)$receta['Imagen'])))) : []);
    $portadaReceta = $imagenesReceta[0] ?? null;
    $imagenesSecundarias = array_slice($imagenesReceta, 1);

    $nutricionReceta = [
        'calorias' => 0.0,
        'proteina' => 0.0,
        'carbohidratos' => 0.0,
        'grasas' => 0.0
    ];

    foreach (($receta['ingredientes'] ?? []) as $ingrediente) {
        $nutricionReceta['calorias'] += is_numeric($ingrediente['Calorias'] ?? null) ? (float)$ingrediente['Calorias'] : 0;
        $nutricionReceta['proteina'] += is_numeric($ingrediente['Proteina'] ?? null) ? (float)$ingrediente['Proteina'] : 0;
        $nutricionReceta['carbohidratos'] += is_numeric($ingrediente['Carbohidratos'] ?? null) ? (float)$ingrediente['Carbohidratos'] : 0;
        $nutricionReceta['grasas'] += is_numeric($ingrediente['Grasas'] ?? null) ? (float)$ingrediente['Grasas'] : 0;
    }

    $mostrarNutricion = !empty($receta['EsFit']) || array_sum($nutricionReceta) > 0;
?>

<div class="container py-4 py-md-5">

    <a href="/pages/individual" class="btn btn-outline-secondary mb-3 rounded-pill px-3">
        Volver
    </a>

    <div class="card shadow-sm border-0 glass-card">
        <div class="card-body p-3 p-md-4">
            <div class="recipe-overview">
                <?php if ($portadaReceta !== null): ?>
                    <aside class="recipe-media">
                        <div class="recipe-img-frame">
                            <img src="<?= htmlspecialchars($portadaReceta) ?>" class="recipe-img" alt="Portada de la receta" onerror="this.onerror=null; this.src='/uploads/NoImg.jpg';">
                        </div>
                    </aside>
                <?php endif; ?>

                <div>
                    <div class="recipe-hero">
                        <div>
                            <h2 class="fw-bold mb-2"><?= htmlspecialchars($receta['Titulo']) ?></h2>
                            <div class="recipe-meta">
                                <div class="recipe-meta-pill">
                                    <span class="material-symbols-outlined recipe-meta-icon">person</span>
                                    <?= htmlspecialchars($receta['Username']) ?>
                                </div>
                                <div class="recipe-meta-pill">
                                    <span class="material-symbols-outlined recipe-meta-icon">schedule</span>
                                    <?= htmlspecialchars((string)($receta['Tiempo'] ?? 0)) ?> min
                                </div>
                                <div class="recipe-meta-pill">
                                    <span class="material-symbols-outlined recipe-meta-icon">restaurant</span>
                                    <?= htmlspecialchars((string)($receta['Porciones'] ?? 0)) ?> porciones
                                </div>
                            </div>
                            <?php if (!empty($receta['etiquetas'])): ?>
                                <div class="recipe-tags">
                                    <?php foreach ($receta['etiquetas'] as $etiqueta): ?>
                                        <span class="recipe-tag">#<?= htmlspecialchars($etiqueta['Nombre']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($receta['EsFit'])): ?>
                            <div class="recipe-fit-badge">
                                <span class="material-symbols-outlined recipe-meta-icon">monitor_heart</span>
                                FIT
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($mostrarNutricion): ?>
                        <div class="row g-3 mb-4 mt-1">
                            <div class="col-6 col-md-3">
                                <div class="nutrition-box">
                                    <div class="text-muted small">Calorias</div>
                                    <div class="nutrition-value"><?= htmlspecialchars($formatearNutricion($nutricionReceta['calorias'])) ?> kcal</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="nutrition-box">
                                    <div class="text-muted small">Proteina</div>
                                    <div class="nutrition-value"><?= htmlspecialchars($formatearNutricion($nutricionReceta['proteina'])) ?> g</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="nutrition-box">
                                    <div class="text-muted small">Carbohidratos</div>
                                    <div class="nutrition-value"><?= htmlspecialchars($formatearNutricion($nutricionReceta['carbohidratos'])) ?> g</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="nutrition-box">
                                    <div class="text-muted small">Grasas</div>
                                    <div class="nutrition-value"><?= htmlspecialchars($formatearNutricion($nutricionReceta['grasas'])) ?> g</div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($receta['DescripcionVisible'])): ?>
                <div class="description-card mb-4">
                    <h5 class="section-title mb-2">Descripcion</h5>
                    <p class="text-body-secondary mb-0">
                        <?= nl2br(htmlspecialchars($receta['DescripcionVisible'])) ?>
                    </p>
                </div>
            <?php endif; ?>

            <?php if (!empty($imagenesSecundarias)): ?>
                <div class="description-card mb-4">
                    <h5 class="section-title mb-3">Mas fotos</h5>
                    <div class="recipe-gallery-grid">
                        <?php foreach ($imagenesSecundarias as $indice => $imagenSecundaria): ?>
                            <figure class="recipe-gallery-card mb-0">
                                <img
                                    src="<?= htmlspecialchars($imagenSecundaria) ?>"
                                    class="recipe-gallery-thumb"
                                    alt="Foto adicional <?= $indice + 1 ?>"
                                    onerror="this.onerror=null; this.src='/uploads/NoImg.jpg';">
                                <figcaption class="recipe-gallery-caption">Foto adicional <?= $indice + 1 ?></figcaption>
                            </figure>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($receta['Pasos'])): ?>
                <div class="steps-card mb-4">
                    <h5 class="section-title">Pasos</h5>
                    <div class="steps-list">
                        <?php foreach ($receta['Pasos'] as $indice => $paso): ?>
                            <div class="step-card">
                                <div class="step-number"><?= $indice + 1 ?></div>
                                <div class="pt-1"><?= htmlspecialchars($paso) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($receta['ingredientes'])): ?>
                <div class="ingredients-card">
                    <h5 class="section-title">Ingredientes</h5>
                    <div class="ingredients-grid">
                        <?php foreach ($receta['ingredientes'] as $ing): ?>
                            <article class="ingredient-card">
                                <div class="ingredient-head">
                                    <div class="ingredient-name"><?= htmlspecialchars($ing['Nombre']) ?></div>
                                    <?php if (!empty($ing['Cantidad'])): ?>
                                        <div class="ingredient-qty"><?= htmlspecialchars($ing['Cantidad']) ?></div>
                                    <?php endif; ?>
                                </div>

                                <details class="ingredient-nutrition-toggle">
                                    <summary>
                                        <span>Ver valores nutricionales</span>
                                        <span class="material-symbols-outlined">expand_more</span>
                                    </summary>
                                    <div class="ingredient-nutrition-body">
                                        <div class="ingredient-macros">
                                            <div class="macro-chip">
                                                <div class="macro-chip-label">Calorias</div>
                                                <div class="macro-chip-value"><?= htmlspecialchars($formatearNutricion((float)($ing['Calorias'] ?? 0))) ?> kcal</div>
                                            </div>
                                            <div class="macro-chip">
                                                <div class="macro-chip-label">Proteina</div>
                                                <div class="macro-chip-value"><?= htmlspecialchars($formatearNutricion((float)($ing['Proteina'] ?? 0))) ?> g</div>
                                            </div>
                                            <div class="macro-chip">
                                                <div class="macro-chip-label">Carbohidratos</div>
                                                <div class="macro-chip-value"><?= htmlspecialchars($formatearNutricion((float)($ing['Carbohidratos'] ?? 0))) ?> g</div>
                                            </div>
                                            <div class="macro-chip">
                                                <div class="macro-chip-label">Grasas</div>
                                                <div class="macro-chip-value"><?= htmlspecialchars($formatearNutricion((float)($ing['Grasas'] ?? 0))) ?> g</div>
                                            </div>
                                        </div>
                                    </div>
                                </details>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
