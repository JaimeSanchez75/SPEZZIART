<?php declare(strict_types=1);
require_once __DIR__ . '/../../../core/db.php';

class NotificacionesModel
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::conectar();
    }

    public function getNotificacionesAdmin($usuarioId)
    {
        $usuarioId = (int)$usuarioId;
        Logger::info('notificacionesModel.php', 'getNotificacionesAdmin', 'BD', "Query para user ID=$usuarioId");

        $sql = "SELECT n.ID_Notificacion,
                       n.ID_Usuario_Destino,
                       n.ID_Usuario_Origen,
                       n.Mensaje,
                       n.Tipo,
                       n.Leida,
                       n.EsAdmin,
                       n.Fecha,
                       uo.Nombre AS ApodoOrigen,
                       ud.Nombre AS ApodoDestino
                FROM Notificacion n
                INNER JOIN Usuario ud ON n.ID_Usuario_Destino = ud.ID_Usuario
                LEFT JOIN Usuario uo ON n.ID_Usuario_Origen = uo.ID_Usuario
                WHERE n.ID_Usuario_Destino = :usuarioId and n.EsAdmin = 1
                ORDER BY n.Fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        Logger::info('notificacionesModel.php', 'getNotificacionesAdmin', 'BD', "Filas devueltas: " . count($rows) . " para user ID=$usuarioId");
        return $rows;
    }

    public function contarNoLeidasAdmin($usuarioId): int
    {
        $usuarioId = (int)$usuarioId;
        $sql = "SELECT COUNT(*) AS total
                FROM Notificacion n
                WHERE n.ID_Usuario_Destino = :usuarioId AND n.Leida = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }

    public function marcarLeidasAdmin($usuarioId): bool
    {
        $usuarioId = (int)$usuarioId;
        $sql = "UPDATE Notificacion
                SET Leida = 1
                WHERE ID_Usuario_Destino = :usuarioId AND Leida = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function eliminarNotificacion(int $idNotificacion, int $usuarioId): bool
    {
        $sql = "DELETE FROM Notificacion
                WHERE ID_Notificacion = :id AND ID_Usuario_Destino = :usuarioId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $idNotificacion, PDO::PARAM_INT);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function limpiarNotificaciones(int $usuarioId): bool
    {
        $sql = "DELETE FROM Notificacion WHERE ID_Usuario_Destino = :usuarioId";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':usuarioId', $usuarioId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
