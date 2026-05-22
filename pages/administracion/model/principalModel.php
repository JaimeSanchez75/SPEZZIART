<?php declare(strict_types=1); ?>
<?php
    require_once __DIR__ . '/../../../core/db.php';

    class PrincipalModel
    {
        private $db;

        function contarUsuariosNuevosHoy()
        {
            $this->db = Conexion::conectar();

            $sql = "
                SELECT COUNT(*) AS total FROM Usuario WHERE DATE(fechaRegistro) = CURDATE();
                
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarUsuariosNuevosAyer()
        {
            $this->db = Conexion::conectar();

            $sql = "
                SELECT COUNT(*) AS total FROM Usuario WHERE DATE(fechaRegistro) = CURDATE()- INTERVAL 1 DAY
                
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarRecetasHoy()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Receta WHERE DATE(FechaCreacion) = CURDATE()");
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarRecetasAyer()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Receta WHERE DATE(FechaCreacion) = CURDATE() - INTERVAL 1 DAY");
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarRecetasPendientes()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Reporte WHERE Estado = 'Pendiente'");
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarReportesNuevosHoy()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Reporte WHERE DATE(Fecha) = CURDATE()");
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarReportesNuevosAyer()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Reporte WHERE DATE(Fecha) = CURDATE() - INTERVAL 1 DAY");
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarComentariosHoy()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Comentario WHERE DATE(Fecha) = CURDATE()");
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarComentariosAyer()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Comentario WHERE DATE(Fecha) = CURDATE() - INTERVAL 1 DAY");
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarTotalUsuarios()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Usuario");
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarTotalRecetas()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Receta");
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarTotalIngredientes()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Ingrediente");
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarTotalEtiquetas()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Etiqueta");
            $stmt->execute();

            return (int)$stmt->fetchColumn();
        }

        function contarRecetasPorDia()
        {
            $this->db = Conexion::conectar();

            $sql = "
                SELECT
                    DATE(d.dia) AS dia,
                    DAYNAME(d.dia) AS nombreDia,
                    COALESCE(r.total, 0) AS total
                FROM (
                            SELECT (CURDATE() - INTERVAL WEEKDAY(CURDATE()) DAY) AS dia
                    UNION ALL SELECT (CURDATE() - INTERVAL (WEEKDAY(CURDATE()) - 1) DAY)
                    UNION ALL SELECT (CURDATE() - INTERVAL (WEEKDAY(CURDATE()) - 2) DAY)
                    UNION ALL SELECT (CURDATE() - INTERVAL (WEEKDAY(CURDATE()) - 3) DAY)
                    UNION ALL SELECT (CURDATE() - INTERVAL (WEEKDAY(CURDATE()) - 4) DAY)
                    UNION ALL SELECT (CURDATE() - INTERVAL (WEEKDAY(CURDATE()) - 5) DAY)
                    UNION ALL SELECT (CURDATE() - INTERVAL (WEEKDAY(CURDATE()) - 6) DAY)
                ) AS d
                LEFT JOIN (
                    SELECT DATE(FechaCreacion) AS dia, COUNT(*) AS total
                    FROM Receta
                    GROUP BY DATE(FechaCreacion)
                ) r ON DATE(d.dia) = r.dia
                ORDER BY d.dia ASC
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        
        function ultimasAprobaciones()
        {
            $this->db = Conexion::conectar();

            $sql = "
                (SELECT
                    'receta' AS tipo,
                    rp.ID_Reporte,
                    rp.Fecha,
                    rp.Estado AS estado,
                    rp.Motivo,
                    r.Titulo AS contenido,
                    u.Nombre AS autor,
                    rep.Nombre AS reportador
                 FROM Reporte rp
                 INNER JOIN Receta r  ON rp.ID_Receta = r.ID_Receta
                 INNER JOIN Usuario u ON r.ID_Creador = u.ID_Usuario
                 LEFT  JOIN Usuario rep ON rp.ID_Reportador = rep.ID_Usuario
                 WHERE rp.ID_Receta IS NOT NULL)

                UNION ALL

                (SELECT
                    'comentario' AS tipo,
                    rp.ID_Reporte,
                    rp.Fecha,
                    rp.Estado AS estado,
                    rp.Motivo,
                    c.Descripcion AS contenido,
                    u.Nombre AS autor,
                    rep.Nombre AS reportador
                 FROM Reporte rp
                 INNER JOIN Comentario c ON rp.ID_Comentario = c.ID_Comentario
                 INNER JOIN Usuario u    ON c.ID_Creador     = u.ID_Usuario
                 LEFT  JOIN Usuario rep  ON rp.ID_Reportador = rep.ID_Usuario
                 WHERE rp.ID_Comentario IS NOT NULL)

                UNION ALL

                (SELECT
                    'perfil' AS tipo,
                    rp.ID_Reporte,
                    rp.Fecha,
                    rp.Estado AS estado,
                    rp.Motivo,
                    u.Nombre AS contenido,
                    u.Nombre AS autor,
                    rep.Username AS reportador
                 FROM Reporte rp
                 INNER JOIN Usuario u   ON rp.ID_UsuarioReportado = u.ID_Usuario
                 LEFT  JOIN Usuario rep ON rp.ID_Reportador = rep.ID_Usuario
                 WHERE rp.ID_UsuarioReportado IS NOT NULL
                   AND rp.ID_Receta IS NULL
                   AND rp.ID_Comentario IS NULL)

                ORDER BY Fecha DESC
                LIMIT 4
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    }
?>
