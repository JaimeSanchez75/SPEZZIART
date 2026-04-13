<?php require_once __DIR__ . '/../../../../../core/csrfcheck.php'; ?>

<div class="modal fade" id="modalEditarEtiqueta" tabindex="-1" aria-labelledby="modalEditarEtiquetaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" >

            <div class="modal-header border-0 d-flex justify-content-end p-3">
                <div class=""><button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-light" data-bs-dismiss="modal" aria-label="Cerrar" ></button></div>
            </div>

            <form id="formEditarEtiqueta" action="/App/pages/administracion/etiquetas/editar" method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="modal-body ps-4 pe-4 pt-0 pb-2">
                    <?php if (isset($_GET['error']) && $_GET['error'] === 'existe') { ?>
                        <div class="alert alert-danger  texto alert-dismissible" id="alertEditarEtiqueta">
                            Ya existe una etiqueta con ese nombre.
                        </div>
                    <?php } ?>

                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div>
                            <span><i class="bi bi-bookmark-star texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span>
                        </div>
                        <div>
                            <h3 class="fw-bold subtitulo letraRomana m-0">Editar Etiqueta</h3>
                            <p class="texto text-secondary m-0">Completa los campos para editar la etiqueta.</p>
                        </div>
                    </div>


                    <div class="mb-3">
                        <label for="nombre" class="form-label text-dark fw-semibold">Nombre</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-bookmark texto-rojo textoMediano"></i></span>
                            <input type="text" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="nombreEditar" name="nombre" placeholder="Escribe el nombre" required>
                            <div class="invalid-feedback">Por favor ingresa un nombre.</div>
                        </div>
                        
           
                    </div>

                   
                </div>

                <input type="hidden" name="etiqueta_id" id="etiqueta_id">


                <div class="modal-footer border-0">
                    <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="bg-rojo text-white border-0 p-2  texto rounded-4 px-4">Editar etiqueta</button>
                </div>
                
            </form>
        </div>
    </div>
</div>
