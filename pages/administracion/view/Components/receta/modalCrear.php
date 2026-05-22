<?php require_once __DIR__ . '/../../../../../core/auth.php'; ?>
<div class="modal fade" id="modalCrearReceta" tabindex="-1" aria-labelledby="modalCrearRecetaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
            <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
            <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formCrearReceta" action="/pages/administracion/receta/crear" method="post" enctype="multipart/form-data">

                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                    
                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div><span><i class="bi bi-egg-fried texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                        <div>
                            <h3 class="modal-title fw-bold subtitulo letraRomana m-0" id="modalCrearRecetaLabel">Crear Receta</h3>
                            <p class="texto text-secondary m-0" id="modalCrearRecetaSubtitulo">Completa los campos para crear una nueva receta.</p>
                        </div>
                    </div>

                    
                    <div class="mb-3">
                        <label for="Titulo" class="form-label text-dark fw-semibold">Título</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-card-heading texto-rojo textoMediano"></i></span>
                            <input type="text" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="Titulo" name="datos[Titulo]" placeholder="Escribe el título" required minlength="2" maxlength="120">
                            <div class="invalid-feedback">El título es obligatorio (entre 2 y 120 caracteres).</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="Descripcion" class="form-label text-dark fw-semibold">Descripción</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0 align-items-start pt-2"><i class="bi bi-text-paragraph texto-rojo textoMediano"></i></span>
                            <textarea class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="Descripcion" name="datos[Descripcion]" rows="3" placeholder="Escribe una descripción breve de la receta" required minlength="5" maxlength="1000"></textarea>
                            <div class="invalid-feedback">La descripción es obligatoria.</div>
                        </div>
                    </div>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="Tiempo" class="form-label text-dark fw-semibold">Tiempo (minutos)</label>
                            <div class="input-group rounded-3 overflow-hidden">
                                <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-clock texto-rojo textoMediano"></i></span>
                                <input type="number" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="Tiempo" name="datos[Tiempo]" placeholder="Ej: 30" required min="1" max="1440" step="1">
                                <div class="invalid-feedback">Indica un tiempo válido .</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="Porciones" class="form-label text-dark fw-semibold">Porciones</label>
                            <div class="input-group rounded-3 overflow-hidden">
                                <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-people texto-rojo textoMediano"></i></span>
                                <input type="number" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="Porciones" name="datos[Porciones]" placeholder="Ej: 4" required min="1" max="100" step="1">
                                <div class="invalid-feedback">Indica las porciones que hay en tu receta.</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="EtiquetasSearch" class="form-label text-dark fw-semibold">Etiquetas</label>
                        <div class="input-group rounded-3 flex-nowrap">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0 align-items-start pt-2"><i class="bi bi-tags texto-rojo textoMediano"></i></span>
                            <div class="combobox combobox-tags flex-grow-1 combobox-admin" id="comboEtiquetas" data-etiquetas='<?= htmlspecialchars(json_encode(array_map(function ($e) {return ["id" => (string)$e["ID_Etiqueta"], "nombre" => $e["Nombre"]];}, $etiquetas), JSON_UNESCAPED_UNICODE), ENT_QUOTES, "UTF-8") ?>'>
                                <div class="combobox-control">
                                    <div class="combobox-chips" id="chipsEtiquetas"></div>
                                    <input type="text" id="EtiquetasSearch" class="combobox-search" placeholder="Busca y selecciona etiquetas..." autocomplete="off">
                                </div>
                                <ul class="combobox-dropdown" hidden></ul>
                                <div class="hidden-inputs" hidden></div>
                            </div>
                        </div>
                        <div class="form-text">Escribe para filtrar. Pulsa una etiqueta para añadirla; en el chip, la × para quitarla.</div>
                    </div>
                    
                    <div class="mb-2">
                        <label class="form-label text-dark fw-semibold">Ingredientes</label>
                        <div id="contenedorIngredientes"></div>
                        <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-3 mt-2" id="agregarIngrediente"> <i class="bi bi-plus-lg me-1"></i>Agregar ingrediente</button>
                    </div>
                    
                    <div class="mb-2 mt-4">
                        <label class="form-label text-dark fw-semibold">Pasos</label>
                        <div id="contenedorPasos"></div>
                        <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-3 mt-2" id="agregarPaso"> <i class="bi bi-plus-lg me-1"></i>Agregar paso</button>
                    </div>
                    
                    <div class="mb-3 mt-4">
                        <label class="form-label text-dark fw-semibold">Imágenes</label>
                        <input type="file" name="imagen[]" id="inputImagenesReceta" class="form-control" accept="image/*" multiple>
                        <div class="form-text">Puedes subir varias fotos. La primera será la portada (puedes cambiarla pulsando una imagen).</div>
                        <input type="hidden" name="portada_imagen" id="inputPortadaImagen" value="">
                        <div id="imagenesRecetaHiddenInputs"></div>
                        <div id="gestorImagenesReceta" class="row row-cols-2 row-cols-md-3 g-2 mt-2"></div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-2">
                        <div>
                            <p class="texto fw-semibold text-dark mb-0">¿Es fit?</p>
                            <p class="textoPequeno text-secondary mb-0">Marca la receta como opción saludable.</p>
                        </div>
                        <div class="form-check form-switch m-0"><input class="form-check-input" type="checkbox" role="switch" name="datos[esfit]" id="esfit" checked></div>
                    </div>
                    <input type="hidden" name="datos[id_receta]" id="receta_id" value="0">
                    <input type="hidden" name="datos[calorias]" id="inputCalorias" value="0">
                    <input type="hidden" name="datos[proteina]" id="inputProteina" value="0">
                    <input type="hidden" name="datos[carbohidratos]" id="inputCarbohidratos" value="0">
                    <input type="hidden" name="datos[grasas]" id="inputGrasas" value="0">
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4" id="btnSubmitReceta">Crear receta</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="/pages/administracion/assets/receta/crearReceta.js"></script>
