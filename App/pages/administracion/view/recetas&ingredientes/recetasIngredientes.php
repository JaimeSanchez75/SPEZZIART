<section class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2>Recetas e Ingredientes</h2>
            <p>Crea, edita o elimina el contenido base de la aplicación.</p>
        </div>
        <div>
            <button class="btn btn-primary"><i class="bi bi-plus"></i> Nueva receta</button>
        </div>
    </div>

    <ul class="nav nav-underline">
        <li class="nav-item">
            <a href="#" class="nav-link active" data-bs-toggle="tab" data-bs-target="#recetas">Recetas</a>
        </li>
        <li class="nav-item">
            <a href="#" class="nav-link" data-bs-toggle="tab" data-bs-target="#ingredientes">Ingredientes</a>
        </li>
    </ul>

    <div class="d-flex justify-content-between align-items-center mt-3 gap-2">
        <div class="input-group search-box">
            <span class="input-group-text border-0">
                <i class="bi bi-search"></i>
            </span>
            <input type="text" class="form-control border-0" placeholder="Buscar recetas...">
        </div>

        <!-- Botón filtros -->
        <button class="btn btn-light filter-btn">
            <i class="bi bi-funnel me-1"></i> Filtros
        </button>
    </div>

    <div class="tab-content">

        <div class="tab-pane fade show active" id="recetas" role="tabpanel">

            <div class="row g-5 mt-4">
                <div class="col-md-4"> <!-- 12/4 = 3 cards por fila -->
                    <div class="card border-0 shadow-sm">
                        <img src="https://media.istockphoto.com/id/1297400965/es/foto/un-primer-plano-de-una-tortilla-espa%C3%B1ola-fresca-y-sabrosa-un-plato-tradicional-de-espa%C3%B1a.jpg?s=612x612&w=0&k=20&c=BIz2CCaqwhCR4Yngx-hV9H9kaK3tiyARz7wqqvA3Ges=" class="card-img-top" alt="Imagen de receta">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-primary">ALMUERZO</span>
                                <small class="text-muted"><i class="bi bi-clock"></i> 30 min</small>
                            </div>
                            <h5 class="card-title">Tortilla de patatas con cebolla</h5>
                            <div class="d-flex align-items-center mt-3">
                                <i class="bi bi-person-circle me-2"></i>
                                <small class="text-muted">Por @chef_admin</small>
                                <a href="#" class="ms-auto text-decoration-none"><i class="bi bi-eye me-1"></i>Ver detalles</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Copia este div por cada card -->
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <img src="https://media.istockphoto.com/id/1297400965/es/foto/un-primer-plano-de-una-tortilla-espa%C3%B1ola-fresca-y-sabrosa-un-plato-tradicional-de-espa%C3%B1a.jpg?s=612x612&w=0&k=20&c=BIz2CCaqwhCR4Yngx-hV9H9kaK3tiyARz7wqqvA3Ges=" class="card-img-top" alt="Imagen de receta">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-primary">ALMUERZO</span>
                                <small class="text-muted"><i class="bi bi-clock"></i> 30 min</small>
                            </div>
                            <h5 class="card-title">Tortilla de patatas con cebolla</h5>
                            <div class="d-flex align-items-center mt-3">
                                <i class="bi bi-person-circle me-2"></i>
                                <small class="text-muted">Por @chef_admin</small>
                                <a href="#" class="ms-auto text-decoration-none"><i class="bi bi-eye me-1"></i>Ver detalles</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm">
                        <img src="https://media.istockphoto.com/id/1297400965/es/foto/un-primer-plano-de-una-tortilla-espa%C3%B1ola-fresca-y-sabrosa-un-plato-tradicional-de-espa%C3%B1a.jpg?s=612x612&w=0&k=20&c=BIz2CCaqwhCR4Yngx-hV9H9kaK3tiyARz7wqqvA3Ges=" class="card-img-top" alt="Imagen de receta">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="badge bg-primary">ALMUERZO</span>
                                <small class="text-muted"><i class="bi bi-clock"></i> 30 min</small>
                            </div>
                            <h5 class="card-title">Tortilla de patatas con cebolla</h5>
                            <div class="d-flex align-items-center mt-3">
                                <i class="bi bi-person-circle me-2"></i>
                                <small class="text-muted">Por @chef_admin</small>
                                <a href="#" class="ms-auto text-decoration-none"><i class="bi bi-eye me-1"></i>Ver detalles</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="ingredientes" role="tabpanel">

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="text-muted small">
                        <tr>
                            <th>INGREDIENTE</th>
                            <th>CATEGORIA</th>
                            <th>CALORIAS</th>
                            <th class="text-end">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>

                        <!-- Ingrediente 1 -->
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-3">PP</div>
                                    <div>
                                        <div class="fw-semibold">Patata</div>
                                        
                                    </div>
                                </div>
                            </td>
                            <td>
                                Verdura
                            </td>
                            <td class="text-warning">
                                <i class="bi bi-fire"></i> 77 kcal
                            </td>
                            <td class="text-end">
                                <i class="bi bi-pencil me-3 text-muted"></i>
                                <i class="bi bi-trash text-muted"></i>
                            </td>
                        </tr>

                        <!-- Ingrediente 2 -->
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="user-avatar me-3">CE</div>
                                    <div>
                                        <div class="fw-semibold">Cebolla</div>
                                        
                                    </div>
                                </div>
                            </td>
                            <td>
                                Verdura
                            </td>
                            <td class="text-warning">
                                <i class="bi bi-fire"></i> 40 kcal
                            </td>
                            <td class="text-end">
                                <i class="bi bi-pencil me-3 text-muted"></i>
                                <i class="bi bi-trash text-muted"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>



</section>