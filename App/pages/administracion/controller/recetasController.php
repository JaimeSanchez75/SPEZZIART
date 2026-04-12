<?php

require_once __DIR__ . '/AdministracionControllers.php';

class RecetasController extends AdministracionControllers
{

    public function index()
    {
        $datos['recetas'] = $this->obtenerRecetasBase();
        $datos['etiquetas'] = $this->etiquetas();
        $datos['ingredientes']=$this->obtenerIngredientesBase();
        
        $this->mostrarAdministracion("recetas/recetas.php", "Gestión de Recetas",$datos);
    }

    // funcion para obtener los datos de las recetas
    // se obtienen el id,titulo,descripcion, tiempo, nombre de las etiquetas y el nombre del creador de la receta
    private function obtenerRecetasBase()
    {

        $objReceta = $this->cargarModelo("recetasModel");
        $recetas = is_array($objReceta->obtenerRecetasBase()) ? $objReceta->obtenerRecetasBase(): [];
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

    function etiquetas(){

        $objEtiqueta = $this->cargarModelo("etiquetasModel");

        return is_array($objEtiqueta->obtenerEtiquetas()) ? $objEtiqueta->obtenerEtiquetas(): [];
    }

    function obtenerIngredientesBase(){

        $objIngrediente = $this->cargarModelo("ingredientesModel");
        return is_array($objIngrediente->obtenerIngredientesBase()) ? $objIngrediente->obtenerIngredientesBase(): [];
    }

    function ingredientesJson(){
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->obtenerIngredientesBase(),JSON_UNESCAPED_UNICODE);
        exit;
    }


    function crearReceta(){

        $datos = $_POST['datos'] ?? [];
        $datosReceta = [
            'calorias' => is_numeric($datos['calorias'] ?? null) ? $datos['calorias'] : 0,
            'proteina' => is_numeric($datos['proteina'] ?? null) ? $datos['proteina'] : 0,
            'carbohidratos' => is_numeric($datos['carbohidratos'] ?? null) ? $datos['carbohidratos'] : 0,
            'grasas' => is_numeric($datos['grasas'] ?? null) ? $datos['grasas'] : 0,
            'esfit' => isset($datos['esfit']) && $datos['esfit'] === 'on' ? 1 : 0,
        ];

        foreach($datos as $clave =>$valor){
            if (empty($valor) && $valor !== '0') {
                continue;//retornar error
            }
            switch($clave){
                case 'Titulo':
                case 'Descripcion':
                case 'Tiempo':
                case 'Porciones':
                case 'proteina':
                case 'calorias':
                case 'carbohidratos':
                case 'grasas':
                    $datosReceta[$clave]=$valor;
                break;
            }
            
        }

        $datosReceta['paso']=json_encode($datos['paso'] ?? [],JSON_UNESCAPED_UNICODE);

        $objReceta = $this->cargarModelo("recetasModel");

        // creamos primero la receta y recogemos el id de ella

        $idReceta= $objReceta->crearRecetaBase($datosReceta);

        if(empty($idReceta)){
            return;//error que se manejara en el siguiente capitulo
        }

        if (!empty($datos['Etiquetas'])) {
            foreach($datos['Etiquetas'] as $idEtiqueta){
                $objReceta->etiquetasEnReceta($idEtiqueta,$idReceta);
            }
        }

        if (!empty($datos['Ingrediente'])) {
            foreach($datos['Ingrediente'] as $indice =>$idIngrediente){
                $objReceta->ingredientesEnReceta($idIngrediente,$idReceta,$datos['Cantidad'][$indice] ?? '');
            }
        }
        
        header('Location: /App/pages/administracion/recetas?mensaje=true');
    }

    function obtenerRecetaJson($id){
        $objReceta = $this->cargarModelo("recetasModel");
        $receta = $objReceta->obtenerRecetaBasePorId($id);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($receta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    function editarReceta(){
        $datos = $_POST['datos'] ?? [];
        $idReceta = $datos['id_receta'] ?? null;
        if (empty($idReceta)) {
            header('Location: /App/pages/administracion/recetas?error=missing_id');
            return;
        }

        $datosReceta = [
            'calorias' => is_numeric($datos['calorias'] ?? null) ? $datos['calorias'] : 0,
            'proteina' => is_numeric($datos['proteina'] ?? null) ? $datos['proteina'] : 0,
            'carbohidratos' => is_numeric($datos['carbohidratos'] ?? null) ? $datos['carbohidratos'] : 0,
            'grasas' => is_numeric($datos['grasas'] ?? null) ? $datos['grasas'] : 0,
            'esfit' => isset($datos['esfit']) && $datos['esfit'] === 'on' ? 1 : 0,
        ];

        foreach($datos as $clave =>$valor){
            if (empty($valor) && $valor !== '0') {
                continue;
            }
            switch($clave){
                case 'Titulo':
                case 'Descripcion':
                case 'Tiempo':
                case 'Porciones':
                case 'proteina':
                case 'calorias':
                case 'carbohidratos':
                case 'grasas':
                    $datosReceta[$clave]=$valor;
                break;
            }
        }

        $datosReceta['paso']=json_encode($datos['paso'] ?? [],JSON_UNESCAPED_UNICODE);

        $objReceta = $this->cargarModelo("recetasModel");
        $objReceta->actualizarRecetaBase($idReceta, $datosReceta);
        $objReceta->eliminarEtiquetasDeReceta($idReceta);
        $objReceta->eliminarIngredientesDeReceta($idReceta);

        if (!empty($datos['Etiquetas'])) {
            foreach($datos['Etiquetas'] as $idEtiqueta){
                $objReceta->etiquetasEnReceta($idEtiqueta,$idReceta);
            }
        }

        if (!empty($datos['Ingrediente'])) {
            foreach($datos['Ingrediente'] as $indice =>$idIngrediente){
                $objReceta->ingredientesEnReceta($idIngrediente,$idReceta,$datos['Cantidad'][$indice] ?? '');
            }
        }

        header('Location: /App/pages/administracion/recetas?mensaje=true');
    }

}
