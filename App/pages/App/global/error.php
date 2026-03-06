<?php
// error.php
//Aun no se usa en nada, pero ya lo pondremos.
$codigo = http_response_code() ?: 500;
$titulo = $codigo === 404 ? "Página no encontrada" : ($codigo === 403 ? "Acceso denegado" : "Error del servidor");
$mensaje = $codigo === 404 ? "Lo sentimos, la página que buscas no existe." :
           ($codigo === 403 ? "No tienes permisos para acceder a esta página." :
           "Ha ocurrido un error inesperado. Intenta más tarde.");
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $codigo ?> - <?= $titulo ?></title>
<style>
body { font-family: sans-serif; text-align: center; padding: 5em; background:#f8f8f8; color:#333; }
h1 { font-size: 3em; margin-bottom:0; }
p { font-size: 1.2em; margin-top:0.5em; }
a { color:#007bff; text-decoration:none; }
a:hover { text-decoration:underline; }
</style>
</head>
<body>
<h1><?= $codigo ?> - <?= $titulo ?></h1>
<p><?= $mensaje ?></p>
<p><a href="/">Volver al inicio</a></p>
</body>
</html>