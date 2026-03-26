<?php 

require_once __DIR__ . '/../../../core/db.php';

class EtiquetasModel{

    function obtenerEtiquetas(){
        $db = Conexion::conectar();

        $stmt = $db->prepare("SELECT ID_Etiqueta, Nombre FROM Etiqueta");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function crearEtiqueta($nombreEtiqueta){

        $db =Conexion::conectar();

        $stmt= $db->prepare('INSERT INTO Etiqueta (Nombre) value (:etiqueta);');

        $stmt->bindParam(':etiqueta', $nombreEtiqueta, PDO::PARAM_STR);

        $stmt->execute();
    }

    function editarEtiqueta($nombreEtiqueta, $id){

        $db =Conexion::conectar();

        $stmt= $db->prepare('UPDATE Etiqueta set Nombre = :nombre where ID_Etiqueta= :id');

        $stmt->bindParam(':nombre', $nombreEtiqueta, PDO::PARAM_STR);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();
    }

    function eliminarEtiqueta($id){
        $db =Conexion::conectar();

        $stmt= $db->prepare('DELETE FROM Etiqueta where ID_Etiqueta=:id');
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        $stmt->execute();
    }
}