<?php declare(strict_types=1); ?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="<?= htmlspecialchars($_SESSION['user']['tema'] ?? 'sistema', ENT_QUOTES) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" href="/global/favicon/logo.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/global/styles/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/dayjs/dayjs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs/plugin/relativeTime.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/dayjs/locale/es.js"></script>
    <script>window.userTheme = <?= json_encode($_SESSION['user']['tema'] ?? 'sistema') ?>;</script>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <script src="/global/js/theme.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title><?= htmlspecialchars($__titulo, ENT_QUOTES) ?> | SPEZZIART</title>
</head>
<body class="w-100 vh-100">
    <div class="container-fluid ">
        <div class="row">
            
            <div class="col-lg-3 col-xl-2 fixed-top bg-white vh-100 d-none d-lg-flex flex-column border-end px-3 ">
                <?php require __DIR__ . "/Components/navegador.php";?>
            </div>
            
            <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="sidebarMobile">
                <div class="offcanvas-body p-0 m-0">
                    <div class="bg-white vh-100 d-flex flex-column border-end px-3">
                    <?php require __DIR__ . "/Components/navegador.php"; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-lg-9 col-xl-10 offset-lg-3 offset-xl-2 pt-5 pt-lg-0">
                <div class="fixed-top offset-lg-3 offset-xl-2 bg-white "><?php require __DIR__ . "/Components/header.php";?></div>
                <div class="mt-lg-5 pt-4 w-100"><?php require __DIR__ . "/" . $__view;?></div>
            </div>
        </div>
    </div>
    <?php require __DIR__ . "/Components/configAdmin/modalConfig.php";?>
    <?php require ROOT_PATH . '/global/modalConfirmacionGenerico.php';?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>window.__alertasPendientes = <?= json_encode(Flash::consume()) ?>;</script>
    <script src="/global/js/alertas.js"></script>
</body>
<script src="/pages/administracion/assets/chart.js"></script>
<script src="/pages/administracion/assets/notificaciones/notificaciones.js"></script>
<script src="/pages/administracion/assets/inputsNumericos.js"></script>

</html>
