
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Spezziart | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
    <body class="bg-light">
        <nav class="navbar navbar-dark bg-dark">
            <div class="container">
                <span class="navbar-brand">Spezziart</span>
                <?php if ($isLoggedIn): ?>
                    <a href="/App/auth/logout" class="btn btn-outline-danger btn-sm">Cerrar Sesión</a>
                <?php else: ?>
                    <a href="/App/pages/login" class="btn btn-outline-primary btn-sm">Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </nav>
        <div class="container mt-5">
            <div class="card p-5 shadow-sm">
                <?php if ($isLoggedIn): ?>
                    <h1>¡Bienvenido, <?php echo htmlspecialchars($username); ?>!</h1>
                    <p class="lead">Has iniciado sesión correctamente. Tu token JWT es válido.</p>
                <?php else: ?>
                    <h1>¡Hola, Invitado!</h1>
                    <p class="lead">Estás viendo el feed público. Inicia sesión para publicar.</p>
                <?php endif; ?>
                <hr>
                <p>Este es tu panel de control principal.</p>
            </div>
        </div>
    </body>
</html>