<?php

require_once __DIR__ . '/AdministracionControllers.php';

class IngredientesController extends AdministracionControllers
{

    public function index()
    {
        $datos['ingredientes'] = $this->obtenerIngredientes();
        
        $this->mostrarAdministracion("ingredientes/ingredientes.php", "Gestión de Ingredientes",$datos);
    }

    private function obtenerIngredientes()
    {
        $objIngrediente = $this->cargarModelo("ingredientesModel");
        return is_array($objIngrediente->obtenerIngredientes()) ? $objIngrediente->obtenerIngredientes(): [];
    }

}