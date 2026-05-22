<?php declare(strict_types=1); ?>
<?php
class ConfiguracionView {
    public function render($config) {
        ?>
        <!DOCTYPE html>
        <html lang="es" data-bs-theme="<?= $_SESSION['user']['tema'] ?? 'sistema' ?>">
        <head>
            <meta charset="UTF-8">
            <title>Configuración | Spezziart</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <meta name="csrf-token" content="<?= csrf_token() ?>">
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" />
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="/pages/administracion/assets/styles.css">
            <link rel="stylesheet" href="/global/styles/global.css">
            <link rel="stylesheet" href="/global/styles/configuracion.css">
            <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
            <script>window.userTheme = '<?= $_SESSION['user']['tema'] ?? 'sistema' ?>';</script>
            <script src="/global/js/theme.js"></script>
        </head>
        <body>
            <main class="container py-4 py-lg-5 mb-5 mx-auto" style="max-width: 1040px;">
                <section class="d-flex flex-column p-4 sombra border rounded-4 gap-2 bg-white">
                    <div class="d-flex gap-2 align-items-center mb-4 cabecera-page">
                        <div class="flex-shrink-0"><span><i class="bi bi-gear texto-rojo iconos bg-rojoClaro p-3 rounded-3 perfilUsuarioGrande cabecera-icono"></i></span></div>
                        <div class="cabecera-text">
                            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                                <p class="titulo letraRomana fw-bold texto-rojo m-0 cabecera-titulo">SPEZZIART</p>
                                <span class="subtitulo letraRomana fw-bold text-secondary cabecera-sep">|</span>
                                <h3 class="modal-title fw-bold subtitulo letraRomana m-0 cabecera-titulo">Configuración</h3>
                            </div>
                            <p class="texto text-secondary m-0 cabecera-subtitulo">Personaliza tu experiencia en Spezziart</p>
                        </div>
                    </div>

                    <ul class="nav nav-tabs adminTabs mt-3" id="configTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="general-tab" data-bs-toggle="tab" data-bs-target="#general" type="button" role="tab">
                                <i class="bi bi-sliders2"></i> General
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy" type="button" role="tab">
                                <i class="bi bi-shield-lock"></i> Privacidad
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="terms-tab" data-bs-toggle="tab" data-bs-target="#terms" type="button" role="tab">
                                <i class="bi bi-file-text"></i> Términos
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content pt-4">
                        <div class="tab-pane fade show active" id="general" role="tabpanel">
                            <form id="configForm">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                                <p class="texto fw-bold text-dark mb-2 textoMediano">Apariencia</p>
                                <div class="config-item d-flex flex-wrap justify-content-between align-items-center gap-3 border rounded-4 p-3 sombra mb-3 bg-white">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="config-icon bg-rojoClaro rounded-3 d-inline-flex align-items-center justify-content-center">
                                            <i class="bi bi-palette texto-rojo"></i>
                                        </div>
                                        <div>
                                            <label class="texto fw-semibold text-dark mb-0" for="tema">Tema de la aplicación</label>
                                            <p class="textoPequeno text-secondary mb-0">Elige entre claro, oscuro o automático según tu sistema</p>
                                        </div>
                                    </div>
                                    <select name="tema" id="tema" class="form-select texto text-secondary rounded-3 w-auto">
                                        <option value="sistema" <?= $config['Tema'] == 'sistema' ? 'selected' : '' ?>>Sistema (automático)</option>
                                        <option value="claro" <?= $config['Tema'] == 'claro' ? 'selected' : '' ?>>Claro</option>
                                        <option value="oscuro" <?= $config['Tema'] == 'oscuro' ? 'selected' : '' ?>>Oscuro</option>
                                    </select>
                                </div>

                                <p class="texto fw-bold text-dark mb-2 textoMediano">Preferencias</p>
                                <div class="config-item d-flex flex-wrap justify-content-between align-items-center gap-3 border rounded-4 p-3 sombra mb-3 bg-white">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="config-icon bg-rojoClaro rounded-3 d-inline-flex align-items-center justify-content-center">
                                            <i class="bi bi-activity texto-rojo"></i>
                                        </div>
                                        <div>
                                            <label class="texto fw-semibold text-dark mb-0" for="modoFit">Modo Fit</label>
                                            <p class="textoPequeno text-secondary mb-0">Muestra información nutricional en las recetas</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="modo_fit" id="modoFit" <?= $config['ModoFit'] ? 'checked' : '' ?>>
                                    </div>
                                </div>

                                <!-- <div class="config-item d-flex flex-wrap justify-content-between align-items-center gap-3 border rounded-4 p-3 sombra mb-3 bg-white">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="config-icon bg-rojoClaro rounded-3 d-inline-flex align-items-center justify-content-center">
                                            <i class="bi bi-bell texto-rojo"></i>
                                        </div>
                                        <div>
                                            <label class="texto fw-semibold text-dark mb-0" for="notificaciones">Notificaciones</label>
                                            <p class="textoPequeno text-secondary mb-0">Recibe alertas de interacciones y novedades</p>
                                        </div>
                                    </div>
                                    <div class="form-check form-switch m-0">
                                        <input class="form-check-input" type="checkbox" role="switch" name="notificaciones" id="notificaciones" <?= $config['NotificacionOn'] ? 'checked' : '' ?>>
                                    </div>
                                </div> -->

                                <p class="texto fw-bold text-dark mb-2 textoMediano">Cuenta</p>
                                <div class="config-item d-flex flex-wrap justify-content-between align-items-center gap-3 border rounded-4 p-3 sombra mb-3 bg-white">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="config-icon bg-rojoClaro rounded-3 d-inline-flex align-items-center justify-content-center">
                                            <i class="bi bi-box-arrow-right texto-rojo"></i>
                                        </div>
                                        <div>
                                            <p class="texto fw-semibold text-dark mb-0">Cerrar sesión</p>
                                            <p class="textoPequeno text-secondary mb-0">Cierra tu sesión actual en este dispositivo</p>
                                        </div>
                                    </div>
                                    <button type="button" id="logoutBtn" class="border border-rojo bg-white texto-rojo py-1 px-3 rounded-3 texto fw-medium">
                                        Cerrar sesión
                                    </button>
                                </div>

                                <div class="alert rounded-4 d-none" id="alert"></div>

                                <div class="text-end mt-4">
                                    <button type="submit" class="border-0 bg-rojo text-white py-2 px-4 rounded-3 texto fw-medium config-action">
                                        <i class="bi bi-save me-1"></i> Guardar cambios
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="privacy" role="tabpanel">
                            <p class="texto fw-bold text-dark mb-2 textoMediano">Privacidad</p>
                            <div class="config-item d-flex flex-wrap justify-content-between align-items-center gap-3 border rounded-4 p-3 sombra mb-3 bg-white">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="config-icon bg-rojoClaro rounded-3 d-inline-flex align-items-center justify-content-center">
                                        <i class="bi bi-globe2 texto-rojo"></i>
                                    </div>
                                    <div>
                                        <label class="texto fw-semibold text-dark mb-0" for="cuentaPublica">Cuenta pública</label>
                                        <p class="textoPequeno text-secondary mb-0">Permite que otros usuarios vean tu perfil y recetas</p>
                                    </div>
                                </div>
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" role="switch" name="cuenta_publica" id="cuentaPublica" form="configForm" <?= $config['CuentaPublica'] ? 'checked' : '' ?>>
                                </div>
                            </div>

                            <div class="config-item d-flex flex-wrap justify-content-between align-items-center gap-3 border rounded-4 p-3 sombra mb-3 bg-white">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="config-icon bg-rojoClaro rounded-3 d-inline-flex align-items-center justify-content-center">
                                        <i class="bi bi-database-lock texto-rojo"></i>
                                    </div>
                                    <div>
                                        <p class="texto fw-semibold text-dark mb-0">Protección de datos</p>
                                        <p class="textoPequeno text-secondary mb-0">Tus datos personales nunca se comparten sin tu consentimiento</p>
                                    </div>
                                </div>
                                <span class="badge bg-verdeClaro texto-verde rounded-pill">Seguro</span>
                            </div>

                            <div class="config-item border rounded-4 p-3 sombra mb-3 bg-white">
                                <h6 class="texto fw-bold text-dark mb-1"><i class="bi bi-info-circle texto-rojo me-1"></i> Información adicional</h6>
                                <p class="textoPequeno text-secondary mb-0">Puedes solicitar la eliminación de tus datos personales escribiendo a <strong>equipospezziart@gmail.com</strong>. Cumplimos con el RGPD.</p>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="terms" role="tabpanel">
                            <div class="config-item border rounded-4 p-3 p-lg-4 sombra bg-white">
                                <h5 class="textoMediano fw-bold text-dark mb-1"><i class="bi bi-file-earmark-text texto-rojo me-1"></i> Términos y condiciones de uso</h5>
                                <p class="textoPequeno text-secondary">Última actualización: 20 de abril de 2026</p>

                                <h6 class="texto fw-bold text-dark mt-3">1. Aceptación de los términos</h6>
                                <p class="texto text-secondary">Al acceder y utilizar Spezziart, aceptas cumplir con estos términos y condiciones.</p>

                                <h6 class="texto fw-bold text-dark mt-3">2. Contenido generado por el usuario</h6>
                                <p class="texto text-secondary">Eres el único responsable de las recetas, comentarios y demás contenido que publiques. Nos reservamos el derecho de eliminar contenido inapropiado.</p>

                                <h6 class="texto fw-bold text-dark mt-3">3. Propiedad intelectual</h6>
                                <p class="texto text-secondary">Todo el contenido original de la plataforma está protegido por derechos de autor. Las recetas que compartes siguen siendo tuyas, pero nos das licencia para mostrarlas en la web.</p>

                                <h6 class="texto fw-bold text-dark mt-3">4. Conducta prohibida</h6>
                                <p class="texto text-secondary">No está permitido publicar contenido ofensivo, ilegal, o que infrinja derechos de terceros.</p>

                                <h6 class="texto fw-bold text-dark mt-3">5. Modificaciones</h6>
                                <p class="texto text-secondary">Podemos actualizar estos términos en cualquier momento. Te notificaremos de cambios importantes.</p>

                                <div class="alert bg-rojoClaro texto-rojo rounded-4 mt-3 mb-0">
                                    <i class="bi bi-envelope me-1"></i> Para preguntas, contacta con <strong>equipospezziart@gmail.com</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </main>

            <?php require_once __DIR__ . '/../../../global/navbar/view/NavbarView.php'; ?>

            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
            <script type="module" src="/pages/configuracion/assets/configuracion.js"></script>
        </body>
        </html>
        <?php
    }
}
