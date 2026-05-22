<?php declare(strict_types=1); ?>
<?php
require_once __DIR__ . '/../../../core/db.php';
class UsuariosModel
{
    function obtenerUsuarios()
    {
        

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Usuario, Nombre, Username, Telefono, Email, EsAdmin, Tema, ModoFit, NotificacionOn, CuentaPublica, FotoPerfil, Activa, fechaRegistro FROM Usuario");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    

    
    public function cambiarRolUsuario(int $idUsuario, int $esAdmin): bool
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("UPDATE Usuario SET EsAdmin = :esAdmin WHERE ID_Usuario = :id");
        $stmt->bindValue(':esAdmin', $esAdmin, PDO::PARAM_INT);
        $stmt->bindValue(':id',      $idUsuario, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function cambiarEstadoUsuario(int $idUsuario, int $activa): bool
    {


        $db = Conexion::conectar();
        $stmt = $db->prepare("UPDATE Usuario SET Activa = :activa WHERE ID_Usuario = :id");
        $stmt->bindValue(':activa', $activa, PDO::PARAM_INT);
        $stmt->bindValue(':id',     $idUsuario, PDO::PARAM_INT);
        return $stmt->execute();
    }
    function obtenerUsuarioPorId($idUsuario)
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Usuario, Nombre,Username,Telefono,Email,EsAdmin,Tema, ModoFit,NotificacionOn, CuentaPublica FROM Usuario WHERE ID_Usuario = :id");
        $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    function obtenerRoles()
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT DISTINCT EsAdmin FROM Usuario");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    function crearUsuario($datos)
    {
        $db = Conexion::conectar();
        $stmt = $db->prepare("INSERT INTO Usuario (Nombre, Username, Email, Contrasena, EsAdmin) VALUES (:nombre, :username, :email, :password, 1)");
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
        $stmt = $db->prepare("DELETE FROM Usuario WHERE ID_Usuario = :id");
        $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function guardarTokenRecuperacion($idUsuario, $token)
    {
        $db = Conexion::conectar();
        $expiracion = date("Y-m-d H:i:s", strtotime('+1 hour'));
        $stmt = $db->prepare("UPDATE Usuario SET ResetearToken = :token, ResetearExpira = :exp WHERE ID_Usuario = :id");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':exp', $expiracion);
        $stmt->bindParam(':id', $idUsuario);
        return $stmt->execute();
    }
    public function editarUsuario($datos)
    {
        $db = Conexion::conectar();
        $sql = "UPDATE Usuario SET Nombre = :nombre, Username = :username, Email = :email";
        if (!empty($datos['password'])) {$sql .= ", Contrasena = :password";}
        $sql .= " WHERE ID_Usuario = :id";
        $stmt=$db->prepare($sql);
        $stmt->bindParam(':nombre', $datos['apodo']);
        $stmt->bindParam(':username', $datos['username']);
        $stmt->bindParam(':email', $datos['email']);
        $stmt->bindParam(':id',  $datos['ID_Usuario']);
        if (!empty($datos['password'])) 
        {
            $hash = password_hash($datos['password'], PASSWORD_DEFAULT);
            $stmt->bindParam(':password', $hash); 
        }
        return $stmt->execute();
    }
}
