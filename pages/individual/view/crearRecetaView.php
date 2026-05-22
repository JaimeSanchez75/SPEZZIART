<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= isset($receta['ID_Receta']) ? 'Editar Receta' : 'Crear Receta' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="stylesheet" href="/global/styles/global.css">
    <link rel="stylesheet" href="/global/styles/individual.css">
    <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
    <script>window.userTheme = '<?= $_SESSION['user']['tema'] ?? 'sistema' ?>';</script>
    <script src="/global/js/theme.js"></script>
</head>
<body class="recipe-editor-page">
    <?php
        $ingredientesJson = json_encode($ingredientesDisponibles ?? [], JSON_UNESCAPED_UNICODE);
        $etiquetasJson = json_encode($etiquetasDisponibles ?? [], JSON_UNESCAPED_UNICODE);
        $esEdicion = !empty($receta['ID_Receta']);
        $ingredientesFormulario = $ingredientesFormulario ?? [['ID_Ingrediente' => '', 'Nombre' => '', 'Cantidad' => '']];
        $etiquetasDisponibles = $etiquetasDisponibles ?? [];
        $etiquetasSeleccionadas = $etiquetasSeleccionadas ?? [];
        $imagenesReceta = $imagenesReceta ?? [];
    ?>

    <div class="recipe-editor-shell">
    <div class="container py-4 py-lg-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <h1 class="h2 fw-bold mb-2"><?= $esEdicion ? 'Editar receta' : 'Crear receta' ?></h1>
                        <p class="text-body-secondary mb-0">Completa la receta en bloques claros y revisa el resumen antes de guardarla.</p>
                    </div>
                    <a href="/pages/individual" class="btn btn-outline-secondary rounded-pill px-4">Volver</a>
                </div>

                <form action="/pages/individual/guardar" method="POST" enctype="multipart/form-data" id="formReceta" novalidate>
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="id" value="<?= $receta['ID_Receta'] ?? '' ?>">
                    <input type="hidden" name="calorias" id="inputCalorias" value="<?= htmlspecialchars((string)($nutricionFormulario['calorias'] ?? 0)) ?>">
                    <input type="hidden" name="proteina" id="inputProteina" value="<?= htmlspecialchars((string)($nutricionFormulario['proteina'] ?? 0)) ?>">
                    <input type="hidden" name="carbohidratos" id="inputCarbohidratos" value="<?= htmlspecialchars((string)($nutricionFormulario['carbohidratos'] ?? 0)) ?>">
                    <input type="hidden" name="grasas" id="inputGrasas" value="<?= htmlspecialchars((string)($nutricionFormulario['grasas'] ?? 0)) ?>">
                    <input type="hidden" name="portada_imagen" id="inputPortadaImagen" value="<?= htmlspecialchars((string)($imagenesReceta[0] ?? '')) ?>">

                    <div class="row g-4">
                        <div class="col-12 col-lg-8 order-2 order-lg-1">
                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-body p-4 p-lg-5">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h2 class="h4 fw-bold mb-1">Informacion principal</h2>
                                            <p class="text-body-secondary mb-0">Define el titulo, la descripcion y las etiquetas.</p>
                                        </div>
                                        <span class="badge text-bg-light border text-secondary rounded-pill px-3 py-2">
                                            <?= $esEdicion ? 'Modo edicion' : 'Nueva receta' ?>
                                        </span>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Titulo <span class="text-danger">*</span></label>
                                        <input type="text" name="titulo" class="form-control form-control-lg" required maxlength="60" value="<?= htmlspecialchars($receta['Titulo'] ?? '') ?>">
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Descripcion</label>
                                        <textarea name="descripcion" id="descripcionReceta" class="form-control" rows="5" maxlength="350"><?= htmlspecialchars($descripcionFormulario ?? '') ?></textarea>
                                        <div class="form-text d-flex justify-content-between gap-3 recipe-description-help">
                                            <span>Maximo 350 caracteres.</span>
                                            <span id="descripcionWordCount">0/350 caracteres</span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="form-label fw-semibold">Etiquetas</label>
                                        <div class="input-group mb-3">
                                            <input
                                                type="text"
                                                class="form-control"
                                                id="selectorEtiquetas"
                                                placeholder="Buscar y seleccionar etiquetas"
                                                readonly>
                                            <button type="button" class="btn btn-outline-secondary" id="abrirEtiquetas">Elegir</button>
                                        </div>
                                        <div id="etiquetasSeleccionadasPreview" class="d-flex flex-wrap gap-2 mb-2"></div>
                                        <div id="etiquetasHiddenInputs">
                                            <?php foreach ($etiquetasSeleccionadas as $idEtiqueta): ?>
                                                <input type="hidden" name="etiquetas[]" value="<?= (int)$idEtiqueta ?>">
                                            <?php endforeach; ?>
                                        </div>
                                        <div class="form-text">Selecciona una o varias etiquetas desde el buscador.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-body p-4 p-lg-5">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h2 class="h4 fw-bold mb-1">Ingredientes</h2>
                                            <p class="text-body-secondary mb-0">Anade ingredientes y cantidades. La nutricion solo se edita si activas el modo fit.</p>
                                        </div>
                                    </div>

                                    <div id="listaIngredientes" class="d-grid gap-3">
                                        <?php foreach ($ingredientesFormulario as $ingrediente): ?>
                                            <div class="card border bg-body-tertiary rounded-4 ingrediente-item">
                                                <div class="card-body p-3 p-md-4">
                                                    <div class="row g-3 align-items-start">
                                                        <div class="col-12 col-md-6">
                                                            <label class="form-label small fw-semibold text-body-secondary">Ingrediente</label>
                                                            <input type="hidden" name="ingrediente_id[]" class="ingrediente-id" value="<?= htmlspecialchars((string)($ingrediente['ID_Ingrediente'] ?? '')) ?>">
                                                            <input type="hidden" name="ingrediente_calorias[]" class="ingrediente-calorias" value="<?= htmlspecialchars((string)($ingrediente['Calorias'] ?? 0)) ?>">
                                                            <input type="hidden" name="ingrediente_proteina[]" class="ingrediente-proteina" value="<?= htmlspecialchars((string)($ingrediente['Proteina'] ?? 0)) ?>">
                                                            <input type="hidden" name="ingrediente_carbohidratos[]" class="ingrediente-carbohidratos" value="<?= htmlspecialchars((string)($ingrediente['Carbohidratos'] ?? 0)) ?>">
                                                            <input type="hidden" name="ingrediente_grasas[]" class="ingrediente-grasas" value="<?= htmlspecialchars((string)($ingrediente['Grasas'] ?? 0)) ?>">
                                                            <input
                                                                type="text"
                                                                name="ingrediente_nombre[]"
                                                                class="form-control ingrediente-nombre"
                                                                placeholder="Escribe o selecciona un ingrediente"
                                                                maxlength="60"
                                                                value="<?= htmlspecialchars($ingrediente['Nombre'] ?? '') ?>"
                                                                autocomplete="off">
                                                        </div>

                                                        <div class="col-9 col-md-5">
                                                            <label class="form-label small fw-semibold text-body-secondary">Cantidad</label>
                                                            <input type="number" name="ingrediente_cantidad[]" class="form-control ingrediente-cantidad" placeholder="Cantidad" min="0.01" max="9999" step="0.01" value="<?= htmlspecialchars((string)($ingrediente['Cantidad'] ?? '')) ?>">
                                                            <select class="form-select form-select-sm ingrediente-unidad-select d-none mt-1"
                                                                    name="ingrediente_unidad_nombre[]">
                                                                <option value="">Selecciona unidad</option>
                                                                <option value="g">g</option>
                                                                <option value="ml">ml</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-3 col-md-1 d-grid">
                                                            <label class="form-label small fw-semibold text-body-secondary d-none d-md-block">&nbsp;</label>
                                                            <button type="button" class="btn btn-outline-danger eliminar-ingrediente">&times;</button>
                                                        </div>

                                                        <div class="col-12 ingrediente-nutricion-wrapper">
                                                            <div class="border rounded-4 p-3 bg-body ingrediente-nutricion" data-modo="<?= !empty($ingrediente['ID_Ingrediente']) ? 'existente' : 'nuevo' ?>">
                                                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                                                    <span class="fw-semibold small">Informacion nutricional</span>
                                                                    <span class="badge text-bg-light border text-secondary rounded-pill">Por ingrediente</span>
                                                                </div>
                                                                <div class="ingrediente-nutricion-resumen small text-secondary mb-3"></div>
                                                                <div class="row g-2 ingrediente-nutricion-formulario">
                                                                    <div class="col-6 col-lg-3">
                                                                        <label class="form-label small mb-1">Calorias</label>
                                                                        <input type="number" min="0" max="9999" step="0.01" class="form-control form-control-sm ingrediente-nutricion-input no-negative" data-campo="calorias" value="<?= htmlspecialchars((string)($ingrediente['Calorias'] ?? 0)) ?>">
                                                                    </div>
                                                                    <div class="col-6 col-lg-3">
                                                                        <label class="form-label small mb-1">Proteina</label>
                                                                        <input type="number" min="0" max="9999" step="0.01" class="form-control form-control-sm ingrediente-nutricion-input no-negative" data-campo="proteina" value="<?= htmlspecialchars((string)($ingrediente['Proteina'] ?? 0)) ?>">
                                                                    </div>
                                                                    <div class="col-6 col-lg-3">
                                                                        <label class="form-label small mb-1">Carbohidratos</label>
                                                                        <input type="number" min="0" max="9999" step="0.01" class="form-control form-control-sm ingrediente-nutricion-input no-negative" data-campo="carbohidratos" value="<?= htmlspecialchars((string)($ingrediente['Carbohidratos'] ?? 0)) ?>">
                                                                    </div>
                                                                    <div class="col-6 col-lg-3">
                                                                        <label class="form-label small mb-1">Grasas</label>
                                                                        <input type="number" min="0" max="9999" step="0.01" class="form-control form-control-sm ingrediente-nutricion-input no-negative" data-campo="grasas" value="<?= htmlspecialchars((string)($ingrediente['Grasas'] ?? 0)) ?>">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div class="mt-3">
                                        <button type="button" class="btn btn-danger rounded-pill px-4" id="agregarIngrediente">+ ingrediente</button>
                                    </div>

                                    <div id="ingredientesError" class="small text-danger mt-2 d-none">
                                        Debes anadir al menos un ingrediente con su nombre y cantidad antes de guardar.
                                    </div>

                                    <div class="form-text mt-3">Al escribir te apareceran ingredientes predeterminados y tambien puedes guardar uno nuevo.</div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-body p-4 p-lg-5">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h2 class="h4 fw-bold mb-1">Preparacion</h2>
                                            <p class="text-body-secondary mb-0">Ordena el proceso en pasos simples y claros.</p>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger rounded-pill px-4" id="agregarPaso">+ paso</button>
                                    </div>

                                    <div id="listaPasos" class="d-grid gap-3">
                                        <?php foreach (($pasosFormulario ?? ['']) as $indice => $paso): ?>
                                            <div class="input-group paso-item">
                                                <span class="input-group-text bg-body-secondary border-0 fw-semibold">Paso <?= $indice + 1 ?></span>
                                                <input
                                                    type="text"
                                                    name="pasos[]"
                                                    class="form-control"
                                                    maxlength="100"
                                                    value="<?= htmlspecialchars($paso) ?>"
                                                    placeholder="Describe este paso">
                                                <button type="button" class="btn btn-outline-secondary eliminar-paso">Eliminar</button>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>

                                    <div id="pasosError" class="small text-danger mt-2 d-none">
                                        Debes anadir al menos un paso antes de guardar.
                                    </div>
                                </div>
                            </div>

                            <div class="card border-0 shadow-sm rounded-4 mb-4">
                                <div class="card-body p-4 p-lg-5">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                                        <div>
                                            <h2 class="h4 fw-bold mb-1">Portada y fotos</h2>
                                            <p class="text-body-secondary mb-0">Elige la portada y conserva solo las fotos que quieras mostrar en la receta.</p>
                                        </div>
                                    </div>
                                    <div class="recipe-upload-zone mb-3">
                                        <span class="material-symbols-outlined recipe-upload-zone__icon">add_photo_alternate</span>
                                        <p class="recipe-upload-zone__title">Subir fotos</p>
                                        <p class="recipe-upload-zone__hint">Haz clic para seleccionar imágenes. Elige cuál será la portada y elimina las que no necesites.</p>
                                        <input type="file" name="imagen[]" id="inputImagenesReceta" accept="image/*" multiple class="recipe-upload-zone__input">
                                    </div>
                                    <div id="imagenesRecetaHiddenInputs"></div>
                                    <div id="gestorImagenesReceta" class="recipe-image-manager-grid row row-cols-1 row-cols-md-2 row-cols-xl-3"></div>
                                    <div id="controlesCarruselImagenes" class="recipe-image-carousel-header d-none"></div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-lg-4 order-1 order-lg-2">
                            <div class="recipe-editor-sidebar">
                                <div class="card border-0 shadow-sm rounded-4 mb-4">
                                    <div class="card-body p-4">
                                        <h2 class="h5 fw-bold mb-3">Resumen</h2>
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item px-0 d-flex justify-content-between align-items-center" id="resumenNutricionItem">
                                                <span class="text-body-secondary">Nutricion</span>
                                                <button type="button" class="btn btn-outline-success btn-sm rounded-pill" id="abrirNutricion">
                                                    Ver total
                                                </button>
                                            </div>
                                            <div class="list-group-item px-0">
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" name="fit" id="fit" <?= !empty($receta['EsFit']) ? 'checked' : '' ?>>
                                                    <label class="form-check-label fw-semibold" for="fit">Receta Fit</label>
                                                </div>
                                                <div class="form-text mb-0">Marca esta opcion si quieres destacarla como fit.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card border-0 shadow-sm rounded-4 mb-4">
                                    <div class="card-body p-4">
                                        <h2 class="h5 fw-bold mb-3">Detalles</h2>
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Tiempo (min) <span class="text-danger">*</span></label>
                                                <input type="number" name="tiempo" min="0" max="1440" step="1" class="form-control no-negative" value="<?= htmlspecialchars((string)($receta['Tiempo'] ?? '')) ?>">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">Porciones <span class="text-danger">*</span></label>
                                                <input type="number" name="porciones" min="0" max="100" step="1" class="form-control no-negative" value="<?= htmlspecialchars((string)($receta['Porciones'] ?? '')) ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid d-none d-lg-grid">
                                    <button type="submit" class="btn btn-danger btn-lg rounded-4 shadow-sm">
                                        <?= $esEdicion ? 'Actualizar receta' : 'Guardar receta' ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </div>

    <div class="modal fade" id="ingredienteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar ingrediente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-3" id="buscadorIngredienteModal" placeholder="Escribe para buscar o crear uno nuevo" maxlength="60">
                    <div class="list-group recipe-suggestions-list" id="listaSugerenciasIngredientes"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="usarTextoIngrediente">Usar texto escrito</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="etiquetaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Seleccionar etiquetas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mb-3" id="buscadorEtiquetaModal" placeholder="Escribe para buscar etiquetas">
                    <div class="list-group recipe-suggestions-list" id="listaSugerenciasEtiquetas"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="nutricionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title">Nutricion de la receta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Calorias (kcal)</label>
                        <input type="number" class="form-control" id="modalCalorias" value="<?= htmlspecialchars((string)($nutricionFormulario['calorias'] ?? 0)) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Proteina (g)</label>
                        <input type="number" class="form-control" id="modalProteina" value="<?= htmlspecialchars((string)($nutricionFormulario['proteina'] ?? 0)) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Carbohidratos (g)</label>
                        <input type="number" class="form-control" id="modalCarbohidratos" value="<?= htmlspecialchars((string)($nutricionFormulario['carbohidratos'] ?? 0)) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Grasas (g)</label>
                        <input type="number" class="form-control" id="modalGrasas" value="<?= htmlspecialchars((string)($nutricionFormulario['grasas'] ?? 0)) ?>" readonly>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <template id="ingredienteTemplate">
        <div class="card border bg-body-tertiary rounded-4 ingrediente-item">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3 align-items-start">
                    <div class="col-12 col-md-6">
                        <label class="form-label small fw-semibold text-body-secondary">Ingrediente</label>
                        <input type="hidden" name="ingrediente_id[]" class="ingrediente-id" value="">
                        <input type="hidden" name="ingrediente_calorias[]" class="ingrediente-calorias" value="0">
                        <input type="hidden" name="ingrediente_proteina[]" class="ingrediente-proteina" value="0">
                        <input type="hidden" name="ingrediente_carbohidratos[]" class="ingrediente-carbohidratos" value="0">
                        <input type="hidden" name="ingrediente_grasas[]" class="ingrediente-grasas" value="0">
                        <input type="text" name="ingrediente_nombre[]" class="form-control ingrediente-nombre" placeholder="Escribe o selecciona un ingrediente" maxlength="60" autocomplete="off">
                    </div>
                    <div class="col-9 col-md-5">
                        <label class="form-label small fw-semibold text-body-secondary">Cantidad</label>
                        <input type="number" name="ingrediente_cantidad[]" class="form-control ingrediente-cantidad" placeholder="Cantidad" min="0.01" max="9999" step="0.01" value="">
                        <select class="form-select form-select-sm ingrediente-unidad-select d-none mt-1"
                                name="ingrediente_unidad_nombre[]">
                            <option value="">Selecciona unidad</option>
                            <option value="g">g</option>
                            <option value="ml">ml</option>
                        </select>
                    </div>
                    <div class="col-3 col-md-1 d-grid">
                        <label class="form-label small fw-semibold text-body-secondary d-none d-md-block">&nbsp;</label>
                        <button type="button" class="btn btn-outline-danger eliminar-ingrediente">&times;</button>
                    </div>
                    <div class="col-12 ingrediente-nutricion-wrapper">
                        <div class="border rounded-4 p-3 bg-body ingrediente-nutricion" data-modo="nuevo">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                                <span class="fw-semibold small">Informacion nutricional</span>
                                <span class="badge text-bg-light border text-secondary rounded-pill">Por ingrediente</span>
                            </div>
                            <div class="ingrediente-nutricion-resumen small text-secondary mb-3"></div>
                            <div class="row g-2 ingrediente-nutricion-formulario">
                                <div class="col-6 col-lg-3">
                                    <label class="form-label small mb-1">Calorias</label>
                                    <input type="number" min="0" max="9999" step="0.01" class="form-control form-control-sm ingrediente-nutricion-input no-negative" data-campo="calorias" value="0">
                                </div>
                                <div class="col-6 col-lg-3">
                                    <label class="form-label small mb-1">Proteina</label>
                                    <input type="number" min="0" max="9999" step="0.01" class="form-control form-control-sm ingrediente-nutricion-input no-negative" data-campo="proteina" value="0">
                                </div>
                                <div class="col-6 col-lg-3">
                                    <label class="form-label small mb-1">Carbohidratos</label>
                                    <input type="number" min="0" max="9999" step="0.01" class="form-control form-control-sm ingrediente-nutricion-input no-negative" data-campo="carbohidratos" value="0">
                                </div>
                                <div class="col-6 col-lg-3">
                                    <label class="form-label small mb-1">Grasas</label>
                                    <input type="number" min="0" max="9999" step="0.01" class="form-control form-control-sm ingrediente-nutricion-input no-negative" data-campo="grasas" value="0">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <template id="pasoTemplate">
        <div class="input-group paso-item">
            <span class="input-group-text bg-body-secondary border-0 fw-semibold"></span>
            <input type="text" name="pasos[]" class="form-control" maxlength="100" placeholder="Describe este paso">
            <button type="button" class="btn btn-outline-secondary eliminar-paso">Eliminar</button>
        </div>
    </template>

    <div class="recipe-save-bar d-lg-none">
        <button type="submit" form="formReceta" class="btn btn-danger btn-lg rounded-4 w-100">
            <?= $esEdicion ? 'Actualizar receta' : 'Guardar receta' ?>
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/global/js/alertas.js"></script>
    <script>
        window.RecetaData = {
            ingredientesDisponibles: <?= $ingredientesJson ?: '[]' ?>,
            etiquetasDisponibles:    <?= $etiquetasJson ?: '[]' ?>,
            etiquetasSeleccionadas:  <?= json_encode(array_values(array_map('intval', $etiquetasSeleccionadas))) ?>,
            imagenesExistentes:      <?= json_encode(array_values($imagenesReceta), JSON_UNESCAPED_UNICODE) ?>
        };
    </script>
    <script src="/pages/individual/assets/CrearReceta.js"></script>
</body>
</html>
