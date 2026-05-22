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
    <title>Login | Spezziart</title>
    <script src="https://www.google.com/recaptcha/api.js?render=<?= $recaptchaSiteKey ?>"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/global/styles/styles.css">
    <link rel="stylesheet" href="/global/styles/auth.css">
    <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script>
        window.__USER_THEME__ = 'sistema';
    </script>
    <script src="/global/js/theme.js"></script>
</head>
<body class="container-fluid container-md d-flex justify-content-center min-vh-100 login-page">
    <div class="row justify-content-center align-items-center g-0 w-100 py-4 py-md-5">
        <div class="col-12 col-md-6 mb-3 mb-md-5 px-3 px-md-4 text-center text-md-start login-hero">
            <img src="/global/favicon/logo.png" alt="Logo Spezziart" class="logo">
            <h1 class="login-hero__title letraRomana fw-semibold m-0 p-0">SPEZZI<span class="texto-rojo">ART</span></h1>
            <p class="tituloPequeno letraRomana fst-italic fw-medium p-0 m-0">
                <span class="texto-rojo bg-rojoClaro">Comparte</span>, cocina, repite.
            </p>
            <p class="texto text-secondary m-0 p-0 mt-2 login-hero__desc">
                Tu recetario y tu comunidad de cocina, en un mismo sitio. Guarda lo que cocinas, descubre lo que cocinan otros.
            </p>
            <a href="/pages/feed" class="texto texto-rojo text-decoration-none d-inline-flex align-items-center gap-1 mt-3 fw-medium">
                <i class="bi bi-arrow-left"></i> Volver al feed
            </a>
        </div>
        <div class="col-12 col-md-6 px-2 px-sm-3 px-md-0">
            <div class="p-3 p-sm-4 p-md-5 sombra border rounded rounded-5 bg-white efectoEscala login-card">
                
                <div id="alert-container" class="mb-3"></div>
                <form id="login-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="d-flex flex-wrap gap-3 align-items-center mb-4">
                        <div>
                            <span>
                                <i class="bi bi-box-arrow-in-right texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i>
                            </span>
                        </div>
                        <div>
                            <h3 class="modal-title fw-bold subtitulo letraRomana m-0">INICIAR SESIÓN</h3>
                            <p class="texto text-secondary m-0">Inicia sesión para descubrir y guardar recetas.</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="login" class="form-label text-dark fw-semibold">Email o Username</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0">
                                <i class="bi bi-at"></i>
                            </span>
                            <input type="text" name="login" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" placeholder="Email o Username" required>
                        </div>
                    </div>
                    <div class="mb-2">
                        <label for="login-password" class="form-label text-dark fw-semibold">Contraseña</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input
                                type="password"
                                id="login-password"
                                name="contra"
                                class="form-control texto text-secondary border-start-0 border-end-0"
                                placeholder="Contraseña"
                                autocomplete="current-password"
                                required
                            >
                            <button
                                type="button"
                                class="input-group-text bg-white border border-rojo rounded-3 rounded-start-0 toggle-password"
                                data-target="login-password"
                                aria-label="Mostrar contraseña"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="text-end">
                        <a href="/pages/recuperar" class="textoPequeno texto-rojo">¿Olvidaste la contraseña?</a>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4 w-100 fw-semibold texto">
                            Iniciar sesión <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                    <div class="text-center mt-4 pt-3 border-top">
                        <small class="texto text-secondary">
                            ¿No tienes cuenta?
                            <a href="#" id="toggle-register" class="texto texto-rojo fw-bold">Regístrate</a>
                        </small>
                    </div>
                </form>
                <form id="register-form" class="d-none">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div>
                            <span>
                                <i class="bi bi-box-arrow-in-right texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i>
                            </span>
                        </div>
                        <div>
                            <h3 class="modal-title fw-bold subtitulo letraRomana m-0">¡Te estamos esperando!</h3>
                            <p class="texto text-secondary m-0">Únete y descubre recetas increíbles compartidas por otros cocineros.</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label text-dark fw-semibold">Apodo de la App</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0">
                                <i class="bi bi-tag"></i>
                            </span>
                            <input type="text" name="nombre" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" placeholder="Apodo en la App" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="username" class="form-label text-dark fw-semibold">Nombre de usuario</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0">
                                <i class="bi bi-person"></i>
                            </span>
                            <input type="text" name="username" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" placeholder="Nombre de usuario" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label text-dark fw-semibold">Correo electrónico</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" name="email" class="form-control texto text-secondary border-start-0 rounded-3 rounded-start-0" placeholder="Correo electrónico" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="register-password" class="form-label text-dark fw-semibold">Contraseña</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input
                                type="password"
                                id="register-password"
                                name="contra"
                                class="form-control texto text-secondary border-start-0 border-end-0"
                                placeholder="Contraseña"
                                autocomplete="new-password"
                                required
                            >
                            <button
                                type="button"
                                class="input-group-text bg-white border border-rojo rounded-3 rounded-start-0 toggle-password"
                                data-target="register-password"
                                aria-label="Mostrar contraseña"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="register-password-confirm" class="form-label text-dark fw-semibold">Confirmar contraseña</label>
                        <div class="input-group rounded-3 overflow-hidden">
                            <span class="input-group-text bg-rojoClaro border border-rojo rounded-3 rounded-end-0">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input
                                type="password"
                                id="register-password-confirm"
                                name="contra_confirm"
                                class="form-control texto text-secondary border-start-0 border-end-0"
                                placeholder="Repite la contraseña"
                                autocomplete="new-password"
                                required
                            >
                            <button
                                type="button"
                                class="input-group-text bg-white border border-rojo rounded-3 rounded-start-0 toggle-password"
                                data-target="register-password-confirm"
                                aria-label="Mostrar contraseña"
                            >
                                <i class="bi bi-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="acceptTerms" required>
                        <label class="form-check-label textoPequeno text-secondary" for="acceptTerms">
                            Acepto los
                            <a href="#" class="texto-rojo" data-bs-toggle="modal" data-bs-target="#termsModal">
                                Términos y Condiciones
                            </a>
                            y he leído la
                            <a href="#" class="texto-rojo" data-bs-toggle="modal" data-bs-target="#privacyModal">
                                Política de Privacidad
                            </a>
                        </label>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button type="submit" class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4 w-100 fw-semibold texto">
                            Registrarse <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                    <div class="text-center mt-3">
                        <a href="#" id="toggle-login" class="texto text-secondary fw-semibold text-decoration-none btns border-0 p-2 texto rounded-4 px-4">
                            Volver al Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                    <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div>
                            <span>
                                <i class="bi bi-file-text texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i>
                            </span>
                        </div>
                        <div>
                            <h3 class="modal-title fw-bold subtitulo letraRomana m-0">Términos y condiciones</h3>
                            <p class="texto text-secondary m-0">Lee atentamente las condiciones antes de continuar con el registro.</p>
                        </div>
                    </div>
                    <h6 class="textoMediano letraRomana fw-semibold">1. Aceptación de los términos</h6>
                    <p class="texto text-secondary">Al acceder y utilizar Spezziart, aceptas cumplir con estos términos y condiciones.</p>
                    <h6 class="textoMediano letraRomana fw-semibold">2. Contenido generado por el usuario</h6>
                    <p class="texto text-secondary">Eres el único responsable de las recetas, comentarios y demás contenido que publiques. Nos reservamos el derecho de eliminar contenido inapropiado.</p>
                    <h6 class="textoMediano letraRomana fw-semibold">3. Propiedad intelectual</h6>
                    <p class="texto text-secondary">Todo el contenido original de la plataforma está protegido por derechos de autor. Las recetas que compartes siguen siendo tuyas, pero nos das licencia para mostrarlas en la web.</p>
                    <h6 class="textoMediano letraRomana fw-semibold">4. Conducta prohibida</h6>
                    <p class="texto text-secondary">No está permitido publicar contenido ofensivo, ilegal, o que infrinja derechos de terceros.</p>
                    <h6 class="textoMediano letraRomana fw-semibold">5. Modificaciones</h6>
                    <p class="texto text-secondary">Podemos actualizar estos términos en cualquier momento. Te notificaremos de cambios importantes.</p>
                    <div class="alert border-rojo bg-rojoClaro texto-rojo texto mt-3 d-flex align-items-center gap-2">
                        <i class="bi bi-envelope fs-5"></i>Para preguntas, contacta con <strong>equipospezziart@gmail.com</strong>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="border text-secondary p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-labelledby="privacyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                    <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div>
                            <span>
                                <i class="bi bi-file-text texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i>
                            </span>
                        </div>
                        <div>
                            <h3 class="modal-title fw-bold subtitulo letraRomana m-0">Política de Privacidad</h3>
                            <p class="texto text-secondary m-0">Consulta cómo protegemos y tratamos tus datos personales en la plataforma.</p>
                        </div>
                    </div>
                    <h6 class="textoMediano letraRomana fw-semibold">1. Responsable del tratamiento</h6>
                    <p class="texto text-secondary">Spezziart, con correo de contacto equipospezziart@gmail.com, es el responsable del tratamiento de tus datos personales.</p>
                    <h6 class="textoMediano letraRomana fw-semibold">2. Datos que recogemos</h6>
                    <p class="texto text-secondary">Recogemos tu nombre, nombre de usuario, correo electrónico, contraseña encriptada, preferencias de tema y configuración de privacidad.</p>
                    <h6 class="textoMediano letraRomana fw-semibold">3. Finalidad del tratamiento</h6>
                    <p class="texto text-secondary">Los datos se utilizan para gestionar tu cuenta, personalizar tu experiencia, mostrarte contenido relevante y mejorar nuestros servicios.</p>
                    <h6 class="textoMediano letraRomana fw-semibold">4. Conservación de datos</h6>
                    <p class="texto text-secondary">Tus datos se conservarán mientras mantengas tu cuenta activa. Puedes solicitar su eliminación en cualquier momento.</p>
                    <h6 class="textoMediano letraRomana fw-semibold">5. Derechos del usuario</h6>
                    <p class="texto text-secondary">Puedes acceder, rectificar, suprimir u oponerte al tratamiento de tus datos escribiendo a equipospezziart@gmail.com.</p>
                    <h6 class="textoMediano letraRomana fw-semibold">6. Seguridad</h6>
                    <p class="texto text-secondary">Aplicamos medidas técnicas y organizativas para proteger tu información frente a accesos no autorizados.</p>
                    <div class="alert border-rojo bg-rojoClaro texto-rojo texto mt-3 d-flex align-items-center gap-2">
                        <i class="bi bi-info-circle"></i>Cumplimos con el RGPD (Reglamento General de Protección de Datos).
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="border text-secondary p-2 bg-white texto rounded-4 px-4" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="cookiesRegisterModal" tabindex="-1" aria-labelledby="cookiesRegisterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content position-relative overflow-hidden border-0 rounded-4 shadow-sm">
                <div class="modal-header border-0 d-flex justify-content-end p-3 position-relative z-1">
                    <button type="button" class="btn-close texto rounded-circle sombra border p-2 bg-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body ps-4 pe-4 pt-0 pb-2 position-relative z-1">
                    <div class="d-flex gap-3 align-items-center mb-4">
                        <div>
                            <span>
                                <i class="bi bi-cookie texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande"></i>
                            </span>
                        </div>

                        <div>
                            <h3 class="modal-title fw-bold subtitulo letraRomana m-0" id="cookiesRegisterModalLabel">
                                Cookies esenciales
                            </h3>
                            <p class="texto text-secondary m-0">
                                Necesarias para crear tu cuenta y proteger tu sesión.
                            </p>
                        </div>
                    </div>

                    <div class="alert border-rojo bg-rojoClaro texto-rojo texto d-flex align-items-start gap-2 mb-3">
                        <i class="bi bi-shield-check fs-5"></i>
                        <span>
                            Usamos cookies esenciales para mantener la sesión, reforzar la seguridad del registro y recordar acciones básicas mientras navegas.
                        </span>
                    </div>

                    <p class="texto text-secondary mb-0">
                        Si continúas, aceptas el uso de estas cookies necesarias para el funcionamiento de Spezziart.
                    </p>
                </div>

                <div class="modal-footer justify-content-between border-0 pt-2">
                    <button
                        type="button"
                        id="accept-cookies-register"
                        class="bg-rojo text-white border-0 p-2 texto rounded-4 px-4 fw-semibold"
                    >
                        Acepto el uso de Cookies esenciales
                    </button>
                    <button
                        type="button"
                        id="reject-cookies-register"
                        class="border text-secondary p-2 bg-white texto rounded-4 px-4"
                        data-bs-dismiss="modal"
                    >
                        Volver
                    </button>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/pages/login/assets/Login.js"></script>
</body>
</html>