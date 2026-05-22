<?php declare(strict_types=1); ?>
<?php 

require_once __DIR__ . '/../../../core/db.php';

class IngredientesModel {

    function obtenerIngredientesUsu() {

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT * FROM Ingrediente where verificada = 0");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    function crearIngrediente($datos){

        $db = Conexion::conectar();
        $stmt = $db->prepare("INSERT INTO Ingrediente (Nombre,Grasas,Calorias,Proteina,Carbohidratos,Verificada,Unidad_Base) values (:nombre, :grasas,:calorias, :proteina, :carbohidratos, :verificada,:unidad);");
        
        $stmt->execute([
            ':nombre'=>$datos['nombre'],
            ':unidad'=>$datos['unidad'],
            ':grasas'=> $datos['grasas'],
            ':calorias'=>$datos['calorias'],
            ':carbohidratos'=> $datos['carbohidratos'],
            ':proteina'=>$datos['proteina'],
            ':verificada'=>1
        ]);
        
        

    }

    function editarIngrediente($datos, $id){

        $db = Conexion::conectar();
        $stmt = $db->prepare("UPDATE Ingrediente SET Nombre = :nombre,Grasas = :grasas,Calorias = :calorias,Proteina = :proteina,Carbohidratos = :carbohidratos,Unidad_Base=:unidad WHERE ID_Ingrediente = :id");
        $stmt->execute([
            ':id'=>$id,
            ':nombre'=>$datos['nombre'],
            ':unidad'=>$datos['unidad'],
            ':grasas'=> $datos['grasas'],
            ':calorias'=>$datos['calorias'],
            ':carbohidratos'=> $datos['carbohidratos'],
            ':proteina'=>$datos['proteina'],
            
        ]);
    }

    function existeIngrediente($nombre){

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Ingrediente FROM Ingrediente WHERE Nombre = :nombre AND ID_Creador IS NULL LIMIT 1");
        $stmt->execute([':nombre' => $nombre]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['ID_Ingrediente'] : false;

    }

    function existePorId($id){

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Ingrediente FROM Ingrediente WHERE ID_Ingrediente = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        return (bool)$stmt->fetch(PDO::FETCH_ASSOC);
    }

    function obtenerIngredientePorId($id){

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT * FROM Ingrediente WHERE ID_Ingrediente = :id LIMIT 1");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    function obtenerIngredientesBase(){

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT * FROM Ingrediente where ID_Creador is null and Verificada =1; ");

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

    function obtenerIngredientesBasePorNombre($nombre){

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT * FROM Ingrediente where ID_Creador is null and Verificada = 1 and Nombre = :nombre; ");

        $stmt->execute([':nombre' => $nombre]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

}