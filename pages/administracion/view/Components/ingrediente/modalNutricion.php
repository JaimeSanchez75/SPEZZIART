<div class="modal fade" id="modalNutricion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
            <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
            <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                
                <div class="d-flex gap-3 align-items-center mb-4">
                    <div><span><i class="bi bi-bar-chart-line texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                    <div>
                        <h3 class="fw-bold subtitulo letraRomana m-0">Nutrición</h3>
                        <p class="texto text-secondary m-0">Información nutricional por 100g.</p>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="calorias" class="form-label text-dark fw-semibold">Calorías (kcal)</label>
                    <div class="input-group rounded-3 overflow-hidden">
                        <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-fire texto-rojo textoMediano"></i></span>
                        <input type="number" min="0.01" max="9999" step="0.01" oninput="if(this.value > 9999) this.value = 9999; if(this.value < 0) this.value = 0.01;" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="calorias" placeholder="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="proteina" class="form-label text-dark fw-semibold">Proteína (g)</label>
                    <div class="input-group rounded-3 overflow-hidden">
                        <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-egg-fried texto-rojo textoMediano"></i></span>
                        <input type="number" min="0.01" max="9999" step="0.01" oninput="if(this.value > 9999) this.value = 9999; if(this.value < 0) this.value = 0.01;" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="proteina" placeholder="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="carbohidratos" class="form-label text-dark fw-semibold">Carbohidratos (g)</label>
                    <div class="input-group rounded-3 overflow-hidden">
                        <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-basket texto-rojo textoMediano"></i></span>
                        <input type="number" min="0.01" max="9999" step="0.01" oninput="if(this.value > 9999) this.value = 9999; if(this.value < 0) this.value = 0.01;" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="carbohidratos" placeholder="0">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="grasas" class="form-label text-dark fw-semibold">Grasas (g)</label>
                    <div class="input-group rounded-3 overflow-hidden">
                        <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-droplet texto-rojo textoMediano"></i></span>
                        <input type="number" min="0.01" max="9999" step="0.01" oninput="if(this.value > 9999) this.value = 9999; if(this.value < 0) this.value = 0.01;" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="grasas" placeholder="0">
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 px-4 pb-4 position-relative z-1">
                <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Volver</button>
                <button type="button" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4" id="btnGuardarNutricion">Guardar</button>
            </div>
        </div>
    </div>
</div>
<script src="/pages/administracion/assets/ingrediente/nutricion.js"></script>
