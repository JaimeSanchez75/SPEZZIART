<?php declare(strict_types=1); ?>
<?php
require_once __DIR__ . '/../../../core/db.php';
class RecetasModel 
{
    function obtenerRecetasBase() 
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT Receta.ID_Receta, Titulo, Descripcion, Tiempo, Receta.Imagen, Usuario.Nombre as NombreUsuario, GROUP_CONCAT(Etiqueta.Nombre SEPARATOR ', ') AS Etiquetas FROM Receta left join Usuario on Receta.ID_Creador = Usuario.ID_Usuario left join Etiqueta_Receta er on Receta.ID_Receta = er.ID_Receta left join Etiqueta on Etiqueta.ID_Etiqueta = er.ID_Etiqueta where Receta.ID_Creador = 1 and Receta.EsBase=1 group by Receta.ID_Receta;");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    function crearRecetaBase($datos)
    {
        $db = Conexion::conectar();
        $sql = "INSERT INTO Receta( ID_Creador, Titulo, Descripcion, Tiempo, Porciones, Pasos, EsBase, EsFit, FechaCreacion, Imagen)
        values(:id,:titulo,:descripcion,:tiempo,:porciones,:pasos,:esbase,:esfit,:fecha,:imagen)";
        $parametros =
        [
            ':id'=> 1,
            ':titulo'=>$datos['Titulo'],
            ':descripcion'=>$datos['Descripcion'],
            ':tiempo'=>$datos['Tiempo'],
            ':porciones'=>$datos['Porciones'],
            ':pasos'=>$datos['paso'],
            ':esbase'=>'1',
            ':esfit'=> $datos['esfit'],
            ':fecha'=>date('Y-m-d H:i:s'),
            ':imagen' => $datos['imagen'] ?? null
        ];
        $stmt = $db->prepare($sql);
        $stmt->execute($parametros);
        return $db->lastInsertId();
    }
    function obtenerRecetaBasePorId($id)
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT Receta.*, GROUP_CONCAT(Etiqueta.ID_Etiqueta SEPARATOR ',') AS EtiquetasIds FROM Receta LEFT JOIN Etiqueta_Receta er ON Receta.ID_Receta = er.ID_Receta LEFT JOIN Etiqueta ON Etiqueta.ID_Etiqueta = er.ID_Etiqueta WHERE Receta.ID_Receta = :id GROUP BY Receta.ID_Receta");
        $stmt->execute([':id' => $id]);
        $receta = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$receta) {return [];}
        $receta['Etiquetas'] = !empty($receta['EtiquetasIds']) ? explode(',', $receta['EtiquetasIds']) : [];
        $receta['paso'] = !empty($receta['Pasos']) ? json_decode($receta['Pasos'], true) : [];
        $stmt = $db->prepare("SELECT ri.ID_Ingrediente, ri.Cantidad FROM Receta_Ingrediente ri WHERE ri.ID_Receta = :id");
        $stmt->execute([':id' => $id]);
        $receta['ingredientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $receta;
    }
   
    function obtenerRecetaCompleta($id)
    {
        $db = Conexion::conectar();

        $stmt = $db->prepare("
            SELECT  Receta.*, Usuario.Nombre AS NombreCreador, Usuario.FotoPerfil AS FotoCreador
            FROM Receta
            LEFT JOIN Usuario ON Usuario.ID_Usuario = Receta.ID_Creador
            WHERE Receta.ID_Receta = :id
            LIMIT 1
        ");
        
        $stmt->execute([':id' => $id]);
        $receta = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$receta) {return [];}
        $receta['Pasos'] = !empty($receta['Pasos']) ? json_decode($receta['Pasos'], true) : [];
        if (!is_array($receta['Pasos'])) {$receta['Pasos'] = [];}
        $receta['Imagenes'] = !empty($receta['Imagen']) ? array_values(array_filter(array_map('trim', explode(',', (string)$receta['Imagen'])))) : [];
        $stmt = $db->prepare
            ("SELECT  e.ID_Etiqueta, e.Nombre
            FROM Etiqueta_Receta er
            INNER JOIN Etiqueta e ON e.ID_Etiqueta = er.ID_Etiqueta
            WHERE er.ID_Receta = :id
            ORDER BY e.Nombre ASC");
        $stmt->execute([':id' => $id]);
        $receta['etiquetas'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt = $db->prepare
            ("SELECT  i.ID_Ingrediente,
                    i.Nombre,
                    i.Calorias,
                    i.Proteina,
                    i.Carbohidratos,
                    i.Grasas,
                    i.Unidad_Base,
                    ri.Cantidad
            FROM Receta_Ingrediente ri
            INNER JOIN Ingrediente i ON i.ID_Ingrediente = ri.ID_Ingrediente
            WHERE ri.ID_Receta = :id
            ORDER BY i.Nombre ASC");
        $stmt->execute([':id' => $id]);
        $receta['ingredientes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $receta;
    }

    private function calcularNutricionReceta($idReceta){

        
    }
    function actualizarRecetaBase($id, $datos){
        $db = Conexion::conectar();
        $campos = 
        [
            'Titulo = :titulo',
            'Descripcion = :descripcion',
            'Tiempo = :tiempo',
            'Porciones = :porciones',
            'Pasos = :pasos',
            'EsFit = :esfit',
            'Imagen = :imagen',
        ];
        $parametros = 
        [
            ':titulo' => $datos['Titulo'],
            ':descripcion' => $datos['Descripcion'],
            ':tiempo' => $datos['Tiempo'],
            ':porciones' => $datos['Porciones'],
            ':pasos' => $datos['paso'],
            ':esfit' => $datos['esfit'],
            ':imagen' => $datos['imagen'] ?? null,
            ':id' => $id
        ];
        $sql = "UPDATE Receta SET " . implode(', ', $campos) . " WHERE ID_Receta = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute($parametros);
    }
    function eliminarEtiquetasDeReceta($idReceta)
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare('DELETE FROM Etiqueta_Receta WHERE ID_Receta = :id');
        return $stmt->execute([':id' => $idReceta]);
    }
    function eliminarIngredientesDeReceta($idReceta)
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare('DELETE FROM Receta_Ingrediente WHERE ID_Receta = :id');
        return $stmt->execute([':id' => $idReceta]);
    }
    function etiquetasEnReceta($idEtiqueta,$idReceta)
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("Insert into Etiqueta_Receta(ID_Etiqueta,ID_Receta) values(:idE,:idR);");
        return $stmt->execute
        ([
            ':idE'=> $idEtiqueta,
            ':idR'=> $idReceta
        ]);
    }
    function ingredientesEnReceta($idIngrediente,$idReceta,$cantidad)
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("Insert into Receta_Ingrediente(ID_Ingrediente,ID_Receta,Cantidad) values(:idI,:idR,:cantidad);");
        return $stmt->execute
        ([
            ':idI'=> $idIngrediente,
            ':idR'=> $idReceta,
            ':cantidad'=>$cantidad
        ]);
    }
    function eliminarReceta($id)
    {
        $db = Conexion::conectar();
        $stmt= $db->prepare('DELETE FROM Receta where ID_Receta=:id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
    }
}
