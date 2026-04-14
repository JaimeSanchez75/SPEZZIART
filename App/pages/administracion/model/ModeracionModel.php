<?php
require_once __DIR__ . '/../../../core/db.php';

class ModeracionModel
{

    /* ============================
       OBTENER REPORTES PENDIENTES
    ============================ */
    public function getReportesPendientes()
    {
        $db = Conexion::conectar();

        $sql = "SELECT 
                    rp.ID_Reporte,
                    rp.Motivo,
                    rp.Fecha,
                    rp.Estado,

                    r.ID_Receta,
                    r.Titulo,
                    r.Imagen,
                    r.Descripcion,

                    u.Username AS Reportador,
                    ur.Username AS UsuarioReportado

                FROM Reporte rp

                LEFT JOIN Receta r 
                ON rp.ID_Receta = r.ID_Receta

                LEFT JOIN Usuario u 
                ON rp.ID_Reportador = u.ID_Usuario

                LEFT JOIN Usuario ur 
                ON rp.ID_UsuarioReportado = ur.ID_Usuario

                WHERE rp.Estado = 'Pendiente'
                ORDER BY rp.Fecha DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /* ============================
       CONTAR REPORTES
    ============================ */
    public function contarPendientes()
    {
        $db = Conexion::conectar();

        $sql = "SELECT COUNT(*) as total
                FROM Reporte
                WHERE Estado = 'Pendiente'";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }


    /* ============================
       MARCAR COMO REVISADO
    ============================ */
    public function marcarRevisado($idReporte)
    {
        $db = Conexion::conectar();

        $sql = "UPDATE Reporte
                SET Estado = 'Revisado'
                WHERE ID_Reporte = ?";

        $stmt = $db->prepare($sql);

        return $stmt->execute([$idReporte]);
    }


    /* ============================
       RECHAZAR REPORTE
    ============================ */
    public function rechazarReporte($idReporte)
    {
        $db = Conexion::conectar();

        $sql = "UPDATE Reporte
                SET Estado = 'Rechazado'
                WHERE ID_Reporte = ?";

        $stmt = $db->prepare($sql);

        return $stmt->execute([$idReporte]);
    }

    public function eliminarReceta($id)
{
    $db = Conexion::conectar();

    $stmt = $db->prepare(
        "DELETE FROM Receta WHERE ID_Receta = ?"
    );

    return $stmt->execute([$id]);
}
public function eliminarUsuarioPorReceta($receta_id)
{
    $db = Conexion::conectar();

    // obtener creador
    $stmt = $db->prepare("
        SELECT ID_Creador 
        FROM Receta 
        WHERE ID_Receta = ?
    ");
    $stmt->execute([$receta_id]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$usuario) return false;

    // eliminar usuario
    $stmt = $db->prepare("
        DELETE FROM Usuario 
        WHERE ID_Usuario = ?
    ");

    return $stmt->execute([$usuario['ID_Creador']]);
}
}