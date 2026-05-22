<?php

declare(strict_types=1); ?>

<section class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
        <div>
            <h2 class="subtitulo letraRomana fw-bold m-0">Ingredientes</h2>
            <p class="texto text-secondary">Crea, edita o elimina el contenido base de la aplicación.</p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <button class="text-white px-4 py-2 rounded-pill border-0 bg-rojo texto fw-medium" data-bs-toggle="modal" data-bs-target="#modalCrearIngrediente">
                <i class="bi bi-plus-lg me-2"></i>Nuevo ingrediente
            </button>
            <button class="border border-rojo bg-rojoClaro texto-rojo px-4 py-2 rounded-pill texto fw-medium" data-bs-toggle="modal" data-bs-target="#modalImportarIngredientes">
                <i class="bi bi-file-earmark-arrow-up me-2"></i>Importar
            </button>
        </div>
    </div>


    <div class="row justify-content-between align-items-center mb-4">

        <div class="col-12 col-sm-6 col-md-5">
            <div class="input-group border rounded rounded-4">
                <span class="input-group-text bg-light border-0 rounded-start-4">
                    <i class="bi bi-search text-secondary"></i>
                </span>
                <input type="text" class="form-control input border-0 bg-light texto rounded-end-4 text-secondary"
                    placeholder="Buscar por nombre ..." id="buscador">
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs adminTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabIngredientesBase" type="button" role="tab">Base</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabIngredientesUsu" type="button" role="tab">Hechos por usuarios</button>
        </li>
    </ul>

    <div class="tab-content ">

        <div class="tab-pane fade show active mt-3" id="tabIngredientesBase" role="tabpanel">

            <div id="no-resultados" class="text-center py-4"></div>

            <div class="table-responsive  ">
                <table class="w-100 tablaAdmin tablaAdmin--responsive " id="tablaPaginada" data-paginacion-pendiente="true">
                    <thead class="bg-light text-secondary text-uppercase texto">
                        <tr>
                            <th class="py-3 ps-4" data-ordenacion="string" data-orden="desc">INGREDIENTE <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                            <th class="py-3 text-center" >UNIDAD</th>
                            <th class="py-3 text-center" data-ordenacion="number" data-orden="desc">CAL <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                            <th class="py-3 text-center" data-ordenacion="number" data-orden="desc">PROT <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                            <th class="py-3 text-center" data-ordenacion="number" data-orden="desc">HC <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                            <th class="py-3 pe-3 text-center" data-ordenacion="number" data-orden="desc">GR <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingredientesBase as $ingrediente) { ?>

                            <tr data-id="<?= (int)$ingrediente['ID_Ingrediente'] ?>" class="registroTabla border-bottom">

                                <td class="py-2 ps-3" data-label="Ingrediente">

                                    <span class="d-none text-break" data-buscado="true"><?= htmlspecialchars($ingrediente['Nombre']) ?></span>

                                    <div class="celda-contenido d-flex flex-column align-items-end align-items-md-start gap-1">

                                        <input type="text" class="form-control texto" data-contenido="nomb" value="<?= htmlspecialchars($ingrediente['Nombre']) ?>" required minlength="2" maxlength="100">
                                        <span class="badge <?= ((int)$ingrediente['Verificada'] === 1) ? 'bg-verdeClaro texto-verde' : 'bg-grisClaro texto-gris' ?> textoPequeno">
                                            <?= ((int)$ingrediente['Verificada'] === 1) ? 'Verificada' : 'No verificada' ?>
                                        </span>

                                    </div>

                                </td>

                                <td class="text-center" data-label="UNIDAD">
                                    <div class="badge bg-rojoClaro texto-rojo textoPequeno"><?= htmlspecialchars($ingrediente['Unidad_Base'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>

                                <td class="text-center" data-label="Cal">
                                    <input type="number" min="0" max="9999" step="0.01" class="form-control texto text-center celda-contenido" data-contenido="cal" value="<?= number_format((float)$ingrediente['Calorias'], 2, '.', '') ?>">
                                </td>

                                <td class="text-center" data-label="Prot">
                                    <input type="number" min="0" max="999" step="0.01" class="form-control texto text-center celda-contenido" data-contenido="prot" value="<?= number_format((float)$ingrediente['Proteina'], 2, '.', '') ?>">
                                </td>

                                <td class="text-center" data-label="HC">
                                    <input type="number" min="0" max="999" step="0.01" class="form-control texto text-center celda-contenido" data-contenido="ch" value="<?= number_format((float)$ingrediente['Carbohidratos'], 2, '.', '') ?>">
                                </td>

                                <td class="text-center pe-3" data-label="Gr">
                                    <input type="number" min="0" max="999" step="0.01" class="form-control texto text-center celda-contenido" data-contenido="gr" value="<?= number_format((float)$ingrediente['Grasas'], 2, '.', '') ?>">
                                </td>

                            </tr>

                        <?php } ?>

                    </tbody>
                </table>
            </div>
        </div>


        <div class="tab-pane fade mt-3" id="tabIngredientesUsu" role="tabpanel">

            <div id="no-resultados" class="text-center py-4"></div>

            <div class="table-responsive rounded-4 ">
                <table class="w-100 tablaAdmin tablaAdmin--responsive" id="tablaIngredientesUsuario" data-paginacion-pendiente="true">
                    <thead class="bg-light text-secondary text-uppercase texto">
                        <tr>
                            <th class="py-3 ps-3" data-ordenacion="string" data-orden="desc">INGREDIENTE <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                            <th class="py-3 text-center" >UNIDAD</th>
                            <th class="py-3 text-center" data-ordenacion="number">CAL <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                            <th class="py-3 text-center" data-ordenacion="number">PROT <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                            <th class="py-3 text-center" data-ordenacion="number">HC <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                            <th class="py-3 text-center" data-ordenacion="number">GR <span class="orden"><i class="bi bi-caret-down-fill"></i></span></th>
                            <th class="py-3 text-end pe-3">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ingredientesUsu as $ingrediente) { ?>

                            <tr data-id="<?= (int)$ingrediente['ID_Ingrediente'] ?>" class="registroTabla border-bottom align-middle">

                                <td class="py-2 ps-3" data-label="Ingrediente">
                                    <div class="celda-contenido d-flex flex-column align-items-md-start align-items-end gap-1">
                                        <div class="fw-semibold texto text-break" data-buscado="true"><?= htmlspecialchars($ingrediente['Nombre']) ?></div>
                                        <span class="badge <?= ((int)$ingrediente['Verificada'] === 1) ? 'bg-verdeClaro texto-verde' : 'bg-grisClaro texto-gris' ?> textoPequeno">
                                            <?= ((int)$ingrediente['Verificada'] === 1) ? 'Verificada' : 'No verificada' ?>
                                        </span>
                                    </div>
                                </td>

                                <td class="text-center" data-label="UNIDAD">
                                    <div class="badge bg-rojoClaro texto-rojo textoPequeno"><?= htmlspecialchars($ingrediente['Unidad_Base'], ENT_QUOTES, 'UTF-8') ?></div>
                                </td>

                                <td class="text-center texto text-break" data-label="Cal"><span class="celda-contenido"><?= (int)$ingrediente['Calorias'] ?></span></td>

                                <td class="text-center texto text-break" data-label="Prot"><span class="celda-contenido"><?= (int)$ingrediente['Proteina'] ?></span></td>

                                <td class="text-center texto text-break" data-label="HC"><span class="celda-contenido"><?= (int)$ingrediente['Carbohidratos'] ?></span></td>

                                <td class="text-center texto text-break" data-label="Gr"><span class="celda-contenido"><?= (int)$ingrediente['Grasas'] ?></span></td>

                                <td class="text-end pe-3 ocultaAcciones" data-label="Acciones">
                                    <span class="celda-contenido">
                                        <i class="bi bi-check-square text-secondary cursor-pointer"
                                            data-bs-toggle="modal" data-bs-target="#verificaringrediente"
                                            data-id="<?= (int)$ingrediente['ID_Ingrediente'] ?>"
                                            data-nombre="<?= htmlspecialchars($ingrediente['Nombre']) ?>">
                                        </i>
                                    </span>

                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php
require __DIR__ . "/../Components/ingrediente/modalCrear.php";
require __DIR__ . "/../Components/ingrediente/modalNutricion.php";
require __DIR__ . "/../Components/ingrediente/modalConfirmacion.php";
require __DIR__ . "/../Components/ingrediente/modalConfirmacionVerificacion.php";
require __DIR__ . "/../Components/ingrediente/modalConfirmacionActualizacion.php";
require __DIR__ . "/../Components/ingrediente/modalImportar.php";
?>
<script src="assets/ingrediente/editarIngredienteTabla.js"></script>
<script src="assets/ingrediente/verificarIngrediente.js"></script>
<script src="assets/componentePaginacion.js"></script>