<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1">Etiquetas</h2>
        <p class="text-muted mb-0">Crea, edita o elimina el contenido de la aplicación.</p>
    </div>
    <button class="btn text-white px-4 py-2 rounded-pill"
        style="background: var(--brand-wine);" data-bs-toggle="modal" data-bs-target="#modalCrearEtiqueta">
        <i class="bi bi-person-plus me-2"></i>Nueva etiqueta
    </button>
</div>

<?php if (empty($etiquetas)) { ?>

    <div class="text-center py-5">
        <i class="bi bi-exclamation-triangle display-4 text-muted"></i>
        <p class="text-muted mt-3">No hay etiquetas registradas.</p>
    </div>

<?php } else { ?>

    <!-- Filtros -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <!-- Buscador -->
        <div class="input-group w-50">
            <span class="input-group-text bg-light border-0 rounded-start-pill">
                <i class="bi bi-search text-muted"></i>
            </span>
            <input type="text" class="form-control input border-0 bg-light rounded-end-pill"
                placeholder="Buscar por nombre " id="buscadorIngredientes">
        </div>

    </div>

    <div class="table-responsive">

        <table class="table align-middle">
            <thead class="text-muted small">
                <tr>
                    <th>Etiqueta</th>
                    <th class="text-end">ACCIONES</th>
                </tr>
            </thead>
            <tbody>


                <?php foreach ($etiquetas as $etiqueta) { ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3"><?php echo strtoupper(substr($etiqueta['Nombre'], 0, 2)); ?></div>
                                <div>
                                    <div class="fw-semibold"><?php echo $etiqueta['Nombre']; ?></div>

                                </div>
                            </div>
                        </td>
                        <td class="text-end">
                            <i class="bi bi-pencil me-3 text-muted editarEtiqueta" data-bs-toggle="modal" data-bs-target="#modalEditarEtiqueta" data-id=<?php echo $etiqueta['ID_Etiqueta']?>></i>
                            <i class="bi bi-trash text-muted" data-bs-toggle="modal" data-bs-target="#eliminarEtiqueta" data-id=<?php echo $etiqueta['ID_Etiqueta']?>></i>
                            
                        </td>
                    </tr>
                <?php } ?>

            </tbody>
        </table>


    </div>
<?php } ?>

<script src="assets/etiqueta/EditarEtiqueta.js"></script>