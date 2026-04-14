<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/App/pages/administracion/assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Panel de Administración | SPEZZIART</title>
</head>

<body class="w-100 vh-100">
    <div class="container-fluid ">

        <div class="row">
            <!-- sidebar -->
            <div class="col-2 fixed-top bg-white vh-100 d-none d-md-flex flex-column border-end px-3 ">
                <?php
                require __DIR__ . "/Components/navegador.php";
                ?>
            </div>

            <!-- SIDEBAR MOVIL -->
            <div class="offcanvas offcanvas-start d-md-none" tabindex="-1" id="sidebarMobile">
                <div class="offcanvas-body p-0 m-0">
                    <div class="bg-white vh-100 d-flex flex-column border-end px-3">
                        <?php require __DIR__ . "/Components/navegador.php"; ?>
                    </div>
                </div>
            </div>

            <!-- navbar -->
            <div class="col-md-10 col-12 offset-md-2 pt-5 pt-md-0">
             
                <div class="fixed-top offset-md-2 bg-white ">
                    <?php
                    require __DIR__ . "/Components/header.php";
                    ?>
                </div>
                <div class="mt-md-5 pt-4 w-100">
                    <button class="bg-rojo border-0 text-white d-md-none position-fixed botonMenu texto fw-medium px-5 py-1"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#sidebarMobile">
                        Menú
                    </button>

                    <?php


                    require __DIR__ . "/" . $__view;
                    require __DIR__ . "/Components/usuario/modalCrear.php";
                    require __DIR__ . "/Components/usuario/modalConfirmacion.php";
                    require __DIR__ . "/Components/usuario/modalDatos.php";
               
                    require __DIR__ . "/Components/ingrediente/modalCrear.php";
                    require __DIR__ . "/Components/ingrediente/modalEditar.php";
                    require __DIR__ . "/Components/ingrediente/modalNutricion.php";
                    require __DIR__ . "/Components/ingrediente/modalConfirmacionEliminacion.php";


                    ?>

                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

<script src="/App/pages/administracion/assets/chart.js"></script>

</html>

<!-- offset-3: mueve tres columnas a la derecha -->