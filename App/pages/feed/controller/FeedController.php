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

    /**
     * Página principal del feed
     */
    public function index()
    {
        $userId = Auth::check() ? Auth::id() : null;
        $recetas = $this->model->getPostsFiltrados('', [], 5, 0, $userId);
        $etiquetas = $this->model->getEtiquetasDisponibles();
        $config = $userId ? $this->model->getUserConfig($userId) : null;

        $this->view->render($recetas, $etiquetas, $_GET['cat'] ?? null, $config);
    }

    /**
     * Endpoint para filtrar (búsqueda, etiquetas) y scroll infinito
     */
    public function filtrar()
    {
        // Leer JSON si es application/json
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $etiquetas = $input['etiquetas'] ?? [];
            $busqueda = $input['busqueda'] ?? null;
        } else {
            $etiquetas = $_POST['etiquetas'] ?? [];
            $busqueda = $_POST['busqueda'] ?? null;
        }

        $limit = 5;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
        $userId = Auth::check() ? Auth::id() : null;

        $posts = $this->model->getPostsFiltrados($busqueda, $etiquetas, $limit, $offset, $userId);

        $html = '';
        foreach ($posts as $receta) {
            $html .= $this->view->renderRecipeCard($receta);
        }

        header('Content-Type: application/json');
        echo json_encode([
            'html' => $html,
            'count' => count($posts)
        ]);
        exit;
    }

    /**
     * Endpoint para dar/quitar like
     */
    public function toggleLike($id)
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Sesión requerida']);
            exit;
        }

        $resultado = $this->model->toggleLike($id, Auth::id());
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'newLikes' => $resultado['likes'],
            'action' => $resultado['accion']
        ]);
    }

    /**
     * Endpoint para obtener comentarios de una receta
     */
    public function obtenerComentarios($id)
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Sesión requerida']);
            exit;
        }

        $comentarios = $this->model->getComentarios($id);
        foreach ($comentarios as &$c) {
            $c['Fecha'] = date('d M, H:i', strtotime($c['Fecha']));
        }
        header('Content-Type: application/json');
        echo json_encode($comentarios);
    }

    /**
     * Endpoint para publicar un comentario
     */
    public function postearComentario()
    {
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Sesión requerida']);
            exit;
        }

        $idReceta = $_POST['id_receta'] ?? null;
        $texto = trim($_POST['comentario'] ?? '');

        if ($idReceta && !empty($texto)) {
            $ok = $this->model->agregarComentario($idReceta, Auth::id(), $texto);
            if ($ok) {
                echo json_encode(['status' => 'success']);
                exit;
            }
        }
        echo json_encode(['status' => 'error']);
    }
}