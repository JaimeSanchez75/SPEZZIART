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
            <h2 class="mb-4"><?= isset($receta) ? 'Editar receta' : 'Crear receta' ?></h2>
            <form action="/App/pages/individual/guardar" method="POST">
                <!-- ID OCULTO -->
                <input type="hidden" name="id" value="<?= $receta['ID_Receta'] ?? '' ?>">
                <!-- TITULO -->
                <div class="mb-3">
                    <label class="form-label">Titulo</label>
                    <input type="text" name="titulo" class="form-control" required value="<?= htmlspecialchars($receta['Titulo'] ?? '') ?>">
                </div>
                <!-- DESCRIPCION -->
                <div class="mb-3">
                    <label class="form-label">Descripcion</label>
                    <textarea name="descripcion" class="form-control"><?= htmlspecialchars($descripcionFormulario ?? '') ?></textarea>
                </div>
                <!-- PASOS -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="form-label mb-0">Pasos</label>
                        <button type="button" class="btn btn-outline-danger btn-sm" id="agregarPaso">+</button>
                    </div>
                    <div id="listaPasos">
                        <?php foreach (($pasosFormulario ?? ['']) as $indice => $paso): ?>
                            <div class="input-group mb-2 paso-item">
                                <span class="input-group-text">Paso <?= $indice + 1 ?></span>
                                <input
                                    type="text"
                                    name="pasos[]"
                                    class="form-control"
                                    value="<?= htmlspecialchars($paso) ?>"
                                    placeholder="Describe este paso">
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <!-- TIEMPO + PORCIONES -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tiempo (min)</label>
                        <input type="number" name="tiempo" class="form-control" value="<?= $receta['Tiempo'] ?? '' ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Porciones</label>
                        <input type="number" name="porciones" class="form-control" value="<?= $receta['Porciones'] ?? '' ?>">
                    </div>
                </div>
                <!-- IMAGEN -->
                <div class="mb-3">
                    <label class="form-label">Imagen (URL o ruta)</label>
                    <input type="text" name="imagen" class="form-control" value="<?= htmlspecialchars($receta['Imagen'] ?? '') ?>">
                </div>
                <!-- FIT -->
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="fit" <?= (!empty($receta) && $receta['EsFit']) ? 'checked' : '' ?>>
                    <label class="form-check-label">Receta Fit</label>
                </div>
                <!-- BOTON -->
                <button type="submit" class="btn btn-danger w-100"><?= isset($receta) ? 'Actualizar receta' : 'Guardar receta' ?></button>
            </form>
        </div>
    </div>

    <template id="pasoTemplate">
        <div class="input-group mb-2 paso-item">
            <span class="input-group-text"></span>
            <input type="text" name="pasos[]" class="form-control" placeholder="Describe este paso">
        </div>
    </template>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const listaPasos = document.getElementById('listaPasos');
            const agregarPaso = document.getElementById('agregarPaso');
            const pasoTemplate = document.getElementById('pasoTemplate');

            const renumerarPasos = () => {
                listaPasos.querySelectorAll('.paso-item').forEach((paso, indice) => {
                    const etiqueta = paso.querySelector('.input-group-text');
                    if (etiqueta) {
                        etiqueta.textContent = `Paso ${indice + 1}`;
                    }
                });
            };

            agregarPaso.addEventListener('click', () => {
                const nuevoPaso = pasoTemplate.content.firstElementChild.cloneNode(true);
                listaPasos.appendChild(nuevoPaso);
                renumerarPasos();
            });

            renumerarPasos();
        });
    </script>
</body>
</html>
