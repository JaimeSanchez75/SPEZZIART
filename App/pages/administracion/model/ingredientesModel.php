<?php 

require_once __DIR__ . '/../../../core/db.php';

class IngredientesModel {

    function obtenerIngredientes() {

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Ingrediente, Nombre, Verificada FROM ingrediente");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }

}