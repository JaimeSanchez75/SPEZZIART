<div class="modal fade" id="modalNutricion" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-sm" style="border:none;">

            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Nutrición</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <label class="form-label">Calorías (kcal)</label>
                    <input type="number" class="form-control" id="calorias">
                </div>

                <div class="mb-3">
                    <label class="form-label">Proteína (g)</label>
                    <input type="number" class="form-control" id="proteina">
                </div>

                <div class="mb-3">
                    <label class="form-label">Carbohidratos (g)</label>
                    <input type="number" class="form-control" id="carbohidratos">
                </div>

                <div class="mb-3">
                    <label class="form-label">Grasas (g)</label>
                    <input type="number" class="form-control" id="grasas">
                </div>

            </div>

            <div class="modal-footer border-0">
                <button type="button"
                        class="btn btn-secondary rounded-pill"
                        data-bs-dismiss="modal">
                    Volver
                </button>

                <button type="button"
                        class="btn text-white rounded-pill"
                        style="background: var(--brand-wine);"
                        id="btnGuardarNutricion"
                >
                    Guardar
                </button>
            </div>

        </div>
    </div>
</div>

<script src="assets/ingrediente/nutricion.js"></script>
