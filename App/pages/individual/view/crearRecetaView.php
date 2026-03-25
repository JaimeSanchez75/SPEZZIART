<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= isset($receta) ? 'Editar Receta' : 'Crear Receta' ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow p-4">
        <h2 class="mb-4">
            <?= isset($receta) ? 'Editar receta' : 'Crear receta' ?>
        </h2>

        <form action="/App/pages/individual/guardar" method="POST">

            <!-- ID OCULTO -->
            <input type="hidden" name="id" value="<?= $receta['ID_Receta'] ?? '' ?>">

            <!-- TITULO -->
            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control" required
                       value="<?= htmlspecialchars($receta['Titulo'] ?? '') ?>">
            </div>

            <!-- DESCRIPCION -->
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control"><?= htmlspecialchars($receta['Descripcion'] ?? '') ?></textarea>
            </div>

            <!-- TIEMPO + PORCIONES -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tiempo (min)</label>
                    <input type="number" name="tiempo" class="form-control"
                           value="<?= $receta['Tiempo'] ?? '' ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Porciones</label>
                    <input type="number" name="porciones" class="form-control"
                           value="<?= $receta['Porciones'] ?? '' ?>">
                </div>
            </div>

            <!-- IMAGEN -->
            <div class="mb-3">
                <label class="form-label">Imagen (URL o ruta)</label>
                <input type="text" name="imagen" class="form-control"
                       value="<?= htmlspecialchars($receta['Imagen'] ?? '') ?>">
            </div>

            <!-- FIT -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="fit"
                    <?= (!empty($receta) && $receta['EsFit']) ? 'checked' : '' ?>>
                <label class="form-check-label">Receta Fit</label>
            </div>

            <!-- BOTON -->
            <button type="submit" class="btn btn-danger w-100">
                <?= isset($receta) ? 'Actualizar receta' : 'Guardar receta' ?>
            </button>

        </form>
    </div>

</div>

</body>
</html>