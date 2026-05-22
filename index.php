<?php
declare(strict_types=1);
// ------------------------Configuración inicial.--------------------------
if (!defined('ROOT_PATH')) {define('ROOT_PATH', __DIR__);}
require_once __DIR__ . '/core/logger.php';
Logger::init();
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {mkdir($logDir, 0755, true);}
$logFile = $logDir . '/php-error.log';
$maxSize = 10 * 1024 * 1024; // 10 MB
// Rotación automática de logs.
$logFile = __DIR__ . '/logs/php-error.log';
$maxSize = 10 * 1024 * 1024; // 10 MB
// Configurar el registro de errores de PHP
ini_set('log_errors', 1);
ini_set('error_log', $logFile);
ini_set('display_errors', 0);
error_reporting(E_ALL);
// Rotación de logs (se ejecuta aproximadamente 1 vez cada 100 peticiones)
if (mt_rand(1, 100) === 1 && file_exists($logFile) && filesize($logFile) > $maxSize) 
{
    $backup = $logDir . '/php-error_' . date('Ymd_His') . '.log';
    rename($logFile, $backup);
    // Eliminar backups de más de 30 días
    $backups = glob($logDir . '/php-error_*.log');
    foreach ($backups as $backupFile) {if (filemtime($backupFile) < strtotime('-30 days')) {unlink($backupFile);}}
}
//--------------------------------------------------------------------------
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/session.php';
require_once __DIR__ . '/core/auth.php';
require_once __DIR__ . '/core/flash.php';
require_once __DIR__ . '/core/error_pages.php';


use Phroute\Phroute\RouteCollector;
use Phroute\Phroute\Dispatcher;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
initSession(); // Inicializar sesión segura
// Procesar URL
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$basePath = '/';
if (strpos($uri, $basePath) === 0) {$uri = substr($uri, strlen($basePath));}
$uri = trim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];
$router = new RouteCollector();
// ========== RUTAS PÚBLICAS ==========
$router->post('api/log-frontend', function() //Logs del Front.
{
    csrf_verify();
    $input = json_decode(file_get_contents('php://input'), true);
    if (isset($input['level'], $input['file'], $input['method'], $input['message'])) 
    {
        $level = strtoupper($input['level']);
        $file = $input['file'];
        $method = $input['method'];
        $message = $input['message'];
        require_once __DIR__ . '/core/logger.php';
        Logger::init(); 
        
        switch ($level) 
        {
            case 'ERROR':
                Logger::error($file, $method, 'FRONTEND', 0, $message);
                break;
            case 'WARNING':
                Logger::warning($file, $method, 'FRONTEND', $message);
                break;
            case 'OK':
                Logger::success($file, $method, 'FRONTEND', $message);
                break;
            default:
                Logger::info($file, $method, 'FRONTEND', $message);
                break;
        }
    }
    echo json_encode(['status' => 'ok']);
    exit;
});
$router->get('/', function()  // Ruta Raíz es Feed.
{
    header('Location: /pages/feed');
    exit;
});
$router->get('pages/login', function() // Página Login.
{
    require_once __DIR__ . '/pages/login/view/LoginView.php';
});
$router->get('pages/recuperar', function() // Página Recuperar Contraseña.
{
    require_once __DIR__ . '/pages/login/view/recuperarContrasena.php';
});
$router->get('pages/login/resetear/{token}', function($token) // Página Reiniciar Contraseña.
{
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->mostrarFormularioPassword($token);
});
$router->post('auth/login', function()  // Método login.
{
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->login();
});
$router->post('auth/register', function() // Método register.
{
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->register();
});
$router->get('auth/logout', function()  // Método logout.
{
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->logout();
});
$router->post('/pages/login/actualizarContrasena', function()  // Método actualizar contraseña.
{
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->guardarContrasenaEditada();
});
$router->post('/pages/login/RecuperarCuenta', function()  // Método pedir reseteo.
{
    require_once __DIR__ . '/pages/login/controller/AuthController.php';
    (new AuthController())->resetearContrasena();
});
// Rutas públicas con vistas
$router->get('pages/buscar', function()  // Página Buscador.
{
    require_once __DIR__ . '/pages/busqueda/controller/BusquedaController.php';
    (new BusquedaController())->index();
});
$router->get('pages/feed', function() // Página Feed.
{
    require_once __DIR__ . '/pages/feed/controller/FeedController.php';
    (new FeedController())->index();
});
$router->post('pages/feed/filtrar', function() // Método filtrado en el Feed.
{
    require_once __DIR__ . '/pages/feed/controller/FeedController.php';
    (new FeedController())->filtrar();
});
$router->post('pages/buscar/filtrar', function() // Método filtrado en el Buscador.
{
    require_once __DIR__ . '/pages/busqueda/controller/BusquedaController.php';
    (new BusquedaController())->filtrar();
});
$router->get('pages/buscar/recomendaciones', function()  // Método recomendaciones en el Buscador.
{
    require_once __DIR__ . '/pages/busqueda/controller/BusquedaController.php';
    (new BusquedaController())->recomendaciones();
});
$router->get('api/receta/{id:i}', function($id) // Método obtener receta para el modal.
{
    require_once __DIR__ . '/pages/receta/controller/RecetaController.php';
    (new RecetaController())->obtenerRecetaApi($id);
}); 
// ========== RUTAS PROTEGIDAS (requieren autenticación) ==========
$router->group(['before' => 'requireAuth'], function($router) 
{
    // ----- Perfil ------
    $router->get('pages/perfil/{id:i}?', function($id = null) // Página del Perfil 
    {
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
        (new PerfilController())->index($id);
    });
    $router->post('pages/perfil/guardar-vitrina', function() // Método guardar vitrina.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
        (new PerfilController())->guardarVitrina();
    });
    $router->post('pages/perfil/seguir/{id:i}', function($id) // Método seguir.
    {
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
        (new PerfilController())->seguir($id);
    });
    $router->post('pages/perfil/actualizar-nombre', function()  // Método cambiar nombre.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
        (new PerfilController())->actualizarNombre();
    });
    $router->post('pages/perfil/subir-foto', function()  // Método subir foto perfil.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
        (new PerfilController())->subirFoto();
    });
    $router->get('pages/perfil/banners-todos', function()  // Método conseguir banners.
    {
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
        (new PerfilController())->obtenerTodosBanners(); 
    });
    $router->post('pages/perfil/cambiar-banner', function()  // Método cambiar banner.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
        (new PerfilController())->cambiarBanner();
    });
    $router->get('pages/perfil/logro-detalle', function()  // Método ver detalles logro.
    {
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
        (new PerfilController())->obtenerDetalleLogro();
    });
    // --- Feed - API endpoints ---
    $router->get('pages/feed/comentarios/{id:i}', function($id)  // Método ver comentarios receta.
    {
        require_once __DIR__ . '/pages/feed/controller/FeedController.php';
        (new FeedController())->obtenerComentarios($id);
    });
    $router->get('api/notificaciones', function() // Método obtener notificaciones.
    {
        require_once __DIR__ . '/pages/feed/controller/FeedController.php';
        (new FeedController())->obtenerNotificaciones(); 
    });    
    $router->post('api/notificaciones/leer', function() // Método marcar leído notificaciones.
    {
        require_once __DIR__ . '/pages/feed/controller/FeedController.php';
        (new FeedController())->leerNotificaciones(); 
    });     
    $router->delete('api/notificaciones/eliminar/{id:i}', function($id) // Método borrar notificación.
    {
        require_once __DIR__ . '/pages/feed/controller/FeedController.php';
        (new FeedController())->eliminarNotificacion($id); 
    });    
    $router->delete('api/notificaciones/limpiar', function() // Método limpiar todas las notificaciones.
    {
        require_once __DIR__ . '/pages/feed/controller/FeedController.php';
        (new FeedController())->limpiarNotificaciones(); 
    });    
    $router->post('api/solicitud/aceptar', function()  // Método aceptar solicitud de seguimiento.
    {
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
        (new PerfilController())->aceptarSolicitud();
    });
    $router->post('api/solicitud/rechazar', function() // Método rechazar solicitud de seguimiento.
    {
        require_once __DIR__ . '/pages/perfil/controller/PerfilController.php'; 
        (new PerfilController())->rechazarSolicitud();
    });
    $router->post('api/receta/like/{id:i}', function($id) // Método darle me gusta a receta.
    { 
        csrf_verify();
        require_once __DIR__ . '/pages/feed/controller/FeedController.php';
        (new FeedController())->toggleLike($id);
    }); 
    $router->post('api/receta/comentar', function() // Método comentar.
    { 
        csrf_verify();
        require_once __DIR__ . '/pages/feed/controller/FeedController.php';
        (new FeedController())->postearComentario();
    });
    $router->post('api/comentario/eliminar', function()
    {
        require_once __DIR__ . '/pages/feed/controller/FeedController.php';
        (new FeedController())->eliminarComentario();
    });
    $router->get('api/usuario/actual', function()  // Método obtener usuario actual
    {
        require_once __DIR__ . '/pages/feed/controller/FeedController.php';
        (new FeedController())->getUsuarioActual();
    });
    $router->get('pages/configuracion', function() // Configuración
    { 
        require_once __DIR__ . '/pages/configuracion/controller/ConfiguracionController.php';
        (new ConfiguracionController())->index();
    });
    $router->post('configuracion/guardar', function() // Página de configuración.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/configuracion/controller/ConfiguracionController.php';
        (new ConfiguracionController())->guardar();
    });
    // --- Recetas individuales ---
    $router->get('pages/individual', function() // Página individual.
    {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->index();
    });
    $router->get('pages/individual/crear', function()  // Método crear receta.
    {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->crear();
    });
    $router->get('pages/individual/ver', function()  // Método ver receta.
    {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->ver();
    });
    $router->post('pages/individual/guardar', function()  // Método guardar edición receta.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->guardar();
    });
    $router->post('pages/individual/eliminar', function()  // Método borrar receta.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->eliminar();
    });
    $router->post('pages/individual/crear-coleccion', function()  // Método crear colección.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->crearColeccion();
    });
    $router->post('api/receta/guardar', function()  // Método guardar receta en colección desde otros lados.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->guardarRecetaApi();
    });
    $router->get('pages/individual/coleccion', function()  // Página de la Colección.
    {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->verColeccion();
    });
    $router->get('api/colecciones', function()  // Método para obtener colecciones.
    {
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->obtenerColecciones();
    });
    $router->post('pages/individual/coleccion/agregar', function()  // Método añadir receta desde individual.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->agregarReceta();
    });
    $router->post('pages/individual/coleccion/eliminar-receta', function()  // Método quitar receta de colección.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->eliminarRecetaDeColeccion();
    });
    $router->post('pages/individual/eliminar-coleccion', function() // Método borrar la colección.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->eliminarColeccion();
    });
    $router->post('pages/individual/renombrar-coleccion', function() // Método renombrar colección.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/individual/controller/individualController.php';
        (new individualController())->renombrarColeccion();
    });
    // ================= FIT =================
    $router->get('pages/modofit', function ()
    {
        require_once __DIR__ . '/pages/modofit/controller/ModoFitController.php';
        (new ModoFitController())->index();
    });
    // ================= PLAN =================
    $router->post('fit/updateCantidad', function ()
    {
        require_once __DIR__ . '/pages/modofit/controller/ModoFitController.php';
        (new ModoFitController())->updateCantidad();
    });
    $router->post('fit/changeObjetivo', function ()
    {
        require_once __DIR__ . '/pages/modofit/controller/ModoFitController.php';
        (new ModoFitController())->changeObjetivo();
    });
    $router->post('fit/savePlan', function ()
    {
        require_once __DIR__ . '/pages/modofit/controller/ModoFitController.php';
        (new ModoFitController())->savePlan();
    });
    $router->post('fit/updateFitRealtime', function ()
    {
        require_once __DIR__ . '/pages/modofit/controller/ModoFitController.php';
        (new ModoFitController())->updateFitRealtime();
    });
    $router->post('fit/saveFitData', function ()
    {
        require_once __DIR__ . '/pages/modofit/controller/ModoFitController.php';
        (new ModoFitController())->saveFitData();
    });
    // ================= PLANES =================
    $router->get('fit/loadPlan', function ()
    {
        require_once __DIR__ . '/pages/modofit/controller/ModoFitController.php';
        (new ModoFitController())->loadPlan();
    });
    $router->get('fit/deletePlan', function ()
    {
        require_once __DIR__ . '/pages/modofit/controller/ModoFitController.php';
        (new ModoFitController())->deletePlan();
    });
    $router->get('fit/previewPlan', function ()
    {
        require_once __DIR__ . '/pages/modofit/controller/ModoFitController.php';
        (new ModoFitController())->previewPlan();
    });
    // ================= RECETAS =================
    $router->get('fit/addRecipe', function ()
    {
        require_once __DIR__ . '/pages/modofit/controller/ModoFitController.php';
        (new ModoFitController())->addRecipe();
    });
    // ================= COMIDAS =================
    $router->get('fit/setMeals', function ()
    {
        require_once __DIR__ . '/pages/modofit/controller/ModoFitController.php';
        (new ModoFitController())->setMeals();
    });
    $router->post('fit/removeMeal', function ()
    {
        require_once __DIR__ . '/pages/modofit/controller/ModoFitController.php';
        (new ModoFitController())->removeMeal();
    });
    // --- Reportes ---
    $router->post('api/reportar/receta', function() // Método reporte de receta.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/reporte/controller/ReporteController.php';
        (new ReporteController())->reportarReceta();
    });
    $router->post('api/reportar/comentario', function()  // Método reporte de comentario.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/reporte/controller/ReporteController.php';
        (new ReporteController())->reportarComentario();
    });
    $router->post('api/reportar/usuario', function()  // Método reporte de usuario.
    {
        csrf_verify();
        require_once __DIR__ . '/pages/reporte/controller/ReporteController.php';
        (new ReporteController())->reportarUsuario();
    });
    // ========== RUTAS DE ADMIN ==========
    $router->group(['before' => 'requireAdmin'], function($router) 
    {
        $router->get('pages/administracion', function() // Página Panel de Administración.
        {
            require_once __DIR__ . '/pages/administracion/controller/principalController.php';
            (new PrincipalController())->index();
        });
        $router->get('pages/administracion/moderacion', function() // Componente del Panel principal de moderación.
        {
            require_once __DIR__ . '/pages/administracion/controller/moderacionController.php';
            (new moderacionController())->index();
        });
        $router->get('pages/administracion/moderacion/historial', function() // Historial de reportes aprobados/rechazados.
        {
            require_once __DIR__ . '/pages/administracion/controller/moderacionController.php';
            (new moderacionController())->historial();
        });
        $router->get('pages/administracion/recetas', function()  // Componente de Recetas.
        {
            require_once __DIR__ . '/pages/administracion/controller/recetasController.php';
            (new RecetasController())->index();
        });
        $router->get('pages/administracion/recetas/ver/{id:i}', function($id)  // Vista de detalle de receta.
        {
            require_once __DIR__ . '/pages/administracion/controller/recetasController.php';
            (new RecetasController())->verReceta($id);
        });
        $router->get('pages/administracion/ingredientes', function()  // Componente de Ingredientes.
        {
            require_once __DIR__ . '/pages/administracion/controller/ingredientesController.php';
            (new IngredientesController())->index();
        });
        $router->post('pages/administracion/Ingredientes/importar', function()  // Componente de Importar Ingredientes.
        {
            require_once __DIR__ . '/pages/administracion/controller/ingredientesController.php';
            (new IngredientesController())->importar();
        });
        $router->get('pages/administracion/etiquetas', function()  // Componente de Etiquetas.
        {
            require_once __DIR__ . '/pages/administracion/controller/etiquetasController.php';
            (new EtiquetasController())->index();
        });
        $router->get('pages/administracion/usuarios', function()  // Componente de Usuarios.
        {
            require_once __DIR__ . '/pages/administracion/controller/usuariosController.php';
            (new UsuariosController())->index();
        });
        $router->get('pages/administracion/principal/recetasPorDia', function()  // Componente gráfico de Recetas por Día.
        {
            require_once __DIR__ . '/pages/administracion/controller/principalController.php';
            (new PrincipalController())->ajax();
        });
        $router->post('pages/administracion/usuarios/crear', function()  // Método de crear usuarios.
        {
            csrf_verify();
            require_once __DIR__ . '/pages/administracion/controller/usuariosController.php';
            (new UsuariosController())->crearUsuario();
        });
        $router->post('pages/administracion/usuarios/editar', function()  // Método de editar usuarios.
        {
            csrf_verify();
            require_once __DIR__ . '/pages/administracion/controller/usuariosController.php'; 
            (new UsuariosController())->editarUsuario();
        });
        $router->post('pages/administracion/subir-foto', function()  // Método subir foto perfil desde admin.
        {
            csrf_verify();
            require_once __DIR__ . '/pages/perfil/controller/PerfilController.php';
            (new PerfilController())->subirFoto();
        });
        $router->post('pages/administracion/usuarios/confirmarEliminacion', function() // Método eliminar usuario.
        {
            csrf_verify();
            require_once __DIR__ . '/pages/administracion/controller/usuariosController.php';
            (new UsuariosController())->eliminarUsuario();
        });
        $router->post('pages/administracion/usuarios/cambiarEstado', function()  // Activar/deshabilitar usuario desde la tabla.
        {
            require_once __DIR__ . '/pages/administracion/controller/usuariosController.php';
            (new UsuariosController())->cambiarEstadoUsuario();
        });
        $router->post('pages/administracion/usuarios/cambiarRol', function()  // Cambiar rol usuario/administrador.
        {
            require_once __DIR__ . '/pages/administracion/controller/usuariosController.php';
            (new UsuariosController())->cambiarRolUsuario();
        });
        $router->post('pages/administracion/receta/crear', function()  // Método crear receta.
        {
            csrf_verify();
            require_once __DIR__ . '/pages/administracion/controller/recetasController.php';
            (new RecetasController())->crearReceta();
        });
        $router->post('pages/administracion/receta/editar', function()  // Método editar receta.
        {
            csrf_verify();
            require_once __DIR__ . '/pages/administracion/controller/recetasController.php';
            (new RecetasController())->editarReceta();
        });
        $router->get('pages/administracion/receta/json/{id:i}', function($id)  // Método Obtener recetas en JSON.
        {
            require_once __DIR__ . '/pages/administracion/controller/recetasController.php';
            (new RecetasController())->obtenerRecetaJson($id);
        });
        $router->get('pages/administracion/ingredientes/json', function()  // Método obtener ingredientes en JSON.
        {
            require_once __DIR__ . '/pages/administracion/controller/recetasController.php';
            (new RecetasController())->ingredientesJson();
        });
        $router->post('pages/administracion/receta/eliminar', function()  // Método eliminar receta.
        {
            csrf_verify();
            require_once __DIR__ . '/pages/administracion/controller/recetasController.php';
            (new RecetasController())->eliminarReceta();
        });
        $router->post('pages/administracion/etiquetas/crear', function()  // Método crear etiqueta.
        {
            csrf_verify();
            require_once __DIR__ . '/pages/administracion/controller/etiquetasController.php';
            (new EtiquetasController())->crearEtiqueta();
        });
        $router->post('pages/administracion/etiquetas/editar', function()  // Método editar etiqueta.
        {
            csrf_verify();
            require_once __DIR__ . '/pages/administracion/controller/etiquetasController.php';
            (new EtiquetasController())->editarEtiqueta();
        });
        $router->post('pages/administracion/etiquetas/eliminar', function()  // Método eliminar etiqueta.
        {
            csrf_verify();
            require_once __DIR__ . '/pages/administracion/controller/etiquetasController.php';
            (new EtiquetasController())->eliminarEtiqueta();
        });
        $router->post('pages/administracion/Ingredientes/crear', function()  // Método crear ingrediente.
        {

            require_once __DIR__ . '/pages/administracion/controller/ingredientesController.php';
            (new IngredientesController())->crearIngrediente();
        });
        $router->post('pages/administracion/Ingredientes/editar', function()  // Método editar ingrediente.
        {
            require_once __DIR__ . '/pages/administracion/controller/ingredientesController.php';
            (new IngredientesController())->editarIngrediente();
        });
        $router->post('pages/administracion/Ingredientes/verificar', function()  // Método verificar ingrediente.
        {
            require_once __DIR__ . '/pages/administracion/controller/ingredientesController.php';
            (new IngredientesController())->verificarIngrediente();
        });
        $router->post('pages/administracion/usuarios/resetearContrasena', function()  // Método resetear contraseña usuario.
        {
            require_once __DIR__ . '/pages/administracion/controller/usuariosController.php';
            (new UsuariosController())->resetearContrasena();
        });
        $router->post('moderacion/marcarRevisado', function()  // Método marcar revisión de reporte.
        {
            csrf_verify();
            require_once __DIR__ . '/pages/administracion/controller/moderacionController.php';
            (new moderacionController())->marcarRevisado();
        });
        $router->post('moderacion/aceptarReporte', function()  // Método aceptar reporte.
        {
            csrf_verify();
            require_once __DIR__ . '/pages/administracion/controller/moderacionController.php';
            (new moderacionController())->aceptarReporte();
        });
        $router->post('/pages/administracion/configuracion/tema', function()  // Método cambiar tema.
        {
            
            require_once __DIR__ . '/pages/administracion/controller/configuracionContoller.php';
            (new ConfiguracionController())->modoVision();
        });
        $router->post('/pages/administracion/configuracion/notificaciones', function()  // Método cambiar notificaciones.
        {

            require_once __DIR__ . '/pages/administracion/controller/configuracionContoller.php';
            (new ConfiguracionController())->notificaciones();
        });
        $router->get('pages/administracion/notificaciones/obtener', function()  // Obtener notificaciones del administrador.
        {
            require_once __DIR__ . '/pages/administracion/controller/notificacionesController.php';
            (new NotificacionesController())->obtener();
        });
        $router->post('pages/administracion/notificaciones/leer', function()  // Marcar como leídas.
        {
            require_once __DIR__ . '/pages/administracion/controller/notificacionesController.php';
            (new NotificacionesController())->marcarLeidas();
        });
        $router->post('pages/administracion/notificaciones/eliminar', function()  // Eliminar notificación.
        {
            require_once __DIR__ . '/pages/administracion/controller/notificacionesController.php';
            (new NotificacionesController())->eliminar();
        });
        $router->post('pages/administracion/notificaciones/limpiar', function()  // Limpiar todas.
        {
            require_once __DIR__ . '/pages/administracion/controller/notificacionesController.php';
            (new NotificacionesController())->limpiar();
        });
    });
});
// --- Registrar middlewares ---
$router->filter('requireAuth', 'requireAuth');
$router->filter('requireAdmin', 'requireAdmin');
// --- Dispatch ---
$dispatcher = new Dispatcher($router->getData());
try 
{
    $response = $dispatcher->dispatch($method, $uri);
    if ($response !== null) {echo $response;}
} 
catch (Phroute\Phroute\Exception\HttpRouteNotFoundException $e) 
{
    renderErrorPage(
        404,
        'Página no encontrada',
        'La página que estás buscando no existe o ha sido movida.',
        ''
    );
}
catch (Phroute\Phroute\Exception\HttpMethodNotAllowedException $e) 
{
    renderErrorPage(
        405,
        'Método no permitido',
        'Este recurso no admite el método HTTP utilizado.',
        ''
    );
}
catch (Throwable $e) 
{
    renderErrorPage(
        500,
        'Error interno del servidor',
        'Ha ocurrido un error inesperado. Por favor, inténtalo de nuevo más tarde.',
        $e->getMessage()
    );
}
