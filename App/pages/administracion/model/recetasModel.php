<?php

require_once __DIR__ . '/../../../core/db.php';

class RecetasModel {

    function obtenerRecetas() {

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT Receta.ID_Receta, Titulo,Descripcion,Tiempo,usuario.Nombre as NombreUsuario , GROUP_CONCAT(Etiqueta.Nombre SEPARATOR ', ') AS Etiquetas FROM receta join usuario on Receta.ID_Creador = usuario.ID_Usuario left join Etiqueta_Receta er on receta.ID_Receta = er.ID_Receta left join Etiqueta on etiqueta.ID_Etiqueta = er.ID_Etiqueta group by Receta.ID_Receta;");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    function crearReceta(){

    }

    function editarReceta(){

    }

    function eliminarReceta($id){
        $db = Conexion::conectar();
        
        $stmt= $db->prepare('DELETE FROM receta where ID_Receta=:id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();

       
    }

}