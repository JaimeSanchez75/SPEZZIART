<?php
require_once __DIR__ . '/../../../core/db.php';

class UsuariosModel {

    function obtenerUsuarios() {

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Usuario, Nombre,Username,Telefono,Email,EsAdmin,Seguidores,ModoOscuro, ModoFit,NotificacionOn, CuentaPublica FROM usuario");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    }
    
}