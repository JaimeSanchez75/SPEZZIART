<?php declare(strict_types=1);

require_once __DIR__ . '/AdministracionControllers.php';

class NotificacionesController extends AdministracionControllers
{

    public function obtener()
    {
        header('Content-Type: application/json');

        if (!Auth::isAdmin()) {

            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            exit;

        }

        $idUsuario = (int)($_SESSION['user']['id'] ?? 0);

        Logger::info('notificacionesController.php', 'obtener', 'AJAX', "Petición notificaciones. user_id=$idUsuario role=" . ($_SESSION['user']['role'] ?? 'null'));

        $model = $this->cargarModelo('notificacionesModel');
        $notificaciones = $model->getNotificacionesAdmin($idUsuario);
        $noLeidas = $model->contarNoLeidasAdmin($idUsuario);

        Logger::info('notificacionesController.php', 'obtener', 'AJAX', "Resultado: " . count($notificaciones) . " notificaciones, noLeidas=$noLeidas (user $idUsuario)");

        
        echo json_encode([
            'notificaciones' => $notificaciones,
            'noLeidas' => $noLeidas
        ]);

        exit;
    }

    public function marcarLeidas()
    {
        header('Content-Type: application/json');

        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            exit;
        }

        $idUsuario = (int)($_SESSION['user']['id'] ?? 0);
        $model = $this->cargarModelo('notificacionesModel');
        $model->marcarLeidasAdmin($idUsuario);
        
        echo json_encode(['success' => true]);

        exit;
    }

    public function eliminar()
    {
        header('Content-Type: application/json');

        $id = json_decode(file_get_contents("php://input"), true)['id'] ?? null;

        if (!Auth::isAdmin()) {

            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            exit;

        }

        $idUsuario = (int)($_SESSION['user']['id'] ?? 0);

        $model = $this->cargarModelo('notificacionesModel');

        $ok = $model->eliminarNotificacion((int)$id, $idUsuario);

    
        if ($ok) {

            echo json_encode(['success' => true]);

        } else {

            http_response_code(404);

            echo json_encode(['success' => false, 'message' => 'Notificación no encontrada']);

        }
        exit;
    }

    public function limpiar()
    {

        header('Content-Type: application/json');

        if (!Auth::isAdmin()) {

            http_response_code(403);
            echo json_encode(['error' => 'Acceso denegado']);
            exit;

        }

        $idUsuario = (int)($_SESSION['user']['id'] ?? 0);

        $model = $this->cargarModelo('notificacionesModel');

        $ok = $model->limpiarNotificaciones($idUsuario);

        echo json_encode(['success' => $ok]);
        exit;
    }
}
