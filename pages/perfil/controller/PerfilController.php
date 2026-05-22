<?php
declare(strict_types=1);
require_once __DIR__ . '/../model/PerfilModel.php';
require_once __DIR__ . '/../view/PerfilView.php';
require_once __DIR__ . '/../../feed/model/FeedModel.php';

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
        $idDestino = ($idVer !== null) ? (int)$idVer : $idLogueado;
        if (!$idDestino) 
        {
            header('Location: /pages/login');
            exit;
        }
        $usuario = $this->model->getDatosUsuario($idDestino);
        $numSeguidores = $this->model->getNumSeguidores($idDestino); 
        if (!$usuario) 
        {
            http_response_code(500);
            if (history.length > 1) {history.back();} 
            else {header('Location: /');}

        }
        if ($idLogueado === $idDestino) 
        {
            $this->model->verificarYEntregarLogros($idLogueado);
            $this->model->verificarYDesbloquearBanners($idLogueado); 
        }
        $loSigue = false;
        if ($idLogueado && $idLogueado !== $idDestino) {$loSigue = $this->model->comprobarSeguimiento($idDestino, $idLogueado);}
        $isOwnProfile = ($idLogueado == $idDestino);
        $esPublico = $this->model->esPerfilPublico($idDestino);
        $solicitudPendiente = false;
        if ($idLogueado && $idLogueado !== $idDestino && !$loSigue) {$solicitudPendiente = $this->model->tieneSolicitudPendiente($idDestino, $idLogueado);}
        $perfilVisible = $isOwnProfile || $esPublico || $loSigue;
        $recetas = $this->model->getRecetasUsuario($idDestino);
        $vitrina = $this->model->getVitrinaLogros($idDestino);
        $config = null;
        if (Auth::check()) {$config = $this->model->getUserConfig(Auth::id());}
        $bannersDesbloqueados = $this->model->obtenerBannersDesbloqueados($idDestino);
        $bannerActual = $this->model->obtenerBannerActual($idDestino);
        $todosLogros = [];
        $logrosExpuestosIds = [];
        if ($isOwnProfile) 
        {
            $todosLogros = $this->model->getTodosLogrosConEstado($idLogueado);
            $logrosExpuestosIds = array_column($vitrina, 'ID_Logro');
        }
        $this->view->render
        (
            $usuario, $numSeguidores, $vitrina, $recetas, $idLogueado,
            $isOwnProfile, $loSigue, $config, $bannersDesbloqueados, $bannerActual,
            $todosLogros, $logrosExpuestosIds, $perfilVisible, $solicitudPendiente
        );
    }
    public function obtenerDetalleLogro() 
    {
        header('Content-Type: application/json');
        $user = Auth::user();
        if (!$user) 
        {
            echo json_encode(['error' => 'No autorizado']);
            return;
        }
        $idLogro = (int)($_GET['id'] ?? 0);
        if ($idLogro <= 0) 
        {
            echo json_encode(['error' => 'ID inválido']);
            return;
        }
        $userId = (int)($_GET['user_id'] ?? 0);
        if ($userId <= 0) {$userId = (int)$user['id'];}
        $detalle = $this->model->getLogroDetalle($userId, $idLogro);
        echo json_encode($detalle);
        exit();
    }
    public function obtenerTodosBanners() 
    {
        header('Content-Type: application/json');
        $user = Auth::user();
        if (!$user) 
        {
            echo json_encode(['error' => 'No autorizado']);
            return;
        }
        $banners = $this->model->obtenerTodosBannersConEstado((int)$user['id']);
        echo json_encode(['banners' => $banners]);
        exit();
    }
    public function guardarVitrina() 
    {
        csrf_verify();
        header('Content-Type: application/json');
        $user = Auth::user();
        if (!$user) 
        {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            return;
        }
        $idLogueado = (int)$user['id'];
        $logrosIds = $_POST['logros'] ?? [];
        if (count($logrosIds) > 8) 
        {
            echo json_encode(['status' => 'error', 'message' => 'Máximo 8 logros permitidos']);
            return;
        }
        $res = $this->model->actualizarVitrina($idLogueado, $logrosIds);
        echo json_encode(['status' => $res ? 'success' : 'error']);
        exit();
    }
    public function seguir($idDestino) {
        csrf_verify();
        header('Content-Type: application/json');
        $user = Auth::user();
        if (!$user) {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            return;
        }
        $idLogueado = (int)$user['id'];
        $idDestino = (int)$idDestino;
        if ($idLogueado === $idDestino) {
            echo json_encode(['status' => 'error', 'message' => 'No puedes seguirte a ti mismo']);
            return;
        }

        // Verificar si ya se sigue (independientemente de la privacidad)
        $yaSigue = $this->model->comprobarSeguimiento($idDestino, $idLogueado);

        if ($yaSigue) {
            // Dejar de seguir (toggle)
            $accion = $this->model->toggleSeguir($idDestino, $idLogueado); // Devuelve 'unfollowed'
            // Limpiar cualquier solicitud pendiente/aceptada antigua
            $this->model->limpiarSolicitudSeguimiento($idDestino, $idLogueado);
            echo json_encode(['status' => 'success', 'accion' => $accion]);
            return;
        }
        $solicitudPendiente = $this->model->tieneSolicitudPendiente($idDestino, $idLogueado);

        if ($solicitudPendiente) 
        {
            $cancelada = $this->model->cancelarSolicitudSeguimiento($idDestino, $idLogueado);
            if ($cancelada) 
            {
                echo json_encode([
                    'status' => 'success',
                    'accion' => 'solicitud_cancelada'
                ]);
                return;
            }
            echo json_encode(['status' => 'error', 'message' => 'No se pudo cancelar la solicitud']);
            return;
        }
        // Si no se sigue, aplicar lógica según privacidad
        $esPublico = $this->model->esPerfilPublico($idDestino);
        if ($esPublico) 
        {
            $accion = $this->model->toggleSeguir($idDestino, $idLogueado);
            if ($accion === 'followed') 
            {
                $feedModel = new FeedModel();
                if (!$feedModel->existeNotificacion($idDestino, $idLogueado, 'seguidor')) 
                {
                    $mensaje = "{$user['nombre']} ha comenzado a seguirte";
                    $feedModel->crearNotificacion($idDestino, $idLogueado, $mensaje, 'seguidor', null, null);
                }
            }
            echo json_encode(['status' => 'success', 'accion' => $accion]); exit();
        } 
        else 
        {
            // Perfil privado: crear solicitud
            $resultado = $this->model->solicitarSeguir($idDestino, $idLogueado);
            if ($resultado['success']) 
            {
                try 
                {
                    $feedModel = new FeedModel();
                    $nombreUsuario = $user['nombre'] ?? $user['Nombre'] ?? 'Un usuario';
                    $mensaje = "{$nombreUsuario} ha solicitado seguirte";
                    $feedModel->crearNotificacion($idDestino, $idLogueado, $mensaje, 'solicitud_seguimiento', null, null);
                } 
                catch (Exception $e) {error_log("Error creando notificación de solicitud: " . $e->getMessage());}

                echo json_encode([
                    'status' => 'success',
                    'accion' => 'solicitado',
                    'message' => 'Solicitud enviada'
                ]);
                exit();
            } 
            else {
                echo json_encode(['status' => 'error', 'message' => $resultado['message'] ?? 'Error al enviar solicitud']); exit();
            }
        }
    }
    public function actualizarNombre() 
    {
        csrf_verify();
        header('Content-Type: application/json');
        $user = Auth::user();
        if (!$user) 
        {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            return;
        }
        $nuevoNombre = trim($_POST['nombre'] ?? '');
        if (empty($nuevoNombre)) 
        {
            echo json_encode(['status' => 'error', 'message' => 'El nombre no puede estar vacío']);
            return;
        }
        $resultado = $this->model->actualizarNombre((int)$user['id'], $nuevoNombre);
        if ($resultado['success']) 
        {
            $_SESSION['user']['nombre'] = $nuevoNombre;
            echo json_encode(['status' => 'success']); exit();
        } 
        else {echo json_encode(['status' => 'error', 'message' => $resultado['error']]); exit();}
    }
    public function subirFoto()
    {
        csrf_verify();

        header('Content-Type: application/json');
        $user = Auth::user();

        if (!$user) 
        {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            exit;
        }
        if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) 
        {

            echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen']);
            exit;
        }
        $archivo = $_FILES['foto'];

        $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowed)) 
        {
            echo json_encode(['status' => 'error', 'message' => 'Formato no permitido']);
            exit;
        }

        $nombreArchivo = 'user_' . $user['id'] . '_' . time() . '.' . $extension;
        $rutaDestino = $_SERVER['DOCUMENT_ROOT'] . '/uploads/perfiles/' . $nombreArchivo;

        if (!is_dir($_SERVER['DOCUMENT_ROOT'] . '/uploads/perfiles')) {mkdir($_SERVER['DOCUMENT_ROOT'] . '/uploads/perfiles', 0777, true);}
        if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) 
        {
            $rutaWeb = '/uploads/perfiles/' . $nombreArchivo;
            $this->model->actualizarFoto((int)$user['id'], $rutaWeb);
            $_SESSION['user']['avatar'] = $rutaWeb;
            echo json_encode(['status' => 'success', 'ruta' => $rutaWeb]);
            exit;
        } 
        else {echo json_encode(['status' => 'error', 'message' => 'Error al mover la imagen']); exit;}
    }
    public function cambiarBanner() 
    {
        csrf_verify();
        header('Content-Type: application/json');
        $user = Auth::user();
        if (!$user) 
        {
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            return;
        }
        $idBanner = (int)($_POST['banner_id'] ?? 0);
        if ($idBanner <= 0) 
        {
            echo json_encode(['status' => 'error', 'message' => 'Banner inválido']);
            return;
        }
        $ok = $this->model->cambiarBanner((int)$user['id'], $idBanner);
        if ($ok) {echo json_encode(['status' => 'success']); exit();} 
        else {echo json_encode(['status' => 'error', 'message' => 'No tienes este banner desbloqueado']); exit();}
    }
    public function aceptarSolicitud()
    {
        header('Content-Type: application/json');
        
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            return;
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
            return;
        }

        $token = $data['csrf_token'] ?? '';
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Token CSRF inválido']);
            return;
        }

        $idSolicitante = (int)($data['id_solicitante'] ?? 0);
        $idNotificacion = (int)($data['id_notificacion'] ?? 0);
        $idDestino = Auth::id();

        if ($idSolicitante <= 0 || $idNotificacion <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
            return;
        }

        $ok = $this->model->aceptarSolicitud($idDestino, $idSolicitante, $idNotificacion);
        if ($ok) {
            $feedModel = new FeedModel();
            $user = Auth::user();
            $mensaje = "{$user['nombre']} ha aceptado tu solicitud de seguimiento";
            $feedModel->crearNotificacion($idSolicitante, $idDestino, $mensaje, 'solicitud_aceptada', null, null);
            echo json_encode(['status' => 'success']); exit();
        } else {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'La solicitud ya fue procesada anteriormente']);
            exit();
        }
    }

    public function rechazarSolicitud()
    {
        header('Content-Type: application/json');
        
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'No autorizado']);
            return;
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        if (!$data) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
            return;
        }

        $token = $data['csrf_token'] ?? '';
        if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Token CSRF inválido']);
            return;
        }

        $idSolicitante = (int)($data['id_solicitante'] ?? 0);
        $idNotificacion = (int)($data['id_notificacion'] ?? 0);
        $idDestino = Auth::id();

        if ($idSolicitante <= 0 || $idNotificacion <= 0) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Datos inválidos']);
            return;
        }

        $ok = $this->model->rechazarSolicitud($idDestino, $idSolicitante, $idNotificacion);
        if ($ok) {
            echo json_encode(['status' => 'success']);
        } else {
            http_response_code(409);
            echo json_encode(['status' => 'error', 'message' => 'La solicitud ya fue procesada anteriormente']);
            exit();
        }
    }
}