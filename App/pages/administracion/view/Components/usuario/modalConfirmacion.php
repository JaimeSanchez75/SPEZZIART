<!-- MODAL ELIMINAR USUARIO -->
<div class="modal fade" id="eliminarUsuario" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      
      <div class="modal-header border-0 d-flex justify-content-end p-3">
          <div class=""><button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-light" data-bs-dismiss="modal" aria-label="Cerrar" ></button></div>
      </div>

      <div class="modal-body text-center p-4 pt-0">

        <div class=" mb-4">

          <i class="bi bi-trash texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i>
        
        </div>

        <div>

          <h5 class="mb-2 fw-bold letraRomana fs-3">Eliminar usuario</h5>

          <p class=" mb-1 texto">¿Seguro que deseas eliminar a <strong id="nombreUsuario"></strong>?</p>

          <p class="text-secondary mb-2 texto">Esta acción no se puede deshacer.</p>

        </div>

      </div>

      <div class="d-flex justify-content-center gap-2  modal-footer ">

        <button class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">
          Cancelar
        </button>

        <button class="border text-white border p-2 bg-rojo texto rounded-4 px-4" id="EliminarUsuario">
          Eliminar usuario
        </button>
      </div>

    </div>
    
  </div>
</div>

<script src="assets/peticionModalConfirmacion.js"></script>