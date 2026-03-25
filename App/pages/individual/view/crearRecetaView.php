<?php

class CrearRecetaView {

    public function render($config = null) {
        ?>

        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <title>Crear Receta</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="/App/global/styles/global.css">
        </head>

        <!-- ✅ CONFIG DESDE JWT -->
        <body data-bs-theme="<?php echo ($config && $config['ModoOscuro']) ? 'dark' : 'light'; ?>">

        <div class="container mt-4">
            <h2 class="fw-bold mb-4">Crear nueva receta</h2>

            <!-- ✅ RUTA CORRECTA -->
            <form method="POST" action="/App/pages/individual/guardar">

                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="3"></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tiempo</label>
                    <input type="number" name="tiempo" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Porciones</label>
                    <input type="number" name="porciones" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Imagen (ruta)</label>
                    <input type="text" name="imagen" class="form-control">
                </div>

                <div class="form-check mb-3">
                    <input type="checkbox" name="fit" class="form-check-input" id="fitCheck">
                    <label class="form-check-label" for="fitCheck">Receta Fit</label>
                </div>

                <button type="submit" class="btn btn-danger">Guardar receta</button>
                <a href="/App/pages/individual" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
        </body>
        </html>

        <?php
    }
}