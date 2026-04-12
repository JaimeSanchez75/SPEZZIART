<?php require_once __DIR__ . '/../../../../../core/csrfcheck.php'; ?>

<div class="modal fade" id="modalCrearReceta" tabindex="-1" aria-labelledby="modalCrearRecetaLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-sm">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-dark" id="modalCrearRecetaLabel">Crear Receta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formCrearReceta" action="/App/pages/administracion/receta/crear" method="post">

                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="modal-body">


                    <div class="mb-3">
                        <label for="Titulo" class="form-label text-dark fw-semibold">Titulo</label>
                        <input type="text" class="form-control" id="Titulo" name="datos[Titulo]" placeholder="Escribe el titulo" required>
                        <div class="invalid-feedback">Por favor ingresa un titulo.</div>
                    </div>

                    <div class="mb-3">
                        <label for="Descripcion" class="form-label text-dark fw-semibold">Descripción</label>
                        <textarea class="form-control" id="Descripcion" name="datos[Descripcion]" rows="3" placeholder="Escribe una descripción breve de la receta" required></textarea>
                        <div class="invalid-feedback">Por favor ingresa una descripción.</div>
                    </div>

                    <div class="mb-3">
                        <label for="Tiempo" class="form-label text-dark fw-semibold">Tiempo de preparación (minutos)</label>
                        <input type="number" class="form-control" id="Tiempo" name="datos[Tiempo]" placeholder="Ejemplo: 30" required>
                        <div class="invalid-feedback">Por favor ingresa el tiempo de preparación.</div>
                    </div>

                    <div class="mb-3">
                        <label for="Porciones" class="form-label text-dark fw-semibold">Porciones</label>
                        <input type="number" class="form-control" id="Porciones" name="datos[Porciones]" placeholder="Ejemplo: 4" required>
                        <div class="invalid-feedback">Por favor ingresa el número de porciones.</div>
                    </div>

                    <div class="mb-3">
                        <label for="Etiquetas" class="form-label text-dark fw-semibold">Etiquetas</label>
                        <select name="datos[Etiquetas][]" id="Etiquetas" class="form-control" multiple required>
                            <?php foreach ($etiquetas as $etiqueta) { ?>
                                <option value="<?php echo $etiqueta['ID_Etiqueta']; ?>"><?php echo $etiqueta['Nombre']; ?></option>
                            <?php } ?>
                        </select>
                        <div class="form-text">Selecciona las etiquetas.</div>
                    </div>
                    <!-- ingredientes -->
                    <div class="mb-3" id="contenedorIngredientes">
                    
                    </div>
                    

                    <button type="button" class="btn btn-outline-secondary mt-2" id="agregarIngrediente">Agregar Ingrediente</button>
                    <!-- fin de ingredientes -->
                    <!-- pasos -->
                    <div id="contenedorPasos">

                    </div>
                    <button type="button" class="btn btn-outline-secondary mt-2" id="agregarPaso">Agregar Paso</button>

                    <div class="mb-3 mt-4">
                        <label class="form-label text-dark fw-semibold">Nutrición</label>
                        <button type="button"
                                id="btnAbrirNutricionReceta"
                                class="btn btn-light w-100 text-start rounded-pill"
                                data-bs-toggle="modal"
                                data-bs-target="#modalNutricion"
                                data-calorias="0"
                                data-proteina="0"
                                data-carbohidratos="0"
                                data-grasas="0">
                            Añadir información nutricional →
                        </button>
                    </div>

                    <div class="mb-3 mt-4">
                        <label class="form-label text-dark fw-semibold">¿Es fit?</label>
                        <input type="checkbox" name="datos[esfit]" id="esfit" checked>
                    </div>

                    <input type="hidden" name="datos[id_receta]" id="receta_id" value="0">
                    <input type="hidden" name="datos[calorias]" id="inputCalorias" value='0'>
                    <input type="hidden" name="datos[proteina]" id="inputProteina" value='0'>
                    <input type="hidden" name="datos[carbohidratos]" id="inputCarbohidratos" value='0'>
                    <input type="hidden" name="datos[grasas]" id="inputGrasas" value='0'>

                </div>


                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4" style="background: var(--brand-wine);">Crear receta</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script src="assets/receta/crearReceta.js"></script>
