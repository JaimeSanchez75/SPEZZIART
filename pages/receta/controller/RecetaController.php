<?php
require_once __DIR__ . '/../model/RecetaModel.php';
require_once __DIR__ . '/../../../core/auth.php';

class RecetaController
{
    private $model;
    public function __construct(){$this->model = new RecetaModel();}
    public function obtenerRecetaApi($id)
    {
        header('Content-Type: application/json');
        try 
        {
            $id = (int)$id;
            if ($id <= 0) 
            {
                echo json_encode(['success' => false, 'error' => 'ID de receta no válido']);
                exit();
            }
            $userId = Auth::check() ? Auth::id() : null;
            $receta = $this->model->getRecetaCompleta($id, $userId);
            if (!$receta) 
            {
                echo json_encode(['success' => false, 'error' => 'Receta no encontrada']);
                exit();
            }
            echo json_encode(['success' => true, 'receta' => $receta]);
            exit();
        } 
        catch (\Throwable $e) 
        {
            error_log("Error en obtenerRecetaApi: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error interno del servidor']);
            exit();
        }
    }
}