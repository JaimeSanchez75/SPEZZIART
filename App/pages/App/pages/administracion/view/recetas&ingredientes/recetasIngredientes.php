<div class="container-fluid py-4">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="content-title mb-0">Recetas & Ingredientes</h2>
            <p class="content-welcome mb-0">
                Crea, edita o elimina el contenido base de la aplicación.
            </p>
        </div>

        <button class="btn text-white px-4 py-2"
                style="background:#5d0a1a;border-radius:30px;">
            <i class="bi bi-plus-lg me-2"></i> Nuevo Ingrediente
        </button>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs border-0 mb-4">
        <li class="nav-item">
            <button class="nav-link text-secondary fw-semibold">
                Recetas
            </button>
        </li>
        <li class="nav-item">
            <button class="nav-link active fw-semibold"
                    style="color:#5d0a1a;border-bottom:2px solid #5d0a1a;">
                Ingredientes
            </button>
        </li>
    </ul>

    <!-- Buscador + filtros -->
    <div class="d-flex gap-3 mb-4">

        <div class="flex-grow-1">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search text-muted"></i>
                </span>

                <input type="text"
                       class="form-control border-start-0"
                       placeholder="Buscar ingredientes...">
            </div>
        </div>

        <button class="btn btn-light border px-3">
            <i class="bi bi-funnel me-2"></i> Filtros
        </button>

    </div>

    <!-- Tabla ingredientes -->
    <div class="card border-0 shadow-sm rounded-4 p-3">

        <table class="table align-middle mb-0">

            <thead class="text-uppercase text-muted small">
                <tr>
                    <th>Ingrediente</th>
                    <th>Categoría</th>
                    <th>Calorías (100g)</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>

            <tbody>

                <!-- FILA -->
                <tr>

                    <td>
                        <div class="d-flex align-items-center">

                            <img src="https://images.unsplash.com/photo-1582515073490-dc8d2c63f4c0"
                                 width="40"
                                 height="40"
                                 class="rounded-3 me-3"
                                 style="object-fit:cover;">

                            <strong>Patata</strong>

                        </div>
                    </td>

                    <td class="text-muted">
                        Verdura
                    </td>

                    <td style="color:#ff4d00;font-weight:600;">
                        <i class="bi bi-fire me-1"></i>
                        77 kcal
                    </td>

                    <td class="text-end">

                        <button class="btn btn-light btn-sm me-2">
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button class="btn btn-light btn-sm">
                            <i class="bi bi-trash"></i>
                        </button>

                    </td>

                </tr>

                <!-- FILA -->
                <tr>

                    <td>
                        <div class="d-flex align-items-center">

                            <img src="https://images.unsplash.com/photo-1585238342024-78d387f4a707"
                                 width="40"
                                 height="40"
                                 class="rounded-3 me-3"
                                 style="object-fit:cover;">

                            <strong>Huevo</strong>

                        </div>
                    </td>

                    <td class="text-muted">
                        Proteína
                    </td>

                    <td style="color:#ff4d00;font-weight:600;">
                        <i class="bi bi-fire me-1"></i>
                        155 kcal
                    </td>

                    <td class="text-end">

                        <button class="btn btn-light btn-sm me-2">
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button class="btn btn-light btn-sm">
                            <i class="bi bi-trash"></i>
                        </button>

                    </td>

                </tr>

                <!-- FILA -->
                <tr>

                    <td>
                        <div class="d-flex align-items-center">

                            <img src="https://images.unsplash.com/photo-1587049352846-4a222e784d38"
                                 width="40"
                                 height="40"
                                 class="rounded-3 me-3">

                            <strong>Cebolla</strong>

                        </div>
                    </td>

                    <td class="text-muted">
                        Verdura
                    </td>

                    <td style="color:#ff4d00;font-weight:600;">
                        <i class="bi bi-fire me-1"></i>
                        40 kcal
                    </td>

                    <td class="text-end">

                        <button class="btn btn-light btn-sm me-2">
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button class="btn btn-light btn-sm">
                            <i class="bi bi-trash"></i>
                        </button>

                    </td>

                </tr>

                <!-- FILA -->
                <tr>

                    <td>
                        <div class="d-flex align-items-center">

                            <img src="https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5"
                                 width="40"
                                 height="40"
                                 class="rounded-3 me-3">

                            <strong>Aceite de oliva</strong>

                        </div>
                    </td>

                    <td class="text-muted">
                        Grasas
                    </td>

                    <td style="color:#ff4d00;font-weight:600;">
                        <i class="bi bi-fire me-1"></i>
                        884 kcal
                    </td>

                    <td class="text-end">

                        <button class="btn btn-light btn-sm me-2">
                            <i class="bi bi-pencil"></i>
                        </button>

                        <button class="btn btn-light btn-sm">
                            <i class="bi bi-trash"></i>
                        </button>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>