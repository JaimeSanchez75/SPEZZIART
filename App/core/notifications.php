<?php
class NotificacionService
{
    public static function crear($destino, $origen, $mensaje, $tipo)
    {
        $db = Conexion::conectar();

        $stmt = $db->prepare("
            INSERT INTO Notificacion 
            (ID_Usuario_Destino, ID_Usuario_Origen, Mensaje, Tipo)
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([$destino, $origen, $mensaje, $tipo]);
    }
}