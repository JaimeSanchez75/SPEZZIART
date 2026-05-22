<?php require_once __DIR__ . '/../../../../../core/auth.php'; ?>
<div class="modal fade" id="modalImportarIngredientes" tabindex="-1" aria-labelledby="modalImportarIngredientesLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
            <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
            <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formImportarIngredientes" action="/pages/administracion/Ingredientes/importar" method="post" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                    
                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div><span><i class="bi bi-file-earmark-arrow-up texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                        <div>
                            <h3 class="fw-bold subtitulo letraRomana m-0" id="modalImportarIngredientesLabel">Importar Ingredientes</h3>
                            <p class="texto text-secondary m-0">Sube un archivo CSV para añadir ingredientes en bloque.</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="archivo" class="form-label text-dark fw-semibold">Archivo CSV</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-filetype-csv texto-rojo textoMediano"></i></span>
                            <input type="file" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="archivo" name="archivo" accept=".csv" required>
                            <div class="invalid-feedback">Por favor selecciona un archivo CSV.</div>
                        </div>
                        <a class="form-text textoPequeno" href="/uploads/Ingredientes.csv">Descargar aquí plantilla.</a>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 z-5 position-relative">
                    <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4" >Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>
