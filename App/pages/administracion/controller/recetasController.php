<?php

require_once __DIR__ . '/AdministracionControllers.php';

class RecetasController extends AdministracionControllers
{

    public function index()
    {
        $datos['recetas'] = $this->obtenerRecetas();
        
        $this->mostrarAdministracion("recetas/recetas.php", "Gestión de Recetas",$datos);
    }

    // funcion para obtener los datos de las recetas
    // se obtienen el id,titulo,descripcion, tiempo, nombre de las etiquetas y el nombre del creador de la receta
    private function obtenerRecetas()
    {

        $objReceta = $this->cargarModelo("recetasModel");
        $recetas = is_array($objReceta->obtenerRecetas()) ? $objReceta->obtenerRecetas(): [];
        // creamos un array con las etiquetas de cada receta para poder mostrarlas en la vista
        foreach($recetas as &$receta){
            $receta['Etiquetas'] = explode(", ", $receta['Etiquetas']);
        }
        
        return $recetas;
    }

    function eliminarReceta($id){

        $objEtiqueta = $this->cargarModelo("recetasModel");
        $objEtiqueta->eliminarReceta($id);

        header('Location: /App/pages/administracion/recetas?mensaje=true');

    }

    
}