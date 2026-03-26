<?php

declare(strict_types=1);

require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/jwtcheck.php';

use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

session_start();
if (isset($_COOKIE['token'])) {
    $user = JWTcheck();
    Auth::setUser($user);
}

$config = require __DIR__ . '/config/config.php';


$URI = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/App';
if (strpos($URI, $basePath) === 0) {
    $URI = substr($URI, strlen($basePath));
}
$uri = trim($URI, '/');
$method = $_SERVER['REQUEST_METHOD'];

$router = new RouteCollector();


$router->filter('auth', function () {
    $user = JWTcheck();
    if (!$user) {
        header('Location: /App/pages/login');
        exit;
    }
    Auth::setUser($user);
});
$router->filter('admin', function () {
    require_once __DIR__ . '/core/permisos.php';
    if (!requireAdmin()) {
        header('Location: /App/pages/login');
        exit;
    }
});




$router->get('/', function () {
    header('Location: /App/pages/feed');
    exit;
});

$router->get('pages/login', function () {
    require_once __DIR__ . '/pages/login/view/LoginView.php';
});

$router->post('auth/login', function () {
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->login();
});


$router->post('auth/register', function () {
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->register();
});


$router->get('auth/logout', function () {
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->logout();
});


$router->get('pages/feed', function () {
    require_once __DIR__ . '/pages/feed/controller/FeedController.php';
    (new FeedController())->index();
});
$router->post('pages/feed/filtrar', function () {
    require_once __DIR__ . '/pages/feed/controller/FeedController.php';
    (new FeedController())->filtrar();
});

// rutas protegidas por autenticación
$router->group(['before' => 'auth'], function ($router) {

    // Administración
    $router->group(['before' => 'admin'], function ($router) {
        // Administración
        $router->get('pages/administracion', function () {
            require_once __DIR__ . '/pages/administracion/controller/principalController.php';
            (new PrincipalController())->index();
        });

        $router->get('pages/administracion/principal/recetasPorDia', function () {
            require_once __DIR__ . '/pages/administracion/controller/principalController.php';
            $controller = new PrincipalController();
            $controller->ajax(); // devuelve JSON
        });

        $router->get('pages/administracion/usuarios', function () {
            require_once __DIR__ . '/pages/administracion/controller/usuariosController.php';
            $controller = new UsuariosController();
            $controller->index();
        });

        $router->post('pages/administracion/usuarios/crear', function () {
            require_once __DIR__ . '/pages/administracion/controller/usuariosController.php';
            $controller = new UsuariosController();
            $controller->crearUsuario();
        });

        $router->get('pages/administracion/usuarios/confirmarEliminacion/{id:i}', function ($id) {
            require_once __DIR__ . '/pages/administracion/controller/usuariosController.php';
            $controller = new UsuariosController();
            $controller->eliminarUsuario($id);
        });


        $router->get('pages/administracion/moderacion', function () {
            require_once __DIR__ . '/pages/administracion/controller/moderacionController.php';
            $controller = new moderacionController();
            $controller->index();
        });

        $router->get('pages/administracion/recetas', function () {
            require_once __DIR__ . '/pages/administracion/controller/recetasController.php';
            $controller = new RecetasController();
            $controller->index();
        });

        $router->get('pages/administracion/receta/eliminar/{id:i}', function ($id) {
            require_once __DIR__ . '/pages/administracion/controller/recetasController.php';
            $controller = new RecetasController();
            $controller->eliminarReceta($id);
        });

        $router->get('pages/administracion/ingredientes', function () {
            require_once __DIR__ . '/pages/administracion/controller/ingredientesController.php';
            $controller = new IngredientesController();
            $controller->index();
        });

        $router->get('pages/administracion/etiquetas', function () {
            require_once __DIR__ . '/pages/administracion/controller/etiquetasController.php';
            $controller = new EtiquetasController();
            $controller->index();
        });
        $router->post('pages/administracion/etiquetas/crear', function () {
            require_once __DIR__ . '/pages/administracion/controller/etiquetasController.php';
            $controller = new EtiquetasController();
            $controller->crearEtiqueta();
        });
        $router->post('pages/administracion/etiquetas/editar', function () {
            require_once __DIR__ . '/pages/administracion/controller/etiquetasController.php';
            $controller = new EtiquetasController();
            $controller->editarEtiqueta();
        });

        $router->get('pages/administracion/etiquetas/eliminar/{id:i}', function ($id) {
            require_once __DIR__ . '/pages/administracion/controller/etiquetasController.php';
            $controller = new EtiquetasController();
            $controller->eliminarEtiqueta($id);
        });

        $router->post('pages/administracion/Ingredientes/crear', function () {
            require_once __DIR__ . '/pages/administracion/controller/ingredientesController.php';
            $controller = new IngredientesController();
            $controller->crearIngrediente();
        });

        $router->post('pages/administracion/Ingredientes/editar', function () {
            require_once __DIR__ . '/pages/administracion/controller/ingredientesController.php';
            $controller = new IngredientesController();
            $controller->editarIngrediente();
        });

        $router->post('pages/administracion/usuarios/resetearContrasena', function () {
            require_once __DIR__ . '/pages/administracion/controller/usuariosController.php';
            $controller = new UsuariosController();
            $controller->resetearContrasena(); // Método que crearemos abajo
        });
    });

    $router->get('pages/perfil/{id:i}?', function ($id = null) {
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
        (new PerfilController())->index($id);
    });
    $router->get('pages/feed/comentarios/{id:i}', function ($id) {
        require_once __DIR__ . '/pages/feed/controller/FeedController.php';
        (new FeedController())->obtenerComentarios($id);
    });
    $router->post('pages/perfil/guardar-vitrina', function () {
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
        (new PerfilController())->guardarVitrina();
    });
    $router->post('pages/perfil/seguir/{id:i}', function ($id) {
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
        (new PerfilController())->seguir($id);
    });

    //parte individual
    // ---------- PARTE INDIVIDUAL ----------
    $router->get('pages/individual', function () {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->index();
    });

    $router->get('pages/individual/crear', function () {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->crear();
    });
    
    $router->get('pages/individual/ver', function () {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->ver();
    });

    $router->post('pages/individual/guardar', function () {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->guardar();
    });

    $router->post('api/receta/like/{id:i}', function ($id) {
        require_once __DIR__ . '/pages/feed/controller/FeedController.php';
        (new FeedController())->toggleLike($id);
    });
    $router->post('api/receta/comentar', function () {
        require_once __DIR__ . '/pages/feed/controller/FeedController.php';
        (new FeedController())->postearComentario();
    });
     $router->post('pages/individual/eliminar', function () {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->eliminar();
    });

    // Ruta POST para crear colecciones
    $router->post('pages/individual/crear-coleccion', function () {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->crearColeccion();
    });

    // Ruta GET para ver recetas de una colección
    $router->get('pages/individual/coleccion', function () {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->verColeccion();
    });

    // Ruta POST para agregar receta a una colección
    $router->post('pages/individual/coleccion/agregar', function () {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->agregarReceta();
    });

    $router->post('pages/individual/eliminar-coleccion', function () {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->eliminarColeccion();
    });

    $router->post('pages/individual/coleccion/eliminar-receta', function () {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->eliminarRecetaDeColeccion();
    });
});


$router->get('pages/login/resetear/{token}', function ($token) {

    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->mostrarFormularioPassword($token);
});

$router->post('/pages/login/actualizarContrasena', function () {

    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->guardarContrasenaEditada();
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
}
