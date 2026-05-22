

<div class="modal fade" id="modalActualizarIngredienteBase" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
            <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
            <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center p-4 pt-0 position-relative z-1">
                <div class="mb-4"><i class="bi bi-exclamation-triangle texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></div>
                <h3 class="fw-bold letraRomana subtitulo m-0 mb-2">El ingrediente ya existe</h3>
                <p class="texto text-dark mb-1">Ya hay un ingrediente base llamado <strong id="nombreIngredienteExistente" class="text-break"></strong>.</p>
                <p class="text-secondary texto mb-0">Si continúas, sus datos nutricionales se actualizarán con los del nuevo ingrediente.</p>
            </div>
            <div class="modal-footer border-0 d-flex justify-content-center gap-2 px-4 pb-4 position-relative z-1">
                <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4" id="confirmarActualizarIngredienteBase">Actualizar datos</button>
            </div>
        </div>
    </div>
</div>
