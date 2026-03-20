<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Receta</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="card shadow p-4">
        <h2 class="mb-4">Crear receta</h2>

        <form action="/App/pages/individual/guardar" method="POST">

            <!-- TITULO -->
            <div class="mb-3">
                <label class="form-label">Título</label>
                <input type="text" name="titulo" class="form-control" required>
            </div>

            <!-- DESCRIPCION -->
            <div class="mb-3">
                <label class="form-label">Descripción</label>
                <textarea name="descripcion" class="form-control"></textarea>
            </div>

            <!-- TIEMPO + PORCIONES -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tiempo (min)</label>
                    <input type="number" name="tiempo" class="form-control">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Porciones</label>
                    <input type="number" name="porciones" class="form-control">
                </div>
            </div>

            <!-- IMAGEN -->
            <div class="mb-3">
                <label class="form-label">Imagen (URL o ruta)</label>
                <input type="text" name="imagen" class="form-control">
            </div>

            <!-- FIT -->
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="fit">
                <label class="form-check-label">Receta Fit</label>
            </div>

            <!-- BOTON -->
            <button type="submit" class="btn btn-danger w-100">
                Guardar receta
            </button>

        </form>
    </div>

</div>

</body>
</html>