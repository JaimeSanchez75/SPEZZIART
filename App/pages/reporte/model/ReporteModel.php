<?php
require_once __DIR__ . '/../../../core/db.php';
require_once __DIR__ . '/../../../core/csrfcheck.php'; 

class ReporteModel
{
    private $db;

    public function __construct()
    {
        $this->db = Conexion::conectar();
    }

    /**
     * Inserta un reporte.
     */
    public function crearReporte($idReportador, $idReceta, $idComentario, $idUsuarioReportado, $motivo)
    {
        // Validar que al menos un tipo de contenido esté presente
        if (is_null($idReceta) && is_null($idComentario) && is_null($idUsuarioReportado)) {
            return false;
        }

        // Construir la condición de duplicado dinámicamente (evitando problemas con NULL)
        $condiciones = [];
        $params = [':idReportador' => $idReportador];

        if (!is_null($idReceta)) {
            $condiciones[] = "ID_Receta = :idReceta";
            $params[':idReceta'] = $idReceta;
        }
        if (!is_null($idComentario)) {
            $condiciones[] = "ID_Comentario = :idComentario";
            $params[':idComentario'] = $idComentario;
        }
        if (!is_null($idUsuarioReportado)) {
            $condiciones[] = "ID_UsuarioReportado = :idUsuarioReportado";
            $params[':idUsuarioReportado'] = $idUsuarioReportado;
        }

        // Verificar si ya reportó en los últimos 30 días
        $sql = "SELECT COUNT(*) FROM Reporte 
                WHERE ID_Reportador = :idReportador 
                AND (" . implode(' OR ', $condiciones) . ")
                AND Fecha > DATE_SUB(NOW(), INTERVAL 30 DAY)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        if ($stmt->fetchColumn() > 0) {
            return false; // Ya reportó recientemente
        }

        // Insertar el nuevo reporte
        $insert = $this->db->prepare("
            INSERT INTO Reporte (ID_Reportador, ID_Receta, ID_Comentario, ID_UsuarioReportado, Motivo, Estado)
            VALUES (:idReportador, :idReceta, :idComentario, :idUsuarioReportado, :motivo, 'Pendiente')
        ");

        $paramsInsert = [
            ':idReportador' => $idReportador,
            ':idReceta' => $idReceta,
            ':idComentario' => $idComentario,
            ':idUsuarioReportado' => $idUsuarioReportado,
            ':motivo' => $motivo
        ];

        return $insert->execute($paramsInsert);
    }
}