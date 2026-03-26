<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../model/ReporteModel.php';
require_once __DIR__ . '/../../../core/auth.php';

class ReporteController
{
    private $model;

    public function __construct()
    {
        $this->model = new ReporteModel();
    }

    public function reportarReceta()
    {
        error_log("POST: " . print_r($_POST, true));
        $this->verificarAutenticacion();

        $idReceta = (int)($_POST['id_receta'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $detalles = trim($_POST['detalles'] ?? '');

        if (!$idReceta || empty($motivo)) {
            $this->jsonError('Datos incompletos', 400);
        }

        $motivoCompleto = $motivo;
        if (!empty($detalles)) {
            $motivoCompleto .= " - Detalles: " . $detalles;
        }

        $idReportador = Auth::id();

        $dueno = $this->obtenerCreadorReceta($idReceta);
        if ($dueno == $idReportador) {
            $this->jsonError('No puedes reportar tu propia receta', 400);
        }

        $resultado = $this->model->crearReporte($idReportador, $idReceta, null, null, $motivoCompleto);

        if ($resultado) {
            $this->jsonSuccess('Reporte enviado correctamente');
        } else {
            $this->jsonError('Ya has reportado esta receta recientemente', 400);
        }
    }

    public function reportarComentario()
{
    // Log todo lo que llega
    error_log("=== INICIO reportarComentario ===");
    error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
    error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'no content-type'));
    error_log("POST data: " . print_r($_POST, true));
    
    // Si POST está vacío, intentar leer del raw input
    if (empty($_POST)) {
        $rawInput = file_get_contents('php://input');
        error_log("Raw input: " . $rawInput);
        parse_str($rawInput, $parsed);
        error_log("Parsed raw: " . print_r($parsed, true));
    }
    
    $this->verificarAutenticacion();

    $idComentario = (int)($_POST['id_comentario'] ?? 0);
    $motivo = trim($_POST['motivo'] ?? '');
    $detalles = trim($_POST['detalles'] ?? '');
    
    error_log("Variables extraídas: idComentario=$idComentario, motivo=$motivo, detalles=$detalles");

    if (!$idComentario || empty($motivo)) {
        error_log("VALIDACIÓN FALLIDA: idComentario=$idComentario, motivo='$motivo'");
        $this->jsonError('Datos incompletos. id_comentario: ' . $idComentario . ', motivo: ' . $motivo, 400);
    }

        $motivoCompleto = $motivo;
        if (!empty($detalles)) {
            $motivoCompleto .= " - Detalles: " . $detalles;
        }

        $idReportador = Auth::id();

        $creador = $this->obtenerCreadorComentario($idComentario);
        if ($creador == $idReportador) {
            $this->jsonError('No puedes reportar tu propio comentario', 400);
        }

        $resultado = $this->model->crearReporte($idReportador, null, $idComentario, null, $motivoCompleto);

        if ($resultado) {
            $this->jsonSuccess('Reporte enviado correctamente');
        } else {
            $this->jsonError('Ya has reportado este comentario recientemente', 400);
        }
    }

    public function reportarUsuario()
    {
        $this->verificarAutenticacion();

        $idUsuarioReportado = (int)($_POST['id_usuario'] ?? 0);
        $motivo = trim($_POST['motivo'] ?? '');
        $detalles = trim($_POST['detalles'] ?? '');

        if (!$idUsuarioReportado || empty($motivo)) {
            $this->jsonError('Datos incompletos', 400);
        }

        $motivoCompleto = $motivo;
        if (!empty($detalles)) {
            $motivoCompleto .= " - Detalles: " . $detalles;
        }

        $idReportador = Auth::id();

        if ($idUsuarioReportado == $idReportador) {
            $this->jsonError('No puedes reportarte a ti mismo', 400);
        }

        $resultado = $this->model->crearReporte($idReportador, null, null, $idUsuarioReportado, $motivoCompleto);

        if ($resultado) {
            $this->jsonSuccess('Reporte enviado correctamente');
        } else {
            $this->jsonError('Ya has reportado este usuario recientemente', 400);
        }
    }

    private function verificarAutenticacion()
    {
        if (!Auth::check()) 
        {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Sesión requerida']);
            exit;
        }
    }

    private function jsonSuccess($mensaje)
    {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => $mensaje]);
        exit;
    }

    private function jsonError($mensaje, $codigo = 400)
    {
        http_response_code($codigo);
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $mensaje]);
        exit;
    }

    private function obtenerCreadorReceta($idReceta)
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Creador FROM Receta WHERE ID_Receta = ?");
        $stmt->execute([$idReceta]);
        return $stmt->fetchColumn();
    }

    private function obtenerCreadorComentario($idComentario)
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Creador FROM Comentario WHERE ID_Comentario = ?");
        $stmt->execute([$idComentario]);
        return $stmt->fetchColumn();
    }
}