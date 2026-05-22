<?php

require_once __DIR__ . '/../../../core/db.php';

class ConfiguracionModel
{
    function actualizarModoVision($modo, $id)
    {
        $db = Conexion::conectar();

        $stmt = $db->prepare("UPDATE Usuario SET Tema = :modo where ID_Usuario = :id");
        $stmt->bindParam(':modo', $modo, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        }
        else {
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el modo de visión.']);
        }

        return $stmt;
        
    }

    // en un futuro
    // function actualizarNotificaciones($estado, $id)
    // {
    //     $db = Conexion::conectar();

    //     $stmt = $db->prepare("UPDATE Usuario SET NotificacionOn = :estado WHERE ID_Usuario = :id");
    //     $stmt->bindParam(':estado', $estado, PDO::PARAM_BOOL);
    //     $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    //     $stmt->execute();
        
       

    //     return $stmt;
        
    // }
}