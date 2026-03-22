<!-- MODAL VER USUARIO -->
<div class="modal fade" id="verDatos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Datos del usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">

        <div class="text-center mb-3">
          <div id="avatarUsuario" class="user-avatar mx-auto"></div>
          <h6 id="nombreUsuarioModal" class="mt-2 fw-bold"></h6>
          <small id="usernameUsuarioModal" class="text-muted"></small>
        </div>

        <div class="mt-3">
          <p><strong>Email:</strong> <span id="emailUsuarioModal"></span></p>
          <p><strong>Rol:</strong> <span id="rolUsuarioModal"></span></p>
        </div>

      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>

    </div>
  </div>
</div>
<script src="assets/modalDatosUsuario.js"></script>