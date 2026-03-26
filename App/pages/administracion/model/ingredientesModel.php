<?php 

require_once __DIR__ . '/../../../core/db.php';

class IngredientesModel {

    function obtenerIngredientes() {

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT * FROM ingrediente");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    function crearIngrediente($datos){

        $db = Conexion::conectar();
        $stmt = $db->prepare("INSERT INTO ingrediente (Nombre,Grasas,Calorias,Proteina,Carbohidratos,Verificada) values (:nombre, :grasas,:calorias, :proteina, :carbohidratos, :verificada);");
        
        $stmt->execute([
            ':nombre'=>$datos['nombre'],
            ':grasas'=> $datos['grasas'],
            ':calorias'=>$datos['calorias'],
            ':carbohidratos'=> $datos['carbohidratos'],
            ':proteina'=>$datos['proteina'],
            ':verificada'=>1
        ]);
        

    }

    function editarIngrediente($datos, $id){

        $db = Conexion::conectar();
        $stmt = $db->prepare("UPDATE ingrediente SET Nombre = :nombre,Grasas = :grasas,Calorias = :calorias,Proteina = :proteina,Carbohidratos = :carbohidratos WHERE ID_Ingrediente = :id");
        $stmt->execute([
            ':id'=>$id,
            ':nombre'=>$datos['nombre'],
            ':grasas'=> $datos['grasas'],
            ':calorias'=>$datos['calorias'],
            ':carbohidratos'=> $datos['carbohidratos'],
            ':proteina'=>$datos['proteina'],
            
        ]);
    }

}