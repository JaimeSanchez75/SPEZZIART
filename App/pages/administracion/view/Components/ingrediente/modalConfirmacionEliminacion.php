<!-- MODAL ELIMINAR INGREDIENTE -->
<div class="modal fade" id="eliminarIngrediente" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content admin-modal">

      <div class="modal-body text-center p-4">

        <div class="delete-icon mb-3">
          <i class="bi bi-trash"></i>
        </div>

        <h5 class="mb-2 fw-bold">Eliminar Ingrediente</h5>

        <p class="text-muted mb-1">
          ¿Seguro que deseas eliminar a la Ingrediente
          <strong id="nombreIngrediente"></strong>?
        </p>

        <p class="delete-warning">
          Esta acción no se puede deshacer.
        </p>

        <div class="d-flex justify-content-center gap-2 mt-4">
          <button class="btn-cancel" data-bs-dismiss="modal">
            Cancelar
          </button>

          <button class="btn-delete-user" id="eliminarIngrediente">
            Eliminar Ingrediente
          </button>
        </div>

      </div>
    </div>
  </div>
</div>

