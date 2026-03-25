<?php
require_once __DIR__ . '/../model/FeedModel.php';
require_once __DIR__ . '/../view/FeedView.php';
require_once __DIR__ . '/../../../core/auth.php';

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
        $recetas = $this->model->getPostsFiltrados('', [], 5, 0, $userId);
        $etiquetas = $this->model->getEtiquetasDisponibles();
        $config = $userId ? $this->model->getUserConfig($userId) : null;

        $this->view->render($recetas, $etiquetas, $_GET['cat'] ?? null, $config);
    }

    public function filtrar()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) 
        {
            $etiquetas = $input['etiquetas'] ?? [];
            $busqueda = $input['busqueda'] ?? null;
        } 
        else 
        {
            $etiquetas = $_POST['etiquetas'] ?? [];
            $busqueda = $_POST['busqueda'] ?? null;
        }

        $limit = 5;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $userId = Auth::check() ? Auth::id() : null;

        $posts = $this->model->getPostsFiltrados($busqueda, $etiquetas, $limit, $offset, $userId);

        $html = '';
        foreach ($posts as $receta) {$html .= $this->view->renderRecipeCard($receta);}

        header('Content-Type: application/json');
        echo json_encode(
        [
            'html' => $html,
            'count' => count($posts)
        ]);
        exit;
    }
  
    public function toggleLike($id)
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['status' => 'error']);
            exit;
        }

        $userId = Auth::id();
        $resultado = $this->model->toggleLike($id, $userId);

        if ($resultado['accion'] === 'added') 
        {

            $dueno = $this->model->getCreadorReceta($id);

            if ($dueno != $userId) 
            {
                if (!$this->model->existeNotificacion($dueno, $userId, 'like', $id))
                {
                $this->model->crearNotificacion
                (
                    $dueno,
                    $userId,
                    "le ha gustado tu receta",
                    "like",
                    $id
                );
                }
            }
        }

        echo json_encode(
        [
            'status' => 'success',
            'newLikes' => $resultado['likes'],
            'action' => $resultado['accion']
        ]);
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
    }

    public function postearComentario()
    {
        if (!Auth::check()) 
        {
            http_response_code(401);
            echo json_encode(['status' => 'error']);
            exit;
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
                    if (!$this->model->existeNotificacion($dueno, $userId, 'comentario', $idReceta)) {
    

                    $this->model->crearNotificacion(
                        $dueno,
                        $userId,
                        "ha comentado: " . substr($texto, 0, 40),
                        "comentario",
                        $idReceta,
                        $idComentario 
                    );
                    }
                }
                echo json_encode(['status' => 'success']);
                exit;
            }
        }
        echo json_encode(['status' => 'error']);
    }
    public function obtenerNotificaciones()
    {
        $user = Auth::user();
        $model = new FeedModel();

        $data = $model->getNotificaciones($user['id']);
        $noLeidas = $model->contarNoLeidas($user['id']);

        echo json_encode([
            'notificaciones' => $data,
            'noLeidas' => $noLeidas
        ]);
    }

    public function leerNotificaciones()
    {
        $user = Auth::user();
        $model = new FeedModel();

        $model->marcarLeidas($user['id']);

        echo json_encode(['ok' => true]);
    }
    public function buscarIngredientes()
    {
        if (!Auth::check()) {
            http_response_code(401);
            exit;
        }

        $query = $_GET['q'] ?? '';
        $userId = Auth::id();

        $resultados = $this->model->buscarIngredientes($query, $userId);

        echo json_encode($resultados);
    }
}