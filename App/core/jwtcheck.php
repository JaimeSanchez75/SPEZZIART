<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
//============================Autenticación JWT============================
/*Autenticación JWT para rutas protegidas. 
Si el token es válido, se guarda la información del usuario en la sesión para su uso posterior (como en permisos.php). 
Si no es válido, se devuelve un error 401 con un mensaje específico según el tipo de error.*/

//En el Token se guarda el ID, Email, Nombre de Usuario y Rol.
function JWTcheck(): array
{
    if (empty($_COOKIE['token'])) {
        header('Location: /App/pages/login');
        exit;
    }

    $config = require __DIR__ . '/../config/config.php';
    $secret = $config['JWT_SECRET'];
    $token = $_COOKIE['token'];

    try {

    $decoded = JWT::decode($token, new Key($secret, 'HS256'));

    if (!isset($decoded->id)) {
        throw new Exception('Invalid payload');
    }

    return (array) $decoded;

    } catch (Throwable $e) {

        setcookie('token', '', time() - 3600, '/');
        header('Location: /App/pages/login');
        exit;
    }
}