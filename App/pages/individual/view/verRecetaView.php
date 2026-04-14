<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($receta['Titulo']) ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <a href="/App/pages/individual" class="btn btn-secondary mb-3">Volver</a>
        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <!-- IMAGEN -->
            <?php if (!empty($receta['Imagen'])): ?><img src="/App/uploads/<?= htmlspecialchars($receta['Imagen']) ?>" class="w-100" style="max-height:400px; object-fit:cover;"><?php endif; ?>
            <div class="card-body p-4">
                <!-- TITULO -->
                <h2 class="fw-bold"><?= htmlspecialchars($receta['Titulo']) ?></h2>
                <!-- INFO -->
                <p class="text-muted mb-2">
                    <?= htmlspecialchars($receta['Username']) ?> ·
                    <?= $receta['Tiempo'] ?> min ·
                    <?= $receta['Porciones'] ?> porciones
                </p>
                <!-- FIT -->
                <?php if ($receta['EsFit']): ?><span class="badge bg-success mb-3">FIT</span><?php endif; ?>
                <!-- DESCRIPCION -->
                <?php if (!empty($receta['DescripcionVisible'])): ?>
                    <h5 class="mt-3">Descripcion</h5>
                    <p><?= nl2br(htmlspecialchars($receta['DescripcionVisible'])) ?></p>
                <?php endif; ?>
                <!-- PASOS -->
                <?php if (!empty($receta['Pasos'])): ?>
                    <h5 class="mt-4">Pasos</h5>
                    <ol class="list-group list-group-numbered">
                        <?php foreach ($receta['Pasos'] as $paso): ?>
                            <li class="list-group-item"><?= htmlspecialchars($paso) ?></li>
                        <?php endforeach; ?>
                    </ol>
                <?php endif; ?>
                <!-- INGREDIENTES -->
                <?php if (!empty($receta['ingredientes'])): ?>
                    <h5 class="mt-4">Ingredientes</h5>
                    <ul class="list-group">
                        <?php foreach ($receta['ingredientes'] as $ing): ?><li class="list-group-item"><?= htmlspecialchars($ing['Nombre']) ?></li><?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
