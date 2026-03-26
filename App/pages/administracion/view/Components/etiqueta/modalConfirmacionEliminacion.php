<!-- MODAL ELIMINAR ETIQUETA -->
<div class="modal fade" id="eliminarEtiqueta" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content admin-modal">

      <div class="modal-body text-center p-4">

        <div class="delete-icon mb-3">
          <i class="bi bi-trash"></i>
        </div>

        <h5 class="mb-2 fw-bold">Eliminar etiqueta</h5>

        <p class="text-muted mb-1">
          ¿Seguro que deseas eliminar a la etiqueta
          <strong id="nombreEtiqueta"></strong>?
        </p>

        <p class="delete-warning">
          Esta acción no se puede deshacer.
        </p>

        <div class="d-flex justify-content-center gap-2 mt-4">
          <button class="btn-cancel" data-bs-dismiss="modal">
            Cancelar
          </button>

          <button class="btn-delete-user" id="eliminarEtiqueta">
            Eliminar etiqueta
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

<script src="assets/etiqueta/eliminarUsuario.js"></script>