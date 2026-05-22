<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../core/auth.php';
$csrfToken = csrf_token();
$recaptchaSiteKey = htmlspecialchars($_ENV['RECAPTCHA_SITE_KEY'] ?? '', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="recaptcha-site-key" content="<?= $recaptchaSiteKey ?>">
    <title>Recuperar Contraseña | Spezziart</title>
    <!-- reCAPTCHA v3 -->
    <script src="https://www.google.com/recaptcha/api.js?render=<?= $recaptchaSiteKey ?>"></script>
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
<body class="container d-flex justify-content-center align-items-center vh-100">
    <div class="row justify-content-center align-items-center  g-0 vh-100">
        <div class="col-12 col-md-6 mb-5">
            <img src="/global/favicon/logo.png" alt="Logo Spezziart" class="logo">
            <h1 class="nombreWeb letraRomana fw-semibold m-0 p-0">SPEZZI<span class="texto-rojo">ART</span></h1>
            <p class="tituloPequeno letraRomana fst-italic fw-medium p-0  m-0"><span class="texto-rojo bg-rojoClaro">Comparte</span>, cocina, repite.</p>
            <p class="texto text-secondary m-0 p-0 mt-1 w-75">Tu recetario y tu comunidad de cocina, en un mismo sitio. Guarda lo que cocinas, descubre lo que cocinan otros.</p>
        </div>
        <div class="col-12 col-md-6">
            <div class="p-5 sombra border rounded rounded-5 bg-white efectoEscala">
                <div id="alert-container" class="mb-3">
                    <?php if (isset($_GET['estado'])): ?>
                        <?php if ($_GET['estado'] === 'correcto'): ?>
                            <div class="alert alert-success">✅¡Email enviado correctamente! Revisa tu bandeja de entrada.</div>
                        <?php else: ?>
                            <div class="alert alert-danger">❌No se pudo enviar el correo... Verifica la dirección e inténtalo de nuevo.</div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <form id="recuperar-form" method="POST" action="/pages/login/RecuperarCuenta">
                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div><span><i class="bi bi-box-arrow-in-right texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i></span></div>
                        <div>
                            <h3 class="modal-title fw-bold subtitulo letraRomana m-0">Recuperar Contraseña</h3>
                            <p class="texto text-secondary m-0">Introduce tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
                        </div>
                    </div>
                    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                    <div class="mb-3">
                        <label for="login" class="form-label text-dark fw-semibold">Email</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0"><i class="bi bi-at"></i></span>
                            <input type="email" name="email" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" placeholder="Correo electrónico" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4 w-100 fw-semibold texto">Enviar enlace</button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="/pages/login" class="texto text-secondary fw-semibold text-decoration-none btns border-0 p-2 texto rounded-4 px-4">Volver al Login</a>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/pages/login/assets/Recuperar.js"></script>
</body>
</html>