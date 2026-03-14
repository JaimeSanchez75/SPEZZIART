<?php
declare(strict_types=1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/jwtcheck.php';
session_start();

use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;


$config = require __DIR__ . '/config/config.php';

// recoger la url
$URI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/App';
if (strpos($URI, $basePath) === 0) {
    $URI = substr($URI, strlen($basePath));
}
$uri = trim($URI, '/');
$method = $_SERVER['REQUEST_METHOD'];

$router = new RouteCollector();


$router->filter('auth', function() {
    return JWTcheck();
});


$router->get('/', function() {
    header('Location: /App/pages/feed');
    exit;
});

$router->get('pages/login', function() {
    require_once __DIR__ . '/pages/login/view/LoginView.php';
});


$router->post('auth/login', function() {
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->login();
});


$router->post('auth/register', function() {
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->register();
});


$router->get('auth/logout', function() {
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->logout();
});


$router->get('pages/feed', function() {
    require_once __DIR__ . '/pages/feed/controller/FeedController.php';
    (new FeedController())->index();
});

// rutas protegidas por autenticación
$router->group(['before' => 'auth'], function($router) {
    
    // Administración
    $router->get('pages/administracion', function() {
        require_once __DIR__ . '/pages/administracion/controller/principalController.php';
        (new PrincipalController())->index();
    });

    $router->get('pages/administracion/principal/recetasPorDia', function() {
        require_once __DIR__ . '/pages/administracion/controller/principalController.php';
        $controller = new PrincipalController();
        $controller->ajax(); // devuelve JSON
    });

    $router->get('pages/administracion/usuarios', function() {
        require_once __DIR__ . '/pages/administracion/controller/usuariosController.php';
        $controller = new UsuariosController();
        $controller->index();
    });

    $router->get('pages/administracion/moderacion', function() {
        require_once __DIR__ . '/pages/administracion/controller/moderacionController.php';
        $controller = new moderacionController();
        $controller->index();
    });

    
});


$dispatcher = new Dispatcher($router->getData());

try {
    $response = $dispatcher->dispatch($method, $uri);
    if ($response !== null) {
        echo $response;
    }
} catch (Phroute\Phroute\Exception\HttpRouteNotFoundException $e) {
    http_response_code(404);
    echo "404 - Página no encontrada: /$uri";
} catch (Phroute\Phroute\Exception\HttpMethodNotAllowedException $e) {
    http_response_code(405);
    echo "405 - Método no permitido";
}