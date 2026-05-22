<?php

require_once __DIR__ . '/AdministracionControllers.php';

class ConfiguracionController extends AdministracionControllers
{
    public function modoVision()
    {
        header('Content-Type: application/json');

        $modo = json_decode(file_get_contents('php://input'), true)['tema'] ?? 'sistema';

        
        try{
            $configuracionModel = $this->cargarModelo('configuracionModel');
            $result=$configuracionModel->actualizarModoVision($modo, $_SESSION['user']['id']);
            if($result){
                $_SESSION['user']['tema'] = $modo;
            
            } 
            Flash::success('Cambiado al modo '. $modo. '.');
            echo json_encode([
                'ok' => true,
                'message' => 'Modo actualizado'
            ]);
            return ;
        }catch(\Throwable $e){
            echo json_encode([
                'ok' => false,
                'message' => 'Modo no actualizado'
            ]);
            Flash::success('Error al cambiar al modo '. $modo. '.');
            exit;
        }
       
        exit();
    }

    // futuro 
    // public function notificaciones()
    // {
    //     header('Content-Type: application/json');

    //     $input = json_decode(file_get_contents('php://input'), true);
    
    //     $estado = !empty($input['notificaciones']) ? 1 : 0;

    //     $_SESSION['user']['notificaciones'] = $estado;

    //     if (!isset($_SESSION['user']['id'])) {
    //         http_response_code(401);
    //         echo json_encode(['message' => 'Usuario no autenticado']);
    //         exit;
    //     }

    //     try{
    //         $configuracionModel = $this->cargarModelo('configuracionModel');
    //         $result = $configuracionModel->actualizarNotificaciones($estado, $_SESSION['user']['id']);

    //         if ($result) {
    //             echo json_encode(['success' => true, 'notificaciones' => $estado]);
                
    //             Flash::success('Cambiado el estado con exito');
    //             exit;

    //         } else {
    //             echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el estado de las notificaciones.']);
    //             Flash::error('No se pudo actualizar el estado de las notificaciones.');
    //             exit;
    //         }
    //     }catch(\Throwable $e){
            
    //         Flash::error('No se pudo actualizar el estado de las notificaciones.');
    //     }

    //     exit();
    // }
}
?>