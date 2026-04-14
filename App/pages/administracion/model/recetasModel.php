<?php

require_once __DIR__ . '/../../../core/db.php';

class RecetasModel {

    

    function obtenerRecetasBase() {

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT Receta.ID_Receta, Titulo,Descripcion,Tiempo,usuario.Nombre as NombreUsuario , GROUP_CONCAT(Etiqueta.Nombre SEPARATOR ', ') AS Etiquetas FROM receta left join usuario on Receta.ID_Creador = usuario.ID_Usuario left join Etiqueta_Receta er on receta.ID_Receta = er.ID_Receta left join Etiqueta on etiqueta.ID_Etiqueta = er.ID_Etiqueta where Receta.ID_Creador = 1 and Receta.EsBase=1 group by Receta.ID_Receta;");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    function crearRecetaBase($datos){
        $db = Conexion::conectar();

        $sql = "INSERT INTO receta( ID_Creador, Titulo, Descripcion, Tiempo, Porciones, Pasos, EsBase, EsFit, FechaCreacion,Calorias,Proteina,Carbohidratos,Grasas) 
        values(:id,:titulo,:descripcion,:tiempo,:porciones,:pasos,:esbase,:esfit,:fecha,:calorias,:proteina,:carbohidratos,:grasas)";

        $parametros = [
            ':id'=> 1,
            ':titulo'=>$datos['Titulo'],
            ':descripcion'=>$datos['Descripcion'],
            ':tiempo'=>$datos['Tiempo'],
            ':porciones'=>$datos['Porciones'],
            ':pasos'=>$datos['paso'],
            ':esbase'=>'1',
            ':esfit'=> $datos['esfit'],
            ':fecha'=>date('Y-m-d H:i:s'),
            ':calorias' => $datos['calorias'],
            ':proteina' => $datos['proteina'],
            ':carbohidratos' => $datos['carbohidratos'],
            ':grasas' => $datos['grasas']
        ];

        $stmt = $db->prepare($sql);
        $stmt->execute($parametros);

        return $db->lastInsertId();

    }

    function obtenerRecetaBasePorId($id){
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT Receta.*, GROUP_CONCAT(Etiqueta.ID_Etiqueta SEPARATOR ',') AS EtiquetasIds FROM receta LEFT JOIN etiqueta_receta er ON Receta.ID_Receta = er.ID_Receta LEFT JOIN etiqueta ON etiqueta.ID_Etiqueta = er.ID_Etiqueta WHERE Receta.ID_Receta = :id GROUP BY Receta.ID_Receta");
        $stmt->execute([':id' => $id]);
        $receta = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$receta) {
            return [];
        }
        $receta['Etiquetas'] = !empty($receta['EtiquetasIds']) ? explode(',', $receta['EtiquetasIds']) : [];
        $receta['paso'] = !empty($receta['Pasos']) ? json_decode($receta['Pasos'], true) : [];

        $stmt = $db->prepare("SELECT ri.ID_Ingrediente, ri.Cantidad FROM receta_ingrediente ri WHERE ri.ID_Receta = :id");
        $stmt->execute([':id' => $id]);
        $receta['ingredientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $receta;
    }

    function actualizarRecetaBase($id, $datos){
        $db = Conexion::conectar();
        $campos = [
            'Titulo = :titulo',
            'Descripcion = :descripcion',
            'Tiempo = :tiempo',
            'Porciones = :porciones',
            'Pasos = :pasos',
            'EsFit = :esfit',
        ];
        $parametros = [
            ':titulo' => $datos['Titulo'],
            ':descripcion' => $datos['Descripcion'],
            ':tiempo' => $datos['Tiempo'],
            ':porciones' => $datos['Porciones'],
            ':pasos' => $datos['paso'],
            ':esfit' => $datos['esfit'],
            ':id' => $id
        ];

        $mapaNutricion = [
            'Calorias' => ':calorias',
            'Proteina' => ':proteina',
            'Carbohidratos' => ':carbohidratos',
            'Grasas' => ':grasas',
        ];

        foreach ($mapaNutricion as $columna => $placeholder) {
            if ($this->recetaTieneColumna($columna)) {
                $campos[] = $columna . ' = ' . $placeholder;
                $parametros[$placeholder] = $datos[strtolower($columna)] ?? 0;
            }
        }

        $sql = "UPDATE receta SET " . implode(', ', $campos) . " WHERE ID_Receta = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($parametros);
    }

    function eliminarEtiquetasDeReceta($idReceta){
        $db = Conexion::conectar();
        $stmt = $db->prepare('DELETE FROM etiqueta_receta WHERE ID_Receta = :id');
        return $stmt->execute([':id' => $idReceta]);
    }

    function eliminarIngredientesDeReceta($idReceta){
        $db = Conexion::conectar();
        $stmt = $db->prepare('DELETE FROM receta_ingrediente WHERE ID_Receta = :id');
        return $stmt->execute([':id' => $idReceta]);
    }

    function etiquetasEnReceta($idEtiqueta,$idReceta){
        $db = Conexion::conectar();
        $stmt = $db->prepare("Insert into etiqueta_receta(ID_Etiqueta,ID_Receta) values(:idE,:idR);");
        return $stmt->execute([
            ':idE'=> $idEtiqueta,
            ':idR'=> $idReceta
        ]);

    }

     function ingredientesEnReceta($idIngrediente,$idReceta,$cantidad){
        $db = Conexion::conectar();
        $stmt = $db->prepare("Insert into receta_ingrediente(ID_Ingrediente,ID_Receta,Cantidad) values(:idI,:idR,:cantidad);");
        return $stmt->execute([
            ':idI'=> $idIngrediente,
            ':idR'=> $idReceta,
            ':cantidad'=>$cantidad
        ]);

    }
    

    

    function eliminarReceta($id){
        $db = Conexion::conectar();
        
        $stmt= $db->prepare('DELETE FROM receta where ID_Receta=:id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

       
    }

}
