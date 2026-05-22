<?php
require_once __DIR__ . '/../model/FeedModel.php';
require_once __DIR__ . '/../view/FeedView.php';
require_once __DIR__ . '/../../../core/auth.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
class FeedController
{
    private $model;
    private $view;
    public function __construct()
    {
        $this->model = new FeedModel();
        $this->view = new FeedView();
    }
    public function index()
    {
        $userId = Auth::check() ? Auth::id() : null;
        $orden = $_GET['orden'] ?? 'populares';
        // Generar una semilla única para esta carga de página
        $seed = mt_rand(1, 1000000);
        $limit = 5;
        $offset = 0;
        $recetas = $this->model->getPostsFiltradosConSemilla('', [], $limit, $offset, $userId, $orden, $seed);
        $etiquetas = $this->model->getEtiquetasDisponibles();
        $config = $userId ? $this->model->getUserConfig($userId) : null;
        $fotoPerfilUsuario = $this->model->getFotoUsuarioActual($userId);
        $this->view->render($recetas, $etiquetas, $_GET['cat'] ?? null, $config, $orden, $fotoPerfilUsuario, $seed);
    }
    public function eliminarComentario()
    {
        csrf_verify();
        header('Content-Type: application/json');
        if (!Auth::check()) 
        {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'No autenticado']);
            exit;
        }
        $idComentario = (int)($_POST['id_comentario'] ?? 0);
        $userId = Auth::id();
        if ($idComentario <= 0) 
        {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Comentario inválido']);
            exit;
        }
        $resultado = $this->model->eliminarComentario($idComentario, $userId);
        if (!$resultado || empty($resultado['success'])) 
        {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'No puedes eliminar este comentario']);
            exit;
        }
        echo json_encode
        ([
            'success' => true,
            'id_receta' => $resultado['id_receta']
        ]);
        exit;
    }
    public function filtrar()
    {
        if (ob_get_level()) ob_clean();
        try 
        {
            $input = json_decode(file_get_contents('php://input'), true);
            $etiquetas = $input['etiquetas'] ?? [];
            $busqueda = $input['busqueda'] ?? null;
            $orden = $input['orden'] ?? 'populares';
            $seed = intval($input['seed'] ?? 1);  
            $limit = 5;
            $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;
            $userId = Auth::check() ? Auth::id() : null;
            $posts = $this->model->getPostsFiltradosConSemilla($busqueda, $etiquetas, $limit, $offset, $userId, $orden, $seed);
            $html = '';
            foreach ($posts as $receta) {$html .= $this->view->renderRecipeCard($receta);}
            if (headers_sent()) throw new Exception('Headers already sent');
            header('Content-Type: application/json');
            echo json_encode
            ([
                'html' => $html,
                'count' => count($posts)
            ]);
            exit();
        } 
        catch (Exception $e) 
        {
            error_log('Error en filtrar: ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Error interno del servidor']);
            exit();
        }
        
    }
    public function toggleLike($id)
    {
        csrf_verify();
        if (!Auth::check()) 
        {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
            exit;
        }
        $userId = Auth::id();
        $resultado = $this->model->toggleLike($id, $userId);
        if ($resultado['accion'] === 'added') 
        {
            $dueno = $this->model->getCreadorReceta($id);
            if ($dueno != $userId) {if (!$this->model->existeNotificacion($dueno, $userId, 'like', $id)) {$this->model->crearNotificacion($dueno, $userId, "le ha gustado tu receta", "like", $id);}}
        }
        echo json_encode
        ([
            'status' => 'success',
            'newLikes' => $resultado['likes'],
            'action' => $resultado['accion']
        ]);
        exit();
    }
    public function obtenerComentarios($id)
    {
        if (!Auth::check()) 
        {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Sesión requerida']);
            exit;
        }
        $comentarios = $this->model->getComentarios($id);
        foreach ($comentarios as &$c) {$c['Fecha'] = date('d M, H:i', strtotime($c['Fecha']));}
        header('Content-Type: application/json');
        echo json_encode($comentarios);
        exit();
    }
    public function postearComentario()
    {
        csrf_verify();
        if (!Auth::check()) 
        {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
            exit();
        }
        $idReceta = $_POST['id_receta'] ?? null;
        $texto = trim($_POST['comentario'] ?? '');
        $userId = Auth::id();
        if ($idReceta && !empty($texto)) 
        {
            $idComentario = $this->model->agregarComentario($idReceta, $userId, $texto);
            if ($idComentario) 
            {
                $dueno = $this->model->getCreadorReceta($idReceta);
                if ($dueno != $userId)
                {
                    if (!$this->model->existeNotificacion($dueno, $userId, 'comentario', $idReceta)) 
                    {
                        $this->model->crearNotificacion
                        (
                            $dueno,
                            $userId,
                            "ha comentado: " . substr($texto, 0, 40),
                            "comentario",
                            $idReceta,
                            $idComentario
                        );
                    }
                }
                echo json_encode(['status' => 'success', 'id_comentario' => $idComentario]);
                exit();
            }
        }
        echo json_encode(['status' => 'error', 'message' => 'No se pudo publicar el comentario']);
        exit();
    }
    public function obtenerNotificaciones()
    {
        if (!Auth::check()) 
        {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            exit();
        }
        $userId = Auth::id();
        $model = new FeedModel();
        $data = $model->getNotificaciones($userId);
        $noLeidas = $model->contarNoLeidas($userId);
        echo json_encode
        ([
            'notificaciones' => $data,
            'noLeidas' => $noLeidas
        ]);
        exit();
    }
    public function leerNotificaciones()
    {
        if (!Auth::check()) 
        {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            exit();
        }
        $userId = Auth::id();
        $model = new FeedModel();
        $model->marcarLeidas($userId);
        echo json_encode(['ok' => true]);
        exit();
    }
    public function eliminarNotificacion($id)
    {
        if (!Auth::check()) 
        {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            exit();
        }
        $userId = Auth::id();
        $model = new FeedModel();
        $result = $model->eliminarNotificacion($id, $userId);
        if ($result) {echo json_encode(['status' => 'success']); exit();} 
        else 
        {
            http_response_code(404);
            echo json_encode(['status' => 'error', 'message' => 'Notificación no encontrada']);
            exit();
        }
    }
    public function limpiarNotificaciones()
    {
        if (!Auth::check()) 
        {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            exit();
        }
        $userId = Auth::id();
        $model = new FeedModel();
        $result = $model->limpiarNotificaciones($userId);
        if ($result) {echo json_encode(['status' => 'success']); exit();} 
        else 
        {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => 'Error al eliminar notificaciones']);
            exit();
        }
    }
    public function getUsuarioActual() 
    {
        header('Content-Type: application/json');
        if (!Auth::check()) 
        {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            return;
        }
        $userId = Auth::id();
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Usuario, Nombre, FotoPerfil FROM Usuario WHERE ID_Usuario = ?");
        $stmt->execute([$userId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($usuario) 
        {
            echo json_encode
            ([
                'success' => true,
                'id' => $usuario['ID_Usuario'],
                'nombre' => $usuario['Nombre'],
                'foto_perfil' => $usuario['FotoPerfil'] ?? null
            ]);
            exit();
        } 
        else {echo json_encode(['error' => 'Usuario no encontrado']); exit();}
    }
}