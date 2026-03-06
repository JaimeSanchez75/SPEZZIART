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
function JWTcheck(): void
{
    if (empty($_COOKIE['token'])) 
    {
        header('Location: /App/pages/login');
        exit;
    }

    $config = require __DIR__ . '/../config/config.php';
    $secret = $config['JWT_SECRET'];
    $token = $_COOKIE['token'];
    
    try 
    {
        $decoded = JWT::decode($token, new Key($secret, 'HS256'));

        // Guardamos datos del usuario en sesión
        // Usamos (array) para poder acceder como $_SESSION['user']['id']
        $_SESSION['user'] = (array) $decoded;

    } 
    catch (ExpiredException | SignatureInvalidException | Exception $e) 
    {
        //Si el token falla por cualquier motivo:
        //Borramos la cookie para limpiar el navegador
        setcookie('token', '', time() - 3600, '/');
        
        //Redirigimos al login
        header('Location: /App/pages/login');
        exit;
    }
}