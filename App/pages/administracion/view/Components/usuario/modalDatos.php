<!-- MODAL VER USUARIO -->
<div class="modal fade" id="verDatos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content ">

      <div class="modal-header border-0 d-flex justify-content-end p-3">
        <div class=""><button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-light" data-bs-dismiss="modal" aria-label="Cerrar" ></button></div>
      </div>

      <div class="modal-body ps-4 pe-4 pt-0 pb-2">

        <div class="d-flex mb-3 gap-3 align-items-center">
          <div >
            <span class="perfilUsuarioGrande  bg-rojo d-flex align-items-center justify-content-center text-white fw-semibold text-uppercase texto rounded-circle titulo sombra "  id="avatarUsuario"></span>
          </div>
          <div>
            <p class="text-secondary textoPequeno m-0">DATOS DEL USUARIO</p>
            <h6 id="nombreUsuarioModal" class="subtitulo fw-bold m-0"></h6>
            <div id="usernameUsuarioModal" class="text-secondary texto"></div>
          </div>
        </div>

        <div class="mt-4 d-flex flex-column gap-2 mb-2">
          
          <div class="border sombra p-3 rounded-3 d-flex gap-2 align-items-center justify-content-between">

            <div class="d-flex gap-2 align-items-center">

              <div class="mt-1">
                <span class="bg-rojoClaro p-2 pt-3 rounded rounded-3"><i class="bi bi-envelope iconos texto-rojo "></i></span>
              </div>

              <div class="d-flex flex-column justify-content-between">
                <p class="text-secondary textoPequeno m-0">EMAIL:</p> 
                <p id="emailUsuarioModal" class="texto m-0"></p>
              </div>

            </div>

            <div>
              <button class="bg-rojoClaro texto-rojo rounded-circle border-0" id="btnCopiar">
                <i class="bi bi-clipboard"></i>
              </button>
            </div>

          </div>
          <div>
            <div class="border sombra p-3 rounded-3 d-flex gap-2 align-items-center">
              <div class="mt-1">
                <span class="bg-rojoClaro p-2 pt-3 rounded rounded-3"><i class="bi bi-shield iconos texto-rojo " id="iconoRol"></i></span>
              </div>
              <div class="d-flex flex-column justify-content-between">
                <p class="text-secondary textoPequeno m-0">ROL:</p> 
                <p id="rolUsuarioModal"  class="texto m-0"></p>
              </div>
            </div>
          </div>
          
      </div>

      <div class="modal-footer ">
        <button type="button" class="bg-rojo text-white border-0 p-2  texto rounded-4 px-4 d-flex justify-content-between gap-2 align-items-center" data-bs-dismiss="modal">Cerrar <span class="rounded-circle bg-white texto-rojo px-1 texto"><i class="bi bi-x"></i></span></button>
      </div>

    </div>
  </div>
</div>
<script src="assets/modalDatosUsuario.js"></script>