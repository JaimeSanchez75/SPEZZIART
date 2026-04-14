<?php

require_once __DIR__ . '/AdministracionControllers.php';

class IngredientesController extends AdministracionControllers
{

    public function index()
    {
        $datos['ingredientesBase'] = $this->obtenerIngredientesBase();
        $datos['ingredientesUsu'] = $this->obtenerIngredientesUsu();

        $this->mostrarAdministracion("ingredientes/ingredientes.php", "Gestión de Ingredientes", $datos);
    }

    private function obtenerIngredientesBase()
    {
        $objIngrediente = $this->cargarModelo("ingredientesModel");
        return is_array($objIngrediente->obtenerIngredientesBase()) ? $objIngrediente->obtenerIngredientesBase(): [];
    }

    private function obtenerIngredientesUsu()
    {
        $objIngrediente = $this->cargarModelo("ingredientesModel");
        return is_array($objIngrediente->obtenerIngredientesUsu()) ? $objIngrediente->obtenerIngredientesUsu(): [];
    }

    public function crearIngrediente(){
        $datosPost=$_POST['datos'];
        $datos = [
            'nombre' => $datosPost['nombre'] ?? null,
            'grasas' => is_numeric($datosPost['grasas']) ? $datosPost['grasas'] : 0,
            'calorias' => is_numeric($datosPost['calorias']) ? $datosPost['calorias'] : 0,
            'proteina' => is_numeric($datosPost['proteina']) ? $datosPost['proteina'] : 0,
            'carbohidratos' => is_numeric($datosPost['carbohidratos']) ? $datosPost['carbohidratos'] : 0
        ];

        $objIngrediente = $this->cargarModelo("ingredientesModel");

        $objIngrediente=$objIngrediente->crearIngrediente($datos);

        header('Location: /App/pages/administracion/ingredientes?mensaje=true');
    }

    function editarIngrediente(){
        $datosPost=$_POST['datos'];

        $datos = [
            'nombre' => $datosPost['nombre'] ?? null,
            'grasas' => is_numeric($datosPost['grasas']) ? $datosPost['grasas'] : 0,
            'calorias' => is_numeric($datosPost['calorias']) ? $datosPost['calorias'] : 0,
            'proteina' => is_numeric($datosPost['proteina']) ? $datosPost['proteina'] : 0,
            'carbohidratos' => is_numeric($datosPost['carbohidratos']) ? $datosPost['carbohidratos'] : 0
        ];

    
        $objIngrediente = $this->cargarModelo("ingredientesModel");
        $objIngrediente=$objIngrediente->editarIngrediente($datos);

        header('Location: /App/pages/administracion/ingredientes?mensaje=true');
    }

    

}