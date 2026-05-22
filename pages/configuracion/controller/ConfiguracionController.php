<?php
require_once __DIR__ . '/../model/ConfiguracionModel.php';
require_once __DIR__ . '/../view/ConfiguracionView.php';
require_once __DIR__ . '/../../../core/auth.php';

class ConfiguracionController {
    private $model;
    private $view;

    public function __construct() {
        $this->model = new ConfiguracionModel();
        $this->view = new ConfiguracionView();
    }

    public function index() {
        if (!Auth::check()) {
            header('Location: /pages/login');
            exit;
        }
        $userId = Auth::id();
        $config = $this->model->obtenerConfiguracion($userId);
        $this->view->render($config);
    }

    public function guardar() {
        csrf_verify();
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['error' => 'No autenticado']);
            exit;
        }
        $userId = Auth::id();
        $tema = $_POST['tema'] ?? 'sistema';
        $modoFit = isset($_POST['modo_fit']) ? 1 : 0;
        $notificaciones = isset($_POST['notificaciones']) ? 1 : 0;
        $cuentaPublica = isset($_POST['cuenta_publica']) ? 1 : 0;

        $resultado = $this->model->actualizarConfiguracion($userId, $tema, $modoFit, $notificaciones, $cuentaPublica);
        if ($resultado) {
            // Actualizar la sesión con el nuevo tema (para que se aplique inmediatamente)
            $_SESSION['user']['tema'] = $tema;
            $_SESSION['user']['ModoFit'] = $modoFit;
            echo json_encode(['success' => true, 'message' => 'Configuración guardada']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar']);
        }
        exit;
    }
}