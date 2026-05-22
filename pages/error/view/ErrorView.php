<?php
declare(strict_types=1);
$statusCode = $statusCode ?? 500;
$title = $title ?? 'Ha ocurrido algo inesperado';
$message = $message ?? 'No hemos podido completar la acción.';
$detail = $detail ?? '';
$heroImage = $heroImage ?? '/global/img/error-lupa.png';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error <?= (int)$statusCode ?> | Spezziart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/global/styles/styles.css">
    <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script>
        window.__USER_THEME__ = 'sistema';
    </script>
    <script src="/global/js/theme.js"></script>
</head>
<body class="container-fluid container-md d-flex justify-content-center min-vh-100 login-page">
    <main class="row justify-content-center align-items-center g-4 g-lg-5 w-100 py-4 py-md-5">
        <section class="col-12 col-lg-6 text-center text-lg-start">
            <div class="d-flex align-items-center justify-content-center justify-content-lg-start gap-2 mb-4">
                <img src="/global/favicon/logo.png" alt="Logo Spezziart" class="logo">
                <div>
                    <h1 class="login-hero__title letraRomana fw-semibold m-0 p-0">
                        SPEZZI<span class="texto-rojo">ART</span>
                    </h1>
                    <p class="tituloPequeno letraRomana fst-italic fw-medium p-0 m-0">
                        <span class="texto-rojo bg-rojoClaro">Comparte</span>, cocina, repite.
                    </p>
                </div>
            </div>
            <div class="p-3 p-sm-4 p-md-5 sombra border rounded rounded-5 bg-white efectoEscala">
                <div class="d-flex gap-3 align-items-center justify-content-center justify-content-lg-start mb-4">
                    <div>
                        <span>
                            <i class="bi bi-exclamation-triangle texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i>
                        </span>
                    </div>
                    <div class="text-start">
                        <p class="textoPequeno texto-rojo fw-semibold text-uppercase m-0">
                            Error <?= (int)$statusCode ?>
                        </p>
                        <h2 class="modal-title fw-bold subtitulo letraRomana m-0">
                            <?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?>
                        </h2>
                    </div>
                </div>
                <p class="texto text-secondary mb-3">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </p>
                <?php if (!empty($detail)): ?>
                    <div class="alert border-rojo bg-rojoClaro texto-rojo texto d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle fs-5"></i>
                        <span><?= htmlspecialchars($detail, ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                <?php endif; ?>
                <div class="d-flex flex-column justify-content-between flex-sm-row gap-2 mt-4">
                    <a href="/pages/feed" class="bg-rojo text-white border-0 p-2  texto rounded-4 px-4 text-decoration-none fw-semibold text-center">
                        Ir al feed <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                    <button type="button" class="border text-secondary p-2   bg-white texto rounded-4 px-4" onclick="history.back()">
                        Volver atrás
                    </button>
                </div>
            </div>
        </section>
        <section class="col-12 col-lg-6 d-flex justify-content-center align-items-center">
            <div class="text-center w-100">
                <img
                    src="<?= htmlspecialchars($heroImage, ENT_QUOTES, 'UTF-8') ?>"
                    alt="Personaje buscando con una lupa"
                    class="img-fluid"
                    style="max-height: 420px; object-fit: contain;"
                >
            </div>
        </section>
    </main>
</body>
</html>