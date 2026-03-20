<?php

require_once __DIR__ . '/AdministracionControllers.php';

class EtiquetasController extends AdministracionControllers {
    public function index()
    {
        $datos['etiquetas'] = $this->obtenerEtiquetas();
        
        $this->mostrarAdministracion("etiquetas/etiquetas.php", "Gestión de Etiquetas",$datos);
    }

    private function obtenerEtiquetas(){

        $objEtiqueta = $this->cargarModelo("etiquetasModel");
        return is_array($objEtiqueta->obtenerEtiquetas()) ? $objEtiqueta->obtenerEtiquetas(): [];
    }

    public function crearEtiqueta(){
        $nombreEtiqueta = $_POST['nombre'] ?? null;

        if (!$nombreEtiqueta) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos de la etiqueta no proporcionados']);
            return;
        }

        $objEtiqueta = $this->cargarModelo("etiquetasModel");
        $objEtiqueta->crearEtiqueta($nombreEtiqueta);

        header('Location: /App/pages/administracion/etiquetas?mensaje=true');
    }

    function editarEtiqueta(){
        $idEtiqueta= intval($_POST['etiqueta_id']) ?? null;
        $nombreEtiqueta = $_POST['nombre'] ?? null;

        if (!$nombreEtiqueta || !$idEtiqueta ) {
            http_response_code(400);
            echo json_encode(['error' => 'Datos de la etiqueta no proporcionados']);
            return;
        }

        $objEtiqueta = $this->cargarModelo("etiquetasModel");
        $objEtiqueta->editarEtiqueta($nombreEtiqueta,$idEtiqueta);

        header('Location: /App/pages/administracion/etiquetas?mensaje=true');
    }

    function eliminarEtiqueta($id){

        $objEtiqueta = $this->cargarModelo("etiquetasModel");
        $objEtiqueta->eliminarEtiqueta($id);

        header('Location: /App/pages/administracion/etiquetas?mensaje=true');

    }
}