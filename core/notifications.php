<?php
declare(strict_types=1);
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/logger.php';
class NotificacionService
{
    public static function crear($destino, $origen, $mensaje, $tipo) // Tipo: 0=general, 1=receta, 2=logro, 3=reportes, 4=admin 
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("INSERT INTO Notificacion (ID_Usuario_Destino, ID_Usuario_Origen, Mensaje, Tipo) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute([$destino, $origen, $mensaje, $tipo]);
        if ($result) {Logger::info('notifications.php', 'NotificacionService::crear', 'BD', "Notificación creada: destino=$destino, tipo=$tipo");
        } 
        else {Logger::error('notifications.php', 'NotificacionService::crear', 'BD', 500, "Error al crear notificación para destino=$destino");}
    }
    public static function crearParaAdmins($origen, $mensaje, $tipo): int // Devuelve el número de notificaciones creadas después de crear para todos los admins.
    {
        $db = Conexion::conectar();
        $stmtAdmins = $db->prepare("SELECT ID_Usuario FROM Usuario WHERE EsAdmin = 1");
        $stmtAdmins->execute();
        $admins = $stmtAdmins->fetchAll(PDO::FETCH_COLUMN);
        if (empty($admins)) 
        {
            Logger::warning('notifications.php', 'NotificacionService::crearParaAdmins', 'BD', 'No se encontraron administradores');
            return 0;
        }
        $stmt = $db->prepare("INSERT INTO Notificacion (ID_Usuario_Destino, ID_Usuario_Origen, Mensaje, Tipo, EsAdmin) VALUES (?, ?, ?, ?, 1)");
        $total = 0;
        foreach ($admins as $idAdmin) 
        {
            if ($origen !== null && (int)$origen === (int)$idAdmin) continue;

            $stmt->execute([(int)$idAdmin, $origen, $mensaje, $tipo]);
            
            $total++;
        }
        Logger::info('notifications.php', 'NotificacionService::crearParaAdmins', 'BD', "Notificaciones enviadas a $total administradores");
        return $total;
    }
}