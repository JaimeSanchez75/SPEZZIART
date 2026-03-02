<?php
require_once __DIR__ . '/../../../core/csrfcheck.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Spezziart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./global/styles/global.css">
    <link href="https://fonts.googleapis.com/css2?family=Averia+Serif+Libre:wght@300;400;700&display=swap" rel="stylesheet">
</head>

<body class="d-flex justify-content-center align-items-center vh-100">

    <div class="container" style="max-width: 450px;">
       <div class="text-center mb-4">
            <img src="/App/global/logo.png" alt="Logo Spezziart" class="logo mb-2">
            <h1 class="auth-logo h3">Spezziart</h1>
        </div>

        <div class="card shadow p-4 auth-card">
            <div id="alert-container"></div>

            <h4 class="text-center mb-4" id="form-title">Iniciar Sesión</h4>

            <form id="login-form" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Email o Usuario</label>
                    <input type="text" name="login" class="form-control" placeholder="Introduce tu usuario" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Contraseña</label>
                    <input type="password" name="contra" class="form-control" placeholder="********" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2 py-2">Entrar</button>

                <div class="text-center mt-3">
                    <small class="text-muted">¿No tienes cuenta? 
                        <a href="#" id="toggle-register" class="text-decoration-none fw-bold">Regístrate</a>
                    </small>
                </div>
            </form>

            <form id="register-form" method="POST" class="d-none">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nombre Completo</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Tu nombre" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Correo Electrónico</label>
                    <input type="email" name="email" class="form-control" placeholder="ejemplo@correo.com" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Contraseña</label>
                    <input type="password" name="contra" class="form-control" placeholder="Mínimo 6 caracteres" required>
                </div>

                <button type="submit" class="btn btn-success w-100 mt-2 py-2">Crear mi cuenta</button>

                <div class="text-center mt-3">
                    <small class="text-muted">¿Ya eres miembro? 
                        <a href="#" id="toggle-login" class="text-decoration-none fw-bold">Inicia sesión</a>
                    </small>
                </div>
            </form>
        </div>
    </div>

    <script src="/App/global/js/login.js"></script>
</body>
</html>