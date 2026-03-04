<?php
require_once __DIR__ . '/../../../core/db.php';
require_once __DIR__ . '/../../../core/csrfcheck.php';

class AuthController
{
    private function jsonResponse($data, $code = 200) 
    {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode($data);
        exit;
    }
    public function login()
    {
        csrf_verify();
        $login  = trim($_POST['login'] ?? '');
        $contra = $_POST['contra'] ?? '';

        if (!$login || !$contra) {$this->jsonResponse(['error' => 'Campos obligatorios'], 400);}

        $result = login_usuario($login, $contra);

        if (!$result) {$this->jsonResponse(['error' => 'Credenciales incorrectas'], 401);}
        setcookie('token', $result['token'], 
        [
            'expires'  => time() + 3600,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        $this->jsonResponse(['success' => true]);
    }
    public function register()
    {
        csrf_verify(); 
        $nombre   = trim($_POST['nombre'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['contra'] ?? '';

        if (!$nombre || !$username || !$email || !$password) {$this->jsonResponse(['error' => 'Todos los campos son obligatorios'], 400);}
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {$this->jsonResponse(['error' => 'Email inválido'], 400);}

        try 
        {
            $ok = crear_usuario($nombre, $username, $email, $password);
            
            if ($ok) {$this->jsonResponse(['success' => true]);} 
            else {$this->jsonResponse(['error' => 'No se pudo crear el usuario'], 500);}
        } 
        catch (PDOException $e) 
        {

            if ($e->getCode() == 23000) 
            {
                $msg = $e->getMessage();
                if (str_contains($msg, 'Nombre')) $err = 'El Apodo ya está en uso.';
                elseif (str_contains($msg, 'Username')) $err = 'El Nombre de usuario ya existe.';
                elseif (str_contains($msg, 'Email')) $err = 'El Correo ya está registrado.';
                else $err = 'Los datos ya existen.';
                $this->jsonResponse(['error' => $err], 409);
            }
            $this->jsonResponse(['error' => 'Error en la base de datos'], 500);
        }
    }
    public function logout()
    {
        setcookie('token', '', 
        [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        header('Location: /App/pages/feed'); 
        exit;
    }
}