<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/App/pages/administracion/assets/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Panel de Administración | SPEZZIART</title>
</head>

<body class="w-100 vh-100">
    <div class="container-fluid ">

        <div class="row">
            <!-- sidebar -->
            <div class="col-3 fixed-top bg-white vh-100 d-flex flex-column">
                <?php
                require __DIR__ . "/Components/navegador.php";
                ?>
            </div>

            <!-- navbar -->
            <div class="col-9 offset-3">
                <div class="fixed-top offset-3 bg-white">
                    <?php
                    require __DIR__ . "/Components/header.php";
                    ?>
                </div>
                <div class="mt-5 pt-4">

                    <?php
                
                
                    require __DIR__ . "/".$__view;
                    require __DIR__ . "/Components/usuario/modalPerfil.php";
                    require __DIR__ . "/Components/usuario/modalCrear.php"; 
                    require __DIR__ . "/Components/usuario/modalConfirmacion.php";
                    require __DIR__ . "/Components/usuario/modalDatos.php";
        
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