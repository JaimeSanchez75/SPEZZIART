<?php
    require_once __DIR__ . '/../../../core/db.php';

    class PrincipalModel 
    {   
        private $db;

        function contarUsuarios()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM usuario");
            $stmt->execute();
            
            return $stmt->fetchColumn();
        }       

        function contarRecetasSemana()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Receta WHERE YEARWEEK(FechaCreacion, 1) = YEARWEEK(CURDATE(), 1)");
            $stmt->execute();

            return $stmt->fetchColumn();
        }

        function contarRecetasPendientes()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT COUNT(*) AS total FROM Reporte ;");
            $stmt->execute();

            return $stmt->fetchColumn();
        }

        function contarRecetasPorDia()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT DATE(FechaCreacion) AS dia, COUNT(*) AS total FROM Receta GROUP BY DATE(FechaCreacion) ORDER BY dia DESC LIMIT 7");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        // ultimas recetas aprobadas en reporte
        function ultimasRecetasAprobadas()
        {
            $this->db = Conexion::conectar();

            $stmt = $this->db->prepare("SELECT r.ID_Receta, r.Titulo, u.Nombre AS Autor, r.FechaCreacion FROM Receta r JOIN Usuario u ON r.ID_Creador = u.ID_Usuario WHERE r.ID_Receta IN (SELECT ID_Receta FROM Reporte) ORDER BY r.FechaCreacion DESC LIMIT 5");
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    }
?>