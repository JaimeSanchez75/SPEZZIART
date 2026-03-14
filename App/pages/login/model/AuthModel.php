<?php
require_once __DIR__ . '/../../../core/db.php';
use Firebase\JWT\JWT;

class AuthModel extends Conexion
{
    public function loginUsuario($login, $contra, $jwtSecret, $exp)
    {
        $db = self::conectar();
        $sql = "SELECT * FROM Usuario WHERE Email = ? OR Username = ? LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute([$login, $login]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($contra, $usuario['Contrasena'])) 
        {
            $payload = 
            [
                'id'       => $usuario['ID_Usuario'],
                'email'    => $usuario['Email'],
                'username' => $usuario['Username'],
                'role'     => $usuario['EsAdmin'] ? 'admin' : 'user',
                'exp'      => time() + $exp
            ];

            $token = JWT::encode($payload, $jwtSecret, 'HS256');
            return ['usuario' => $usuario, 'token' => $token];
        }
        return false;
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
}