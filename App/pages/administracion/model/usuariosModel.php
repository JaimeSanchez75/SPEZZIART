<?php
require_once __DIR__ . '/../../../core/db.php';

class UsuariosModel
{

    function obtenerUsuarios()
    {

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Usuario, Nombre,Username,Telefono,Email,EsAdmin,Seguidores,ModoOscuro, ModoFit,NotificacionOn, CuentaPublica FROM usuario");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function obtenerUsuarioPorId($idUsuario)
    {

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Usuario, Nombre,Username,Telefono,Email,EsAdmin,Seguidores,ModoOscuro, ModoFit,NotificacionOn, CuentaPublica FROM usuario WHERE ID_Usuario = :id");
        $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    function obtenerRoles()
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT DISTINCT EsAdmin FROM usuario");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    function crearUsuario($datos)
    {

        $db = Conexion::conectar();

        $stmt = $db->prepare("INSERT INTO usuario (Nombre, Username, Email, Contrasena, EsAdmin) VALUES (:nombre, :username, :email, :password, 1)");

        $stmt->bindParam(':nombre', $datos['apodo']);
        $stmt->bindParam(':username', $datos['username']);
        $stmt->bindParam(':email', $datos['email']);

        $hashedPassword = password_hash($datos['password'], PASSWORD_DEFAULT);
        $stmt->bindParam(':password', $hashedPassword);

        return $stmt->execute();
    }

    function usuarioEliminar($idUsuario)
    {
        $db = Conexion::conectar();

        $stmt = $db->prepare("DELETE FROM usuario WHERE ID_Usuario = :id");
        $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);

        return $stmt->execute();
    }

    public function guardarTokenRecuperacion($idUsuario, $token)
    {
        $db = Conexion::conectar();
        
        $expiracion = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $stmt = $db->prepare("UPDATE usuario SET ResetearToken = :token, ResetearExpira = :exp WHERE ID_Usuario = :id");

        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':exp', $expiracion);
        $stmt->bindParam(':id', $idUsuario);

        return $stmt->execute();
    }
}
