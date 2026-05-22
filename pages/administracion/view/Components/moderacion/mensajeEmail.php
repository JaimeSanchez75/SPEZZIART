<div class="modal fade" id="modalModeracion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
            <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
            <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="/moderacion/aceptarReporte">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                    
                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div><span><i class="bi bi-flag texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                        <div>
                            <h3 class="fw-bold subtitulo letraRomana m-0" id="modalModeracionTitulo">Aceptar reporte</h3>
                            <p class="texto text-secondary m-0">Selecciona la acción y notifica al usuario.</p>
                        </div>
                    </div>
                    <input type="hidden" name="reporte_id"        id="reporte_id">
                    <input type="hidden" name="receta_id"         id="receta_id">
                    <input type="hidden" name="comentario_id"     id="comentario_id">
                    <input type="hidden" name="usuario_reportado" id="usuario_reportado">
                    <input type="hidden" name="tipo"              id="tipo_reporte">
                    
                    <div class="mb-3">
                        <label for="accionReporte" class="form-label text-dark fw-semibold">Acción tomada</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-shield-check texto-rojo textoMediano"></i></span>
                            <select name="accion" id="accionReporte" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" required>
                                <option value="">Seleccionar acción</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 position-relative z-1">
                    <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4">Aplicar acción</button>
                </div>
            </form>
        </div>
    </div>
</div>
