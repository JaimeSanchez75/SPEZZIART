<?php require_once __DIR__ . '/../../../core/csrfcheck.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Spezziart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/App/global/styles/global.css">
    <link href="https://fonts.googleapis.com/css2?family=Averia+Serif+Libre:wght@300;400;700&family=Galada&display=swap" rel="stylesheet">
</head>

<body class="d-flex justify-content-center align-items-center vh-100">

    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="col-12 col-sm-10 col-md-8 col-lg-8 col-xl-5">
            <div class="text-center mb-4">
                <img src="/App/global/logo.png" alt="Logo Spezziart" class="logo mb-2 mx-auto d-block">
                <h1 class="auth-logo">Spezziart</h1>
            </div>
            
            <div class="auth-card-container">
                <div class="auth-card">
                    <div id="alert-container" class="mb-3"></div>

                    <form id="login-form" method="POST" action="/auth/login">
                        <h4 class="text-center mb-4">Inicia Sesión</h4>
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="field mb-3">
                            <svg viewBox="0 0 16 16" class="input-icon"><path d="M13.106 7.222c0-2.967-2.249-5.032-5.482-5.032-3.35 0-5.646 2.318-5.646 5.702 0 3.493 2.235 5.708 5.762 5.708.862 0 1.689-.123 2.304-.335v-.862c-.43.199-1.354.328-2.29.328-2.926 0-4.813-1.88-4.813-4.798 0-2.844 1.921-4.881 4.594-4.881 2.735 0 4.608 1.688 4.608 4.156 0 1.682-.554 2.769-1.416 2.769-.492 0-.772-.28-.772-.76V5.206H8.923v.834h-.11c-.266-.595-.881-.964-1.6-.964-1.4 0-2.378 1.162-2.378 2.823 0 1.737.957 2.906 2.379 2.906.8 0 1.415-.39 1.709-1.087h.11c.081.67.703 1.148 1.503 1.148 1.572 0 2.57-1.415 2.57-3.643zm-7.177.704c0-1.197.54-1.907 1.456-1.907.93 0 1.524.738 1.524 1.907S8.308 9.84 7.371 9.84c-.895 0-1.442-.725-1.442-1.914z"></path></svg>
                            <input type="text" name="login" class="input-field" placeholder="Email o Username" required>
                        </div>

                        <div class="field mb-3">
                            <svg viewBox="0 0 16 16" class="input-icon"><path d="M8 1a2 2 0 0 1 2 2v4H6V3a2 2 0 0 1 2-2zm3 6V3a3 3 0 0 0-6 0v4a2 2 0 0 0-2 2v5a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2z"></path></svg>
                            <input type="password" name="contra" class="input-field" placeholder="Contraseña" required>
                        </div>

                        <div class="mt-3">
                            <p class="btn btn-link p-0 text-decoration-none olvidado text-danger">¿Olvidaste la contraseña?</p>
                        </div>
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button type="submit" class="btn btn-submit py-2 px-4 w-100">Login</button>
                        </div>
                        <div class="text-center mt-3">
                            <small class="text-body-secondary">¿No tienes cuenta? 
                                <a href="#" id="toggle-register" class="text-decoration-none text-danger fw-bold">Regístrate</a>
                            </small>
                        </div>
                    </form>

                    <form id="register-form" method="POST" action="/auth/register" class="d-none">
                        <h4 class="text-center mb-4">¡Te estamos esperando!</h4>
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                        
                        <div class="field mb-3">
                            <input type="text" name="nombre" class="input-field" placeholder="Apodo en la App" required>
                        </div>
                        <div class="field mb-3">
                            <input type="text" name="username" class="input-field" placeholder="Nombre de usuario" required>
                        </div>
                        <div class="field mb-3">
                            <input type="email" name="email" class="input-field" placeholder="Correo electrónico" required>
                        </div>
                        <div class="field mb-3">
                            <input type="password" name="contra" class="input-field" placeholder="Contraseña" required>
                        </div>
                        
                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button type="submit" class="btn btn-submit py-2 px-4 w-100">Registrarse</button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="#" id="toggle-login" class="text-body-secondary small text-decoration-none">Volver al Login</a>
                        </div>
                    </form>
                </div> 
            </div>
        </div> 
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/App/global/js/login.js"></script>
</body>
</html>