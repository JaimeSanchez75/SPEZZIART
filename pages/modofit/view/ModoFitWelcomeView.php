<!DOCTYPE html>
<html lang="es" data-bs-theme="<?= $_SESSION['user']['tema'] ?? 'sistema' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/pages/administracion/assets/styles.css">
    <script>window.userTheme = '<?= $_SESSION['user']['tema'] ?? 'sistema' ?>';</script>
    <script src="/global/js/theme.js"></script>
    <title>Modo Fit | SPEZZIART</title>
</head>
<body class="w-100 p-0 m-0">

<div class="container-fluid ">

    

    <!-- Tarjeta de bienvenida / formulario -->
    <div class="row justify-content-center align-items-center vh-100">
        <div class="col-12 col-md-10 col-lg-8 col-xl-6">
            <div class="p-4 sombra border rounded-4 bg-white">

                <div class="d-flex gap-3 align-items-start mb-4">
                    <span class="bg-rojoClaro p-3 rounded-3 d-inline-flex flex-shrink-0">
                        <i class="bi bi-heart-pulse texto-rojo iconos"></i>
                    </span>
                    <div>
                        <span class="badge bg-rojoClaro texto-rojo textoPequeno mb-2 d-inline-block">
                            CONFIGURACIÓN INICIAL
                        </span>
                        <h3 class="fw-bold letraRomana m-0">Bienvenido a Modo Fit</h3>
                        <p class="texto text-secondary m-0">
                            Modo Fit calculará tus calorías, macronutrientes y objetivos diarios según tu cuerpo y actividad.
                        </p>
                    </div>
                </div>

                <form method="POST" action="/fit/saveFitData">
                    <div class="row g-3">

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold texto">Sexo</label>
                            <div class="input-group">
                                <span class="input-group-text bg-rojoClaro border border-rojo">
                                    <i class="bi bi-person texto-rojo textoMediano"></i>
                                </span>
                                <select name="sexo" class="form-select texto" required>
                                    <option value="">Seleccionar</option>
                                    <option value="hombre">Hombre</option>
                                    <option value="mujer">Mujer</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold texto">Edad</label>
                            <div class="input-group">
                                <span class="input-group-text bg-rojoClaro border border-rojo">
                                    <i class="bi bi-calendar2-heart texto-rojo textoMediano"></i>
                                </span>
                                <input type="number" name="edad" class="form-control texto"
                                       required min="14" max="90" step="1" placeholder="Años">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold texto">Altura (cm)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-rojoClaro border border-rojo">
                                    <i class="bi bi-rulers texto-rojo textoMediano"></i>
                                </span>
                                <input type="number" name="altura" class="form-control texto"
                                       required min="110" max="220" step="1" placeholder="cm">
                            </div>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label class="form-label fw-semibold texto">Peso (kg)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-rojoClaro border border-rojo">
                                    <i class="bi bi-speedometer2 texto-rojo textoMediano"></i>
                                </span>
                                <input type="number" name="peso" class="form-control texto"
                                       required min="40" max="300" step="0.1" placeholder="kg">
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold texto">Nivel de actividad</label>
                            <div class="input-group">
                                <span class="input-group-text bg-rojoClaro border border-rojo">
                                    <i class="bi bi-activity texto-rojo textoMediano"></i>
                                </span>
                                <select name="actividad" class="form-select texto" required>
                                    <option value="sedentario">Sedentario – Poco o ningún ejercicio</option>
                                    <option value="ligero">Ligero – Ejercicio 1–3 días/semana</option>
                                    <option value="moderado">Moderado – Ejercicio 3–5 días/semana</option>
                                    <option value="activo">Alto – Ejercicio 6–7 días/semana</option>
                                    <option value="muy_activo">Extremo – Entrenamiento muy intenso diario</option>
                                </select>
                            </div>
                        </div>

                    </div><!-- /row campos -->

                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary texto fw-medium px-4" type="submit">
                            <i class="bi bi-check2-circle me-2"></i>Activar Modo Fit
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

</div><!-- /container-fluid -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>
