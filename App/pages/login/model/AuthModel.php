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

    function obtenerUsuarioPorEmail($email)
    {

        $db = Conexion::conectar();
        $stmt = $db->prepare("SELECT ID_Usuario, Nombre,Username,Telefono,Email,EsAdmin,ModoOscuro, ModoFit,NotificacionOn, CuentaPublica FROM usuario WHERE Email = :email");
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

    public function validarTokenRecuperacion($token) {
        $db = Conexion::conectar();
        $ahora = date("Y-m-d H:i:s");

        // Buscamos un usuario que tenga ese token y que la expiración sea mayor a 'ahora'
        $stmt = $db->prepare("SELECT ID_Usuario,Email FROM usuario 
                            WHERE ResetearToken = :token 
                            AND ResetearExpira  > :ahora 
                            LIMIT 1");
        $stmt->bindParam(':token', $token);
        $stmt->bindParam(':ahora', $ahora);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function editarContraseñaUsuario($contraseña,$email){

        $db = Conexion::conectar();
        $hashContrasena= password_hash($contraseña,PASSWORD_DEFAULT);

        $stmt =$db->prepare('UPDATE Usuario SET Contrasena = :contrasena where Email=:email;');
        $stmt->execute([
            ':email'=>$email,
            ':contrasena'=>$hashContrasena
        ]);

        
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
}