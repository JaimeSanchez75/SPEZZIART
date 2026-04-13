<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar cuenta | SPEZZIART</title>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/App/global/styles/global.css">
    <link rel="stylesheet" href="/App/global/styles/auth.css">
</head>

<body>
    <h1>Recuperar cuenta</h1>
    <div class="">
        <?php
        if (isset($_GET['estado'])) {
            if ($_GET['estado'] === 'correcto') {
                echo "<div class='alert alert-primary' >Email enviado correctamente </div>";
            } else {
                echo "<div class='alert alert-primary' >Email no enviado. Revisa que el email que has escrito este correcto. </div>";
            }
        }
        ?>
    </div>

    <form action="/App/pages/login/RecuperarCuenta" method="post">

        <label for="">Email</label>
        <input type="email" name="email" id="email">

        <input type="submit" value="Recuperar cuenta">

    </form>
</body>

</html>