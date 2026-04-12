<div class="container py-5">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h2 class="fw-bold">Moderacion Social</h2>
            <p class="text-muted mb-0">
                Verifica los reportes enviados por la comunidad.
            </p>
        </div>

        <span class="badge recetas-pendientes">
            <?= $pendientes ?> reportes pendientes
        </span>

    </div>


<?php if(empty($reportes)): ?>

    <div class="alert alert-success">
        No hay reportes pendientes 
    </div>

<?php endif; ?>


<?php foreach($reportes as $r): ?>


    <!-- TARJETA -->
    <div class="card receta-card shadow-sm mb-4">

        <div class="row g-0">

            <!-- Imagen -->
            <div class="col-md-3">

                <?php if(!empty($r['Imagen'])): ?>

                <img src="<?= $r['Imagen'] ?>"
                     class="img-fluid receta-img"
                     alt="receta">

                <?php endif; ?>

            </div>


            <!-- Contenido -->
            <div class="col-md-9 p-4">

                <!-- Top -->
                <div class="d-flex justify-content-between">

                    <div>

                        <span class="badge bg-danger">
                            REPORTE
                        </span>

                        <small class="text-muted ms-2">
                            <?= date("d/m/Y H:i", strtotime($r['Fecha'])) ?>
                        </small>

                        <h4 class="mt-2 fw-bold">
                            <?= htmlspecialchars($r['Titulo']) ?>
                        </h4>

                        <small class="text-muted">
                            Reportado por 
                            <span class="text-danger fw-semibold">
                                @<?= htmlspecialchars($r['Reportador']) ?>
                            </span>
                        </small>

                    </div>

                    <div>

                        <button class="btn btn-outline-secondary btn-sm">
                            Previsualizar
                        </button>

                    </div>

                </div>


                <!-- Descripcion -->
                <div class="descripcion mt-3">

                    <?= htmlspecialchars($r['Descripcion']) ?>

                </div>


                <!-- Motivo -->
                <div class="mt-3">

                    <strong>Motivo del reporte:</strong>

                    <div class="text-muted">

                        <?= htmlspecialchars($r['Motivo']) ?>

                    </div>

                </div>


    <div class="text-end mt-4">

    <!-- ACEPTAR REPORTE -->
    <button 
        class="btn btn-danger rounded-pill me-2 abrirModal"
        data-id="<?= $r['ID_Reporte'] ?>"
        data-receta="<?= $r['ID_Receta'] ?>"
        data-usuario="<?= $r['UsuarioReportado'] ?? '' ?>"
    >
        Aceptar reporte
    </button>

   <a href="/App/moderacion/marcarRevisado?id=<?= $r['ID_Reporte'] ?>"
   class="btn btn-success rounded-pill">
    Marcar como revisado
</a>

</div>

            </div>

        </div>

    </div>

<?php endforeach; ?>


</div>
<?php require_once __DIR__ . '/../components/moderacion/mensajeEmail.php'; ?>
<script src=assets/moderacion/moderacion.js>