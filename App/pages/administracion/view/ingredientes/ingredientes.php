<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h2 class="fw-bold mb-1">Ingredientes</h2>
        <p class="text-muted mb-0">Crea, edita o elimina el contenido base de la aplicación.</p>
    </div>
    <button class="btn text-white px-4 py-2 rounded-pill"
        style="background: var(--brand-wine);" data-bs-toggle="modal" data-bs-target="#modalCrearIngrediente">
        <i class="bi bi-person-plus me-2"></i>Nuevo ingrediente
    </button>
</div>

<?php if (empty($ingredientes)) { ?>

    <div class="text-center py-5">
        <i class="bi bi-exclamation-triangle display-4 text-muted"></i>
        <p class="text-muted mt-3">No hay ingredientes registrados.</p>
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

        <!-- Select -->
        <select class="form-select w-auto rounded-pill px-4" id="filtroRoles">

            <option value="verificado">Verificado</option>
            <option value="Noverificado">No verificado</option>

        </select>

    </div>

    <div class="table-responsive">

        <table class="table align-middle">
            <thead class="text-muted small">
                <tr>
                    <th>INGREDIENTE</th>
                    <th>VERIFICADA</th>
                    <th class="text-end">ACCIONES</th>
                </tr>
            </thead>
            <tbody>


                <?php foreach ($ingredientes as $ingrediente) { ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3"><?php echo strtoupper(substr($ingrediente['Nombre'], 0, 2)); ?></div>
                                <div>
                                    <div class="fw-semibold"><?php echo $ingrediente['Nombre']; ?></div>

                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="badge bg-<?php echo $ingrediente['Verificada'] ? 'success' : 'secondary'; ?>"> <?php echo $ingrediente['Verificada'] ? 'Verificada' : 'No verificada'; ?></div>
                            
                        </td>
                        <td class="text-end">
                            <i class="bi bi-pencil me-3 text-muted" data-bs-toggle="modal" data-bs-target="#modalEditarIngrediente" data-id="<?php echo $ingrediente['ID_Ingrediente']?>" data-nombre="<?php echo $ingrediente['Nombre']?>" data-calorias="<?php echo $ingrediente['Calorias']?>" data-proteina="<?php echo $ingrediente['Proteina']?>" data-carbohidratos="<?php echo $ingrediente['Carbohidratos']?>" data-grasas="<?php echo $ingrediente['Grasas']?>" data-modo="editar"></i>
                            <i class="bi bi-trash text-muted"></i>
                        </td>
                    </tr>
                <?php } ?>

            </tbody>
        </table>


    </div>
<?php } ?>

