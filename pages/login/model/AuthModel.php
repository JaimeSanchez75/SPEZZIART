<?php
declare(strict_types=1);
require_once __DIR__ . '/../../../core/db.php';
class AuthModel extends Conexion
{
    public function getUsuarioPorLogin($login)
    {
        $db = self::conectar();
        $sql = "SELECT * FROM Usuario WHERE Email = ? OR Username = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$login, $login]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function obtenerUsuarioPorEmail($email)
    {
        $db = self::conectar();
        $stmt = $db->prepare("SELECT ID_Usuario, Nombre, Username, Email, EsAdmin, NotificacionOn FROM Usuario WHERE Email = :email");
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function crearUsuario($nombre, $username, $email, $password)
    {
        $db = self::conectar();
        $sql = "INSERT INTO Usuario (Nombre, Username, Email, Contrasena) VALUES (:n, :u, :e, :p)";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':n' => $nombre,
            ':u' => $username,
            ':e' => $email,
            ':p' => password_hash($password, PASSWORD_DEFAULT)
        ]);
    }
    public function otorgarLogroRegistro($idUsuario) 
    {
        $db = self::conectar();
        // INSERT IGNORE 
        $sql = "INSERT IGNORE INTO Logros_Usuario (ID_Usuario, ID_Logro, Fecha) VALUES (:usuario, 13, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([':usuario' => $idUsuario]);
        // rowCount() devuelve 1 si se insertó, 0 si fue ignorado
        return $stmt->rowCount() > 0;
    }
    public function validarTokenRecuperacion($token)
    {
        $db = self::conectar();
        $ahora = date("Y-m-d H:i:s");
        $stmt = $db->prepare("SELECT ID_Usuario, Email FROM Usuario WHERE ResetearToken = :token AND ResetearExpira > :ahora LIMIT 1");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':ahora', $ahora);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    public function editarContraseñaUsuario($contraseña, $email)
    {
        $db = self::conectar();
        $hash = password_hash($contraseña, PASSWORD_DEFAULT);
        $stmt = $db->prepare('UPDATE Usuario SET Contrasena = :contrasena WHERE Email = :email');
        $stmt->execute([':email' => $email, ':contrasena' => $hash]);
    }
    public function guardarTokenRecuperacion($idUsuario, $token)
    {
        $db = self::conectar();
        $expiracion = date("Y-m-d H:i:s", strtotime('+1 hour'));
        $stmt = $db->prepare("UPDATE Usuario SET ResetearToken = :token, ResetearExpira = :exp WHERE ID_Usuario = :id");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':exp', $expiracion);
        $stmt->bindParam(':id', $idUsuario);
        return $stmt->execute();
    }
}