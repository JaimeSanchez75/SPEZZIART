<?php require_once __DIR__ . '/../../../../../core/csrfcheck.php'; ?>

<div class="modal fade" id="modalEditarEtiqueta" tabindex="-1" aria-labelledby="modalEditarEtiquetaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 shadow-sm" style="border:none;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold text-dark" id="modalEditarEtiquetaLabel">Editar Etiqueta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formEditarEtiqueta" action="/App/pages/administracion/etiquetas/editar" method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="modal-body">


                    <div class="mb-3">
                        <label for="nombre" class="form-label text-dark fw-semibold">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" placeholder="Escribe el nombre" required>
                        <div class="invalid-feedback">Por favor ingresa un nombre.</div>
                    </div>

                   
                </div>

                <input type="hidden" name="etiqueta_id" id="etiqueta_id">


                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn text-white rounded-pill px-4" style="background: var(--brand-wine);">Editar etiqueta</button>
                </div>
                
            </form>
        </div>
    </div>
</div>
