<div class="modal fade" id="modalEditarIngrediente" tabindex="-1" aria-labelledby="modalEditarIngredienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-sm" style="border:none;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-dark" id="modalEditarIngredienteLabel">Editar Ingrediente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formEditarIngrediente" action="/App/pages/administracion/Ingredientes/editar" method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="modal-body">

                    <div class="mb-3">
                        <label for="nombreEditar" class="form-label text-dark fw-semibold">Nombre</label>
                        <input type="text" class="form-control" id="nombreEditar" name="datos[nombre]" placeholder="Escribe el nombre" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-dark fw-semibold">Nutrición</label>
                        <button type="button"
                                class="btn btn-light w-100 text-start rounded-pill"
                                id="btn-nutricion"
                                data-bs-toggle="modal"
                                data-bs-target="#modalNutricion"  data-calorias="" data-proteina="" data-carbohidratos="" data-grasas="">
                            Añadir / Editar información nutricional →
                        </button>
                    </div>

                   
                    <input type="hidden" name="datos[calorias]" id="inputCaloriasEditar">
                    <input type="hidden" name="datos[proteina]" id="inputProteinaEditar">
                    <input type="hidden" name="datos[carbohidratos]" id="inputCarbohidratosEditar">
                    <input type="hidden" name="datos[grasas]" id="inputGrasasEditar">
                    <input type="hidden" name="ingrediente_id" id="ingrediente_id">

                </div>

                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit"  class="btn text-white rounded-pill px-4" style="background: var(--brand-wine);">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="assets/ingrediente/editarIngrediente.js"></script>