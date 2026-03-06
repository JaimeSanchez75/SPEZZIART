<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="/App/pages/administracion/assets/styles.css">
    <title>Panel de Administración | SPEZZIART</title>
</head>

<body class="w-100 vh-100">
    <div class="container-fluid ">

        <div class="row">
            <!-- sidebar -->
            <div class="col-3 ">
                <?php
                require __DIR__ . "/Components/navegador.php";
                ?>
            </div>

            <!-- navbar -->
            <div class="col-9">
                <?php
                require __DIR__ . "/Components/header.php";
                $page = $_GET['page'] ?? 'principal';

                switch ($page) {
                    case 'principal':
                        require __DIR__ . "/principal/principal.php";
                        break;

                    case 'usuarios':
                        require __DIR__ . "/usuarios&admin/usuarios.php";
                        break;

                    case 'recetasIngredientes':
                        require __DIR__ . "/recetas&ingredientes/recetasIngredientes.php";
                        break;

                    case 'moderacion':
                        require __DIR__ . "/moderacion/moderacion.php";
                        break;

                    default:
                        require __DIR__ . "/principal/principal.php";
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>