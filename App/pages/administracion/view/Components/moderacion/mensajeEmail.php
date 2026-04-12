<div class="modal fade" id="modalModeracion" tabindex="-1">

    <div class="modal-dialog">

        <div class="modal-content">

            <form method="POST" action="/App/moderacion/aceptarReporte">

                <div class="modal-header">
                    <h5 class="modal-title">Aceptar reporte</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="reporte_id" id="reporte_id">
                    <input type="hidden" name="receta_id" id="receta_id">

                    <label class="form-label">Acción tomada</label>

                    <select name="accion" class="form-control" required>

                        <option value="">Seleccionar acción</option>
                        <option value="eliminar_receta">Eliminar publicación</option>
                        <option value="eliminar_usuario">Eliminar usuario</option>

                    </select>

                    <label class="form-label mt-3">Mensaje para el usuario</label>

                    <textarea
                        name="mensaje"
                        class="form-control"
                        rows="4"
                        placeholder="Mensaje que se enviará por Gmail..."
                        required></textarea>

                </div>

                <div class="modal-footer">

                    <button class="btn btn-danger">
                        Aplicar acción
                    </button>

                </div>

            </form>

        </div>
    </div>
</div>