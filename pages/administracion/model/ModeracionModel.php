<?php declare(strict_types=1); ?>
<?php
require_once __DIR__ . '/../../../core/db.php';

class ModeracionModel
{

  
    public function getReportesPendientesRecetas()
    {
        $db = Conexion::conectar();

        
        $sql = "SELECT rp.ID_Reporte,rp.Motivo,rp.Fecha,rp.Estado,
                       r.ID_Receta,r.Titulo,r.Imagen,r.Descripcion,
                       u.Username  AS Reportador,
                       u.FotoPerfil AS FotoReportador,
                       COALESCE(ur.Username,  urec.Username)  AS UsuarioReportado,
                       COALESCE(ur.FotoPerfil, urec.FotoPerfil) AS FotoReportado
                FROM Reporte rp
                LEFT JOIN Receta r    ON rp.ID_Receta     = r.ID_Receta
                LEFT JOIN Usuario u   ON rp.ID_Reportador = u.ID_Usuario
                LEFT JOIN Usuario ur  ON rp.ID_UsuarioReportado = ur.ID_Usuario
                LEFT JOIN Usuario urec ON r.ID_Creador = urec.ID_Usuario
                WHERE rp.Estado = 'Pendiente' AND rp.ID_Receta IS NOT NULL
                ORDER BY rp.Fecha DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportesPendientesComentarios(){
        $db = Conexion::conectar();

        
        $sql = "SELECT rp.ID_Reporte,rp.Motivo,rp.Fecha,rp.Estado,
                       c.ID_Comentario, c.Descripcion AS Comentario,
                       r.ID_Receta,r.Titulo,r.Imagen,r.Descripcion AS DescripcionReceta,
                       u.Username  AS Reportador,
                       u.FotoPerfil AS FotoReportador,
                       COALESCE(ur.Username,  ucom.Username)  AS UsuarioReportado,
                       COALESCE(ur.FotoPerfil, ucom.FotoPerfil) AS FotoReportado
                FROM Reporte rp
                LEFT JOIN Comentario c ON rp.ID_Comentario = c.ID_Comentario
                LEFT JOIN Receta r    ON rp.ID_Receta     = r.ID_Receta
                LEFT JOIN Usuario u   ON rp.ID_Reportador = u.ID_Usuario
                LEFT JOIN Usuario ur  ON rp.ID_UsuarioReportado = ur.ID_Usuario
                LEFT JOIN Usuario ucom ON c.ID_Creador = ucom.ID_Usuario
                WHERE rp.Estado = 'Pendiente' AND rp.ID_Comentario IS NOT NULL
                ORDER BY rp.Fecha DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportesPendientesPerfiles(){
        $db = Conexion::conectar();

        $sql = "SELECT rp.ID_Reporte,rp.Motivo,rp.Fecha,rp.Estado,
                       u.Username  AS Reportador,
                       u.FotoPerfil AS FotoReportador,
                       ur.ID_Usuario AS IDReportado,
                       ur.Username  AS UsuarioReportado,
                       ur.Nombre    AS NombreReportado,
                       ur.FotoPerfil AS FotoReportado
                FROM Reporte rp
                LEFT JOIN Usuario u  ON rp.ID_Reportador = u.ID_Usuario
                LEFT JOIN Usuario ur ON rp.ID_UsuarioReportado = ur.ID_Usuario
                WHERE rp.Estado = 'Pendiente'
                  AND rp.ID_UsuarioReportado IS NOT NULL
                  AND rp.ID_Receta IS NULL
                  AND rp.ID_Comentario IS NULL
                ORDER BY rp.Fecha DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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

    public function marcarRevisado($idReporte)
    {
        $db = Conexion::conectar();

        $sql = "UPDATE Reporte
                SET Estado = 'Revisado'
                WHERE ID_Reporte = ?";

        $stmt = $db->prepare($sql);

        return $stmt->execute([$idReporte]);
    }

    public function rechazarReporte($idReporte)
    {
        $db = Conexion::conectar();

        $sql = "UPDATE Reporte
                SET Estado = 'Rechazado'
                WHERE ID_Reporte = ?";

        $stmt = $db->prepare($sql);

        return $stmt->execute([$idReporte]);
    }

   

    
    public function aprobarReporte($idReporte)
    {
        $db = Conexion::conectar();

        $sql = "UPDATE Reporte
                SET Estado = 'Revisado'
                WHERE ID_Reporte = ?";

        $stmt = $db->prepare($sql);

        return $stmt->execute([$idReporte]);
    }

    
    public function rechazarReportesPendientesDeUsuario(int $idUsuario, ?int $idReporteExcluir = null): int
    {
        $db = Conexion::conectar();

        $sql = "UPDATE Reporte rp
                LEFT JOIN Receta     r ON rp.ID_Receta     = r.ID_Receta
                LEFT JOIN Comentario c ON rp.ID_Comentario = c.ID_Comentario
                SET rp.Estado = 'Rechazado'
                WHERE rp.Estado = 'Pendiente'
                  AND (
                        rp.ID_UsuarioReportado = :uid
                     OR r.ID_Creador           = :uid2
                     OR c.ID_Creador           = :uid3
                  )";

        $params = [
            ':uid'  => $idUsuario,
            ':uid2' => $idUsuario,
            ':uid3' => $idUsuario,
        ];

        if ($idReporteExcluir !== null && $idReporteExcluir > 0) {
            $sql .= " AND rp.ID_Reporte <> :excluir";
            $params[':excluir'] = $idReporteExcluir;
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    

    public function getReportesHistorialRecetas()
    {
        $db = Conexion::conectar();

        $sql = "SELECT rp.ID_Reporte,rp.Motivo,rp.Fecha,rp.Estado,
                       r.ID_Receta,r.Titulo,r.Imagen,r.Descripcion,
                       u.Username  AS Reportador,
                       u.FotoPerfil AS FotoReportador,
                       COALESCE(ur.Username,  urec.Username)  AS UsuarioReportado,
                       COALESCE(ur.FotoPerfil, urec.FotoPerfil) AS FotoReportado
                FROM Reporte rp
                LEFT JOIN Receta r    ON rp.ID_Receta     = r.ID_Receta
                LEFT JOIN Usuario u   ON rp.ID_Reportador = u.ID_Usuario
                LEFT JOIN Usuario ur  ON rp.ID_UsuarioReportado = ur.ID_Usuario
                LEFT JOIN Usuario urec ON r.ID_Creador = urec.ID_Usuario
                WHERE rp.Estado IN ('Revisado','Rechazado')
                  AND rp.ID_Receta IS NOT NULL
                ORDER BY rp.Fecha DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportesHistorialComentarios()
    {
        $db = Conexion::conectar();

        $sql = "SELECT rp.ID_Reporte,rp.Motivo,rp.Fecha,rp.Estado,
                       c.ID_Comentario, c.Descripcion AS Comentario,
                       r.ID_Receta,r.Titulo,r.Imagen,r.Descripcion AS DescripcionReceta,
                       u.Username  AS Reportador,
                       u.FotoPerfil AS FotoReportador,
                       COALESCE(ur.Username,  ucom.Username)  AS UsuarioReportado,
                       COALESCE(ur.FotoPerfil, ucom.FotoPerfil) AS FotoReportado
                FROM Reporte rp
                LEFT JOIN Comentario c ON rp.ID_Comentario = c.ID_Comentario
                LEFT JOIN Receta r    ON rp.ID_Receta     = r.ID_Receta
                LEFT JOIN Usuario u   ON rp.ID_Reportador = u.ID_Usuario
                LEFT JOIN Usuario ur  ON rp.ID_UsuarioReportado = ur.ID_Usuario
                LEFT JOIN Usuario ucom ON c.ID_Creador = ucom.ID_Usuario
                WHERE rp.Estado IN ('Rechazado','Revisado')
                  AND rp.ID_Comentario IS NOT NULL
                ORDER BY rp.Fecha DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getReportesHistorialPerfiles()
    {
        $db = Conexion::conectar();

        $sql = "SELECT rp.ID_Reporte,rp.Motivo,rp.Fecha,rp.Estado,
                       u.Username  AS Reportador,
                       u.FotoPerfil AS FotoReportador,
                       ur.ID_Usuario AS IDReportado,
                       ur.Username  AS UsuarioReportado,
                       ur.Nombre    AS NombreReportado,
                       ur.FotoPerfil AS FotoReportado
                FROM Reporte rp
                LEFT JOIN Usuario u  ON rp.ID_Reportador = u.ID_Usuario
                LEFT JOIN Usuario ur ON rp.ID_UsuarioReportado = ur.ID_Usuario
                WHERE rp.Estado IN (
                'Rechazado','Revisado')
                  AND rp.ID_UsuarioReportado IS NOT NULL
                  AND rp.ID_Receta IS NULL
                  AND rp.ID_Comentario IS NULL
                ORDER BY rp.Fecha DESC";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    $stmt = $db->prepare("
        SELECT ID_Creador
        FROM Receta
        WHERE ID_Receta = ?
    ");
    $stmt->execute([$receta_id]);

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$usuario) return false;

    $stmt = $db->prepare("
        DELETE FROM Usuario
        WHERE ID_Usuario = ?
    ");

    return $stmt->execute([$usuario['ID_Creador']]);
}

    public function eliminarComentario($id)
    {
        $db = Conexion::conectar();

        $stmt = $db->prepare("DELETE FROM Comentario WHERE ID_Comentario = ?");

        return $stmt->execute([$id]);
    }

    public function eliminarUsuarioPorUsername($username)
    {
        $db = Conexion::conectar();

        $stmt = $db->prepare("DELETE FROM Usuario WHERE Username = ?");

        return $stmt->execute([$username]);
    }

    public function eliminarUsuarioPorComentario($comentario_id)
    {
        $db = Conexion::conectar();

        $stmt = $db->prepare("SELECT ID_Creador FROM Comentario WHERE ID_Comentario = ?");
        $stmt->execute([$comentario_id]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) return false;

        $stmt = $db->prepare("DELETE FROM Usuario WHERE ID_Usuario = ?");

        return $stmt->execute([$usuario['ID_Creador']]);
    }

    
    

    
    public function deshabilitarUsuarioPorId(int $idUsuario): bool
    {
        

        $db = Conexion::conectar();
        $stmt = $db->prepare("UPDATE Usuario SET Activa = 0 WHERE ID_Usuario = ?");

        return $stmt->execute([$idUsuario]);
    }

    
    public function deshabilitarUsuarioPorReceta(int $recetaId): bool
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Creador FROM Receta WHERE ID_Receta = ?");
        $stmt->execute([$recetaId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) return false;

        return $this->deshabilitarUsuarioPorId((int)$usuario['ID_Creador']);
    }

    
    public function deshabilitarUsuarioPorComentario(int $comentarioId): bool
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Creador FROM Comentario WHERE ID_Comentario = ?");
        $stmt->execute([$comentarioId]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$usuario) return false;

        return $this->deshabilitarUsuarioPorId((int)$usuario['ID_Creador']);
    }

    
    public function deshabilitarUsuarioPorUsername(string $username): bool
    {
        

        $db = Conexion::conectar();
        $stmt = $db->prepare("UPDATE Usuario SET Activa = 0 WHERE Username = ?");

        return $stmt->execute([$username]);
    }

    public function obtenerUsuarioPorReceta($receta_id)
    {
        $db = Conexion::conectar();

        $stmt = $db->prepare("
            SELECT u.ID_Usuario, u.Nombre, u.Username, u.Email
            FROM Receta r
            INNER JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
            WHERE r.ID_Receta = ?
        ");
        $stmt->execute([$receta_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerUsuarioPorComentario($comentario_id)
    {
        $db = Conexion::conectar();

        $stmt = $db->prepare("
            SELECT u.ID_Usuario, u.Nombre, u.Username, u.Email
            FROM Comentario c
            INNER JOIN Usuario u ON c.ID_Creador = u.ID_Usuario
            WHERE c.ID_Comentario = ?
        ");
        $stmt->execute([$comentario_id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerUsuarioPorUsername($username)
    {
        $db = Conexion::conectar();

        $stmt = $db->prepare("
            SELECT ID_Usuario, Nombre, Username, Email
            FROM Usuario
            WHERE Username = ?
        ");
        $stmt->execute([$username]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerTituloReceta($receta_id)
    {
        $db = Conexion::conectar();

        $stmt = $db->prepare("SELECT Titulo FROM Receta WHERE ID_Receta = ?");
        $stmt->execute([$receta_id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        return $r ? $r['Titulo'] : '';
    }

    public function obtenerComentario($comentario_id)
    {
        $db = Conexion::conectar();

        $stmt = $db->prepare("SELECT Descripcion FROM Comentario WHERE ID_Comentario = ?");
        $stmt->execute([$comentario_id]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        return $r ? $r['Descripcion'] : '';
    }
}