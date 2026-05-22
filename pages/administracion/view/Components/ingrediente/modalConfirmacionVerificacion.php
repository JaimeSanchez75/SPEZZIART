
<div class="modal fade" id="verificaringrediente" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
           <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
            <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body text-center p-4 pt-0 position-relative z-1">
                <div class="mb-4"><i class="bi bi-check2-circle texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></div>
                <h3 class="fw-bold letraRomana subtitulo m-0 mb-2">Verificar ingrediente</h3>
                <p class="texto text-dark mb-1">¿Seguro que deseas verificar el ingrediente <strong id="nombreIngredienteVerificar"></strong>?</p>
                <p class="text-secondary texto mb-0">Una vez verificado pasará a la base de la app.</p>
            </div>
            <div class="modal-footer border-0 d-flex justify-content-center gap-2 px-4 pb-4 position-relative z-1">
                <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4" id="siverificarIngrediente">Verificar ingrediente</button>
            </div>
        </div>
    </div>
</div>
