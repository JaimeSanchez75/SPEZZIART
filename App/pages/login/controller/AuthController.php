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
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['contra'] ?? '';

        if (!$nombre || !$email || !$password) {$this->jsonResponse(['error' => 'Todos los campos son obligatorios'], 400);}

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {$this->jsonResponse(['error' => 'Email inválido'], 400);}

        $ok = crear_usuario($nombre, $email, $password);

        if (!$ok) {$this->jsonResponse(['error' => 'El email o nombre de usuario ya existe'], 409);}
        $this->jsonResponse(['success' => true]);
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
        if (session_status() === PHP_SESSION_NONE) 
        {
            session_start();
        }
        $_SESSION = []; 
        session_unset();
        session_destroy();
        if (ini_get("session.use_cookies")) 
        {
            $params = session_get_cookie_params();
            setcookie
            (session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        header('Location: /App/pages/feed'); 
        exit;
    }
}