<?php require_once __DIR__ . '/../../../../../core/auth.php'; ?>
<div class="modal fade" id="modalCrearIngrediente" tabindex="-1" aria-labelledby="modalCrearIngredienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
            <?php include ROOT_PATH . '/global/svgDecoracionModal.php'; ?>
            <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formCrearIngrediente" action="/pages/administracion/Ingredientes/crear" method="post">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                    
                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div><span><i class="bi bi-basket texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                        <div>
                            <h3 class="fw-bold subtitulo letraRomana m-0" id="modalCrearIngredienteLabel">Crear Ingrediente</h3>
                            <p class="texto text-secondary m-0">Completa los campos para crear un nuevo ingrediente.</p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label text-dark fw-semibold">Nombre del ingrediente</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-tag texto-rojo textoMediano"></i></span>
                            <input type="text" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" id="nombre" name="datos[nombre]" placeholder="Escribe el nombre" required minlength="2" maxlength="100">
                            <div class="invalid-feedback">El nombre debe tener entre 2 y 100 caracteres.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="unidad" class="form-label text-dark fw-semibold">Unidad de medida</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0">
                                <i class="bi bi-rulers texto-rojo textoMediano"></i>
                            </span>
                            <select class="form-select texto text-secondary border-start-0 rounded-3 rounded-start-0" id="unidad" name="datos[unidad]" required>
                                <option value="" disabled selected>Selecciona una unidad</option>
                                <option value="g">g (gramos)</option>
                                <option value="ml">ml (mililitros)</option>
                            </select>
                            <div class="invalid-feedback">Selecciona una unidad de medida.</div>
                        </div>
                    </div>
                                        
                    <div class="mb-3">
                        <label class="form-label text-dark fw-semibold">Nutrición</label>
                        <button type="button" class="border text-secondary border p-2 bg-white texto rounded-3 w-100 text-start d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#modalNutricion">
                            <span><i class="bi bi-bar-chart-line me-2 texto-rojo"></i>Añadir información nutricional</span>
                            <i class="bi bi-arrow-right text-secondary"></i>
                        </button>
                    </div>
                    <input type="hidden" name="datos[calorias]" id="inputCalorias">
                    <input type="hidden" name="datos[proteina]" id="inputProteina">
                    <input type="hidden" name="datos[carbohidratos]" id="inputCarbohidratos">
                    <input type="hidden" name="datos[grasas]" id="inputGrasas">
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="button" class="border text-secondary border p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4">Crear ingrediente</button>
                </div>
            </form>
        </div>
    </div>
</div>
