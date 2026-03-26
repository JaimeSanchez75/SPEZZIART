<?php
declare(strict_types=1);

require_once __DIR__ . '/../model/PerfilModel.php';
require_once __DIR__ . '/../view/PerfilView.php';

class PerfilController 
{
    private $model;
    private $view;

    public function __construct() 
    {
        $this->model = new PerfilModel();
        $this->view = new PerfilView();
    }

    public function index($idVer = null) 
    {
        $userLogueado = Auth::user();
        $idLogueado = $userLogueado ? (int)$userLogueado['id'] : null;

        // Si no se pasa ID en la URL, mostramos el del logueado
        $idDestino = ($idVer !== null) ? (int)$idVer : $idLogueado;

        if (!$idDestino) {
            header('Location: /App/pages/login');
            exit;
        }

        $usuario = $this->model->getDatosUsuario($idDestino);
        $numSeguidores = $this -> model ->getNumSeguidores($idDestino);
        
        if (!$usuario) {
            http_response_code(404);
            die("Usuario no encontrado");
        }

        // Logros: Solo si entro a MI perfil
        if ($idLogueado === $idDestino) {$this->model->verificarYEntregarLogros($idLogueado);}

        // Comprobar seguimiento
        $loSigue = false;
        if ($idLogueado && $idLogueado !== $idDestino) {$loSigue = $this->model->comprobarSeguimiento($idDestino, $idLogueado);}

        $recetas = $this->model->getRecetasUsuario($idDestino);
        $vitrina = $this->model->getVitrinaLogros($idDestino);
        $config = null; //Tomamos la configuración
        if (Auth::check()) {$config = $this->model->getUserConfig(Auth::id());}
        // Renderizamos la vista
        $this->view->render($usuario, $numSeguidores, $vitrina, $recetas, $idLogueado, $loSigue,$config);
    }

    public function guardarVitrina() 
    {
        header('Content-Type: application/json');
        
        $user = Auth::user();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            return;
        }

        $idLogueado = (int)$user['id'];
        $logrosIds = $_POST['logros'] ?? [];

        // Validación de logros (máximo 8)
        if (count($logrosIds) > 8) 
        {
            echo json_encode(['status' => 'error', 'message' => 'Máximo 8 logros permitidos']);
            return;
        }

        $res = $this->model->actualizarVitrina($idLogueado, $logrosIds);
        
        echo json_encode(['status' => $res ? 'success' : 'error']);
    }

    public function seguir($idDestino) 
    {
        header('Content-Type: application/json');

        $user = Auth::user();
        if (!$user) 
        {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            return;
        }

        $idLogueado = (int)$user['ID_Usuario'];
        $idDestino = (int)$idDestino;

        if ($idLogueado === $idDestino) 
        {
            echo json_encode(['status' => 'error', 'message' => 'No puedes seguirte a ti mismo']);
            return;
        }

        $accion = $this->model->toggleSeguir($idDestino, $idLogueado);
        
        echo json_encode(['status' => 'success', 'accion' => $accion]);
    }
}