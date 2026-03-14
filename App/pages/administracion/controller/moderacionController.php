<?php

require_once __DIR__ . '/AdministracionControllers.php';

class moderacionController extends AdministracionControllers
{

    public function index()
    {
        // $datos['recetas'] = $this->obtenerRecetas();
        $this->mostrarAdministracion("moderacion/moderacion.php", "Moderación de Contenido");
    }

    // private function obtenerRecetas()
    // {
    //     $objReceta = $this->cargarModelo("moderacionModel");
    //     return $objReceta->obtenerRecetasPendientes();
    // }
}