<section class="content-header">
    <div class="container py-4">
        <div class="container py-4">
            <h2 class="dashboard-title">Panel de Control</h2>
            <p class="text-muted">Bienvenido de nuevo, esto es lo que está pasando hoy.</p>

            <!-- Stats -->
            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <div class="card-stat d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Usuarios Totales</div>
                            <div class="stat-value"><?php echo $usuariosTotales; ?></div>
                            <div class="stat-change-positive">+12.5%</div>
                        </div>
                        <div class="stat-icon bg-soft-primary">
                            <i class="bi bi-people"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-stat d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Recetas Publicadas</div>
                            <div class="stat-value"><?php echo $recetasTotalesSem; ?></div>
                            <div class="stat-change-positive">+8.2%</div>
                        </div>
                        <div class="stat-icon bg-soft-success">
                            <i class="bi bi-egg-fried"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-stat d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Eventos Activos</div>
                            <div class="stat-value">12</div>
                            <div class="text-muted small">0</div>
                        </div>
                        <div class="stat-icon bg-soft-purple">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card-stat d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-muted small">Pendientes Revisión</div>
                            <div class="stat-value"><?php echo $pendientesRevision; ?></div>
                            <div class="stat-change-negative">-3</div>
                        </div>
                        <div class="stat-icon bg-soft-orange">
                            <i class="bi bi-clock"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="row g-3 mt-3">
                <div class="col-lg-7">
                    <div class="section-card p-3">
                        <h6 class="fw-semibold">Actividad Semanal</h6>

                        <div><canvas id="graficaRecetasSemanal"></canvas></div>

                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="section-card p-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="fw-semibold m-0">Últimas Aprobaciones</h6>
                            <a href="#" class="small text-danger">Ver todas</a>
                        </div>
                        <?php foreach($ultimasRecetasAprobadas as $receta){ ?>
                            <div class="approval-item">
                                <div class="d-flex align-items-center">

                                    <img src="https://picsum.photos/40" class="avatar">
                                    <div>
                                        <div class="fw-semibold small"><?php echo $receta['Titulo']; ?></div>
                                        <div class="text-muted small">Publicado por @<?php echo $receta['Autor']; ?></div>
                                    </div>
                                </div>
                                <span class="badge-approved">APROBADA</span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
</section>

