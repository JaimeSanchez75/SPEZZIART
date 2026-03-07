<?php
declare(strict_types=1);
require_once __DIR__ . '/vendor/autoload.php';
session_start();

$config = require __DIR__ . '/config/config.php';
//Limpieza de rutas.
$rawUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/App'; 

if (strpos($rawUri, $basePath) === 0) {
    $rawUri = substr($rawUri, strlen($basePath));
}
$uri = trim($rawUri, '/');
//--------------------------------------------------------------------------------------
/*DEFINICIÓN DE RUTAS PÚBLICAS*/
$publicRoutes = ['', 'pages/login', 'auth/login', 'auth/register', 'pages/feed', 'auth/logout'];

$isAdminRoute = str_starts_with($uri, 'pages/administracion');

//--------------------------------------------------------------------------------------
/*PROTECCIÓN JWT*/
if (!in_array($uri, $publicRoutes, true)) 
{
    require_once __DIR__ . '/core/jwtcheck.php';
    JWTcheck(); 
}
/*PROTECCIÓN ADMIN*/
// if ($isAdminRoute) 
// {
//     require_once __DIR__ . '/core/permisos.php';
//     requireAdmin(); 
// }
//--------------------------------------------------------------------------------------
/*ENRUTAMIENTO MANUAL*/
if ($uri === '') 
{
    header('Location: /App/pages/feed');
    exit;
}
// Procesar Login y Registro
if ($uri === 'auth/login' || $uri === 'auth/register') 
{
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    $controller = new AuthController();
    $method = ($uri === 'auth/login') ? 'login' : 'register';
    $controller->$method();
    exit;
}
if($uri === 'pages/administracion') 
{
    require_once __DIR__ . '/pages/administracion/view/dashboard.php';
    exit;
}
// Procesar Logout
if ($uri === 'auth/logout') 
{
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    $controller = new AuthController();
    $controller->logout();
    exit;
}
// Cargar la Vista de Login
if ($uri === 'pages/login') 
{
    require_once __DIR__ . '/pages/login/view/LoginView.php';
    exit;
}
// Cargar el FEED a través de su Controlador (NUNCA directo a la View)
if ($uri === 'pages/feed') 
{
    require_once __DIR__ . '/pages/feed/controller/FeedController.php';
    $controller = new FeedController();
    $controller->index();
    exit;
}
//--------------------------------------------------------------------------------------
/*ENRUTAMIENTO DINÁMICO (Para el resto de páginas)*/
$segments = explode('/', $uri);

if ($segments[0] === 'pages' && isset($segments[1])) 
{
    $pageName = $segments[1];
    $controllerName = ucfirst($pageName) . 'Controller';
    $methodName = $segments[2] ?? 'index';

    $controllerFile = __DIR__ . "/pages/$pageName/controller/$controllerName.php";

    if (file_exists($controllerFile)) 
    {
        require_once $controllerFile;
        if (class_exists($controllerName)) 
        {
            $controller = new $controllerName();
            if (method_exists($controller, $methodName)) 
            {
                $controller->$methodName();
                exit;
            }
        }
    }
}
//Si nada coincide
http_response_code(404);
echo "404 - La página [ $uri ] no existe en este servidor.";